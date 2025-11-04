@extends('layouts.app')

@section('title', 'Reporte de Cajas')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-cash-stack me-2"></i>Reporte de Cajas
        </h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel me-1"></i>Filtros
            </button>
            <button type="button" class="btn btn-success" onclick="exportReport()">
                <i class="bi bi-file-earmark-excel me-1"></i>Exportar
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filtros actuales aplicados -->
        @if($filtro || $fecha)
        <div class="alert alert-info alert-dismissible fade show mb-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Filtros aplicados:</strong>
            @if($filtro) Período: {{ ucfirst($filtro) }} @endif
            @if($fecha) | Fecha: {{ $fecha }} @endif
            <a href="{{ route('caja.reporte') }}" class="btn btn-sm btn-outline-info ms-2">
                <i class="bi bi-x-circle me-1"></i>Limpiar filtros
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Resumen estadístico -->
        @if($cajas->count() > 0)
<div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-cash-coin fs-1 mb-2 text-warning"></i>
                        <h5 class="text-warning">{{ $cajas->count() }}</h5>
                        <small class="text-warning">Total Cajas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-info">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle fs-1 mb-2 text-info"></i>
                        <h5 class="text-info">{{ $cajas->where('estado', 'cerrado')->where('cancelada', false)->count() }}</h5>
                        <small class="text-info">Cerradas (no canceladas)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-clock fs-1 mb-2 text-primary"></i>
                        <h5 class="text-primary">{{ $cajas->where('estado', 'abierto')->count() }}</h5>
                        <small class="text-primary">Cajas Abiertas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-success">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar fs-1 mb-2 text-success"></i>
                        <h5 class="text-success">Q{{ number_format($cajas->sum('monto_final'), 2) }}</h5>
                        <small class="text-success">Total Recaudado</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th><i class="bi bi-hash me-1"></i>ID</th>
                        <th><i class="bi bi-person me-1"></i>Cajero</th>
                        <th><i class="bi bi-arrow-up-circle me-1"></i>Monto Inicial</th>
                        <th><i class="bi bi-arrow-down-circle me-1"></i>Monto Final</th>
                        <th><i class="bi bi-graph-up me-1"></i>Ventas del Día</th>
                        <th><i class="bi bi-circle-fill me-1"></i>Estado</th>
                        <th><i class="bi bi-calendar-plus me-1"></i>Apertura</th>
                        <th><i class="bi bi-calendar-check me-1"></i>Cierre</th>
                        <th><i class="bi bi-gear me-1"></i>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cajas as $caja)
                    <tr>
                        <td><strong>#{{ $caja->id }}</strong></td>
                        <td>
                            @if($caja->user)
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    {{ substr($caja->user->name, 0, 1) }}
                                </div>
                                {{ $caja->user->name }}
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary text-white">
                                Q{{ number_format($caja->monto_inicial, 2) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-dark text-white">
                                Q{{ number_format($caja->monto_final, 2) }}
                            </span>
                        </td>
                        <td>
                            @php
                                // Nueva lógica: las ventas del día son la diferencia entre monto final e inicial
                                // Si el monto final es menor que el inicial, las ventas son 0 (no puede ser negativo)
                                $ventas_dia = max(0, $caja->monto_final - $caja->monto_inicial);
                                
                                // Determinar color del badge según las ventas
                                if ($ventas_dia > 0) {
                                    $badgeClass = 'bg-success';
                                    $icon = 'bi-arrow-up-circle';
                                } else {
                                    $badgeClass = 'bg-info';
                                    $icon = 'bi-dash-circle';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }} text-white">
                                <i class="{{ $icon }} me-1"></i>
                                Q{{ number_format($ventas_dia, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($caja->cancelada)
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-octagon me-1"></i>Cancelada
                                </span>
                            @elseif($caja->estado == 'abierto')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock me-1"></i>Abierta
                                </span>
                            @else
                                <span class="badge bg-success text-white">
                                    <i class="bi bi-check-circle me-1"></i>Cerrada
                                </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($caja->fecha_apertura)->format('d/m/Y H:i') }}
                            </small>
                        </td>
                        <td>
                            @if($caja->fecha_cierre)
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($caja->fecha_cierre)->format('d/m/Y H:i') }}
                            </small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('caja.reporte.detalle', $caja->id) }}" class="btn btn-outline-primary" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($caja->estado == 'cerrado')
                                <a href="{{ route('caja.reporte.detalle', [$caja->id]) }}?print=1" target="_blank" class="btn btn-outline-success" title="Imprimir">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                <h6>No hay cajas para el filtro seleccionado</h6>
                                <p class="mb-0">Intenta ajustar los filtros o seleccionar un período diferente.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($cajas, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $cajas->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Modal de Filtros -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-range me-1"></i>Período
                            </label>
                            <select name="filtro" class="form-select">
                                <option value="dia" {{ $filtro == 'dia' ? 'selected' : '' }}>Día</option>
                                <option value="semana" {{ $filtro == 'semana' ? 'selected' : '' }}>Semana</option>
                                <option value="mes" {{ $filtro == 'mes' ? 'selected' : '' }}>Mes</option>
                                <option value="anio" {{ $filtro == 'anio' ? 'selected' : '' }}>Año</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar me-1"></i>Fecha
                            </label>
                            <input type="date" name="fecha" class="form-control" value="{{ $fecha }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function exportReport() {
    // Implementar exportación
    console.log('Exportar reporte');
    // Construir la URL con los filtros actuales
    const urlParams = new URLSearchParams(window.location.search);
    const exportUrl = '/caja/reporte/export?' + urlParams.toString();
    
    // Descargar el archivo
    window.location.href = exportUrl;
}
</script>

@endsection