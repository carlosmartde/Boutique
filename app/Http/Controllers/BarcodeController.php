<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use PDF;

class BarcodeController extends Controller
{
    public function index()
    {
        return view('barcodes.index');
    }

    public function generateRandom()
    {        
        do {
            $code = mt_rand(1000000000, 9999999999); // Generate 10-digit number
            $exists = Product::where('code', $code)->exists();
        } while ($exists);

        return response()->json(['code' => $code]);
    }

    public function generatePDF(Request $request)
    {
        $request->validate([
            'barcode' => 'required|numeric|digits_between:8,13',
            'copies' => 'required|numeric|min:1|max:100'
        ]);

        $barcode = $request->barcode;
        $copies = $request->copies;
        $generator = new BarcodeGeneratorPNG();
        
        // Generar código de barras de tamaño específico (2x1 proporción para 4x2 cm)
        $barcodeImage = base64_encode($generator->getBarcode($barcode, $generator::TYPE_CODE_128, 2, 40));
        
        $pdf = PDF::loadView('barcodes.pdf', [
            'barcode' => $barcode,
            'barcodeImage' => $barcodeImage,
            'copies' => $copies
        ]);
        
        // Configurar papel y orientación
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf->download('barcodes_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}