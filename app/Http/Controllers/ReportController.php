<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;



class ReportController extends Controller
{
    private function checkRole($allowedRoles = ['admin'])
    {
        $userRole = Auth::user()->rol ?? null;

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
        $userId = $request->user_id ?? 'all';
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? Carbon::now()->format('Y-m-d');

        // Validar que las fechas no sean iguales para período personalizado
        if ($period === 'custom' && $fechaInicio === $fechaFin) {
            return redirect()->back()->with('status', 'La fecha de inicio y fin no pueden ser iguales.');
        }

        $users = User::orderBy('name')->get();

        // Obtener las ventas paginadas
        $salesQuery = $this->getSalesByPeriod($period, $fechaInicio, $fechaFin, $userId);
        $sales = $salesQuery->paginate(10);

        // Obtener totales calculados correctamente
        $totales = $this->calculateTotals($period, $fechaInicio, $fechaFin, $userId);

        return view('reports.index', compact(
            'sales',
            'period',
            'users',
            'userId',
            'fechaInicio',
            'fechaFin'
        ) + $totales);
    }

    private function calculateTotals($period, $fechaInicio, $fechaFin, $userId)
    {
        // Crear query base para obtener los IDs de ventas del período
        $salesQuery = $this->getSalesByPeriod($period, $fechaInicio, $fechaFin, $userId);

        // Obtener solo los IDs de las ventas (sin paginación)
        $saleIds = $salesQuery->pluck('sales.id');

        if ($saleIds->isEmpty()) {
            return [
                'totalSales' => 0,
                'totalCost' => 0,
                'totalProfit' => 0
            ];
        }

        // Calcular totales desde sale_details
        $totales = SaleDetail::whereIn('sale_id', $saleIds)
            ->selectRaw('
            SUM(cost_total) as total_cost,
            SUM(quantity * price) as total_sales
        ')
            ->first();

        $totalCost = $totales->total_cost ?? 0;
        $totalSales = $totales->total_sales ?? 0;
        $totalProfit = $totalSales - $totalCost;

        return [
            'totalSales' => $totalSales,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit
        ];
    }


    public function detail($id)
    {
        $sale = Sale::with(['details.product', 'user'])->findOrFail($id);

        return view('reports.detail', compact('sale'));
    }

    private function getSalesByPeriod($period, $fechaInicio, $fechaFin, $userId)
    {
        $query = Sale::with('user')
            ->select('sales.*', 'users.name as user_name')
            ->join('users', 'sales.user_id', '=', 'users.id');

        if ($userId !== 'all') {
            $query->where('sales.user_id', $userId);
        }

        switch ($period) {
            case 'custom':
                if ($fechaInicio && $fechaFin) {
                    $query->whereDate('sales.created_at', '>=', $fechaInicio)
                        ->whereDate('sales.created_at', '<=', $fechaFin);
                }
                break;
            case 'day':
                $query->whereDate('sales.created_at', $fechaInicio);
                break;
            case 'week':
                $startOfWeek = Carbon::parse($fechaInicio)->startOfWeek();
                $endOfWeek = Carbon::parse($fechaInicio)->endOfWeek();
                $query->whereBetween('sales.created_at', [$startOfWeek, $endOfWeek]);
                break;
            case 'month':
                $startOfMonth = Carbon::parse($fechaInicio)->startOfMonth();
                $endOfMonth = Carbon::parse($fechaInicio)->endOfMonth();
                $query->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth]);
                break;
            case 'year':
                $startOfYear = Carbon::parse($fechaInicio)->startOfYear();
                $endOfYear = Carbon::parse($fechaInicio)->endOfYear();
                $query->whereBetween('sales.created_at', [$startOfYear, $endOfYear]);
                break;
        }

        return $query->orderBy('sales.created_at', 'desc');
    }

    public function export(Request $request)
    {
        // Verificar rol
        $redirectCheck = $this->checkRole(['admin']);
        if ($redirectCheck) {
            return $redirectCheck;
        }

        $period = $request->period ?? 'day';
        $userId = $request->user_id ?? 'all';
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? Carbon::now()->format('Y-m-d');

        // Validar que las fechas no sean iguales para período personalizado
        if ($period === 'custom' && $fechaInicio === $fechaFin) {
            return redirect()->back()->with('error', 'La fecha de inicio y fin no pueden ser iguales para exportar.');
        }

        try {
            // Obtener todas las ventas (sin paginación para exportar todo)
            $salesQuery = $this->getSalesByPeriod($period, $fechaInicio, $fechaFin, $userId);
            
            // Obtener las ventas con los campos necesarios para la exportación
            $sales = $salesQuery->get()->map(function ($sale) {
                // Calcular totales por venta
                $saleDetails = SaleDetail::where('sale_id', $sale->id)
                    ->selectRaw('SUM(cost_total) as total_cost, SUM(quantity * price) as total_sales')
                    ->first();
                
                $sale->total_cost = $saleDetails->total_cost ?? 0;
                $sale->total_sales = $saleDetails->total_sales ?? 0;
                
                return $sale;
            });

            // Obtener totales generales
            $totals = $this->calculateTotals($period, $fechaInicio, $fechaFin, $userId);

            // Obtener nombre del usuario si se filtró por usuario específico
            $userName = 'Todos los usuarios';
            if ($userId !== 'all') {
                $user = User::find($userId);
                $userName = $user ? $user->name : 'Usuario desconocido';
            }

            // Generar nombre del archivo
            $periodText = $this->getPeriodTextForFilename($period);
            $dateText = $period === 'custom' ? 
                Carbon::parse($fechaInicio)->format('Y-m-d') . '_' . Carbon::parse($fechaFin)->format('Y-m-d') :
                Carbon::parse($fechaInicio)->format('Y-m-d');
            
            $filename = "reporte_ventas_{$periodText}_{$dateText}.xlsx";

            // Exportar usando la clase Export
            return Excel::download(
                new SalesReportExport($sales, $totals, $period, $fechaInicio, $fechaFin, $userName),
                $filename
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }

    private function getPeriodTextForFilename($period)
    {
        switch ($period) {
            case 'day':
                return 'dia';
            case 'week':
                return 'semana';
            case 'month':
                return 'mes';
            case 'year':
                return 'año';
            case 'custom':
                return 'personalizado';
            default:
                return 'reporte';
        }
    }
}