<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Rab;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VerifikatorController extends Controller
{
    public function dashboard(): Response
    {
        $sekolahs = Sekolah::whereHas('users', function ($q) {
            $q->where('status', 'aktif');
        })
            ->with(['dokumens', 'rab'])
            ->orderBy('nama_sekolah')
            ->get()
            ->map(function ($s) {
                $totalRab = $s->rab ? (float) $s->rab->total_harga : 0;
                $sisaDana = max(0, 69364000 - $totalRab);
                $buktiSetorDoc = $s->dokumens->firstWhere('jenis_dokumen', 'bukti_setor_sisa_dana');

                $rabApproved = $s->rab && $s->rab->status === 'Disetujui';
                $danaDisalurkan = $s->status_dana === 'Dana Sudah Disalurkan / Ditransfer';
                $siplahDone = $s->dokumens->contains(fn ($d) => $d->jenis_dokumen === 'invoice_siplah' && $d->status === 'Disetujui');
                $bastDone = $s->dokumens->contains(fn ($d) => $d->jenis_dokumen === 'bast' && $d->status === 'Disetujui');
                $inventarisDone = $s->dokumens->contains(fn ($d) => $d->jenis_dokumen === 'buku_inventaris' && $d->status === 'Disetujui');
                $pemanfaatanDone = $s->dokumens->contains(fn ($d) => $d->jenis_dokumen === 'dokumentasi_pemanfaatan' && $d->status === 'Disetujui');

                $currentStage = 1;
                if ($rabApproved) {
                    if (! $danaDisalurkan) {
                        $currentStage = 2;
                    } elseif (! $siplahDone) {
                        $currentStage = 3;
                    } elseif (! $bastDone) {
                        $currentStage = 4;
                    } elseif (! $inventarisDone) {
                        $currentStage = 5;
                    } elseif (! $pemanfaatanDone) {
                        $currentStage = 6;
                    } else {
                        $currentStage = 7;
                    }
                }

                $statusPengembalian = 'Uang Pas';
                if ($sisaDana > 0) {
                    if ($buktiSetorDoc && $buktiSetorDoc->status === 'Disetujui') {
                        $statusPengembalian = 'Sudah Dikembalikan';
                    } elseif ($buktiSetorDoc && $buktiSetorDoc->status === 'Menunggu Verifikasi') {
                        $statusPengembalian = 'Menunggu Verifikasi';
                    } elseif ($buktiSetorDoc && $buktiSetorDoc->status === 'Revisi') {
                        $statusPengembalian = 'Revisi';
                    } elseif ($currentStage >= 7) {
                        $statusPengembalian = 'Belum Dikembalikan';
                    } else {
                        $statusPengembalian = 'Belum Tahap LPJ';
                    }
                }

                $targetDokumen = ($sisaDana > 0 && $rabApproved) ? 15 : 14;

                return [
                    'id' => $s->id,
                    'npsn' => $s->npsn,
                    'nama_sekolah' => $s->nama_sekolah,
                    'provinsi' => $s->provinsi,
                    'kabupaten' => $s->kabupaten,
                    'status_dana' => $s->status_dana,
                    'status_dokumen' => $s->status_dokumen,
                    'current_stage' => $currentStage,
                    'sisa_dana' => $sisaDana,
                    'status_pengembalian' => $statusPengembalian,
                    'total_dokumen' => $targetDokumen,
                    'dokumen_disetujui' => $s->dokumens->where('status', 'Disetujui')->count(),
                    'dokumen_menunggu' => $s->dokumens->where('status', 'Menunggu Verifikasi')->count(),
                ];
            });

        return Inertia::render('Verifikator/Dashboard', [
            'sekolahs' => $sekolahs,
        ]);
    }

    public function showSekolah(int $id): Response
    {
        $sekolah = Sekolah::with(['dokumens', 'rab', 'inventarisLaptops'])->findOrFail($id);

        return Inertia::render('Verifikator/VerifikasiSekolah', [
            'sekolah' => $sekolah,
        ]);
    }

    public function verifyDokumen(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'dokumen_id' => ['required', 'exists:dokumens,id'],
            'status' => ['required', Rule::in(['Disetujui', 'Revisi'])],
            'catatan_revisi' => ['nullable', 'string', 'max:1000'],
        ]);

        $dokumen = Dokumen::where('sekolah_id', $id)->where('id', $validated['dokumen_id'])->firstOrFail();
        $dokumen->update([
            'status' => $validated['status'],
            'catatan_revisi' => $validated['status'] === 'Revisi' ? $validated['catatan_revisi'] : null,
            'verified_at' => now(),
        ]);

        $sekolah = Sekolah::findOrFail($id);
        $sekolah->updateStatusDokumen();

        return redirect()->back()->with('success', 'Status dokumen berhasil diperbarui.');
    }

    public function verifyRab(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Disetujui', 'Revisi'])],
            'catatan_revisi' => ['nullable', 'string', 'max:1000'],
        ]);

        $rab = Rab::where('sekolah_id', $id)->firstOrFail();
        $rab->update([
            'status' => $validated['status'],
            'catatan_revisi' => $validated['status'] === 'Revisi' ? $validated['catatan_revisi'] : null,
        ]);

        return redirect()->back()->with('success', 'Status RAB berhasil diperbarui.');
    }

    public function updateStatusDana(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status_dana' => ['required', Rule::in(['Belum Disalurkan', 'Dana Sudah Disalurkan / Ditransfer'])],
        ]);

        $sekolah = Sekolah::with(['dokumens', 'rab'])->findOrFail($id);

        if ($validated['status_dana'] === 'Dana Sudah Disalurkan / Ditransfer') {
            // Syarat 1: RAB harus Disetujui
            if (! $sekolah->rab || $sekolah->rab->status !== 'Disetujui') {
                return back()->with('error', 'Dana belum dapat disalurkan karena RAB Laptop sekolah ini belum Disetujui oleh Verifikator!');
            }

            // Syarat 2: 3 Berkas Administrasi Awal (PKS, Pakta Integritas, SPTJM) harus Disetujui
            $initialDocs = ['pks', 'pakta_integritas', 'sptjm'];
            $approvedDocKeys = $sekolah->dokumens->where('status', 'Disetujui')->pluck('jenis_dokumen')->toArray();

            foreach ($initialDocs as $docKey) {
                if (! in_array($docKey, $approvedDocKeys)) {
                    return back()->with('error', 'Dana belum dapat disalurkan karena 3 Berkas Administrasi Awal (PKS, Pakta Integritas, SPTJM) belum lengkap disetujui!');
                }
            }
        }

        $sekolah->update([
            'status_dana' => $validated['status_dana'],
        ]);

        return redirect()->back()->with('success', 'Status penyaluran dana berhasil diperbarui.');
    }

    public function uploadDokumen(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_dokumen' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $sekolah = Sekolah::findOrFail($id);
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

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah oleh Verifikator.');
    }
}
