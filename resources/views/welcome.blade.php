@extends('layouts.app')

@section('title', 'Bienvenido')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5 my-5">
                <div class="welcome-content p-5 rounded-4 shadow-lg" style="background-color: white;">
                    <h1 class="display-4 mb-4 fw-bold" style="color: #3a86ff;">Bienvenido a MINI-MARKET</h1>
                    <p class="lead mb-5">Sistema de gestión para tienda de abarrotes</p>
                    @guest
                    <div class="mt-5">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-3 px-4 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </a>
                    </div>
                    @else
                    <div class="mt-5">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-shop me-2"></i>INGRESAR A LA TIENDA EN LINEA
                        </a>
                    </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .welcome-content {
        background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(248,249,250,1) 100%);
        transition: transform 0.3s ease;
    }
    
    .welcome-content:hover {
        transform: translateY(-5px);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3a86ff, #8338ec);
        border: none;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #2667cc, #6019d1);
    }
</style>
@endsection