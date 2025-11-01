<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->decimal('saldo_real', 10, 2)->nullable()->after('monto_final');
            $table->text('observacion')->nullable()->after('saldo_real');
            $table->boolean('cancelada')->default(false)->after('observacion');
            $table->foreignId('cancel_autorizado_por')->nullable()->constrained('users')->nullOnDelete()->after('cancelada');
            $table->timestamp('cancelado_en')->nullable()->after('cancel_autorizado_por');
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropForeign(['cancel_autorizado_por']);
            $table->dropColumn(['saldo_real', 'observacion', 'cancelada', 'cancel_autorizado_por', 'cancelado_en']);
        });
    }
};
