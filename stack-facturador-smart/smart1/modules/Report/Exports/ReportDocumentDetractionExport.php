<?php

namespace Modules\Report\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportDocumentDetractionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithEvents
{
    use Exportable;

    public $records;
    public $company;
    
    public function records($records) {
        $this->records = $records;
        return $this;
    }

    public function company($company) {
        $this->company = $company;
        return $this;
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            '#',
            'Fecha de detracción',
            'Comprobante',
            'Tipo documento',
            'Cliente',
            'Número cliente',
            'Constancia Pago',
            'Total detracción'
        ];
    }

    public function map($row): array
    {
        return [
            $row['id'] ?? '',
            $row['date_of_issue'] ?? '',
            $row['number'] ?? '',
            $row['document_type_description'] ?? '',
            $row['customer_name'] ?? '',
            $row['customer_number'] ?? '',
            isset($row['detraction']->pay_constancy) ? $row['detraction']->pay_constancy : '-',
            isset($row['detraction']->amount) ? number_format($row['detraction']->amount, 2) : '0.00'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Insertar filas de encabezado
                $sheet->insertNewRowBefore(1, 2);
                
                // Fila 1: Título centrado
                $sheet->setCellValue('A1', 'Reporte de Detracciones '.date('Y-m-d'));
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                
                // Fila 2: Información de la empresa
                if ($this->company) {
                    $sheet->setCellValue('A2', 'Empresa: ' . $this->company->name);
                    $sheet->mergeCells('A2:D2');
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    
                    $sheet->setCellValue('E2', 'RUC: ' . $this->company->number);
                    $sheet->mergeCells('E2:H2');
                    $sheet->getStyle('E2')->getFont()->setBold(true);
                }
                
                // Fila 3: Encabezados de la tabla (se mueven automáticamente)
                $sheet->getStyle('A3:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3:H3')->getFont()->setBold(true);
                
                // Aplicar bordes a toda la tabla
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 3) {
                    $tableRange = 'A3:H' . $highestRow;
                    $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                
                // Ajustar ancho de columnas
                $sheet->getColumnDimension('A')->setWidth(8);   // #
                $sheet->getColumnDimension('B')->setWidth(15);  // Fecha de detracción
                $sheet->getColumnDimension('C')->setWidth(20);  // Comprobante
                $sheet->getColumnDimension('D')->setWidth(20);  // Tipo documento
                $sheet->getColumnDimension('E')->setWidth(25);  // Cliente
                $sheet->getColumnDimension('F')->setWidth(15);  // Número cliente
                $sheet->getColumnDimension('G')->setWidth(15);  // Constancia Pago
                $sheet->getColumnDimension('H')->setWidth(15);  // Total detracción
                
                // Agregar totales al final
                if ($highestRow > 3) {
                    $totalRow = $highestRow + 2;
                    
                    // Total de registros
                    $sheet->setCellValue('A' . $totalRow, 'Total de registros:');
                    $sheet->setCellValue('B' . $totalRow, $highestRow - 3);
                    $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
                    
                    // Total detracciones
                    $totalAmountRow = $totalRow + 1;
                    $sheet->setCellValue('A' . $totalAmountRow, 'Total detracciones:');
                    
                    // Calcular suma de detracciones
                    $totalAmount = 0;
                    for ($row = 4; $row <= $highestRow; $row++) {
                        $amount = $sheet->getCell('H' . $row)->getValue();
                        if (is_numeric(str_replace(',', '', $amount))) {
                            $totalAmount += (float)str_replace(',', '', $amount);
                        }
                    }
                    
                    $sheet->setCellValue('B' . $totalAmountRow, number_format($totalAmount, 2));
                    $sheet->getStyle('A' . $totalAmountRow)->getFont()->setBold(true);
                    $sheet->getStyle('B' . $totalAmountRow)->getFont()->setBold(true);
                }
            },
        ];
    }
}
