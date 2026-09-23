<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'sekolah_id' => $user->sekolah_id,
                    'sekolah' => $user->sekolah ? [
                        'id' => $user->sekolah->id,
                        'npsn' => $user->sekolah->npsn,
                        'nama_sekolah' => $user->sekolah->nama_sekolah,
                        'status_dana' => $user->sekolah->status_dana,
                        'status_dokumen' => $user->sekolah->status_dokumen,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
