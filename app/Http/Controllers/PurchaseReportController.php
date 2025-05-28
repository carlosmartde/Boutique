<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\PurchaseExport;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period ?? 'day';
        $date = $request->date ?? Carbon::now()->format('Y-m-d');
        $userId = $request->user_id ?? 'all';
        $month = $request->month;

        $users = User::all();
        $query = $this->getFilteredQuery($period, $date, $userId, $month);

        $purchases = $query->paginate(10);
        $totalAmount = $query->sum('total');

        return view('purchase_reports.index', compact('purchases', 'users', 'period', 'date', 'userId', 'totalAmount'));
    }

    private function getFilteredQuery($period, $date, $userId, $month)
    {
        $query = Purchase::select('purchases.*', 'users.name as user_name')
            ->join('users', 'purchases.user_id', '=', 'users.id');

        if ($userId !== 'all') {
            $query->where('purchases.user_id', $userId);
        }

        if ($period === 'month' && $month !== null && $month !== '') {
            $query->whereMonth('purchases.created_at', $month);
            $year = Carbon::parse($date)->year;
            $query->whereYear('purchases.created_at', $year);
        } else {
            switch ($period) {
                case 'day':
                    $query->whereDate('purchases.created_at', $date);
                    break;
                case 'week':
                    $startOfWeek = Carbon::parse($date)->startOfWeek();
                    $endOfWeek = Carbon::parse($date)->endOfWeek();
                    $query->whereBetween('purchases.created_at', [$startOfWeek, $endOfWeek]);
                    break;
                case 'month':
                    $startOfMonth = Carbon::parse($date)->startOfMonth();
                    $endOfMonth = Carbon::parse($date)->endOfMonth();
                    $query->whereBetween('purchases.created_at', [$startOfMonth, $endOfMonth]);
                    break;
                case 'year':
                    $year = Carbon::parse($date)->year;
                    $query->whereYear('purchases.created_at', $year);
                    break;
            }
        }
        
        return $query->orderBy('purchases.created_at', 'desc');
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

        $fileName = 'reporte_compras_';
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

        return Excel::download(new PurchaseExport($period, $date, $userId, $month), $fileName);
    }

    public function detail($id)
    {
        $purchase = Purchase::with(['details.product', 'user'])->findOrFail($id);
        return view('purchase_reports.detail', compact('purchase'));
    }
}