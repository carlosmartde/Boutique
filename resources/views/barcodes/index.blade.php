@extends('layouts.app')

@section('title', 'Generador de Códigos de Barras')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-upc-scan me-2"></i>Generador de Códigos de Barras
        </h5>
    </div>
    
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('barcodes.generate-pdf') }}" method="POST">
            @csrf            <div class="mb-4">
                <label for="barcode" class="form-label fw-bold">Código de Barras</label>
                <div class="input-group">
                    <input type="text" name="barcode" id="barcode" 
                        class="form-control form-control-lg @error('barcode') is-invalid @enderror" 
                        placeholder="Ingrese un código de barras o genere uno aleatorio"
                        pattern="[0-9]{8,13}" 
                        title="El código debe tener entre 8 y 13 dígitos"
                        value="{{ old('barcode') }}">
                    <button type="button" id="generateRandom" class="btn btn-primary">
                        <i class="bi bi-shuffle me-1"></i>Generar Aleatorio
                    </button>
                </div>
                @error('barcode')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="copies" class="form-label fw-bold">Cantidad de Códigos</label>
                <div class="input-group">
                    <input type="number" name="copies" id="copies" 
                        class="form-control form-control-lg @error('copies') is-invalid @enderror" 
                        min="1" max="100" value="{{ old('copies', 10) }}"
                        placeholder="Número de códigos a generar">
                </div>
                @error('copies')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-printer me-2"></i>Generar PDF
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('generateRandom').addEventListener('click', async function() {
        try {
            const response = await fetch('{{ route("barcodes.generate-random") }}');
            const data = await response.json();
            document.getElementById('barcode').value = data.code;
        } catch (error) {
            console.error('Error generating random barcode:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al generar el código aleatorio'
            });
        }
    });
</script>
@endsection
