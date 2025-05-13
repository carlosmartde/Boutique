<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Primero verificamos si el usuario existe y las credenciales son correctas
        if (Auth::attempt($credentials)) {
            // Ahora verificamos el estado del usuario
            $user = Auth::user();
            if (!$user->status) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Tu cuenta está desactivada. Por favor, contacta al administrador.']);
            }

            $request->session()->regenerate();

            if ($user->rol === 'admin') {
                return redirect()->intended('dashboard');
            } else {
                return redirect()->intended('sales/create');
            }
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.']);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
