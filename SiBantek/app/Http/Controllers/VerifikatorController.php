<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Rab;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VerifikatorController extends Controller
{
    public function dashboard(): Response
    {
        $sekolahs = Sekolah::with(['dokumens', 'rab'])
            ->orderBy('nama_sekolah')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'npsn' => $s->npsn,
                    'nama_sekolah' => $s->nama_sekolah,
                    'provinsi' => $s->provinsi,
                    'kabupaten' => $s->kabupaten,
                    'status_dana' => $s->status_dana,
                    'status_dokumen' => $s->status_dokumen,
                    'total_dokumen' => $s->dokumens->count(),
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

        $sekolah = Sekolah::findOrFail($id);
        $sekolah->update([
            'status_dana' => $validated['status_dana'],
        ]);

        return redirect()->back()->with('success', 'Status penyaluran dana berhasil diperbarui.');
    }
}
