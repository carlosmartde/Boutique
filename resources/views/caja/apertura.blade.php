@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(isset($cajaAbierta) && $cajaAbierta)
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">Caja Abierta</div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="mb-3">
                            <strong>Fecha de apertura:</strong> {{ \Carbon\Carbon::parse($cajaAbierta->fecha_apertura)->format('d/m/Y H:i') }}
                        </div>
                        <div class="mb-3">
                            <strong>Monto inicial:</strong> Q{{ number_format($cajaAbierta->monto_inicial, 2) }}
                        </div>
                        <div class="mb-3">
                            <strong>Monto actual:</strong> Q{{ number_format($cajaAbierta->monto_final ?? $cajaAbierta->monto_inicial, 2) }}
                        </div>

                        <form action="{{ route('caja.cerrar') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas cerrar la caja?');">
                            @csrf
                            <button type="submit" class="btn btn-danger">Cerrar Caja</button>
                        </form>
                        <a href="{{ route('caja.movimiento') }}" class="btn btn-outline-primary ms-2">Ingreso/Retiro</a>
                        <a href="{{ route('caja.arqueo') }}" class="btn btn-outline-secondary ms-2">Arqueo</a>

                        <form action="{{ route('caja.cancelar') }}" method="POST" class="mt-3">
                            @csrf
                            <div class="alert alert-warning">
                                <strong>Cancelar caja</strong>: Requiere credenciales de un supervisor (admin/gerente). Esta acción cierra y marca la caja como cancelada.
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="email" name="supervisor_email" class="form-control" placeholder="Email supervisor" required>
                                </div>
                                <div class="col-md-5">
                                    <input type="password" name="supervisor_password" class="form-control" placeholder="Contraseña" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('¿Confirmar cancelación de caja?');">Cancelar</button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input type="text" name="motivo" class="form-control" placeholder="Motivo (opcional)">
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">Apertura de Caja</div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <form action="{{ route('caja.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="monto_inicial" class="form-label">Monto Inicial</label>
                                <input type="number" step="0.01" class="form-control" id="monto_inicial" name="monto_inicial" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Abrir Caja</button>
                        </form>
                    </div>
                </div>
            @endif



            <!--
            <div class="card mt-4">
                <div class="card-header">Historial de Cajas</div>
                <div class="card-body">
                    @if(isset($cajas) && $cajas->count())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Usuario</th>
                                    <th>Monto Inicial</th>
                                    <th>Monto Final</th>
                                    <th>Estado</th>
                                    <th>Fecha Apertura</th>
                                    <th>Fecha Cierre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cajas as $caja)
                                    <tr>
                                        <td>{{ $caja->id }}</td>
                                        <td>{{ $caja->user->name ?? '-' }}</td>
                                        <td>{{ $caja->monto_inicial }}</td>
                                        <td>{{ $caja->monto_final ?? '-' }}</td>
                                        <td>{{ ucfirst($caja->estado) }}</td>
                                        <td>{{ $caja->fecha_apertura }}</td>
                                        <td>{{ $caja->fecha_cierre ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No hay cajas registradas.</p>
                    @endif
                </div>
            </div>

-->


        </div>
    </div>
</div>
@endsection