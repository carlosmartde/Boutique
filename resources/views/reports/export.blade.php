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
                    <label for="start_date" class="form-label">Fecha de Inicio:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="mb-3">
                    <label for="end_date" class="form-label">Fecha de Fin:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" required>
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
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (new Date(startDate) > new Date(endDate)) {
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
                return;
            }

            this.submit();
        });
    </script>