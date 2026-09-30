<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Rab;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        $totalSekolah = Sekolah::count();
        $sekolahLengkap = Sekolah::where('status_dokumen', 'Lengkap')->count();
        $danaDisalurkan = Sekolah::where('status_dana', 'Dana Sudah Disalurkan / Ditransfer')->count();

        $sekolahLengkapList = Sekolah::where('status_dokumen', 'Lengkap')
            ->select('id', 'npsn', 'nama_sekolah', 'provinsi', 'kabupaten', 'status_dana', 'updated_at')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_sekolah' => $totalSekolah,
                'sekolah_lengkap' => $sekolahLengkap,
                'dana_disalurkan' => $danaDisalurkan,
            ],
            'sekolah_lengkap_list' => $sekolahLengkapList,
        ]);
    }

    public function users(): Response
    {
        $users = User::with('sekolah:id,nama_sekolah,npsn')
            ->select('id', 'name', 'username', 'nip', 'jabatan', 'alamat', 'npsn', 'email', 'role', 'status', 'catatan_nonaktif', 'sekolah_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $role = $request->input('role');

        if ($role === 'verifikator') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'nip' => ['required', 'string', 'max:50', 'unique:users,nip'],
                'jabatan' => ['nullable', 'string', 'max:255'],
                'alamat' => ['nullable', 'string', 'max:500'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            User::create([
                'name' => $validated['name'],
                'nip' => $validated['nip'],
                'jabatan' => $validated['jabatan'] ?? 'Pejabat Pembuat Komitmen (PPK)',
                'alamat' => $validated['alamat'] ?? 'Kompleks Kemendikbudristek Gedung E Lt. 17, Jl. Jenderal Sudirman, Senayan, Jakarta Pusat',
                'email' => $validated['nip'].'@sibantek.local',
                'password' => Hash::make($validated['password']),
                'role' => 'verifikator',
                'status' => 'aktif',
            ]);
        } elseif ($role === 'sekolah') {
            $validated = $request->validate([
                'npsn' => ['required', 'string', 'max:50', 'unique:sekolahs,npsn', 'unique:users,npsn'],
                'nama_sekolah' => ['required', 'string', 'max:255'],
                'provinsi' => ['required', 'string', 'max:255'],
                'kabupaten' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            $sekolah = Sekolah::create([
                'npsn' => $validated['npsn'],
                'nama_sekolah' => $validated['nama_sekolah'],
                'provinsi' => $validated['provinsi'],
                'kabupaten' => $validated['kabupaten'],
                'status_dana' => 'Belum Disalurkan',
                'status_dokumen' => 'Belum Lengkap',
            ]);

            User::create([
                'name' => $validated['nama_sekolah'],
                'npsn' => $validated['npsn'],
                'email' => $validated['npsn'].'@sibantek.local',
                'password' => Hash::make($validated['password']),
                'role' => 'sekolah',
                'sekolah_id' => $sekolah->id,
                'status' => 'aktif',
            ]);

            // Initialize required documents for new school (RAB is created when school submits it)
            $types = [
                'pks', 'pakta_integritas', 'sptjm', 'rab', 'laporan_awal',
                'perbandingan_siplah', 'surat_pemesanan_siplah', 'invoice_siplah',
                'bast', 'buku_inventaris', 'dokumentasi_pemanfaatan',
                'laporan_akhir', 'pengantar_lpj', 'lpj',
            ];
            foreach ($types as $type) {
                Dokumen::create([
                    'sekolah_id' => $sekolah->id,
                    'jenis_dokumen' => $type,
                    'status' => 'Belum Diunggah',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Akun berhasil dibuat.');
    }

    public function updateUser(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'verifikator') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'nip' => ['required', 'string', 'max:50', Rule::unique('users', 'nip')->ignore($user->id)],
                'jabatan' => ['nullable', 'string', 'max:255'],
                'alamat' => ['nullable', 'string', 'max:500'],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $userData = [
                'name' => $validated['name'],
                'nip' => $validated['nip'],
                'jabatan' => $validated['jabatan'] ?? $user->jabatan,
                'alamat' => $validated['alamat'] ?? $user->alamat,
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);
        } elseif ($user->role === 'sekolah') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'npsn' => ['required', 'string', 'max:50', Rule::unique('users', 'npsn')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $userData = [
                'name' => $validated['name'],
                'npsn' => $validated['npsn'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            if ($user->sekolah_id) {
                Sekolah::where('id', $user->sekolah_id)->update([
                    'nama_sekolah' => $validated['name'],
                    'npsn' => $validated['npsn'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function toggleStatusUser(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:aktif,nonaktif'],
            'catatan_nonaktif' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'status' => $validated['status'],
            'catatan_nonaktif' => $validated['status'] === 'nonaktif' ? ($validated['catatan_nonaktif'] ?? null) : null,
        ]);

        $msg = $validated['status'] === 'nonaktif' ? 'Akun telah dinonaktifkan.' : 'Akun berhasil diaktifkan kembali.';

        return redirect()->back()->with('success', $msg);
    }

    public function destroyUser(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'Akun Administrator tidak dapat dihapus.');
        }

        if ($user->sekolah_id) {
            $sekolah = Sekolah::find($user->sekolah_id);
            if ($sekolah) {
                if (Storage::disk('public')->exists("dokumen/{$sekolah->npsn}")) {
                    Storage::disk('public')->deleteDirectory("dokumen/{$sekolah->npsn}");
                }
                $sekolah->delete();
            }
        }

        $user->delete();

        return redirect()->back()->with('success', 'Akun dan seluruh data terkait berhasil dihapus permanen.');
    }

    public function exportAccountsCsv(): BinaryFileResponse
    {
        $filePath = public_path('daftar_akun_173_sekolah_tik_2026.csv');
        if (! file_exists($filePath)) {
            abort(404, 'File daftar akun belum dibuat.');
        }

        return response()->download($filePath, 'Daftar_Akun_173_Sekolah_Bantek_TIK_2026.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportAccountsExcel(): BinaryFileResponse
    {
        $filePath = public_path('daftar_akun_173_sekolah_tik_2026.xls');
        if (! file_exists($filePath)) {
            abort(404, 'File daftar akun belum dibuat.');
        }

        return response()->download($filePath, 'Daftar_Akun_173_Sekolah_Bantek_TIK_2026.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }
}
