<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Importar Log
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class InventarioController extends Controller
{
    public function mostrarFormularioAgregar()
    {
        return view('inventario.agregar');
    }

    public function buscarProducto(Request $request)
    {
        try {
            $codigo = $request->input('codigo');

            $producto = Product::where('code', $codigo)->first(); // Cambiado 'codigo' a 'code'

            if ($producto) {
                return response()->json([
                    'success' => true,
                    'producto' => [
                        'id' => $producto->id,
                        'nombre' => $producto->name,
                        'marca' => $producto->brand,
                        'stock' => $producto->stock,
                        'precio_compra' => $producto->purchase_price,
                        'precio_venta' => $producto->sale_price
                    ]
                ]);
            } 

            return response()->json([
                'success' => false,
                'mensaje' => 'Producto no encontrado'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error al buscar producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Ocurrió un error al buscar el producto'
            ], 500);
        }
    }

    public function actualizarInventario(Request $request)
    {
        try {
            $request->validate([
                'producto_id' => 'required|exists:products,id', // Cambiado 'productos' a 'products'
                'cantidad_nueva' => 'required|numeric|min:1',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0'
            ]);

            $producto = Product::findOrFail($request->producto_id);
            
            // Actualizar stock
            $producto->stock += $request->cantidad_nueva;
            
            // Actualizar precios
            $producto->purchase_price = $request->precio_compra;
            $producto->sale_price = $request->precio_venta;
            
            $producto->save();
            
            return redirect()->route('inventario.mostrar-formulario')
                ->with('success', 'Inventario actualizado correctamente');

        } catch (\Exception $e) {
            Log::error('Error al actualizar inventario: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el inventario.');
        }
    }

    private function checkRole($allowedRoles = ['admin'])
{
    $userRole = Auth::user()->rol ?? null;
    
    if ($userRole === 'gerente') {
        return null; // Gerente tiene acceso total
    }
    
    if (!$userRole || !in_array($userRole, $allowedRoles)) {
        if ($userRole === 'vendedor') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        
        return redirect()->route('login');
    }
    
    return null; // No redirect needed
}
}
