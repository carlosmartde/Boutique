<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('rol', 'admin')->first();
        $gerente = User::where('rol', 'gerente')->first();
        $vendedor = User::where('rol', 'vendedor')->first();
        $products = Product::all();

        // Ventas del mes actual
        for ($i = 0; $i < 10; $i++) {
            $sale = Sale::create([
                'user_id' => $vendedor->id,
                'total' => 0,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);

            $total = 0;
            $numProducts = rand(2, 5);
            $selectedProducts = $products->random($numProducts);

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 5);
                $subtotal = $quantity * $product->sale_price;
                $cost_total = $quantity * $product->purchase_price;
                $total += $subtotal;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->sale_price,
                    'subtotal' => $subtotal,
                    'cost_total' => $cost_total,
                ]);

                // Actualizar stock del producto
                $product->stock -= $quantity;
                $product->save();
            }

            $sale->total = $total;
            $sale->save();
        }

        // Ventas del mes anterior
        for ($i = 0; $i < 15; $i++) {
            $sale = Sale::create([
                'user_id' => rand(0, 1) ? $admin->id : $gerente->id,
                'total' => 0,
                'created_at' => Carbon::now()->subMonth()->subDays(rand(1, 30)),
            ]);

            $total = 0;
            $numProducts = rand(2, 5);
            $selectedProducts = $products->random($numProducts);

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 5);
                $subtotal = $quantity * $product->sale_price;
                $cost_total = $quantity * $product->purchase_price;
                $total += $subtotal;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->sale_price,
                    'subtotal' => $subtotal,
                    'cost_total' => $cost_total,
                ]);

                // Actualizar stock del producto
                $product->stock -= $quantity;
                $product->save();
            }

            $sale->total = $total;
            $sale->save();
        }

        // Ventas de hace dos meses
        for ($i = 0; $i < 20; $i++) {
            $sale = Sale::create([
                'user_id' => $vendedor->id,
                'total' => 0,
                'created_at' => Carbon::now()->subMonths(2)->subDays(rand(1, 30)),
            ]);

            $total = 0;
            $numProducts = rand(2, 5);
            $selectedProducts = $products->random($numProducts);

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 5);
                $subtotal = $quantity * $product->sale_price;
                $cost_total = $quantity * $product->purchase_price;
                $total += $subtotal;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->sale_price,
                    'subtotal' => $subtotal,
                    'cost_total' => $cost_total,
                ]);

                // Actualizar stock del producto
                $product->stock -= $quantity;
                $product->save();
            }

            $sale->total = $total;
            $sale->save();
        }
    }
} 