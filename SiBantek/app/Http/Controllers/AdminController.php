<?php

namespace App\Http\Controllers;

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
            ->select('id', 'name', 'email', 'role', 'sekolah_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $sekolahs = Sekolah::select('id', 'nama_sekolah', 'npsn')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'sekolahs' => $sekolahs,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['verifikator', 'sekolah'])],
            'sekolah_id' => ['nullable', 'required_if:role,sekolah', 'exists:sekolahs,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'sekolah_id' => $validated['role'] === 'sekolah' ? $validated['sekolah_id'] : null,
        ]);

        return redirect()->back()->with('success', 'Akun berhasil dibuat.');
    }
}
