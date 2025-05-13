@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Análisis de Productos</h5>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form id="filterForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="period">Período</label>
                                    <select name="period" id="period" class="form-control">
                                        <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Hoy</option>
                                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Esta Semana</option>
                                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Este Mes</option>
                                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Este Año</option>
                                        <option value="custom" {{ $startDate && $endDate ? 'selected' : '' }}>Personalizado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 custom-date-fields" style="display: {{ $startDate && $endDate ? 'block' : 'none' }}">
                                <div class="form-group">
                                    <label for="start_date">Fecha Inicio</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                            </div>
                            <div class="col-md-3 custom-date-fields" style="display: {{ $startDate && $endDate ? 'block' : 'none' }}">
                                <div class="form-group">
                                    <label for="end_date">Fecha Fin</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <!-- Productos más vendidos -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Top 10 Productos Más Vendidos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Marca</th>
                                                    <th class="text-right">Cantidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($topProducts as $product)
                                                <tr>
                                                    <td>{{ $product->code }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->brand }}</td>
                                                    <td class="text-right">{{ $product->total_quantity }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay datos disponibles</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos menos vendidos -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">Top 10 Productos Menos Vendidos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Marca</th>
                                                    <th class="text-right">Cantidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($leastProducts as $product)
                                                <tr>
                                                    <td>{{ $product->code }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->brand }}</td>
                                                    <td class="text-right">{{ $product->total_quantity }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay datos disponibles</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos con mayor ingreso -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Top 10 Productos con Mayor Ingreso</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Marca</th>
                                                    <th class="text-right">Ingreso</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($topRevenueProducts as $product)
                                                <tr>
                                                    <td>{{ $product->code }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->brand }}</td>
                                                    <td class="text-right">${{ number_format($product->total_sales, 2) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay datos disponibles</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos con menor ingreso -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">Top 10 Productos con Menor Ingreso</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Marca</th>
                                                    <th class="text-right">Ingreso</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($leastRevenueProducts as $product)
                                                <tr>
                                                    <td>{{ $product->code }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->brand }}</td>
                                                    <td class="text-right">${{ number_format($product->total_sales, 2) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay datos disponibles</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period');
    const customDateFields = document.querySelectorAll('.custom-date-fields');

    periodSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateFields.forEach(field => field.style.display = 'block');
        } else {
            customDateFields.forEach(field => field.style.display = 'none');
        }
    });

    // Auto-submit form when period changes
    periodSelect.addEventListener('change', function() {
        if (this.value !== 'custom') {
            document.getElementById('filterForm').submit();
        }
    });
});
</script>
@endpush
@endsection 