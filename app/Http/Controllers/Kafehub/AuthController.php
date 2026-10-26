<?php

namespace App\Http\Controllers\Kafehub;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Masuk, keluar, dan ganti password pengelola kafehub.id. */
class AuthController extends Controller
{
    public function show(): View
    {
        return view('panel.login');
    }

    public function edit(): View
    {
        return view('panel.account');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('kafehub')->attempt($data, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['username' => 'Username atau password salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel.dashboard'));
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password:kafehub'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Password lama salah.',
        ]);

        $request->user('kafehub')->update(['password' => $data['password']]);

        return redirect()->route('panel.account')->with('status', 'Password berhasil diganti.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('kafehub')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('panel.login');
    }
}
