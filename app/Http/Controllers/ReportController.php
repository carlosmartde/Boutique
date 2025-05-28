<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period ?? 'day';
        $date = $request->date ?? Carbon::now()->format('Y-m-d');
        $userId = $request->user_id ?? 'all';
        $month = $request->month;

        $users = User::orderBy('name')->get();
        $salesQuery = $this->getSalesByPeriod($period, $date, $userId);
        
        $saleIds = $salesQuery->pluck('sales.id');
        $totales = SaleDetail::whereIn('sale_id', $saleIds)
            ->selectRaw('SUM(cost_total) as total_cost, SUM(subtotal - cost_total) as total_profit')
            ->first();

        $totalCost = $totales->total_cost ?? 0;
        $totalProfit = $totales->total_profit ?? 0;
        $totalSales = $salesQuery->sum('total');
        $sales = $salesQuery->paginate(10);

        return view('reports.index', compact(
            'sales', 'period', 'date', 'users', 'userId', 'totalCost', 'totalProfit', 'totalSales'
        ));
    }

    private function getSalesByPeriod($period, $date, $userId)
    {
        $query = Sale::with('user')
                    ->select('sales.*', 'users.name as user_name')
                    ->join('users', 'sales.user_id', '=', 'users.id');
        
        if ($userId !== 'all') {
            $query->where('sales.user_id', $userId);
        }
        
        if ($period === 'month' && request('month') !== null && request('month') !== '') {
            $month = intval(request('month'));
            $query->whereMonth('sales.created_at', $month);
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

    private function getMonthName($month)
    {
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        return $months[$month] ?? '';
    }

    public function export(Request $request)
    {
        $period = $request->period ?? 'day';
        $date = $request->date ?? Carbon::now()->format('Y-m-d');
        $userId = $request->user_id ?? 'all';
        $month = $request->month ? intval($request->month) : null;

        $fileName = 'reporte_ventas_';
        switch ($period) {
            case 'day':
                $fileName .= Carbon::parse($date)->format('d-m-Y');
                break;
            case 'week':
                $fileName .= Carbon::parse($date)->startOfWeek()->format('d-m-Y') . '_a_' . 
                           Carbon::parse($date)->endOfWeek()->format('d-m-Y');
                break;
            case 'month':
                if ($month) {
                    $fileName .= 'mes_de_' . $this->getMonthName($month) . '_' . 
                               Carbon::parse($date)->format('Y');
                } else {
                    $fileName .= Carbon::parse($date)->format('d-m-Y');
                }
                break;
            case 'year':
                $fileName .= Carbon::parse($date)->format('d-m-Y');
                break;
        }
        $fileName .= '.xlsx';

        return Excel::download(new SalesExport($period, $date, $userId, $month), $fileName);
    }

    public function detail($id)
    {
        $sale = Sale::with(['details.product', 'user'])->findOrFail($id);
        return view('reports.detail', compact('sale'));
    }
}