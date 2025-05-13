<?php

namespace App\Http\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController
{
    public function store(Request $request)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'rol' => ['required', 'in:admin,vendedor,gerente'],
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'rol' => $request->rol,
    ]);

    // Redirige a la misma vista con mensaje de éxito y NO inicia sesión
    return redirect()->route('register')->with('success', 'Usuario creado con éxito.');
}
}
