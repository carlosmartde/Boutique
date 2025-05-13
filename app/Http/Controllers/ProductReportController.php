<?php

namespace App\Http\Controllers;

use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReportController extends Controller
{
    public function index(Request $request)
    {
        // Obtener el período seleccionado
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Construir la consulta base
        $query = SaleDetail::select(
            'products.name',
            'products.code',
            'products.brand',
            DB::raw('SUM(sale_details.quantity) as total_quantity'),
            DB::raw('SUM(sale_details.subtotal) as total_sales')
        )
        ->join('products', 'sale_details.product_id', '=', 'products.id')
        ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
        ->groupBy('products.id', 'products.name', 'products.code', 'products.brand');

        // Aplicar filtros de fecha
        if ($startDate && $endDate) {
            $query->whereBetween('sales.created_at', [$startDate, $endDate]);
        } else {
            switch ($period) {
                case 'day':
                    $query->whereDate('sales.created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('sales.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('sales.created_at', now()->month)
                          ->whereYear('sales.created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('sales.created_at', now()->year);
                    break;
            }
        }

        // Obtener productos más vendidos
        $topProducts = (clone $query)
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Obtener productos menos vendidos
        $leastProducts = (clone $query)
            ->orderBy('total_quantity', 'asc')
            ->limit(10)
            ->get();

        // Obtener productos con mayor ingreso
        $topRevenueProducts = (clone $query)
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();

        // Obtener productos con menor ingreso
        $leastRevenueProducts = (clone $query)
            ->orderBy('total_sales', 'asc')
            ->limit(10)
            ->get();

        return view('reports.product_analysis', compact(
            'topProducts',
            'leastProducts',
            'topRevenueProducts',
            'leastRevenueProducts',
            'period',
            'startDate',
            'endDate'
        ));
    }
} 