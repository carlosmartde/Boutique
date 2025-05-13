<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PurchaseSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('rol', 'admin')->first();
        $gerente = User::where('rol', 'gerente')->first();
        $products = Product::all();

        // Compras del mes actual
        for ($i = 0; $i < 5; $i++) {
            $purchase = Purchase::create([
                'user_id' => $admin->id,
                'total' => 0,
                'supplier_name' => 'Distribuidora ' . ($i + 1),
                'notes' => 'Compra regular #' . ($i + 1),
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);

            $total = 0;
            $numProducts = rand(3, 6);
            $selectedProducts = $products->random($numProducts);

            foreach ($selectedProducts as $product) {
                $quantity = rand(5, 20);
                $subtotal = $quantity * $product->purchase_price;
                $total += $subtotal;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->purchase_price,
                    'subtotal' => $subtotal,
                ]);

                // Actualizar stock del producto
                $product->stock += $quantity;
                $product->save();
            }

            $purchase->total = $total;
            $purchase->save();
        }

        // Compras del mes anterior
        for ($i = 0; $i < 5; $i++) {
            $purchase = Purchase::create([
                'user_id' => $gerente->id,
                'total' => 0,
                'supplier_name' => 'Proveedor ' . ($i + 1),
                'notes' => 'Compra mensual #' . ($i + 1),
                'created_at' => Carbon::now()->subMonth()->subDays(rand(1, 30)),
            ]);

            $total = 0;
            $numProducts = rand(3, 6);
            $selectedProducts = $products->random($numProducts);

            foreach ($selectedProducts as $product) {
                $quantity = rand(5, 20);
                $subtotal = $quantity * $product->purchase_price;
                $total += $subtotal;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->purchase_price,
                    'subtotal' => $subtotal,
                ]);

                // Actualizar stock del producto
                $product->stock += $quantity;
                $product->save();
            }

            $purchase->total = $total;
            $purchase->save();
        }
    }
} 