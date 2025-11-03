<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cajas')) {
            return; // Si la tabla no existe (p. ej. en entornos antiguos), no hacer nada
        }
        Schema::table('cajas', function (Blueprint $table) {
            if (!Schema::hasColumn('cajas', 'saldo_real')) {
                $table->decimal('saldo_real', 10, 2)->nullable()->after('monto_final');
            }
            if (!Schema::hasColumn('cajas', 'observacion')) {
                $table->text('observacion')->nullable()->after('saldo_real');
            }
            if (!Schema::hasColumn('cajas', 'cancelada')) {
                $table->boolean('cancelada')->default(false)->after('observacion');
            }
            if (!Schema::hasColumn('cajas', 'cancel_autorizado_por')) {
                $table->foreignId('cancel_autorizado_por')->nullable()->constrained('users')->nullOnDelete()->after('cancelada');
            }
            if (!Schema::hasColumn('cajas', 'cancelado_en')) {
                $table->timestamp('cancelado_en')->nullable()->after('cancel_autorizado_por');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('cajas')) {
            return;
        }
        Schema::table('cajas', function (Blueprint $table) {
            if (Schema::hasColumn('cajas', 'cancel_autorizado_por')) {
                $table->dropForeign(['cancel_autorizado_por']);
            }
            foreach (['saldo_real', 'observacion', 'cancelada', 'cancel_autorizado_por', 'cancelado_en'] as $col) {
                if (Schema::hasColumn('cajas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
