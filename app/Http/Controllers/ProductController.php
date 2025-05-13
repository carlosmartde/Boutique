<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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
public function index()
{
    // Verificar permisos
    $redirect = $this->checkRole(['admin']);
    if ($redirect) return $redirect;
    
    // Código normal del método...
    $products = Product::all();
    return view('products.index', compact('products'));
}

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'code' => 'required|unique:products',
                'name' => 'required',
                'brand' => 'required',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0|gt:purchase_price',
                'stock' => 'required|integer|min:0',
            ]);

            // Crear el producto
            $product = Product::create($request->all());

            // Calcular el subtotal de la compra inicial
            $subtotal = $request->stock * $request->purchase_price;

            // Crear el registro de compra inicial
            $purchase = \App\Models\Purchase::create([
                'user_id' => Auth::id(),
                'total' => $subtotal,
                'supplier_name' => 'Compra Inicial',
                'notes' => 'Registro inicial del producto'
            ]);

            // Crear el detalle de la compra inicial
            \App\Models\PurchaseDetail::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $request->stock,
                'price' => $request->purchase_price,
                'subtotal' => $subtotal
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProductByCode($code)
    {
        $product = Product::where('code', $code)->first();
        return response()->json($product);
    }

    public function destroy(Product $product)
{
    $product->delete(); // Esto usará soft delete

    return redirect()->route('inventory.index')->with('success', 'Producto marcado como eliminado.');
}
}