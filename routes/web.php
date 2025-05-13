<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\UserManagementController;

// Rutas públicas
Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', function () {
    if (Auth::check() && Auth::user()->rol === 'admin') {
        return app()->call([app(AuthController::class), 'showRegistrationForm']);
    } else {
        return redirect()->route('sales.create')->with('error', 'Acceso denegado.');
    }
})->middleware('auth')->name('register');

use App\Http\Auth\RegisteredUserController;

Route::post('/register', function (Request $request) {
    if (Auth::check() && Auth::user()->rol === 'admin') {
        return app()->call([app(RegisteredUserController::class), 'store'], ['request' => $request]);
    } else {
        return redirect()->route('sales.create')->with('error', 'Acceso denegado.');
    }
})->middleware('auth');



// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {
    
    // Rutas accesibles para todos los usuarios autenticados (admin y vendedor)
    Route::get('/product/code/{code}', [SaleController::class, 'searchProductByCode']);
    Route::get('/sales/search/{code}', [SaleController::class, 'searchProductByCode']);
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    
    // Rutas solo para administradores (con verificación manual)
    Route::get('/dashboard', function () {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return view('dashboard');
    })->name('dashboard');

    // Rutas de productos (solo admin)
    Route::get('/products', function () {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ProductController::class), 'index']);
    })->name('products.index');

    Route::get('/products/create', function () {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ProductController::class), 'create']);
    })->name('products.create');

    Route::post('/products', function (Illuminate\Http\Request $request) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ProductController::class), 'store'], ['request' => $request]);
    })->name('products.store');

    Route::get('/products/{product}', function ($product) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ProductController::class), 'show'], ['product' => $product]);
    })->name('products.show');

    Route::get('/products/{product}/edit', function ($product) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ProductController::class), 'edit'], ['product' => $product]);
    })->name('products.edit');

    Route::put('/products/{product}', function (Illuminate\Http\Request $request, $product) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ProductController::class), 'update'], ['request' => $request, 'product' => $product]);
    })->name('products.update');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    

    // Rutas de inventario (solo admin)
    Route::get('/inventory', function () {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(InventoryController::class), 'index']);
    })->name('inventory.index');

    Route::get('/inventario/agregar', function () {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(App\Http\Controllers\InventarioController::class), 'mostrarFormularioAgregar']);
    })->name('inventario.mostrar-formulario');

    Route::post('/inventario/actualizar', function (Illuminate\Http\Request $request) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(App\Http\Controllers\InventarioController::class), 'actualizarInventario'], ['request' => $request]);
    })->name('inventario.actualizar');

    Route::get('/inventario/buscar-producto', function (Illuminate\Http\Request $request) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(App\Http\Controllers\InventarioController::class), 'buscarProducto'], ['request' => $request]);
    })->name('inventario.buscar-producto');
    
    Route::get('/inventory/search', function (Illuminate\Http\Request $request) {
         if (Auth::user()->rol !== 'admin') {
             return redirect()->route('sales.create')
                 ->with('error', 'No tienes permiso para acceder a esta sección.');
         }
         return app()->call([app(InventoryController::class), 'search'], ['request' => $request]);
     })->name('inventory.search');
    Route::get('/inventory/search', function (Illuminate\Http\Request $request) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(InventoryController::class), 'search'], ['request' => $request]);
    })->name('inventory.search');

    // Rutas de reportes (solo admin)
    Route::get('/reports', function (Illuminate\Http\Request $request) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ReportController::class), 'index'], ['request' => $request]);
    })->name('reports.index');

    Route::get('/reports/{id}', function ($id) {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return app()->call([app(ReportController::class), 'detail'], ['id' => $id]);
    })->name('reports.detail');

    // Rutas para la gestión de usuarios
    Route::get('/users/management', [UserManagementController::class, 'index'])->name('users.management');
    Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
});
