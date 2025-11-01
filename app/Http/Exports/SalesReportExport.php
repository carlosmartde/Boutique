<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class SalesReportExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $sales;
    protected $totals;
    protected $period;
    protected $fechaInicio;
    protected $fechaFin;
    protected $userName;

    public function __construct($sales, $totals, $period, $fechaInicio, $fechaFin, $userName = 'Todos')
    {
        $this->sales = $sales;
        $this->totals = $totals;
        $this->period = $period;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->userName = $userName;
    }

    public function array(): array
    {
        $data = [];
        
        // Fila 1: Título principal
        $data[] = ['REPORTE DE VENTAS - ' . strtoupper($this->getPeriodText()), '', '', '', '', '', '', ''];
        
        // Fila 2: Vacía (salto de línea)
        $data[] = ['', '', '', '', '', '', '', ''];
        
        // Filas 3-7: Información del reporte (izquierda) y Resumen financiero (derecha)
        $data[] = [
            'Período:', 
            $this->getPeriodText(), 
            '', 
            '', 
            'RESUMEN FINANCIERO', 
            '', 
            '', 
            ''
        ];
        $data[] = [
            'Usuario:', 
            $this->userName, 
            '', 
            '', 
            'Total en Ventas:', 
            'Q ' . number_format($this->totals['totalSales'], 2), 
            '', 
            ''
        ];
        $data[] = [
            'Fecha de Generación:', 
            Carbon::now()->format('d/m/Y H:i:s'), 
            '', 
            '', 
            'Total en Costos:', 
            'Q ' . number_format($this->totals['totalCost'], 2), 
            '', 
            ''
        ];
        $data[] = [
            'Total de Registros:', 
            count($this->sales), 
            '', 
            '', 
            'Total en Ganancias:', 
            'Q ' . number_format($this->totals['totalProfit'], 2), 
            '', 
            ''
        ];
        $data[] = [
            '', 
            '', 
            '', 
            '', 
            'Margen de Ganancia:', 
            $this->totals['totalSales'] > 0 ? number_format(($this->totals['totalProfit'] / $this->totals['totalSales']) * 100, 2) . '%' : '0%', 
            '', 
            ''
        ];
        
        // Fila 8: Vacía (salto de línea)
        $data[] = ['', '', '', '', '', '', '', ''];
        
        // Fila 9: Título "DETALLE DE VENTAS"
        $data[] = ['DETALLE DE VENTAS', '', '', '', '', '', '', ''];
        
        // Fila 10: Vacía (salto de línea)
        $data[] = ['', '', '', '', '', '', '', ''];
        
        // Fila 11: Headers
        $data[] = [
            'ID de Venta',
            'Usuario',
            'Fecha de Venta',
            'Total de Venta (Q)',
            'Costo Total (Q)',
            'Ganancia (Q)',
            'Margen (%)',
            'Reporte'
        ];
        
        // Filas 12+: Datos de ventas
        foreach ($this->sales as $sale) {
            $total = is_array($sale) ? ($sale['total'] ?? 0) : ($sale->total ?? 0);
            $cost = is_array($sale) ? ($sale['total_cost'] ?? 0) : ($sale->total_cost ?? 0);
            $profit = $total - $cost;
            $percent = $total > 0 ? ($profit / $total) * 100 : 0;
            $id = is_array($sale) ? ($sale['id'] ?? '') : $sale->id;
            
            // Usar la ruta real del detalle de reporte
            $url = $id ? (function_exists('route') ? route('reports.detail', ['id' => $id]) : url('/reporte/venta/' . $id)) : '';

            // Formatear la fórmula de hipervínculo
            $hyperlink = $url ? '=HYPERLINK("' . $url . '","Ver Reporte")' : 'Sin URL';

            $data[] = [
                $id,
                is_array($sale) ? ($sale['user_name'] ?? '') : $sale->user_name,
                is_array($sale)
                    ? (isset($sale['created_at']) ? $sale['created_at'] : '')
                    : (isset($sale->created_at) ? Carbon::parse($sale->created_at)->format('d/m/Y H:i:s') : ''),
                number_format($total, 2),
                number_format($cost, 2),
                number_format($profit, 2),
                number_format($percent, 2) . '%',
                $hyperlink
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $dataStartRow = 12; // Fila donde empiezan los datos reales
        $highestRow = $sheet->getHighestRow();

        // Estilo para el título principal (A1:H1)
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => 'FFFFFFFF'],
                'name' => 'Arial Black',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F4E79'],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THICK,
                    'color' => ['argb' => 'FF0F2A44'],
                ],
            ],
        ]);

        // Estilo para las etiquetas de información del reporte (A3:A6)
        $sheet->getStyle('A3:A6')->applyFromArray([
            'font' => [
                'bold' => true, 
                'size' => 11, 
                'name' => 'Calibri',
                'color' => ['argb' => 'FF2F5597'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F3F4'],
            ],
        ]);

        // Estilo para los valores de información del reporte (B3:B6)
        $sheet->getStyle('B3:B6')->applyFromArray([
            'font' => ['size' => 11, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F3F4'],
            ],
        ]);

        // Estilo para el título "RESUMEN FINANCIERO" (E3)
        $sheet->getStyle('E3')->applyFromArray([
            'font' => [
                'bold' => true, 
                'size' => 14, 
                'color' => ['argb' => 'FFFFFFFF'],
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF70AD47'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF507E32'],
                ],
            ],
        ]);

        // Estilo para las etiquetas del resumen financiero (E4:E7)
        $sheet->getStyle('E4:E7')->applyFromArray([
            'font' => [
                'bold' => true, 
                'size' => 11, 
                'name' => 'Calibri',
                'color' => ['argb' => 'FF507E32'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE2EFDA'],
            ],
        ]);

        // Estilo para los valores del resumen financiero (F4:F7)
        $sheet->getStyle('F4:F7')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11, 
                'name' => 'Calibri',
                'color' => ['argb' => 'FF0D5016'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE2EFDA'],
            ],
        ]);

        // Bordes para el área de información del reporte
        $sheet->getStyle('A3:B6')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF2F5597'],
                ],
                'inside' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF8FAADC'],
                ],
            ],
        ]);

        // Bordes para el área de resumen financiero
        $sheet->getStyle('E3:F7')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF507E32'],
                ],
                'inside' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFA9D18E'],
                ],
            ],
        ]);

        // Estilo para el título "DETALLE DE VENTAS" (A9)
        $sheet->getStyle('A9')->applyFromArray([
            'font' => [
                'bold' => true, 
                'size' => 16, 
                'color' => ['argb' => 'FFFFFFFF'],
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFC65911'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF8B4513'],
                ],
            ],
        ]);

        // CORREGIDO: Estilo para los headers de la tabla (A11:H11)
        $sheet->getStyle('A11:H11')->applyFromArray([
            'font' => [
                'bold' => true, 
                'size' => 12, 
                'name' => 'Calibri',
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4472C4'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF2F5597'],
                ],
            ],
        ]);

        // Estilo para los datos de la tabla
        if ($highestRow > 11) {
            $sheet->getStyle('A12:H' . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFBFBFBF'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'name' => 'Calibri',
                    'size' => 10,
                ],
            ]);

            // Alternar colores de fila para mejor legibilidad
            for ($row = 12; $row <= $highestRow; $row++) {
                if (($row - 12) % 2 == 1) {
                    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Estilo especial para columnas de dinero (D, E, F)
        if ($highestRow > 11) {
            $sheet->getStyle('D12:F' . $highestRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FF0D5016'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);

            // Estilo para columna de porcentaje (G)
            $sheet->getStyle('G12:G' . $highestRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FF833C0C'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Estilo para columna de hipervínculos (H)
            $sheet->getStyle('H12:H' . $highestRow)->applyFromArray([
                'font' => [
                    'underline' => true,
                    'color' => ['argb' => 'FF0563C1'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // ID de Venta
            'B' => 18, // Usuario
            'C' => 20, // Fecha de Venta
            'D' => 18, // Total de Venta
            'E' => 16, // Costo Total
            'F' => 16, // Ganancia
            'G' => 18, // Margen
            'H' => 16, // Reporte
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Combinar celdas para el título principal
                $sheet->mergeCells('A1:H1');
                $sheet->getRowDimension(1)->setRowHeight(35);

                // Combinar celdas para el título "RESUMEN FINANCIERO"
                $sheet->mergeCells('E3:F3');
                
                // Combinar celdas para el título "DETALLE DE VENTAS"
                $sheet->mergeCells('A9:H9');
                
                // Ajustar altura de filas importantes
                $sheet->getRowDimension(3)->setRowHeight(25);  // Primera fila de info/resumen
                $sheet->getRowDimension(9)->setRowHeight(30);  // Detalle de ventas
                $sheet->getRowDimension(11)->setRowHeight(25); // Headers (CORREGIDO)

                // Ajustar altura de filas de información
                for ($i = 4; $i <= 7; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(22);
                }

                // Congelar paneles después de los headers
                $sheet->freezePane('A12');
                
                // Proteger las celdas con fórmulas de hipervínculo
                $sheet->getProtection()->setSheet(false);
            },
        ];
    }

    private function getPeriodText()
    {
        switch ($this->period) {
            case 'day':
                return 'Día ' . Carbon::parse($this->fechaInicio)->format('d/m/Y');
            case 'week':
                return 'Semana del ' . Carbon::parse($this->fechaInicio)->format('d/m/Y');
            case 'month':
                return 'Mes ' . Carbon::parse($this->fechaInicio)->format('m/Y');
            case 'year':
                return 'Año ' . Carbon::parse($this->fechaInicio)->format('Y');
            case 'custom':
                return Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($this->fechaFin)->format('d/m/Y');
            default:
                return 'Período personalizado';
        }
    }
} 