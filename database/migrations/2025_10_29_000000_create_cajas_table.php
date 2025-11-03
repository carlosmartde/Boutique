<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear la tabla base de cajas para soportar migraciones posteriores
        if (Schema::hasTable('cajas')) {
            return; // Ya existe, no hacer nada
        }
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            // Debe existir antes de agregar campos en 2025_10_29_000001_add_fields_to_cajas_table
            $table->decimal('monto_final', 10, 2)->nullable();
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->timestamps();
        });
    }

      public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('cajas');
    }
};
