<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Rab;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
            ->select('id', 'name', 'username', 'nip', 'npsn', 'email', 'role', 'status', 'catatan_nonaktif', 'sekolah_id', 'created_at')
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
                'password' => ['required', 'string', 'min:8'],
            ]);

            User::create([
                'name' => $validated['name'],
                'nip' => $validated['nip'],
                'email' => $validated['nip'] . '@sibantek.local',
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
                'email' => $validated['npsn'] . '@sibantek.local',
                'password' => Hash::make($validated['password']),
                'role' => 'sekolah',
                'sekolah_id' => $sekolah->id,
                'status' => 'aktif',
            ]);

            // Initialize required documents & RAB for new school
            $types = [
                'pks', 'pakta_integritas', 'sptjm', 'laporan_awal', 'rab',
                'perbandingan_siplah', 'invoice_siplah', 'bast',
                'foto_fisik_laptop', 'buku_inventaris', 'dokumentasi_pemanfaatan', 'lpj'
            ];
            foreach ($types as $type) {
                Dokumen::create([
                    'sekolah_id' => $sekolah->id,
                    'jenis_dokumen' => $type,
                    'status' => 'Belum Diunggah',
                ]);
            }

            Rab::create([
                'sekolah_id' => $sekolah->id,
                'merek_tipe_laptop' => 'Chromebook / Laptop Standar TIK',
                'spesifikasi_ringkas' => 'Processor 4 Core / 8 Thread, Layar 13-14 inch, RAM 8GB, SSD 256GB, OS GUI Legal',
                'jumlah_unit' => 8,
                'harga_satuan' => 8625000.00,
                'total_harga' => 69000000.00,
                'status' => 'Draft',
            ]);
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
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $userData = [
                'name' => $validated['name'],
                'nip' => $validated['nip'],
            ];

            if (!empty($validated['password'])) {
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

            if (!empty($validated['password'])) {
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
}
