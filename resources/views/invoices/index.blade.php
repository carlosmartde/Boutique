@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Listado de Facturas</h3>
            
            <div class="d-flex gap-2">                <select class="form-select" id="period-filter" style="width: auto;">
                    <option value="">Todos los períodos</option>
                    <option value="day" {{ request('period') == 'day' ? 'selected' : '' }}>Hoy</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Esta semana</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Este mes</option>
                    <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Este año</option>
                    <option value="custom" {{ request('start_date') ? 'selected' : '' }}>Período personalizado</option>
                </select>                <div id="custom-dates" class="d-flex gap-2" style="display: {{ request('period') === 'custom' ? 'flex' : 'none' }} !important;">
                    <input type="text" id="start-date" class="form-control datepicker" placeholder="Fecha inicial" value="{{ request('start_date') }}">
                    <input type="text" id="end-date" class="form-control datepicker" placeholder="Fecha final" value="{{ request('end_date') }}">
                </div>
                <button class="btn btn-primary" id="apply-filter">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <button class="btn btn-secondary" id="clear-filter">
                    <i class="bi bi-x-circle"></i> Limpiar
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Factura</th>
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Método de Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->customer_name }}</td>
                            <td>{{ $invoice->customer_nit ?? 'C/F' }}</td>
                            <td>Q{{ number_format($invoice->total, 2) }}</td>
                            <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @switch($invoice->payment_method)
                                    @case('cash')
                                        <span class="badge bg-success">Efectivo</span>
                                        @break
                                    @case('card')
                                        <span class="badge bg-info">Tarjeta</span>
                                        @break
                                    @case('transfer')
                                        <span class="badge bg-primary">Transferencia</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="bi bi-file-pdf me-1"></i>Ver PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $invoices->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodFilter = document.getElementById('period-filter');
    const customDates = document.getElementById('custom-dates');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const applyFilter = document.getElementById('apply-filter');
    const clearFilter = document.getElementById('clear-filter');

    // Show custom dates if period is custom    // Determinar la visibilidad inicial de las fechas personalizadas
    customDates.style.display = periodFilter.value === 'custom' ? 'flex' : 'none';

    periodFilter.addEventListener('change', function() {
        // Mostrar campos de fecha solo para período personalizado
        customDates.style.display = this.value === 'custom' ? 'flex' : 'none';

        // Limpiar campos de fecha si se cambia a otro período
        if (this.value !== 'custom') {
            startDate.value = '';
            endDate.value = '';
        }
    });

    // Apply filter button handler
    applyFilter.addEventListener('click', function() {
        if (periodFilter.value === 'custom') {
            if (startDate.value && endDate.value) {
                window.location.href = `{{ route('invoices.index') }}?start_date=${startDate.value}&end_date=${endDate.value}`;
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Por favor seleccione ambas fechas',
                    icon: 'error',
                    confirmButtonColor: '#3a86ff'
                });
            }
        } else if (periodFilter.value) {
            window.location.href = `{{ route('invoices.index') }}?period=${periodFilter.value}`;
        } else {
            window.location.href = `{{ route('invoices.index') }}`;
        }
    });

    // Clear filter button handler
    clearFilter.addEventListener('click', function() {
        window.location.href = `{{ route('invoices.index') }}`;
    });

    // Initialize datepickers with new ranges
    flatpickr('.datepicker', {
        locale: 'es',
        dateFormat: 'Y-m-d',
        maxDate: 'today'
    });
});
</script>
@endsection
