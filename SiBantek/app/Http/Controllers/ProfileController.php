<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\User;
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
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
        } elseif ($user->role === 'verifikator') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'nip' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $user->name = $validated['name'];
            $user->nip = $validated['nip'];
            if (!empty($validated['password'])) {
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
                'nama_kepsek' => ['required', 'string', 'max:255'],
                'nip_kepsek' => ['required', 'string', 'max:50'],
                'nama_bendahara' => ['nullable', 'string', 'max:255'],
                'nip_bendahara' => ['nullable', 'string', 'max:50'],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $user->name = $validated['nama_sekolah'];
            $user->npsn = $validated['npsn'];
            if (!empty($validated['password'])) {
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
                    'nama_kepsek' => $validated['nama_kepsek'],
                    'nip_kepsek' => $validated['nip_kepsek'],
                    'nama_bendahara' => $validated['nama_bendahara'] ?? null,
                    'nip_bendahara' => $validated['nip_bendahara'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
