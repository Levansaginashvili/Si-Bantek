<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();
        $sekolah = $user->sekolah ? $user->sekolah : null;

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'nip' => $user->nip,
                'jabatan' => $user->jabatan,
                'npsn' => $user->npsn,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'sekolah' => $sekolah,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $user->name = $validated['name'];
            $user->username = $validated['username'];
            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
        } elseif ($user->role === 'verifikator') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'nip' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
                'jabatan' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $user->name = $validated['name'];
            $user->nip = $validated['nip'];
            $user->jabatan = $validated['jabatan'] ?? null;
            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
        } elseif ($user->role === 'sekolah') {
            $validated = $request->validate([
                'nama_sekolah' => ['required', 'string', 'max:255'],
                'npsn' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
                'provinsi' => ['required', 'string', 'max:255'],
                'kabupaten' => ['required', 'string', 'max:255'],
                'alamat' => ['required', 'string', 'max:500'],
                'rt' => ['nullable', 'string', 'max:10'],
                'rw' => ['nullable', 'string', 'max:10'],
                'nomor_bangunan' => ['nullable', 'string', 'max:20'],
                'desa_kelurahan' => ['nullable', 'string', 'max:100'],
                'kecamatan' => ['nullable', 'string', 'max:100'],
                'kode_pos' => ['nullable', 'string', 'max:10'],
                'nama_kepsek' => ['required', 'string', 'max:255'],
                'nip_kepsek' => ['required', 'string', 'max:50'],
                'nama_bendahara' => ['nullable', 'string', 'max:255'],
                'nip_bendahara' => ['nullable', 'string', 'max:50'],
                'no_telepon' => ['nullable', 'string', 'max:20'],
                'email_sekolah' => ['nullable', 'email', 'max:255'],
                'nama_ketua_komite' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $user->name = $validated['nama_sekolah'];
            $user->npsn = $validated['npsn'];
            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            if ($user->sekolah_id) {
                $sekolah = Sekolah::findOrFail($user->sekolah_id);
                $sekolah->update([
                    'nama_sekolah' => $validated['nama_sekolah'],
                    'npsn' => $validated['npsn'],
                    'provinsi' => $validated['provinsi'],
                    'kabupaten' => $validated['kabupaten'],
                    'alamat' => $validated['alamat'],
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'nomor_bangunan' => $validated['nomor_bangunan'] ?? null,
                    'desa_kelurahan' => $validated['desa_kelurahan'] ?? null,
                    'kecamatan' => $validated['kecamatan'] ?? null,
                    'kode_pos' => $validated['kode_pos'] ?? null,
                    'nama_kepsek' => $validated['nama_kepsek'],
                    'nip_kepsek' => $validated['nip_kepsek'],
                    'nama_bendahara' => $validated['nama_bendahara'] ?? null,
                    'nip_bendahara' => $validated['nip_bendahara'] ?? null,
                    'no_telepon' => $validated['no_telepon'] ?? null,
                    'email_sekolah' => $validated['email_sekolah'] ?? null,
                    'nama_ketua_komite' => $validated['nama_ketua_komite'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
