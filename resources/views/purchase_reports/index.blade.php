@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="float-start">Reporte de Compras</h3>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Formulario de filtros -->
                    <form action="{{ route('purchase_reports.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="period" class="form-label">Período</label>
                            <select name="period" id="period" class="form-select">
                                <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Día</option>
                                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Semana</option>
                                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Mes</option>
                                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Año</option>
                            </select>
                        </div>
                      
                        <div class="col-md-3">
                            <label for="date" class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="date" id="date" value="{{ $date }}">
                        </div>
                         
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">Usuario</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="all">Todos los usuarios</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="month" class="form-label">Mes</label>
                            <select name="month" id="month" class="form-select">
                                <option value="">Seleccione un mes</option>
                                <option value="1" {{ request('month') == '1' ? 'selected' : '' }}>Enero</option>
                                <option value="2" {{ request('month') == '2' ? 'selected' : '' }}>Febrero</option>
                                <option value="3" {{ request('month') == '3' ? 'selected' : '' }}>Marzo</option>
                                <option value="4" {{ request('month') == '4' ? 'selected' : '' }}>Abril</option>
                                <option value="5" {{ request('month') == '5' ? 'selected' : '' }}>Mayo</option>
                                <option value="6" {{ request('month') == '6' ? 'selected' : '' }}>Junio</option>
                                <option value="7" {{ request('month') == '7' ? 'selected' : '' }}>Julio</option>
                                <option value="8" {{ request('month') == '8' ? 'selected' : '' }}>Agosto</option>
                                <option value="9" {{ request('month') == '9' ? 'selected' : '' }}>Septiembre</option>
                                <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>Octubre</option>
                                <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>Noviembre</option>
                                <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>Diciembre</option>
                            </select>
                        </div>
                        
                        <div class="col-md-12 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary px-4">Filtrar</button>
                            <a href="{{ route('purchase_reports.index') }}" class="btn btn-secondary px-4">Reiniciar</a>
                            <a href="{{ route('purchase_reports.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-success px-4">
                                <i class="bi bi-filetype-xlsx me-1"></i>Exportar a Excel
                            </a>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <!-- Total en compras -->
                        <div class="col-md-12 mb-3">
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Total en compras</h6>
                                    <h4 class="text-primary">Q{{ number_format($totalAmount, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de resultados -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Proveedor</th>
                                    <th>Fecha</th>
                                    <th>Monto Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($purchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->id }}</td>
                                        <td>{{ $purchase->user_name }}</td>
                                        <td>{{ $purchase->supplier_name ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('d/m/Y H:i:s') }}</td>
                                        <td>Q{{ number_format($purchase->total, 2) }}</td>
                                        <td>
                                            <a href="{{ route('purchase_reports.detail', $purchase->id) }}" class="btn btn-info btn-sm">Ver Detalles</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay compras registradas en este período</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <!-- Paginación estilo Bootstrap -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $purchases->appends(request()->query())->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Para enviar el formulario automáticamente al cambiar cualquier filtro
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form');
        const filterInputs = filterForm.querySelectorAll('select, input[type=date]');
        
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });
</script>
@endsection