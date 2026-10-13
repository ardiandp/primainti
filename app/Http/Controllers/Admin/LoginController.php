<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->isActive()) {
            // user dinonaktifkan: audit + tolak (tanpa membocorkan status password).
            AuditLogger::log('login.blocked', $user, null, ['email' => $user->email]);

            return back()
                ->withErrors(['email' => 'Akun tidak aktif. Hubungi administrator.'])
                ->onlyInput('email');
        }

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            AuditLogger::log('login.success', Auth::user());

            return redirect()->intended(route('admin.dashboard'));
        }

        // Kredensial salah — catat hanya email, tanpa password.
        AuditLogger::log('login.failed', $user, null, ['email' => $credentials['email']]);

        return back()
            ->withErrors(['email' => 'Kredensial salah.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        AuditLogger::log('logout', Auth::user());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}