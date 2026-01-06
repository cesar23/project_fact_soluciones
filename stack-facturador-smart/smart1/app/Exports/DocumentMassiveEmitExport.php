<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class DocumentMassiveEmitExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $results;
    protected $rowsWithErrors = [];

    public function __construct($data, $results)
    {
        $this->data = $data;
        $this->results = $results;
    }

    public function collection()
    {
        return new Collection($this->data);
    }

    public function headings(): array
    {
        return [
            'Número',
            'RUC',
            'Código interno',
            'Descripción',
            'Cantidad',
            'Moneda',
            'Precio unitario',
            'Código detracción',
            'Fecha vencimiento',
            'Resultado'
        ];
    }

    public function map($row): array
    {
        $invoice_number = $row[0] ?: '';
        $result = '';
        $hasError = false;

        // Buscar el resultado para este documento
        if (isset($this->results["documento_{$invoice_number}"])) {
            $documentResult = $this->results["documento_{$invoice_number}"];
            if (isset($documentResult['number_full'])) {
                $result = $documentResult['number_full'];
            } else if (isset($documentResult['errores'])) {
                $result = 'ERROR: ' . implode(', ', $documentResult['errores']);
                $hasError = true;
            }
        } else {
            $result = 'Sin procesar';
            $hasError = true;
        }

        // Guardar el índice actual + 2 (por el encabezado y que Excel empieza en 1)
        if ($hasError) {
            $currentIndex = array_search($row, $this->data) + 2;
            $this->rowsWithErrors[] = $currentIndex;
        }

        return [
            $row[0] ?: '', // Número
            $row[1] ?: '', // RUC
            $row[2] ?: '', // Código interno
            $row[3] ?: '', // Descripción
            $row[4] ?: '', // Cantidad
            $row[5] ?: '', // Moneda
            $row[6] ?: '', // Precio unitario
            $row[7] ?: '', // Código detracción
            $row[8] ?: '', // Fecha vencimiento
            $result      // Resultado
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el encabezado
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E9ECEF',
                ],
            ],
        ]);

        // Aplicar color rojo a las filas con errores
        foreach ($this->rowsWithErrors as $row) {
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FF0000'],
                ],
            ]);
        }

        // Ajustar el ancho de las columnas
        foreach(range('A','H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
} 