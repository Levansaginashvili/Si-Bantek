<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            return redirect("/{$role}");
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identity' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim($validated['identity']);

        // Find user by Username, NIP, NPSN, or Email
        $user = User::where('username', $identity)
            ->orWhere('nip', $identity)
            ->orWhere('npsn', $identity)
            ->orWhere('email', $identity)
            ->first();

        if ($user && Hash::check($validated['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended("/{$user->role}");
        }

        return back()->withErrors([
            'identity' => 'Kredensial login tidak cocok. Pastikan Username, NIP, atau NPSN dan password sesuai.',
        ])->onlyInput('identity');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
