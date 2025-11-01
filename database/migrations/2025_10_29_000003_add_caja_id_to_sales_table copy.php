<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Duplicado deshabilitado: si la columna ya existe, no hacer nada
        if (!Schema::hasTable('sales') || Schema::hasColumn('sales', 'caja_id')) {
            return;
        }
    }

    public function down(): void
    {
        // No se revierte nada en el duplicado
    }
};
