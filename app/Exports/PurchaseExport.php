<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PurchaseExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $period;
    protected $date;
    protected $userId;
    protected $month;

    public function __construct($period, $date, $userId, $month = null)
    {
        $this->period = $period;
        $this->date = $date;
        $this->userId = $userId;
        $this->month = $month;
    }

    public function collection()
    {
        $query = Purchase::with(['user', 'details.product'])
            ->select('purchases.*', 'users.name as user_name')
            ->join('users', 'purchases.user_id', '=', 'users.id');

        if ($this->userId !== 'all') {
            $query->where('purchases.user_id', $this->userId);
        }

        if ($this->period === 'month' && !empty($this->month)) {
            $query->whereMonth('purchases.created_at', $this->month);
            $year = Carbon::parse($this->date)->year;
            $query->whereYear('purchases.created_at', $year);
        } else {
            switch ($this->period) {
                case 'day':
                    $query->whereDate('purchases.created_at', $this->date);
                    break;
                case 'week':
                    $startOfWeek = Carbon::parse($this->date)->startOfWeek();
                    $endOfWeek = Carbon::parse($this->date)->endOfWeek();
                    $query->whereBetween('purchases.created_at', [$startOfWeek, $endOfWeek]);
                    break;
                case 'month':
                    $startOfMonth = Carbon::parse($this->date)->startOfMonth();
                    $endOfMonth = Carbon::parse($this->date)->endOfMonth();
                    $query->whereBetween('purchases.created_at', [$startOfMonth, $endOfMonth]);
                    break;
                case 'year':
                    $year = Carbon::parse($this->date)->year;
                    $query->whereYear('purchases.created_at', $year);
                    break;
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Información de Compra',
            'ID',
            'Usuario',
            'Proveedor',
            'Fecha',
            'Total Compra (Q)',
            'Notas',
            'Detalles de Productos',
            'Código',
            'Nombre',
            'Marca',
            'Cantidad',
            'Precio Unitario (Q)',
            'Subtotal (Q)'
        ];
    }

    public function map($purchase): array
    {
        $rows = [];
        $first = true;

        foreach ($purchase->details as $detail) {
            if ($first) {
                $rows[] = [
                    'Información de Compra',
                    $purchase->id,
                    $purchase->user_name,
                    $purchase->supplier_name ?? 'N/A',
                    $purchase->created_at->format('d/m/Y H:i:s'),
                    'Q ' . number_format($purchase->total, 2),
                    $purchase->notes ?? 'N/A',
                    'Detalles de Productos',
                    $detail->product->code,
                    $detail->product->name,
                    $detail->product->brand,
                    $detail->quantity,
                    'Q ' . number_format($detail->price, 2),
                    'Q ' . number_format($detail->subtotal, 2)
                ];
                $first = false;
            } else {
                $rows[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $detail->product->code,
                    $detail->product->name,
                    $detail->product->brand,
                    $detail->quantity,
                    'Q ' . number_format($detail->price, 2),
                    'Q ' . number_format($detail->subtotal, 2)
                ];
            }
        }

        // Agregar una línea en blanco después de cada compra
        $rows[] = array_fill(0, 14, '');

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para encabezados
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3A86FF'],
            ],
        ]);

        // Obtener la última fila
        $lastRow = $sheet->getHighestRow();

        // Identificar y aplicar estilo a las filas con "Detalles de Productos"
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($sheet->getCell('H' . $row)->getValue() === 'Detalles de Productos') {
                $sheet->getStyle('H' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '3A86FF'],
                    ],
                ]);
            }
        }

        // Aplicar bordes y alineación
        $sheet->getStyle('A1:N' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Ajustar el ancho de las columnas
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Alinear las columnas numéricas a la derecha
        $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal('right');
        $sheet->getStyle('L2:N' . $lastRow)->getAlignment()->setHorizontal('right');

        // Resaltar las filas de información principal
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($sheet->getCell('A' . $row)->getValue() === 'Información de Compra') {
                $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F2F5'],
                    ],
                    'font' => ['bold' => true],
                ]);
            }
        }

        return $sheet;
    }
}
