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
use App\Http\Controllers\PurchaseReportController;

// Rutas públicas
Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta de registro (solo gerente)
Route::get('/register', function () {
    if (Auth::check() && Auth::user()->rol === 'gerente') {
        return app()->call([app(AuthController::class), 'showRegistrationForm']);
    } else {
        return redirect()->route('sales.create')->with('error', 'Acceso denegado.');
    }
})->middleware('auth')->name('register');

use App\Http\Auth\RegisteredUserController;

Route::post('/register', function (Request $request) {
    if (Auth::check() && Auth::user()->rol === 'gerente') {
        return app()->call([app(RegisteredUserController::class), 'store'], ['request' => $request]);
    } else {
        return redirect()->route('sales.create')->with('error', 'Acceso denegado.');
    }
})->middleware('auth');

// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {
    
    // Rutas accesibles para todos los usuarios autenticados
    Route::get('/product/code/{code}', [SaleController::class, 'searchProductByCode']);
    Route::get('/sales/search/{code}', [SaleController::class, 'searchProductByCode']);
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    
    // Dashboard - accesible para admin, gerente y vendedor
    Route::get('/dashboard', function () {
        if (Auth::user()->rol === 'gerente') {
            return view('dashboard');
        }
        if (!in_array(Auth::user()->rol, ['admin'])) {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return view('dashboard');
    })->name('dashboard');

    // Rutas de productos - accesible para admin y gerente
    Route::middleware(['auth'])->group(function () {
        $productRoutes = function () {
            if (Auth::user()->rol === 'gerente') {
                return null;
            }
            if (!in_array(Auth::user()->rol, ['admin'])) {
                return redirect()->route('sales.create')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return null;
        };

        Route::get('/products', function () use ($productRoutes) {
            if ($redirect = $productRoutes()) return $redirect;
            return app()->call([app(ProductController::class), 'index']);
        })->name('products.index');

        Route::get('/products/create', function () use ($productRoutes) {
            if ($redirect = $productRoutes()) return $redirect;
            return app()->call([app(ProductController::class), 'create']);
        })->name('products.create');

        Route::post('/products', function (Request $request) use ($productRoutes) {
            if ($redirect = $productRoutes()) return $redirect;
            return app()->call([app(ProductController::class), 'store'], ['request' => $request]);
        })->name('products.store');

        Route::get('/products/{product}/edit', function ($product) use ($productRoutes) {
            if ($redirect = $productRoutes()) return $redirect;
            return app()->call([app(ProductController::class), 'edit'], ['product' => $product]);
        })->name('products.edit');

        Route::put('/products/{product}', function (Request $request, $product) use ($productRoutes) {
            if ($redirect = $productRoutes()) return $redirect;
            return app()->call([app(ProductController::class), 'update'], ['request' => $request, 'product' => $product]);
        })->name('products.update');

        Route::delete('/products/{product}', function ($product) use ($productRoutes) {
            if ($redirect = $productRoutes()) return $redirect;
            return app()->call([app(ProductController::class), 'destroy'], ['product' => $product]);
        })->name('products.destroy');
    });

    // Rutas de inventario - accesible para admin y gerente
    Route::middleware(['auth'])->group(function () {
        $inventoryRoutes = function () {
            if (Auth::user()->rol === 'gerente') {
                return null;
            }
            if (!in_array(Auth::user()->rol, ['admin'])) {
                return redirect()->route('sales.create')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return null;
        };

        Route::get('/inventory', function () use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(InventoryController::class), 'index']);
        })->name('inventory.index');

        Route::get('/inventario/agregar', function () use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(App\Http\Controllers\InventarioController::class), 'mostrarFormularioAgregar']);
        })->name('inventario.mostrar-formulario');

        Route::post('/inventario/actualizar', function (Request $request) use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(App\Http\Controllers\InventarioController::class), 'actualizarInventario'], ['request' => $request]);
        })->name('inventario.actualizar');

        Route::get('/inventario/buscar-producto', function (Request $request) use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(App\Http\Controllers\InventarioController::class), 'buscarProducto'], ['request' => $request]);
        })->name('inventario.buscar-producto');

        Route::get('/inventory/search', function (Request $request) use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(InventoryController::class), 'search'], ['request' => $request]);
        })->name('inventory.search');
    });

    // Rutas de reportes - accesible para admin y gerente
    Route::middleware(['auth'])->group(function () {
        $reportRoutes = function () {
            if (Auth::user()->rol === 'gerente') {
                return null;
            }
            if (!in_array(Auth::user()->rol, ['admin'])) {
                return redirect()->route('sales.create')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return null;
        };

        Route::get('/reports', function (Request $request) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(ReportController::class), 'index'], ['request' => $request]);
        })->name('reports.index');

        Route::get('/reports/{id}', function ($id) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(ReportController::class), 'detail'], ['id' => $id]);
        })->name('reports.detail');

        // Rutas para reportes de compras
        Route::get('/purchase-reports', function (Request $request) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(PurchaseReportController::class), 'index'], ['request' => $request]);
        })->name('purchase_reports.index');

        Route::get('/purchase-reports/{id}', function ($id) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(PurchaseReportController::class), 'detail'], ['id' => $id]);
        })->name('purchase_reports.detail');
    });

    // Rutas para la gestión de usuarios - solo gerente
    Route::middleware(['auth'])->group(function () {
        Route::get('/users/management', [UserManagementController::class, 'index'])->name('users.management');
        Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
