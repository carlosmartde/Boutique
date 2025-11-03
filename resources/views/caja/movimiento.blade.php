@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-4">
                <div class="card-header">Movimiento de Caja</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form method="POST" action="{{ route('caja.movimiento.guardar') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select" required>
                                <option value="ingreso">Ingreso</option>
                                <option value="retiro">Retiro</option>
                                <option value="ajuste">Ajuste</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" name="monto" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción (opcional)</label>
                            <input type="text" name="descripcion" class="form-control">
                        </div>
                        <hr>
                        <h6>Autorización de Supervisor</h6>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="supervisor_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="supervisor_password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Guardar Movimiento</button>
                        <a href="{{ route('caja.apertura') }}" class="btn btn-secondary ms-2">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
