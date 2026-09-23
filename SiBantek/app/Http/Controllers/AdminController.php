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
            ->select('id', 'name', 'username', 'nip', 'npsn', 'email', 'role', 'sekolah_id', 'created_at')
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
                'password' => Hash::make($validated['password']),
                'role' => 'verifikator',
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
                'password' => Hash::make($validated['password']),
                'role' => 'sekolah',
                'sekolah_id' => $sekolah->id,
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
                'merek_tipe_laptop' => 'Chromebook / Laptop Standar TIK 2026',
                'spesifikasi_ringkas' => 'Processor 4 Core / 8 Thread, Layar 14 inch, RAM 8GB, SSD 256GB, OS GUI Legal',
                'jumlah_unit' => 8,
                'harga_satuan' => 8625000.00,
                'total_harga' => 69000000.00,
                'status' => 'Draft',
            ]);
        }

        return redirect()->back()->with('success', 'Akun berhasil dibuat.');
    }
}
