<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\InventarisLaptop;
use App\Models\Rab;
use App\Models\Sekolah;
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
            'alamat' => ['required', 'string', 'max:500'],
            'nama_kepsek' => ['required', 'string', 'max:255'],
            'nip_kepsek' => ['required', 'string', 'max:50'],
        ]);

        $sekolah->update($validated);

        return redirect()->back()->with('success', 'Profil sekolah berhasil diperbarui.');
    }

    public function updateRab(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sekolah = Sekolah::findOrFail($user->sekolah_id);

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

        Rab::updateOrCreate(
            ['sekolah_id' => $sekolah->id],
            [
                'merek_tipe_laptop' => $validated['merek_tipe_laptop'],
                'spesifikasi_ringkas' => $validated['spesifikasi_ringkas'],
                'jumlah_unit' => $validated['jumlah_unit'],
                'harga_satuan' => $validated['harga_satuan'],
                'total_harga' => $totalHarga,
                'status' => 'Menunggu Verifikasi',
                'catatan_revisi' => null,
            ]
        );

        return redirect()->back()->with('success', 'RAB berhasil diperbarui dan diajukan untuk verifikasi.');
    }

    public function uploadDokumen(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $sekolah = Sekolah::findOrFail($user->sekolah_id);

        $validated = $request->validate([
            'jenis_dokumen' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $path = $request->file('file')->store("dokumen/{$sekolah->npsn}", 'public');

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

    /**
     * Unduh atau lihat format PDF dokumen resmi.
     * Mengutamakan file template kustom yang diunggah oleh Verifikator di Dashboard (jika ada),
     * atau kembali ke file PDF default pada folder SiBantek_doc.
     */
    public function downloadPdf(string $type): \Symfony\Component\HttpFoundation\Response
    {
        // Map document type keys to static PDF files in SiBantek_doc/
        $fileMap = [
            'pks'                => 'PerjanjianKerjasama.pdf',   // Lampiran III
            'pakta_integritas'   => 'PaktaIntegritas.pdf',       // Lampiran V
            'sptjm'              => 'SPTJM.pdf',                 // Lampiran VI
            'rab'                => 'RAB.pdf',                    // Lampiran IV
            'laporan_awal'       => 'LaporanAwal.pdf',           // Lampiran X
            'perbandingan_siplah'=> 'PerbandinganProduk.pdf',    // Lampiran VIII
            'bast'               => 'BAST.pdf',                  // Lampiran VII
            'laporan_akhir'      => 'LaporanAkhir.pdf',          // Lampiran XI
            'pengantar_lpj'      => 'PengantarLaporan.pdf',      // Lampiran XII
            'lpj'                => 'LaporanPenggunaanDana.pdf', // Lampiran XIII
        ];

        if (!isset($fileMap[$type])) {
            abort(404, 'Format dokumen tidak ditemukan.');
        }

        $filePath = base_path('SiBantek_doc/' . $fileMap[$type]);

        if (!file_exists($filePath)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileMap[$type] . '"',
        ]);
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

        if (!$dokumen->file_path || !Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($dokumen->file_path);
        $mimeType = Storage::disk('public')->mimeType($dokumen->file_path) ?? 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($dokumen->file_path) . '"',
        ]);
    }
}
