<!-- resources/views/inventory/index.blade.php -->
@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-box me-2"></i>Inventario de Productos
            </h5>
            <a href="{{ route('inventory.export') }}" class="btn btn-success px-4">
                <i class="bi bi-filetype-xlsx me-1"></i>Exportar a Excel
            </a>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control" placeholder="Buscar por nombre o código...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover" id="inventory-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th colspan="2" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->brand }}</td>
                                <td>Q{{ number_format($product->purchase_price, 2) }}</td>
                                <td>Q{{ number_format($product->sale_price, 2) }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    @if ($product->stock > 10)
                                        <span class="badge bg-success">Disponible</span>
                                    @elseif ($product->stock > 0)
                                        <span class="badge bg-warning text-dark">Bajo Stock</span>
                                    @else
                                        <span class="badge bg-danger">Agotado</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('inventario.mostrar-formulario', ['product' => $product->id, 'codigo' => $product->code]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                                <!--
                                <td class="text-center">
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Está seguro de eliminar este producto?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            -->
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No hay productos en el inventario</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <!-- Paginación estilo Bootstrap -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                </div>




            </div>
        </div>
    </div>
    
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const tableContainer = document.querySelector('.table-responsive');
            let searchTimer;

            searchInput.addEventListener('keyup', function () {
                clearTimeout(searchTimer);
                const term = this.value.trim();
                
                searchTimer = setTimeout(function() {
                    if (term.length > 0) {
                        // Mostrar indicador de carga
                        tableContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
                        
                        // Realizar búsqueda AJAX
                        fetch(`/inventory/search?query=${encodeURIComponent(term)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            tableContainer.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error en la búsqueda:', error);
                            tableContainer.innerHTML = '<div class="alert alert-danger">Error al realizar la búsqueda</div>';
                        });
                    } else {
                        // Si el campo está vacío, recargar la vista original
                        window.location.href = "{{ route('inventory.index') }}";
                    }
                }, 500); // Esperar 500ms después de que el usuario deje de escribir
            });
        });
    </script>
@endsection