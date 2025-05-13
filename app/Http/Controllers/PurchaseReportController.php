<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseReportController extends Controller
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
        
        return null;
    }

    public function index(Request $request)
    {
        // Verificar permisos
        $redirect = $this->checkRole(['admin']);
        if ($redirect) return $redirect;

        $period = $request->period ?? 'day';
        $date = $request->date ?? Carbon::now()->format('Y-m-d');
        $userId = $request->user_id ?? 'all';

        $users = User::orderBy('name')->get();

        // Obtener todas las compras en el rango
        $purchasesQuery = $this->getPurchasesByPeriod($period, $date, $userId);
        $purchases = $purchasesQuery->paginate(10);

        // Obtener todos los IDs de las compras en esta consulta
        $purchaseIds = $purchasesQuery->pluck('purchases.id');

        // Obtener los detalles de compra relacionados y sumar totales
        $totales = PurchaseDetail::whereIn('purchase_id', $purchaseIds)
            ->selectRaw('SUM(subtotal) as total_amount')
            ->first();

        $totalAmount = $totales->total_amount ?? 0;

        return view('purchase_reports.index', compact(
            'purchases', 'period', 'date', 'users', 'userId', 'totalAmount'
        ));
    }

    public function detail($id)
    {
        // Verificar permisos
        $redirect = $this->checkRole(['admin']);
        if ($redirect) return $redirect;

        $purchase = Purchase::with(['details.product', 'user'])->findOrFail($id);
        
        return view('purchase_reports.detail', compact('purchase'));
    }

    private function getPurchasesByPeriod($period, $date, $userId)
    {
        $query = Purchase::with('user')
                    ->select('purchases.*', 'users.name as user_name')
                    ->join('users', 'purchases.user_id', '=', 'users.id');
        
        if ($userId !== 'all') {
            $query->where('purchases.user_id', $userId);
        }
        
        // Solo aplicar filtro de mes si el periodo es 'month'
        if ($period === 'month' && request()->has('month') && request('month') !== '') {
            $query->whereMonth('purchases.created_at', request('month'));
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
} 