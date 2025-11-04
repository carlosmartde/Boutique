<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Duplicado intencionalmente deshabilitado para evitar aplicar los mismos cambios dos veces
        // Esta migración no realizará ninguna acción
        if (!Schema::hasTable('cajas')) {
            return;
        }
        // Si ya existe alguna de las columnas, no hacer nada
        if (
            Schema::hasColumn('cajas', 'saldo_real') ||
            Schema::hasColumn('cajas', 'observacion') ||
            Schema::hasColumn('cajas', 'cancelada') ||
            Schema::hasColumn('cajas', 'cancel_autorizado_por') ||
            Schema::hasColumn('cajas', 'cancelado_en')
        ) {
            return;
        }
    }

    public function down(): void
    {
        // No se revierte nada en el duplicado
    }
};
