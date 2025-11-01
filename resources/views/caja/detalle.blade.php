@extends('layouts.app')

@section('title', 'Detalle de Caja')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detalle de Caja #{{ $caja->id }}</h4>
        <div>
            <a href="{{ route('caja.reporte') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver al reporte
            </a>
            <button class="btn btn-success btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div><strong>Cajero:</strong></div>
                    <div>{{ $caja->user->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>Estado:</strong></div>
                    <div>
                        @if($caja->cancelada)
                            <span class="badge bg-danger"><i class="bi bi-x-octagon me-1"></i>Cancelada</span>
                        @elseif($caja->estado === 'abierto')
                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Abierta</span>
                        @else
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Cerrada</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div><strong>Monto Inicial:</strong></div>
                    <div>Q{{ number_format($caja->monto_inicial, 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>Monto Final:</strong></div>
                    <div>Q{{ number_format($caja->monto_final ?? $caja->monto_inicial, 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>Apertura:</strong></div>
                    <div>{{ \Carbon\Carbon::parse($caja->fecha_apertura)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>Cierre:</strong></div>
                    <div>{{ $caja->fecha_cierre ? \Carbon\Carbon::parse($caja->fecha_cierre)->format('d/m/Y H:i') : '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>Saldo Teórico:</strong></div>
                    <div>Q{{ number_format($saldo_teorico, 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>Saldo Real (Arqueo):</strong></div>
                    <div>{{ $caja->saldo_real !== null ? 'Q' . number_format($caja->saldo_real, 2) : '-' }}</div>
                </div>
                @if($caja->saldo_real !== null)
                    <div class="col-md-3">
                        <div><strong>Diferencia:</strong></div>
                        @php $dif = $caja->saldo_real - $saldo_teorico; @endphp
                        <div>
                            <span class="badge {{ $dif == 0 ? 'bg-success' : ($dif > 0 ? 'bg-primary' : 'bg-danger') }}">
                                {{ $dif > 0 ? '+' : '' }}Q{{ number_format($dif, 2) }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($caja->cancelada)
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-x-octagon me-1"></i> Información de Cancelación
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div><strong>Autorizado por:</strong></div>
                        <div>{{ $caja->cancelAutorizadoPor->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div><strong>Fecha de cancelación:</strong></div>
                        <div>{{ $caja->cancelado_en ? \Carbon\Carbon::parse($caja->cancelado_en)->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div><strong>Motivo:</strong></div>
                        <div>{{ $cancelMovimiento->descripcion ?? 'No especificado' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($caja->observacion)
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-journal-text me-1"></i> Observación de Arqueo
            </div>
            <div class="card-body">
                {{ $caja->observacion }}
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <i class="bi bi-list-ul me-1"></i> Movimientos de Caja
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th>Autorizado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caja->movimientos as $mov)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @switch($mov->tipo)
                                    @case('venta') <span class="badge bg-success">Venta</span> @break
                                    @case('ingreso') <span class="badge bg-primary">Ingreso</span> @break
                                    @case('retiro') <span class="badge bg-warning text-dark">Retiro</span> @break
                                    @case('ajuste') <span class="badge bg-info text-dark">Ajuste</span> @break
                                    @case('cancelacion') <span class="badge bg-danger">Cancelación</span> @break
                                @endswitch
                            </td>
                            <td>Q{{ number_format($mov->monto, 2) }}</td>
                            <td>{{ $mov->descripcion }}</td>
                            <td>{{ $mov->user->name ?? '-' }}</td>
                            <td>{{ $mov->autorizadoPor->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Sin movimientos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(request()->boolean('print'))
<script>window.addEventListener('load', () => window.print());</script>
@endif
@endsection
