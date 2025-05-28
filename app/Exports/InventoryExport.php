<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Product::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Nombre',
            'Marca',
            'Precio de Compra',
            'Precio de Venta',
            'Stock Actual'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->code,
            $product->name,
            $product->brand,
            'Q' . number_format($product->purchase_price, 2),
            'Q' . number_format($product->sale_price, 2),
            $product->stock
        ];
    }    public function styles(Worksheet $sheet)
    {
        // Obtener el rango total de la tabla
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();
        $range = 'A1:' . $lastCol . $lastRow;

        // Convertir el rango a tabla
        $sheet->setShowGridlines(false);
        $sheet->fromArray($sheet->toArray(), null, 'A1');
        
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3A86FF']
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF']
                ]
            ],
            // Aplicar borde y alineación a toda la tabla
            $range => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }
}
