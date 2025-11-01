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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Verificar si el usuario está activo
            if (!$user->status) {
               $this->logout($request);
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Tu cuenta está desactivada. Por favor, contacta al administrador.']);
            }

            // Guardar el rol en la sesión
            session(['user_role' => $user->rol]);

            // Redireccionar según el rol
            switch ($user->rol) {
                case 'admin':
                    return redirect()->route('dashboard');
                case 'vendedor':
                    return redirect()->route('sales.create');
                case 'gerente':
                    return redirect()->route('dashboard');
                default:
                    $this->logout($request);
                    return redirect('/login')->withErrors([
                        'rol' => 'Rol no autorizado.',
                    ]);
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
        $user = Auth::user();
        if ($user) {
            $cajaAbierta = $user->cajas()->where('estado', 'abierto')->latest()->first();
            if ($cajaAbierta) {
                $cajaAbierta->update([
                    'estado' => 'cerrado',
                    'fecha_cierre' => now(),
                    'onto_final' => $cajaAbierta->monto_final ?? $cajaAbierta->monto_inicial
                ]);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}