<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductoImport implements ToModel, WithHeadingRow
{
    // Mapeo: encabezado del archivo => campo del modelo Product
    protected $map = [ 
        'codigo'         => 'code',
        'nombre'         => 'name',
        'marca'          => 'brand',
        'precio_compra'  => 'purchase_price',
        'precio_venta'   => 'sale_price',
        'cantidad'       => 'stock'
    ];

    /**
     * Procesa cada fila del archivo
     */
    public function model(array $row)
    {
        $datos = [];

        // Normaliza encabezados y asigna al arreglo $datos
        foreach ($row as $clave => $valor) {
            $clave_normalizada = strtolower(trim($clave));
            if (isset($this->map[$clave_normalizada])) {
                $campo = $this->map[$clave_normalizada];
                $datos[$campo] = $valor;
            }
        }

        // Validación básica: campos obligatorios
        if (!isset($datos['code'], $datos['name'], $datos['sale_price'])) {
            return null;
        }

        // Determinar stock (puede no venir)
        $stock = isset($datos['stock']) && is_numeric($datos['stock']) ? (int)$datos['stock'] : 0;

        // Lógica para First_entry
        $firstEntry = ($stock == 0) ? true : false;

        return new Product([
            'code'           => $datos['code'] ?? null,
            'name'           => $datos['name'] ?? null,
            'brand'          => $datos['brand'] ?? null,
            'purchase_price' => $datos['purchase_price'] ?? 0,
            'sale_price'     => $datos['sale_price'] ?? 0,
            'stock'          => $stock,
            'First_entry'    => $firstEntry,
        ]);
    }
}


/*
Muy buena pregunta. Lo que estás buscando es una regla automática en MySQL para que una columna booleana (stock_flexible):

Sea true al momento de crear el producto si el stock inicial es 0.

Sea false si se ingresa stock mayor que 0.

Se mantenga en false incluso si el stock vuelve a 0.

✅ Solución ideal: Trigger en MySQL
Como esta lógica depende de cómo cambia el stock, puedes usar un trigger (disparador) para manejarla directamente en la base de datos.

🛠️ Ejemplo de implementación
Supongamos que tienes esta tabla:

sql
Copiar
Editar
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    stock INT DEFAULT 0,
    stock_flexible BOOLEAN DEFAULT TRUE
);
Luego, puedes crear un trigger para controlar stock_flexible al momento de insertar:

sql
Copiar
Editar
DELIMITER $$

CREATE TRIGGER before_insert_productos
BEFORE INSERT ON productos
FOR EACH ROW
BEGIN
    IF NEW.stock > 0 THEN
        SET NEW.stock_flexible = FALSE;
    ELSE
        SET NEW.stock_flexible = TRUE;
    END IF;
END$$

DELIMITER ;
Y otro trigger para manejar actualizaciones de stock:

sql
Copiar
Editar
DELIMITER $$

CREATE TRIGGER before_update_productos
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
    -- Si el stock anterior era <= 0 y el nuevo es > 0, desactiva el flexible
    IF OLD.stock <= 0 AND NEW.stock > 0 THEN
        SET NEW.stock_flexible = FALSE;
    END IF;
    
    -- Si el stock va de >0 a 0, NO lo vuelve a activar (se mantiene FALSE)
END$$

DELIMITER ;
🔍 ¿Y Laravel?
Laravel puede trabajar perfectamente con esto. Solo asegúrate de que:

No sobrescribas stock_flexible manualmente al crear o actualizar productos.

Laravel solo maneje stock y deje que MySQL determine el valor correcto de stock_flexible.

🚫 Limitación
MySQL no puede hacer que stock_flexible vuelva a true automáticamente cuando el stock baja a 0 si tú quieres que eso no suceda (como dijiste). Así que la lógica anterior cumple con tus reglas perfectamente.

✅ ¿Te gustaría que te dé una migración y ejemplo de código Laravel para esto también?


Tú dijiste:
si

ChatGPT Plus
Perfecto, aquí tienes una implementación completa en Laravel que incluye:

🔧 Migración de tabla productos con campo stock_flexible.

⚙️ Triggers de MySQL para manejar la lógica automáticamente.

🧪 Ejemplo de creación y actualización de productos en Eloquent.

🧱 1. Migración Laravel
Ejecuta:

bash
Copiar
Editar
php artisan make:migration create_productos_table
Luego en el archivo generado:

php
Copiar
Editar
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('stock')->default(0);
            $table->boolean('stock_flexible')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
}
Luego corre:

bash
Copiar
Editar
php artisan migrate
⚙️ 2. Triggers de MySQL
Agrega los siguientes triggers en tu base de datos MySQL. Puedes hacerlo desde una terminal MySQL, phpMyAdmin o un cliente como TablePlus, DBeaver, etc.

🧨 Trigger antes de INSERT
sql
Copiar
Editar
DELIMITER $$

CREATE TRIGGER before_insert_productos
BEFORE INSERT ON productos
FOR EACH ROW
BEGIN
    IF NEW.stock > 0 THEN
        SET NEW.stock_flexible = FALSE;
    ELSE
        SET NEW.stock_flexible = TRUE;
    END IF;
END$$

DELIMITER ;
🔁 Trigger antes de UPDATE
sql
Copiar
Editar
DELIMITER $$

CREATE TRIGGER before_update_productos
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
    -- Si antes no había stock y ahora sí, desactiva el modo flexible
    IF OLD.stock <= 0 AND NEW.stock > 0 THEN
        SET NEW.stock_flexible = FALSE;
    END IF;
    
    -- Si el stock vuelve a 0, no cambia stock_flexible (se mantiene en false)
END$$

DELIMITER ;
🧪 3. Ejemplo en Laravel (Eloquent)
Crear producto (stock 0 = flexible)
php
Copiar
Editar
Producto::create([
    'nombre' => 'Teclado gamer',
    'stock' => 0
]);

// stock_flexible se guarda como TRUE automáticamente
Crear producto (stock > 0 = NO flexible)
php
Copiar
Editar
Producto::create([
    'nombre' => 'Mouse Logitech',
    'stock' => 15
]);

// stock_flexible se guarda como FALSE automáticamente
Actualizar stock
php
Copiar
Editar
$producto = Producto::find(1);
$producto->stock = 50;
$producto->save();

// stock_flexible se pone en FALSE si estaba en TRUE
Bajar stock a 0
php
Copiar
Editar
$producto->stock = 0;
$producto->save();

// stock_flexible NO se vuelve TRUE — se mantiene en FALSE como tú querías
*/
