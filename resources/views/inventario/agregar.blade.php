@extends('layouts.app')

@section('title', 'Agregar al Inventario')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-plus-square me-2"></i>Registrar Compra
        </h5>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="mb-4">
            <label for="codigo" class="form-label fw-bold">Buscar producto por código:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="codigo" placeholder="Ingrese código del producto">
                <button class="btn btn-primary" id="buscarBtn">
                    <i class="bi bi-search me-1"></i>Buscar
                </button>
            </div>
            <div id="error-mensaje" class="text-danger mt-2 d-none"></div>
        </div>

        <form id="formActualizar" action="{{ route('inventario.actualizar') }}" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="producto_id" id="producto_id">
            
            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Producto:</label>
                        <div id="nombre_producto" class="form-control-plaintext"></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Stock Actual:</label>
                            <div id="stock_actual" class="form-control-plaintext"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Precio de Compra Actual:</label>
                            <div id="precio_compra_actual" class="form-control-plaintext"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="cantidad_nueva" class="form-label fw-bold">Cantidad a Agregar:</label>
                <input type="number" class="form-control" id="cantidad_nueva" name="cantidad_nueva" min="1" required>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="precio_compra" class="form-label fw-bold">Nuevo Precio de Compra:</label>
                    <div class="input-group">
                        <span class="input-group-text">Q</span>
                        <input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="precio_venta" class="form-label fw-bold">Nuevo Precio de Venta:</label>
                    <div class="input-group">
                        <span class="input-group-text">Q</span>
                        <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" required>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="supplier_name" class="form-label fw-bold">Nombre del Proveedor:</label>
                    <input type="text" class="form-control" id="supplier_name" name="supplier_name">
                </div>
                <div class="col-md-6">
                    <label for="notes" class="form-label fw-bold">Notas:</label>
                    <textarea class="form-control" id="notes" name="notes" rows="1"></textarea>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-2"></i>Actualizar Inventario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarBtn = document.getElementById('buscarBtn');
    const codigoInput = document.getElementById('codigo');
    const formActualizar = document.getElementById('formActualizar');
    const errorMensaje = document.getElementById('error-mensaje');

    // Obtener parámetros de la URL
    const params = new URLSearchParams(window.location.search);
    const codigoDesdeURL = params.get('codigo');

    if (codigoDesdeURL) {
        codigoInput.value = codigoDesdeURL; // Rellenar el campo con el código recibido
        buscarProducto(codigoDesdeURL); // Ejecutar la búsqueda automáticamente
    }

    // Evento para el botón de búsqueda
    buscarBtn.addEventListener('click', function() {
        const codigo = codigoInput.value.trim();
        if (!codigo) {
            mostrarError('Debe ingresar un código de producto');
            return;
        }
        buscarProducto(codigo);
    });

    // Evento para detectar la tecla Enter
    codigoInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevenir el comportamiento predeterminado de la tecla Enter
            const codigo = codigoInput.value.trim();
            if (!codigo) {
                mostrarError('Debe ingresar un código de producto');
                return;
            }
            buscarProducto(codigo);
        }
    });

    function buscarProducto(codigo) {
        fetch(`{{ route('inventario.buscar-producto') }}?codigo=${codigo}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarProducto(data.producto);
                } else {
                    mostrarError(data.mensaje);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError('Ocurrió un error al buscar el producto');
            });
    }

    function mostrarProducto(producto) {
        document.getElementById('producto_id').value = producto.id;
        document.getElementById('nombre_producto').textContent = producto.nombre;
        document.getElementById('stock_actual').textContent = producto.stock;
        document.getElementById('precio_compra_actual').textContent = `Q${producto.precio_compra}`;
        
        document.getElementById('precio_compra').value = producto.precio_compra;
        document.getElementById('precio_venta').value = producto.precio_venta;
        
        errorMensaje.classList.add('d-none');
        formActualizar.classList.remove('d-none');
    }

    function mostrarError(mensaje) {
        errorMensaje.textContent = mensaje;
        errorMensaje.classList.remove('d-none');
        formActualizar.classList.add('d-none');
    }
});

</script>
@endsection