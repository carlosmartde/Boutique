@extends('layouts.app')

@section('title', 'Detalle de Compra')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-cart-check me-2"></i>Detalle de Compra #{{ $purchase->id }}
        </h5>
        <a href="{{ route('purchase_reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver a Reportes
        </a>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-info-circle me-2"></i>Información de la Compra
                        </h5>
                        <div class="mb-2">
                            <strong>ID:</strong> {{ $purchase->id }}
                        </div>
                        <div class="mb-2">
                            <strong>Usuario:</strong> {{ $purchase->user->name }}
                        </div>
                        <div class="mb-2">
                            <strong>Proveedor:</strong> {{ $purchase->supplier_name ?? 'N/A' }}
                        </div>
                        <div class="mb-2">
                            <strong>Fecha y Hora:</strong> {{ $purchase->created_at->format('d/m/Y H:i:s') }}
                        </div>
                        <div class="mb-2">
                            <strong>Total:</strong> 
                            <span class="badge bg-primary fs-6">Q{{ number_format($purchase->total, 2) }}</span>
                        </div>
                        @if($purchase->notes)
                        <div class="mb-2">
                            <strong>Notas:</strong>
                            <p class="mb-0">{{ $purchase->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-3">
            <i class="bi bi-box-seam me-2"></i>Productos Comprados
        </h5>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->details as $detail)
                        <tr>
                            <td>{{ $detail->product->code }}</td>
                            <td>{{ $detail->product->name }}</td>
                            <td>{{ $detail->product->brand }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Q{{ number_format($detail->price, 2) }}</td>
                            <td>Q{{ number_format($detail->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total:</th>
                        <th>Q{{ number_format($purchase->total, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection 