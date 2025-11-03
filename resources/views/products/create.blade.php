@extends('layouts.app')

@section('title', 'Nuevo Producto')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
        <i class="bi bi-bag-plus me-2"></i>Ingresar Nuevo Producto
    </h5>

    <a href="{{ route('inventario.showimportar') }}" class="btn btn-primary">
        <i class="bi bi-file-earmark-excel me-2"></i>Importar
    </a>
</div>

    <div class="card-body">
        <form id="productForm" method="POST" action="{{ route('products.store') }}">
            @csrf
            
            <div class="mb-4">
                <label for="code" class="form-label fw-bold">Código del Producto</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
            </div>
            
            <div class="mb-4">
                <label for="name" class="form-label fw-bold">Nombre del Producto</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            
            <div class="mb-4">
                <label for="brand" class="form-label fw-bold">Marca</label>
                <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand') }}" required>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="purchase_price" class="form-label fw-bold">Precio de Compra</label>
                    <div class="input-group">
                        <span class="input-group-text">Q</span>
                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="sale_price" class="form-label fw-bold">Precio de Venta</label>
                    <div class="input-group">
                        <span class="input-group-text">Q</span>
                        <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="stock" class="form-label fw-bold">Cantidad en Inventario</label>
                <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock', 0) }}" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Deshabilitar el botón de envío
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        
        // Obtener los datos del formulario
        const formData = new FormData(form);
        
        // Enviar la solicitud AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: data.message,
                    confirmButtonColor: '#3a86ff'
                }).then(() => {
                    // Limpiar el formulario
                    form.reset();
                    // Habilitar el botón de envío
                    submitButton.disabled = false;
                });
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            // Mostrar mensaje de error
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Ocurrió un error al crear el producto',
                confirmButtonColor: '#3a86ff'
            });
            // Habilitar el botón de envío
            submitButton.disabled = false;
        });
    });
});
</script>
@endsection