<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Rab;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\DocxTemplateService;
use App\Services\LabelStickerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SekolahController extends Controller
{
    public function dashboard(): Response
    {
        $user = Auth::user();
        $sekolah = Sekolah::with(['dokumens', 'rab', 'inventarisLaptops'])->findOrFail($user->sekolah_id);

        return Inertia::render('Sekolah/Dashboard', [
            'sekolah' => $sekolah,
        ]);
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sekolah = Sekolah::findOrFail($user->sekolah_id);

        $validated = $request->validate([
            'alamat' => ['nullable', 'string', 'max:500'],
            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],
            'nomor_bangunan' => ['nullable', 'string', 'max:20'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'nama_kepsek' => ['nullable', 'string', 'max:255'],
            'nip_kepsek' => ['nullable', 'string', 'max:50'],
            'nama_bendahara' => ['nullable', 'string', 'max:255'],
            'nip_bendahara' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['nullable', 'string', 'max:255'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
            'atas_nama_rekening' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'email_sekolah' => ['nullable', 'email', 'max:255'],
            'nama_ketua_komite' => ['nullable', 'string', 'max:255'],
        ]);

        $sekolah->update($validated);

        return redirect()->back()->with('success', 'Profile sekolah berhasil diperbarui.');
    }

    public function updateRab(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sekolah = Sekolah::with('rab')->findOrFail($user->sekolah_id);

        // Lock RAB after approval — cannot re-submit
        if ($sekolah->rab && $sekolah->rab->status === 'Disetujui') {
            return back()->withErrors(['merek_tipe_laptop' => 'RAB sudah disetujui dan tidak dapat diubah lagi.']);
        }

        if ($request->has('items') && is_array($request->input('items')) && count($request->input('items')) > 0) {
            $validated = $request->validate([
                'items' => ['required', 'array', 'min:1', 'max:4'],
                'items.*.merek_tipe_laptop' => ['required', 'string', 'max:255'],
                'items.*.spesifikasi_ringkas' => ['nullable', 'string', 'max:1000'],
                'items.*.jumlah_unit' => ['required', 'integer', 'min:1'],
                'items.*.harga_satuan' => ['required', 'numeric', 'min:1000000', 'max:8625000'],
            ]);

            $totalUnits = 0;
            $totalHarga = 0;
            $processedItems = [];

            foreach ($validated['items'] as $item) {
                $itemSubtotal = (int) $item['jumlah_unit'] * (float) $item['harga_satuan'];
                $totalUnits += (int) $item['jumlah_unit'];
                $totalHarga += $itemSubtotal;
                $processedItems[] = [
                    'merek_tipe_laptop' => $item['merek_tipe_laptop'],
                    'spesifikasi_ringkas' => $item['spesifikasi_ringkas'] ?? '',
                    'jumlah_unit' => (int) $item['jumlah_unit'],
                    'harga_satuan' => (float) $item['harga_satuan'],
                    'total_harga' => $itemSubtotal,
                ];
            }

            if ($totalUnits < 8) {
                return back()->withErrors(['items' => 'Total pengadaan seluruh item minimal harus 8 unit laptop.']);
            }

            if ($totalHarga > 69364000) {
                return back()->withErrors(['items' => 'Total biaya RAB tidak boleh melebihi nilai bantuan Rp 69.364.000,00']);
            }

            $primaryMerek = implode(' & ', array_column($processedItems, 'merek_tipe_laptop'));
            $primarySpek = implode(' | ', array_filter(array_column($processedItems, 'spesifikasi_ringkas')));

            Rab::updateOrCreate(
                ['sekolah_id' => $sekolah->id],
                [
                    'merek_tipe_laptop' => $primaryMerek,
                    'spesifikasi_ringkas' => $primarySpek,
                    'items' => $processedItems,
                    'jumlah_unit' => $totalUnits,
                    'harga_satuan' => $processedItems[0]['harga_satuan'],
                    'total_harga' => $totalHarga,
                    'status' => 'Menunggu Verifikasi',
                    'catatan_revisi' => null,
                ]
            );
        } else {
            $validated = $request->validate([
                'merek_tipe_laptop' => ['required', 'string', 'max:255'],
                'spesifikasi_ringkas' => ['required', 'string', 'max:1000'],
                'jumlah_unit' => ['required', 'integer', 'min:8'],
                'harga_satuan' => ['required', 'numeric', 'min:1000000', 'max:8625000'],
            ]);

            $totalHarga = $validated['jumlah_unit'] * $validated['harga_satuan'];
            if ($totalHarga > 69364000) {
                return back()->withErrors(['harga_satuan' => 'Total biaya RAB tidak boleh melebihi nilai bantuan Rp 69.364.000,00']);
            }

            $items = [
                [
                    'merek_tipe_laptop' => $validated['merek_tipe_laptop'],
                    'spesifikasi_ringkas' => $validated['spesifikasi_ringkas'],
                    'jumlah_unit' => $validated['jumlah_unit'],
                    'harga_satuan' => $validated['harga_satuan'],
                    'total_harga' => $totalHarga,
                ],
            ];

            Rab::updateOrCreate(
                ['sekolah_id' => $sekolah->id],
                [
                    'merek_tipe_laptop' => $validated['merek_tipe_laptop'],
                    'spesifikasi_ringkas' => $validated['spesifikasi_ringkas'],
                    'items' => $items,
                    'jumlah_unit' => $validated['jumlah_unit'],
                    'harga_satuan' => $validated['harga_satuan'],
                    'total_harga' => $totalHarga,
                    'status' => 'Menunggu Verifikasi',
                    'catatan_revisi' => null,
                ]
            );
        }

        return redirect()->back()->with('success', 'RAB berhasil diperbarui dan diajukan untuk verifikasi.');
    }

    public function uploadDokumen(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sekolah = Sekolah::findOrFail($user->sekolah_id);

        // Special 6-slot uploader for dokumentasi_pemanfaatan
        if ($request->input('jenis_dokumen') === 'dokumentasi_pemanfaatan' && ($request->hasFile('slot_1') || $request->hasFile('slot_2') || $request->hasFile('slot_3') || $request->hasFile('slot_4') || $request->hasFile('slot_5') || $request->hasFile('slot_6'))) {
            $existingDoc = Dokumen::where('sekolah_id', $sekolah->id)
                ->where('jenis_dokumen', 'dokumentasi_pemanfaatan')
                ->first();

            $slots = $existingDoc?->meta['slots'] ?? [];

            for ($i = 1; $i <= 6; $i++) {
                if ($request->hasFile("slot_{$i}")) {
                    $file = $request->file("slot_{$i}");
                    $path = $file->store("dokumen/{$sekolah->npsn}/dokumentasi", 'public');
                    if (isset($slots[$i]) && Storage::disk('public')->exists($slots[$i])) {
                        Storage::disk('public')->delete($slots[$i]);
                    }
                    $slots[$i] = $path;
                }
            }

            Dokumen::updateOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'jenis_dokumen' => 'dokumentasi_pemanfaatan',
                ],
                [
                    'file_path' => $slots[1] ?? ($existingDoc->file_path ?? null),
                    'meta' => ['slots' => $slots],
                    'status' => 'Menunggu Verifikasi',
                    'catatan_revisi' => null,
                ]
            );

            $sekolah->updateStatusDokumen();

            return redirect()->back()->with('success', 'Foto dokumentasi berhasil disimpan.');
        }

        $validated = $request->validate([
            'jenis_dokumen' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:20480'],
        ]);

        $path = $request->file('file')->store("dokumen/{$sekolah->npsn}", 'public');

        // Delete old file if re-uploading
        $existingDoc = Dokumen::where('sekolah_id', $sekolah->id)
            ->where('jenis_dokumen', $validated['jenis_dokumen'])
            ->first();
        if ($existingDoc?->file_path && Storage::disk('public')->exists($existingDoc->file_path)) {
            Storage::disk('public')->delete($existingDoc->file_path);
        }

        Dokumen::updateOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'jenis_dokumen' => $validated['jenis_dokumen'],
            ],
            [
                'file_path' => $path,
                'status' => 'Menunggu Verifikasi',
                'catatan_revisi' => null,
            ]
        );

        $sekolah->updateStatusDokumen();

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function deleteDokumen(int $id): RedirectResponse
    {
        $user = Auth::user();
        $dokumen = Dokumen::findOrFail($id);

        // Access control: only owner school can delete
        if ($dokumen->sekolah_id !== $user->sekolah_id) {
            abort(403, 'Akses ditolak.');
        }

        // Business rule: Cannot delete if already approved
        if ($dokumen->status === 'Disetujui') {
            return back()->with('error', 'Dokumen yang telah disetujui tidak dapat dihapus.');
        }

        // Delete file from disk
        if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        // If dokumentasi_pemanfaatan with slots
        if ($dokumen->meta && isset($dokumen->meta['slots']) && is_array($dokumen->meta['slots'])) {
            foreach ($dokumen->meta['slots'] as $slotPath) {
                if ($slotPath && Storage::disk('public')->exists($slotPath)) {
                    Storage::disk('public')->delete($slotPath);
                }
            }
        }

        $dokumen->update([
            'file_path' => null,
            'meta' => null,
            'status' => 'Belum Diunggah',
            'catatan_revisi' => null,
        ]);

        $sekolah = Sekolah::findOrFail($user->sekolah_id);
        $sekolah->updateStatusDokumen();

        return redirect()->back()->with('success', 'Berkas dokumen berhasil dihapus.');
    }

    /**
     * Unduh template DOCX dokumen resmi dari SiBantek_doc, auto-filled dengan data sekolah.
     */
    public function downloadPdf(string $type, DocxTemplateService $docxService): \Symfony\Component\HttpFoundation\Response
    {
        $fileMap = [
            'pks' => 'PerjanjianKerjasama.docx',
            'pakta_integritas' => 'PaktaIntegritas.docx',
            'sptjm' => 'SPTJM.docx',
            'rab' => 'RAB.docx',
            'laporan_awal' => 'LaporanAwal.docx',
            'perbandingan_siplah' => 'PerbandinganProduk.docx',
            'survey_harga' => 'SurveyHarga.docx',
            'bast' => 'BAST.docx',
            'buku_inventaris' => 'IdentifikasiAlat.docx',
            'dokumentasi_pemanfaatan' => 'DokumentasiBarang.docx',
            'laporan_akhir' => 'LaporanAkhir.docx',
            'pengantar_lpj' => 'PengantarLaporan.docx',
            'lpj' => 'LaporanPenggunaanDana.docx',
        ];

        if (! isset($fileMap[$type])) {
            abort(404, 'Format dokumen tidak ditemukan.');
        }

        $filePath = base_path('SiBantek_doc/'.$fileMap[$type]);

        if (! file_exists($filePath)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        $user = Auth::user();
        $sekolah = Sekolah::with('rab')->findOrFail($user->sekolah_id);

        $sekolahData = $sekolah->only([
            'npsn',
            'nama_sekolah',
            'provinsi',
            'kabupaten',
            'alamat',
            'rt',
            'rw',
            'nomor_bangunan',
            'desa_kelurahan',
            'kecamatan',
            'kode_pos',
            'nama_kepsek',
            'nip_kepsek',
            'nama_bendahara',
            'nip_bendahara',
            'nama_bank',
            'nomor_rekening',
            'atas_nama_rekening',
            'no_telepon',
            'email_sekolah',
            'nama_ketua_komite',
        ]);
        $rabData = $sekolah->rab?->only(['merek_tipe_laptop', 'spesifikasi_ringkas', 'items', 'jumlah_unit', 'harga_satuan', 'total_harga']);

        $verifikator = User::where('role', 'verifikator')->first();
        if ($verifikator) {
            $sekolahData['nama_ppk'] = $verifikator->name;
            $sekolahData['nip_ppk'] = $verifikator->nip;
            $sekolahData['jabatan_ppk'] = $verifikator->jabatan ?? 'Pejabat Pembuat Komitmen (PPK)';
            if (! empty($verifikator->alamat)) {
                $sekolahData['alamat_ppk'] = $verifikator->alamat;
            }
        }

        $filledPath = $docxService->fill($filePath, $type, $sekolahData, $rabData);

        return response()->download($filledPath, $fileMap[$type], [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function downloadLabels(LabelStickerService $labelService): HttpResponse
    {
        $user = Auth::user();
        $sekolah = Sekolah::with('rab')->findOrFail($user->sekolah_id);
        $qty = $sekolah->rab->jumlah_unit ?? 8;

        $html = $labelService->generateStickersHtml($sekolah, $qty);

        return response($html)->header('Content-Type', 'text/html');
    }

    public function viewDokumenFile(int $id)
    {
        $dokumen = Dokumen::findOrFail($id);

        // Access control: sekolah users can only view their own documents
        $user = Auth::user();
        if ($user->role === 'sekolah' && $dokumen->sekolah_id !== $user->sekolah_id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        if (! $dokumen->file_path || ! Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($dokumen->file_path);
        $mimeType = Storage::disk('public')->mimeType($dokumen->file_path) ?? 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($dokumen->file_path).'"',
        ]);
    }
}
