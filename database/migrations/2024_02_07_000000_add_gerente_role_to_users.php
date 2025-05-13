<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddGerenteRoleToUsers extends Migration
{
    public function up(): void
    {
        // First, modify the enum column to accept the new role
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'vendedor', 'gerente') DEFAULT 'vendedor'");
    }

    public function down(): void
    {
        // In the down method, we'll need to ensure no 'gerente' users exist before modifying the enum
        DB::table('users')->where('rol', 'gerente')->update(['rol' => 'vendedor']);
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'vendedor') DEFAULT 'vendedor'");
    }
};
