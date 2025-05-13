<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            // Bebidas
            [
                'code' => 'B001',
                'name' => 'Coca Cola 2L',
                'brand' => 'Coca Cola',
                'stock' => 50,
                'purchase_price' => 2.50,
                'sale_price' => 3.50,
            ],
            [
                'code' => 'B002',
                'name' => 'Sprite 2L',
                'brand' => 'Sprite',
                'stock' => 45,
                'purchase_price' => 2.40,
                'sale_price' => 3.40,
            ],
            [
                'code' => 'B003',
                'name' => 'Fanta 2L',
                'brand' => 'Fanta',
                'stock' => 40,
                'purchase_price' => 2.45,
                'sale_price' => 3.45,
            ],

            // Snacks
            [
                'code' => 'S001',
                'name' => 'Doritos Nacho',
                'brand' => 'Doritos',
                'stock' => 100,
                'purchase_price' => 1.20,
                'sale_price' => 2.00,
            ],
            [
                'code' => 'S002',
                'name' => 'Cheetos Flamin Hot',
                'brand' => 'Cheetos',
                'stock' => 90,
                'purchase_price' => 1.25,
                'sale_price' => 2.10,
            ],
            [
                'code' => 'S003',
                'name' => 'Ruffles Original',
                'brand' => 'Ruffles',
                'stock' => 85,
                'purchase_price' => 1.30,
                'sale_price' => 2.20,
            ],

            // Limpieza
            [
                'code' => 'L001',
                'name' => 'Detergente Ariel',
                'brand' => 'Ariel',
                'stock' => 30,
                'purchase_price' => 5.00,
                'sale_price' => 7.00,
            ],
            [
                'code' => 'L002',
                'name' => 'Suavizante Downy',
                'brand' => 'Downy',
                'stock' => 25,
                'purchase_price' => 4.50,
                'sale_price' => 6.50,
            ],
            [
                'code' => 'L003',
                'name' => 'Jabón en Polvo Ace',
                'brand' => 'Ace',
                'stock' => 35,
                'purchase_price' => 4.80,
                'sale_price' => 6.80,
            ],

            // Higiene Personal
            [
                'code' => 'H001',
                'name' => 'Shampoo Head & Shoulders',
                'brand' => 'Head & Shoulders',
                'stock' => 40,
                'purchase_price' => 3.50,
                'sale_price' => 5.00,
            ],
            [
                'code' => 'H002',
                'name' => 'Jabón Dove',
                'brand' => 'Dove',
                'stock' => 60,
                'purchase_price' => 1.80,
                'sale_price' => 2.80,
            ],
            [
                'code' => 'H003',
                'name' => 'Pasta Dental Colgate',
                'brand' => 'Colgate',
                'stock' => 50,
                'purchase_price' => 2.00,
                'sale_price' => 3.00,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 