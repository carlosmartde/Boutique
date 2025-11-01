<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use Illuminate\Support\Facades\Auth;

class ReporteCajaController extends Controller
{
    public function reporte(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'gerente'])) {
            abort(403, 'No autorizado');
        }

        $query = Caja::query()->with('user');

        // Filtros por fecha
        $filtro = $request->input('filtro', 'dia');
        $fecha = $request->input('fecha', now()->toDateString());

        switch ($filtro) {
            case 'dia':
                $query->whereDate('fecha_apertura', $fecha);
                break;
            case 'semana':
                $query->whereBetween('fecha_apertura', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'mes':
                $query->whereMonth('fecha_apertura', now()->month)
                      ->whereYear('fecha_apertura', now()->year);
                break;
            case 'anio':
                $query->whereYear('fecha_apertura', now()->year);
                break;
        }

        $cajas = $query->orderByDesc('fecha_apertura')->get();
        return view('caja.reporte', compact('cajas', 'filtro', 'fecha'));
    }

    public function detalle(Caja $caja)
    {
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'gerente'])) {
            abort(403, 'No autorizado');
        }

        $caja->load([
            'user',
            'movimientos' => function ($q) { $q->orderBy('created_at'); },
            'movimientos.user',
            'movimientos.autorizadoPor',
            'cancelAutorizadoPor',
        ]);

        $saldo_teorico = $caja->monto_final ?? $caja->monto_inicial;
        $cancelMovimiento = $caja->movimientos->where('tipo', 'cancelacion')->last();

        return view('caja.detalle', compact('caja', 'saldo_teorico', 'cancelMovimiento'));
    }
}