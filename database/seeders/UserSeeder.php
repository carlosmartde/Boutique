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
        // Usuario Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@minimarket.com',
            'password' => Hash::make('admin123'),
            'rol' => 'admin',
        ]);

        // Usuario Vendedor
        User::create([
            'name' => 'Vendedor User',
            'email' => 'vendedor@minimarket.com',
            'password' => Hash::make('vendedor123'),
            'rol' => 'vendedor',
        ]);

        // Usuario Gerente
        User::create([
            'name' => 'Gerente User',
            'email' => 'gerente@minimarket.com',
            'password' => Hash::make('gerente123'),
            'rol' => 'gerente',
        ]);
    }
}
