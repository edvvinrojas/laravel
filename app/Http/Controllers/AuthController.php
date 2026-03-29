<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Tu cuenta está desactivada.']);
            }
            $request->session()->regenerate();
            AuditService::log('LOGIN', 'User', Auth::id(), 'Inicio de sesión: ' . Auth::user()->email);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        AuditService::log('LOGOUT', 'User', Auth::id(), 'Cierre de sesión: ' . Auth::user()?->email);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
