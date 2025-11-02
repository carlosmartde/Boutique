<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class InvoiceSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('es_ES'); // Usar configuración en español
        
        // Obtener ventas que no tienen factura
        $sales = Sale::whereNotIn('id', function($query) {
            $query->select('sale_id')->from('invoices');
        })->get();

        // Generar facturas para aproximadamente el 70% de las ventas
        $salesToInvoice = $sales->random(ceil($sales->count() * 0.7));

        $lastInvoiceNumber = 0;

        foreach ($salesToInvoice as $sale) {
            $lastInvoiceNumber++;
            
            Invoice::create([
                'sale_id' => $sale->id,
                'invoice_number' => sprintf('FAC-%06d', $lastInvoiceNumber),
                'customer_name' => $faker->name,
                'customer_nit' => $faker->numberBetween(1000000, 9999999) . $faker->randomElement(['K', '']),
                'customer_email' => $faker->optional(0.7)->safeEmail ?? 'N/A',
                'customer_phone' => $faker->optional(0.8)->numerify('########') ?? 'N/A',
                'customer_address' => $faker->optional(0.6)->address ?? 'N/A',
                'payment_method' => $faker->randomElement(['cash', 'card', 'transfer']),
                'total' => $sale->total,
                'is_cf' => $faker->boolean(30), // 30% de probabilidad de que sea C/F
                'created_at' => $sale->created_at,
                'updated_at' => $sale->created_at,
            ]);
        }
    }
}
