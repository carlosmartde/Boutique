@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-4">
                <div class="card-header">Arqueo de Caja</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="mb-3">
                        <strong>Saldo teórico:</strong> Q{{ number_format($saldo_teorico, 2) }}
                    </div>
                    <form method="POST" action="{{ route('caja.arqueo.guardar') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Saldo real contado</label>
                            <input type="number" step="0.01" name="saldo_real" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observación (opcional)</label>
                            <textarea name="observacion" class="form-control"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Guardar Arqueo</button>
                        <a href="{{ route('caja.apertura') }}" class="btn btn-secondary ms-2">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
