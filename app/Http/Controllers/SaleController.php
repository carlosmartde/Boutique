<?php
// app/Http/Controllers/SaleController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Pail\ValueObjects\Origin\Console;

class SaleController extends Controller
{
    public function create()
    {
        return view('sales.create');
    }

    public function searchProductByCode($code)
    {
        try {
            Log::info('Buscando producto con código: ' . $code);

            $product = Product::where('code', (string) $code)->first();

            if (!$product) {
                Log::info('Producto no encontrado para el código: ' . $code);
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }

            if ($product->stock <= 0) {
                Log::info('Producto sin stock: ' . $product->code . ' - ' . $product->name);
                return response()->json(['error' => 'El producto no tiene stock disponible'], 400);
            }

            Log::info('Producto encontrado: ' . $product->id . ' - ' . $product->name);
            return response()->json($product);

        } catch (\Exception $e) {
            // Registra el error detallado
            Log::error('Error al buscar producto: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Devuelve el mensaje de error específico en desarrollo
            if (config('app.debug')) {
                return response()->json([
                    'error' => 'Error al buscar el producto: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }

            return response()->json(['error' => 'Error al buscar el producto. Intente nuevamente.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Verificar que el usuario esté autenticado
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay usuario autenticado. Por favor, inicia sesión nuevamente.'
                ], 401);
            }

            // Verificar el stock antes de procesar la venta
            foreach ($request->products as $product) {
                $productModel = Product::find($product['id']);

                if (!$productModel) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Producto con ID ' . $product['id'] . ' no encontrado'
                    ], 404);
                }

                if ($productModel->stock < $product['quantity']) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuficiente para el producto ' . $productModel->name . '. Disponible: ' . $productModel->stock
                    ], 400);
                }
            }

            $sale = Sale::create([
                'user_id' => Auth::id(),
                'total' => $request->total,
            ]);

            foreach ($request->products as $product) {
                $productModel = Product::find($product['id']);
                $quantity = $product['quantity'];
                $price = $product['price'];
                $subtotal = $product['subtotal'];
                $cost_total = $productModel->purchase_price * $quantity;
                $profit = $subtotal - $cost_total;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productModel->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'cost_total' => $cost_total,
                    'profit' => $profit
                ]);

                // Actualizar stock
                $productModel->stock -= $quantity;
                $productModel->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta realizada exitosamente',
                'sale' => $sale
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al procesar la venta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            DB::beginTransaction();
            
            $sale = Sale::with('details.product')->findOrFail($id);
            
            // Revertir el stock de los productos
            foreach ($sale->details as $detail) {
                $product = $detail->product;
                $product->stock += $detail->quantity;
                $product->save();
            }
            
            // Eliminar la venta y sus detalles (usando eliminación en cascada)
            $sale->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Venta anulada correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al anular la venta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al anular la venta: ' . $e->getMessage()
            ], 500);
        }
    }
}