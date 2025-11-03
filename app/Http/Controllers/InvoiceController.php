<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
    {    public function store(Request $request)
    {
        $sale = Sale::with('details.product')->findOrFail($request->sale_id);
        $total = $sale->total;

        // Determinar si es C/F según la intención del usuario
        // o valores clásicos de C/F en el formulario
        $isCF = (
            $request->boolean('is_cf') ||
            ($request->customer_name === 'Consumidor Final' && $request->customer_nit === 'C/F')
        );

        // Si el total es >= Q2,500 y los datos/intención son C/F, rechazar
        if ($total >= 2500 && $isCF) {
            return response()->json([
                'success' => false,
                'message' => 'Para ventas de Q2,500 o más, todos los datos del cliente son obligatorios.'
            ], 422);
        }
        // Validar y construir la factura
        if (!$isCF) {
            $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'customer_name' => 'required|string|max:255',
                'customer_nit' => 'required|string|max:20',
                'customer_address' => 'required|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'payment_method' => 'required|in:cash,card,transfer'
            ]);
            
            $invoice = new Invoice($request->all());
            $invoice->is_cf = false;
        } else {
            // Para C/F, usar valores por defecto
            $invoice = new Invoice([
                'sale_id' => $sale->id,
                'customer_name' => 'Consumidor Final',
                'customer_nit' => 'C/F',
                'customer_address' => 'Ciudad',
                'customer_phone' => 'N/A',
                'customer_email' => 'N/A',
                'payment_method' => 'cash',
                'is_cf' => true
            ]);
        }
        
        $invoice->invoice_number = $invoice->generateInvoiceNumber();
        $invoice->total = $total;
        $invoice->save();

        if ($request->print) {
            return $this->generatePDF($invoice);
        }

        return response()->json([
            'success' => true,
            'message' => 'Factura generada correctamente',
            'invoice' => $invoice
        ]);
    }

    public function index(Request $request)
    {
        $query = Invoice::with('sale')->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('period')) {
            $date = now();
            switch ($request->period) {
                case 'day':
                    $query->whereDate('created_at', $date);
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        $date->startOfWeek(),
                        $date->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', $date->year);
                    break;
            }
        }

        $invoices = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json($invoices);
        }

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        return $this->generatePDF($invoice);
    }

    protected function generatePDF(Invoice $invoice)
    {
        $sale = $invoice->sale->load('details.product', 'user');
    $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'sale'));
        
        return $pdf->stream("factura-{$invoice->invoice_number}.pdf");
    }
}
