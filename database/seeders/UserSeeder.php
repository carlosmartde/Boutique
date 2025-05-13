<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'rol' => 'admin',
        ]);

        // Crear usuario gerente
        User::create([
            'name' => 'Gerente',
            'email' => 'gerente@example.com',
            'password' => Hash::make('password'),
            'rol' => 'gerente',
        ]);

        // Crear usuario vendedor
        User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@example.com',
            'password' => Hash::make('password'),
            'rol' => 'vendedor',
        ]);
    }
}
