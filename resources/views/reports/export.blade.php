@extends('layouts.app')

@section('title', 'Exportar Reporte de Ventas')

@section('content')
    <div class="container mt-4">
        <h5 class="mb-4">
            <i class="bi bi-file-earmark-excel me-2"></i>Exportar Reporte de Ventas
        </h5>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('reports.export') }}" method="POST" id="exportForm">
                @csrf
                <div class="mb-3">
                    <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="mb-3">
                    <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                </div>
                <div class="mb-3">
                    <input type="hidden" name="period" value="custom">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-file-earmark-excel me-2"></i>Exportar Reporte
                </button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        document.getElementById('exportForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const startDate = document.getElementById('fecha_inicio').value;
            const endDate = document.getElementById('fecha_fin').value;

            if (new Date(startDate) > new Date(endDate)) {
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
                return;
            }

            this.submit();
        });
    </script>