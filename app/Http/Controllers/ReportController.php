<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function checkRole($allowedRoles = ['admin'])
{
    $userRole = Auth::user()->rol ?? null;
    
    if ($userRole === 'gerente') {
        return null; // Gerente tiene acceso total
    }
    
    if (!$userRole || !in_array($userRole, $allowedRoles)) {
        if ($userRole === 'vendedor') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        
        return redirect()->route('login');
    }
    
    return null; // No redirect needed
}
public function index(Request $request)
{
    $period = $request->period ?? 'day';
    $date = $request->date ?? Carbon::now()->format('Y-m-d');
    $userId = $request->user_id ?? 'all';

    $users = User::orderBy('name')->get();

    // Obtener todas las ventas en el rango (sin paginar aún)
    $salesQuery = $this->getSalesByPeriod($period, $date, $userId);

    // Obtener todos los IDs de las ventas en esta consulta
    $saleIds = $salesQuery->pluck('sales.id');

    // Obtener los detalles de venta relacionados y sumar totales
    $totales = SaleDetail::whereIn('sale_id', $saleIds)
        ->selectRaw('SUM(cost_total) as total_cost, SUM(subtotal - cost_total) as total_profit')
        ->first();

    $totalCost = $totales->total_cost ?? 0;
    $totalProfit = $totales->total_profit ?? 0;

    // Calcular el total de ventas según el filtro
    $totalSales = $salesQuery->sum('total');

    // Paginar las ventas para la vista
    $sales = $salesQuery->paginate(10);

    return view('reports.index', compact(
        'sales', 'period', 'date', 'users', 'userId', 'totalCost', 'totalProfit', 'totalSales'
    ));
}

    
    public function detail($id)
    {
        $sale = Sale::with(['details.product', 'user'])->findOrFail($id);
        
        return view('reports.detail', compact('sale'));
    }
    
    
    private function getSalesByPeriod($period, $date, $userId)
    {
        $query = Sale::with('user')
                    ->select('sales.*', 'users.name as user_name')
                    ->join('users', 'sales.user_id', '=', 'users.id');
        
        if ($userId !== 'all') {
            $query->where('sales.user_id', $userId);
        }
        
        // Solo aplicar filtro de mes si el periodo es 'month'
        if ($period === 'month' && request()->has('month') && request('month') !== '') {
            $query->whereMonth('sales.created_at', request('month'));
            $year = Carbon::parse($date)->year;
            $query->whereYear('sales.created_at', $year);
        } else {
            switch ($period) {
                case 'day':
                    $query->whereDate('sales.created_at', $date);
                    break;
                case 'week':
                    $startOfWeek = Carbon::parse($date)->startOfWeek();
                    $endOfWeek = Carbon::parse($date)->endOfWeek();
                    $query->whereBetween('sales.created_at', [$startOfWeek, $endOfWeek]);
                    break;
                case 'month':
                    $startOfMonth = Carbon::parse($date)->startOfMonth();
                    $endOfMonth = Carbon::parse($date)->endOfMonth();
                    $query->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth]);
                    break;
                case 'year':
                    $year = Carbon::parse($date)->year;
                    $query->whereYear('sales.created_at', $year);
                    break;
            }
        }
        
        return $query->orderBy('sales.created_at', 'desc');
    }
}