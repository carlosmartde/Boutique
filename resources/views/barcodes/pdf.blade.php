<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Códigos de Barras</title>
    <style>
        @page {
            size: letter;
            margin: 12.7mm; /* 0.5 inch margin */
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 100%;
        }
        .row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 4mm;
        }
        .barcode-container {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 2mm;
        }
        .barcode-image {
            width: 40mm;  /* 4 cm de ancho */
            height: 20mm; /* 2 cm de alto */
            object-fit: contain;
            max-width: 100%;
        }
        .barcode-number {
            font-size: 12px;
            margin-top: 1mm;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="page">
    @php
        $itemsPerRow = 3;
        $rowCount = ceil($copies / $itemsPerRow);
    @endphp

    @for ($row = 0; $row < $rowCount; $row++)
        <div class="row">
            @for ($col = 0; $col < $itemsPerRow && ($row * $itemsPerRow + $col) < $copies; $col++)
                <div class="barcode-container">
                    <img src="data:image/png;base64,{{ $barcodeImage }}" class="barcode-image">
                    <div class="barcode-number">{{ $barcode }}</div>
                </div>
            @endfor
            
            {{-- Rellenar celdas vacías para mantener la estructura --}}
            @for ($empty = ($row * $itemsPerRow) + $itemsPerRow - $copies; $empty > 0 && $col < $itemsPerRow; $empty--, $col++)
                <div class="barcode-container"></div>
            @endfor
        </div>

        @if(($row + 1) * $itemsPerRow % 21 == 0 && ($row + 1) * $itemsPerRow < $copies)
            </div><div class="page-break"></div><div class="page">
        @endif
    @endfor
    </div>
</body>
</html>