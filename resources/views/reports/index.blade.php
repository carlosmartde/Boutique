@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <!-- Aseguramos que el card tenga fondo blanco -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-square me-2"></i>Reporte de Ventas
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" onclick="exportReport()">
                                <i class="bi bi-file-earmark-excel me-2"></i>Exportar Excel
                            </button>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        
                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                    <!-- Formulario de filtros -->
                    <form action="{{ route('reports.index') }}" method="GET" class="row g-3 mb-4" id="filterForm">
                        <div class="col-md-3">
                            <label for="period" class="form-label">Período</label>
                            <select name="period" id="period" class="form-select">
                                <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Día</option>
                                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Semana</option>
                                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Mes</option>
                                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Año</option>
                                <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Personalizado</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="fechaInicioGroup"
                            style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
                            <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                                value="{{ $fechaInicio ?? '' }}" {{ $period == 'custom' ? '' : 'disabled' }}>
                        </div>
                        <div class="col-md-3" id="fechaFinGroup"
                            style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
                            <label for="fecha_fin" class="form-label">Fecha fin</label>
                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                                value="{{ $fechaFin ?? '' }}" {{ $period == 'custom' ? '' : 'disabled' }}>
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
                    </form>

                    <!-- Formulario oculto para exportar -->
                    <form id="exportForm" action="{{ route('reports.export') }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" name="period" id="export_period">
                        <input type="hidden" name="user_id" id="export_user_id">
                        <input type="hidden" name="fecha_inicio" id="export_fecha_inicio">
                        <input type="hidden" name="fecha_fin" id="export_fecha_fin">
                    </form>

                    <div class="row mb-3">
                        <!-- Total en ventas -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Total en ventas</h6>
                                    <h4 class="text-primary">Q{{ number_format($totalSales ?? 0, 2) }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total en costos -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Total en costos</h6>
                                    <h4 class="text-warning">Q{{ number_format($totalCost, 2) }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Ganancias -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Ganancias</h6>
                                    <h4 class="text-success">Q{{ number_format($totalProfit, 2) }}</h4>
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
                                    <th>Fecha</th>
                                    <th>Monto Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->id }}</td>
                                        <td>{{ $sale->user_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y H:i:s') }}</td>
                                        <td>Q{{ number_format($sale->total, 2) }}</td>
                                        <td>
                                            <a href="{{ route('reports.detail', $sale->id) }}" class="btn btn-info btn-sm">Ver
                                                Detalles</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay ventas registradas en este período</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        @if($sales->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $sales->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('filterForm');
            const periodSelect = document.getElementById('period');
            const fechaInicioGroup = document.getElementById('fechaInicioGroup');
            const fechaFinGroup = document.getElementById('fechaFinGroup');
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            const userId = document.getElementById('user_id');

            let isInitializing = true;

            function toggleFechas() {
                if (periodSelect.value === 'custom') {
                    fechaInicioGroup.style.display = 'block';
                    fechaFinGroup.style.display = 'block';
                    fechaInicio.disabled = false;
                    fechaFin.disabled = false;
                } else {
                    fechaInicioGroup.style.display = 'none';
                    fechaFinGroup.style.display = 'none';
                    fechaInicio.disabled = true;
                    fechaFin.disabled = true;
                    // Limpiar fechas cuando no sea personalizado
                    fechaInicio.value = '';
                    fechaFin.value = '';

                    // Solo enviar si no es la inicialización
                    if (!isInitializing) {
                        filterForm.submit();
                    }
                }
            }

            function mostrarError(mensaje) {
                // Remover alertas existentes
                const alertaExistente = document.querySelector('.alert-danger');
                if (alertaExistente) {
                    alertaExistente.remove();
                }

                // Crear nueva alerta
                const alerta = document.createElement('div');
                alerta.className = 'alert alert-danger alert-dismissible fade show';
                alerta.innerHTML = `
                        ${mensaje}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;

                // Insertar antes del formulario
                filterForm.parentNode.insertBefore(alerta, filterForm);

                // Auto-remover después de 5 segundos
                setTimeout(() => {
                    if (alerta && alerta.parentNode) {
                        alerta.remove();
                    }
                }, 5000);
            }

            periodSelect.addEventListener('change', function () {
                toggleFechas();
            });

            fechaInicio.addEventListener('change', function () {
                if (periodSelect.value === 'custom' && !isInitializing) {
                    if (fechaInicio.value && fechaFin.value) {
                        if (fechaInicio.value > fechaFin.value) {
                            mostrarError('La fecha de inicio no puede ser mayor que la fecha de fin.');
                            return;
                        }
                        // Usar setTimeout para evitar bucles
                        setTimeout(() => {
                            filterForm.submit();
                        }, 100);
                    }
                }
            });

            fechaFin.addEventListener('change', function () {
                if (periodSelect.value === 'custom' && !isInitializing) {
                    if (fechaInicio.value && fechaFin.value) {
                        if (fechaInicio.value > fechaFin.value) {
                            mostrarError('La fecha de fin no puede ser menor que la fecha de inicio.');
                            return;
                        }
                        // Usar setTimeout para evitar bucles
                        setTimeout(() => {
                            filterForm.submit();
                        }, 100);
                    }
                }
            });

            userId.addEventListener('change', function () {
                if (!isInitializing) {
                    filterForm.submit();
                }
            });

            // Inicializar visibilidad al cargar
            toggleFechas();

            // Marcar que la inicialización ha terminado
            setTimeout(() => {
                isInitializing = false;
            }, 500);
        });

        // Función para exportar reporte
        function exportReport() {
            // Obtener valores actuales del formulario de filtros
            const period = document.getElementById('period').value;
            const userId = document.getElementById('user_id').value;
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;

            // Validar si es período personalizado y no hay fechas
            if (period === 'custom' && (!fechaInicio || !fechaFin)) {
                alert('Por favor, selecciona las fechas de inicio y fin para el período personalizado.');
                return;
            }

            // Validar fechas para período personalizado
            if (period === 'custom' && fechaInicio === fechaFin) {
                alert('La fecha de inicio y fin no pueden ser iguales.');
                return;
            }

            // Establecer valores en el formulario de exportación
            document.getElementById('export_period').value = period;
            document.getElementById('export_user_id').value = userId;
            document.getElementById('export_fecha_inicio').value = fechaInicio;
            document.getElementById('export_fecha_fin').value = fechaFin;

            // Enviar formulario de exportación
            document.getElementById('exportForm').submit();
        }
    </script>
@endsection