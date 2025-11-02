<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ReporteCajaController;
use App\Http\Auth\RegisteredUserController;

// ============================================
// Rutas Públicas
// ============================================
Route::get('/', function () {
    return view('welcome');
});

// ============================================
// Rutas de Autenticación
// ============================================
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

Route::post('/register', function (Request $request) {
    if (Auth::check() && Auth::user()->rol === 'gerente') {
        return app()->call([app(RegisteredUserController::class), 'store'], ['request' => $request]);
    } else {
        return redirect()->route('sales.create')->with('error', 'Acceso denegado.');
    }
})->middleware('auth');

// ============================================
// Rutas con Autenticación
// ============================================
Route::middleware(['auth'])->group(function () {
    
    // ============================================
    // Rutas de Notificaciones (Testing)
    // ============================================
    Route::get('/test-notification/{productId}', [App\Http\Controllers\NotificationTestController::class, 'testLowStockNotification'])->name('test.notification');
    
    // ============================================
    // Rutas de Caja (Todos los usuarios autenticados)
    // ============================================
    Route::get('/caja/apertura', [CajaController::class, 'index'])->name('caja.apertura');
    Route::get('/caja/create', [CajaController::class, 'create'])->name('caja.create');
    Route::post('/caja/apertura', [CajaController::class, 'store'])->name('caja.store');
    Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
    Route::get('/caja/movimiento', [CajaController::class, 'movimiento'])->name('caja.movimiento');
    Route::post('/caja/movimiento', [CajaController::class, 'guardarMovimiento'])->name('caja.movimiento.guardar');
    Route::get('/caja/arqueo', [CajaController::class, 'arqueo'])->name('caja.arqueo');
    Route::post('/caja/arqueo', [CajaController::class, 'guardarArqueo'])->name('caja.arqueo.guardar');
    Route::post('/caja/cancelar', [CajaController::class, 'cancelar'])->name('caja.cancelar');
    Route::get('/caja/{caja}', [CajaController::class, 'show'])->name('caja.show');
    Route::get('/caja/{caja}/edit', [CajaController::class, 'edit'])->name('caja.edit');
    Route::put('/caja/{caja}', [CajaController::class, 'update'])->name('caja.update');
    Route::delete('/caja/{caja}', [CajaController::class, 'destroy'])->name('caja.destroy');
    
    // ============================================
    // Rutas de Ventas (Todos los usuarios autenticados)
    // ============================================
    Route::get('/product/code/{code}', [SaleController::class, 'searchProductByCode']);
    Route::get('/sales/search/{code}', [SaleController::class, 'searchProductByCode']);
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::delete('/sales/{id}', [SaleController::class, 'cancel'])->name('sales.cancel');
    
    // ============================================
    // Rutas de Códigos de Barras (Todos los usuarios autenticados)
    // ============================================
    Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcodes.index');
    Route::get('/barcodes/random', [BarcodeController::class, 'generateRandom'])->name('barcodes.generate-random');
    Route::post('/barcodes/pdf', [BarcodeController::class, 'generatePDF'])->name('barcodes.generate-pdf');
    
    // ============================================
    // Rutas de Facturación (Todos los usuarios autenticados)
    // ============================================
    Route::resource('invoices', App\Http\Controllers\InvoiceController::class)->only(['index', 'store', 'show']);
    
    // ============================================
    // Dashboard (Admin y Gerente)
    // ============================================
    Route::get('/dashboard', function () {
        if (!in_array(Auth::user()->rol, ['admin', 'gerente'])) {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        return view('dashboard');
    })->name('dashboard');

    // ============================================
    // Rutas de Productos (Admin y Gerente)
    // ============================================
    Route::middleware(['auth'])->group(function () {
        $productRoutes = function () {
            if (!in_array(Auth::user()->rol, ['admin', 'gerente'])) {
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

        Route::get('/products/{product}', function ($product) use ($productRoutes) {
            if ($redirect = $productRoutes()) return $redirect;
            return app()->call([app(ProductController::class), 'show'], ['product' => $product]);
        })->name('products.show');

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

    // ============================================
    // Rutas de Inventario (Admin y Gerente)
    // ============================================
    Route::middleware(['auth'])->group(function () {
        $inventoryRoutes = function () {
            if (!in_array(Auth::user()->rol, ['admin', 'gerente'])) {
                return redirect()->route('sales.create')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return null;
        };

        Route::get('/inventory', function () use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(InventoryController::class), 'index']);
        })->name('inventory.index');

        Route::get('/inventory/export', function () use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(InventoryController::class), 'export']);
        })->name('inventory.export');

        Route::get('/inventario/agregar', function () use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(App\Http\Controllers\InventarioController::class), 'mostrarFormularioAgregar']);
        })->name('inventario.mostrar-formulario');

        Route::post('/inventario/importar', function (Request $request) use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(App\Http\Controllers\InventarioController::class), 'importar'], ['request' => $request]);
        })->name('inventario.importar');

        Route::get('/inventario/importar', function () use ($inventoryRoutes) {
            if ($redirect = $inventoryRoutes()) return $redirect;
            return app()->call([app(App\Http\Controllers\InventarioController::class), 'showImportar']);
        })->name('inventario.showimportar');

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

    // ============================================
    // Rutas de Reportes (Admin y Gerente)
    // ============================================
    Route::middleware(['auth'])->group(function () {
        $reportRoutes = function () {
            if (!in_array(Auth::user()->rol, ['admin', 'gerente'])) {
                return redirect()->route('sales.create')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return null;
        };

        // Reportes de Ventas
        Route::get('/reports', function (Request $request) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(ReportController::class), 'index'], ['request' => $request]);
        })->name('reports.index');

        Route::get('/reports/export', function (Request $request) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(ReportController::class), 'export'], ['request' => $request]);
        })->name('reports.export');

        Route::get('/reports/{id}', function ($id) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(ReportController::class), 'detail'], ['id' => $id]);
        })->name('reports.detail');

        // Reportes de Compras
        Route::get('/purchase-reports', function (Request $request) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(PurchaseReportController::class), 'index'], ['request' => $request]);
        })->name('purchase_reports.index');

        Route::get('/purchase-reports/export', function (Request $request) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(PurchaseReportController::class), 'export'], ['request' => $request]);
        })->name('purchase_reports.export');

        Route::get('/purchase-reports/{id}', function ($id) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(PurchaseReportController::class), 'detail'], ['id' => $id]);
        })->name('purchase_reports.detail');

        // Reportes de Caja
        Route::get('/caja/reporte', function (Request $request) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(ReporteCajaController::class), 'reporte'], ['request' => $request]);
        })->name('caja.reporte');

        Route::get('/caja/reporte/{caja}', function ($caja) use ($reportRoutes) {
            if ($redirect = $reportRoutes()) return $redirect;
            return app()->call([app(ReporteCajaController::class), 'detalle'], ['caja' => $caja]);
        })->name('caja.reporte.detalle');
    });

    // ============================================
    // Rutas de Gestión de Usuarios (Admin y Gerente)
    // ============================================
    Route::middleware(['auth'])->group(function () {
        $userRoutes = function () {
            if (!in_array(Auth::user()->rol, ['admin', 'gerente'])) {
                return redirect()->route('sales.create')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return null;
        };

        Route::get('/users/management', function () use ($userRoutes) {
            if ($redirect = $userRoutes()) return $redirect;
            return app()->call([app(UserManagementController::class), 'index']);
        })->name('users.management');

        Route::patch('/users/{user}/toggle-status', function ($user) use ($userRoutes) {
            if ($redirect = $userRoutes()) return $redirect;
            return app()->call([app(UserManagementController::class), 'toggleStatus'], ['user' => $user]);
        })->name('users.toggle-status');
    });

    // ============================================
    // Rutas de Análisis de Productos (Admin y Gerente)
    // ============================================
    Route::middleware(['auth'])->group(function () {
        Route::get('/product-analysis', function (Request $request) {
            if (!in_array(Auth::user()->rol, ['admin', 'gerente'])) {
                return redirect()->route('dashboard')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return app()->call([app(ProductReportController::class), 'index'], ['request' => $request]);
        })->name('product_analysis.index');
    });
});