<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Duplicado deshabilitado: si la tabla ya existe, no crear de nuevo
        if (Schema::hasTable('caja_movimientos')) {
            return;
        }
    }

    public function down(): void
    {
        // No se revierte nada en el duplicado
    }
};
