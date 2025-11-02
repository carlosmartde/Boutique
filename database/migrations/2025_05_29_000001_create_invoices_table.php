<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('customer_nit')->default('N/A');
            $table->string('customer_address')->default('N/A');
            $table->string('customer_phone')->default('N/A');
            $table->string('customer_email')->default('N/A');
            $table->enum('payment_method', ['cash', 'card', 'transfer']);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
