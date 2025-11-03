<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class productoTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Generar 10 productos de ejemplo usando el modelo Product
        foreach (range(1, 100) as $index) {
            Product::create([
                'code' => $faker->unique()->numberBetween(1000, 9999), // Código aleatoriogit 
                'name' => $faker->word, // Nombre aleatorio
                'brand' => $faker->company, // Marca aleatoria
                'purchase_price' => $faker->randomFloat(2, 10, 100), // Precio de compra entre 10 y 100
                'sale_price' => $faker->randomFloat(2, 100, 200), // Precio de venta entre 100 y 200
                'stock' => $faker->numberBetween(1, 100), // Stock entre 1 y 100
                'First_entry'=> '1',
                'created_at' => now(), // Fecha de creación actual
                'updated_at' => now(), // Fecha de actualización actual
            ]);
        }
    }
}

# agregar datos a la tabla productos
# php artisan db:seed --class=productoTableSeeder