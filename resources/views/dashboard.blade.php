@extends('layouts.app')

@section('title', 'Panel Principal')

@section('content')
<div class="text-center">
    <div class="welcome-banner p-4 mb-5 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #3a86ff, #8338ec);">
        <h1 class="display-6 fw-bold text-white mb-2">¡Bienvenido, {{ Auth::user()->name }}!</h1>
        <p class="lead text-white mb-0">¿Qué deseas hacer hoy?</p>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-bag-plus fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Nuevo Producto</h5>
                    <p class="card-text text-muted">Agrega nuevos productos al inventario</p>
                    <a href="{{ route('products.create') }}" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-2"></i>Ir a Productos
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-cart-plus fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Ventas</h5>
                    <p class="card-text text-muted">Registra nuevas ventas</p>
                    <a href="{{ route('sales.create') }}" class="btn btn-primary w-100">
                        <i class="bi bi-receipt me-2"></i>Ir a Ventas
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-box fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Inventario</h5>
                    <p class="card-text text-muted">Consulta el stock disponible</p>
                    <a href="{{ route('inventory.index') }}" class="btn btn-primary w-100">
                        <i class="bi bi-list-check me-2"></i>Ver Inventario
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-plus-square fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Registrar Compra</h5>
                    <p class="card-text text-muted">Actualiza stock y precios de productos existentes</p>
                    <a href="{{ route('inventario.mostrar-formulario') }}" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-up-circle me-2"></i>Actualizar Stock
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-file-earmark-bar-graph fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Reporte de ventas</h5>
                    <p class="card-text text-muted">Revisa los datos de las ventas realizadas</p>
                    <a href="{{ route('reports.index') }}" class="btn btn-primary w-100">
                        <i class="bi bi-graph-up me-2"></i>Ver Reportes
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-cart-check fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Reporte de compras</h5>
                    <p class="card-text text-muted">Revisa los datos de las compras realizadas</p>
                    <a href="{{ route('purchase_reports.index') }}" class="btn btn-primary w-100">
                        <i class="bi bi-graph-up me-2"></i>Ver Reportes
                    </a>
                </div>
            </div>
        </div>

        <!-- Nueva card para Gestión de Usuarios -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-people fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Gestión de Usuarios</h5>
                    <p class="card-text text-muted">Administra los usuarios del sistema</p>
                    <a href="{{ route('users.management') }}" class="btn btn-primary w-100">
                        <i class="bi bi-gear-wide-connected me-2"></i>Administrar Usuarios
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="icon-wrapper mb-3">
                        <i class="bi bi-person-plus fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Crear Usuario</h5>
                    <p class="card-text text-muted">Crear nuevos usuarios admin o vendedores</p>
                    <a href="{{ route('register') }}" class="btn btn-primary w-100">
                        <i class="bi bi-person-badge me-2"></i>Crear Usuario
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        height: 80px;
        width: 80px;
        border-radius: 50%;
        background-color: rgba(58, 134, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: all 0.3s;
    }
    
    .card:hover .icon-wrapper {
        background-color: rgba(58, 134, 255, 0.2);
        transform: scale(1.1);
    }
    
    .card:hover .bi {
        color: #8338ec !important;
    }
    
    .card-title {
        color: #212529;
        transition: color 0.3s;
    }
    
    .card:hover .card-title {
        color: #3a86ff;
    }
    
    .welcome-banner {
        background-size: 300% 300%;
        animation: gradientBG 15s ease infinite;
    }
    
    @keyframes gradientBG {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }
</style>
@endsection