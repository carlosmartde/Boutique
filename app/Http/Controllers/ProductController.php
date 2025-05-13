<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'code' => 'required|unique:products',
            'name' => 'required',
            'brand' => 'required',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|gt:purchase_price',
            'stock' => 'required|integer|min:0',
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
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