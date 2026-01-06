<?php

namespace Modules\Report\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class RelationSalesExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $formattedRecords;
    protected $company;
    protected $establishment;
    protected $period;

    public function __construct($formattedRecords, $company, $establishment, $period)
    {
        $this->formattedRecords = $formattedRecords;
        $this->company = $company;
        $this->establishment = $establishment;
        $this->period = $period;
    }

    public function collection()
    {
        $rows = collect();
        
        // Variables para totales generales
        $total_general = 0;
        $total_general_paid = 0;
        $total_general_pending = 0;
        
        // Fila de paginación (primera fila) - ocupará las últimas 5 columnas (G:K)
        $date = date('d/m/Y');
        $time = date('H:i:s');
        $rows->push([
            '', '', '', '', '', '', "Página 1 - Fecha: $date - Hora: $time", '', '', '', ''
        ]);
        
        // Encabezados del reporte - 11 columnas total
        $rows->push([
            'RELACIÓN DE VENTAS',
            '', '', '', '', '', '', '', '', '', ''
        ]);
        $rows->push([
            $this->company->name ?? '',
            '', '', '', '', '', '', '', '', '', ''
        ]);
        if ($this->establishment) {
            $rows->push([
                $this->establishment->description ?? '',
                '', '', '', '', '', '', '', '', '', ''
            ]);
        }
        $rows->push([
            'Del ' . ($this->period['d_start'] ?? '') . ' al ' . ($this->period['d_end'] ?? ''),
            '', '', '', '', '', '', '', '', 'Expresado en S/', ''
        ]);
        
        $rows->push(['', '', '', '', '', '', '', '', '', '', '']); // Fila vacía
        
        foreach ($this->formattedRecords as $clientData) {
            // Encabezados principales de tabla - colspan="8" en PDF
            $rows->push([
                'DOCUMENTO', 'FECHA', 'VENDEDOR', '', '', '', '', '', '', '', ''
            ]);
            
            // Segunda fila de encabezados
            $rows->push([
                '', 'Código', 'Descripción', '', 'Und', 'Cant.', 'P.Venta', 'TOTAL', 'A cuenta:', 'Importe', 'Se Debe'
            ]);
            
            // Información del cliente - colspan="7" y colspan="4"
            $rows->push([
                'CLIENTE: ' . $clientData['client_name'],
                '', '', '', '', '', '', // 6 celdas más para completar colspan="7"
                'CONTACTO: ' . $clientData['client_contact_name'],
                '', '', '' // 3 celdas más para completar colspan="4"
            ]);
            $rows->push([
                'DIRECCIÓN: ' . $clientData['client_address'],
                '', '', '', '', '', '', // 6 celdas más para completar colspan="7"
                'TELEFONO: ' . $clientData['client_contact_phone'],
                '', '', '' // 3 celdas más para completar colspan="4"
            ]);
            
            foreach ($clientData['records_by_date'] as $dateData) {
                // Fecha - colspan="11"
                $rows->push([
                    $dateData['date'], '', '', '', '', '', '', '', '', '', ''
                ]);
                
                foreach ($dateData['records'] as $record) {
                    // Acumular totales generales
                    $total_general += floatval($record->total ?? 0);
                    $total_general_paid += floatval($record->total_paid ?? 0);
                    $total_general_pending += floatval($record->total_pending ?? 0);
                    
                    // Documento principal
                    $rows->push([
                        $record->series . '-' . $record->number,
                        $record->date_of_issue,
                        $record->seller_name ?? '',
                        '', '', '', '', '', '', '', ''
                    ]);
                    
                    // Items - colspan="2" para descripción
                    if (isset($record->items)) {
                        foreach ($record->items as $item) {
                            $rows->push([
                                '', // DOCUMENTO vacío
                                $item['internal_id'] ?? '', // Código
                                $item['description'] ?? '', // Descripción
                                '', // Descripción colspan="2" - celda extra
                                $item['unit_type_id'] ?? '', // Und
                                $item['quantity'] ?? '', // Cant.
                                $item['unit_price'] ?? '', // P.Venta
                                $item['total'] ?? '', // TOTAL del item
                                '', '', '' // 3 columnas vacías
                            ]);
                        }
                    }
                    
                    // Pagos - colspan="5" para celdas vacías, colspan="2" para descripción
                    if (isset($record->payments)) {
                        foreach ($record->payments as $payment) {
                            $paymentDate = isset($payment['date_of_payment']) ? 
                                explode('T', $payment['date_of_payment'])[0] : '';
                            $rows->push([
                                '', '', '', '', '', // 5 celdas vacías (colspan="5")
                                '-CANCELACION ' . $paymentDate, // Descripción
                                $payment['payment_method_type']['description'] ?? '', // colspan="2" - parte 1
                                '', // colspan="2" - parte 2
                                $paymentDate, // A cuenta
                                $payment['payment'] ?? '', // Importe
                                '' // Se Debe vacío
                            ]);
                        }
                    }
                    
                    // Total por documento - colspan="7" para celdas vacías/texto
                    $rows->push([
                        '', '', '', '', '', '', '', // 7 celdas vacías
                        $record->formatted_total ?? '', // TOTAL
                        '', // A cuenta vacío
                        $record->formatted_total_paid ?? '', // Importe
                        $record->formatted_total_pending ?? '' // Se Debe
                    ]);
                }
                
                // Subtotal por fecha - colspan="7" para texto
                $rows->push([
                    '', '', '', '', '', '', // 6 celdas vacías
                    'SUBTOTAL ' . $dateData['date'] . ':', // Texto en posición 7
                    $dateData['totals']['formatted']['total'] ?? '', // TOTAL
                    '', // A cuenta vacío
                    $dateData['totals']['formatted']['total_paid'] ?? '', // Importe
                    $dateData['totals']['formatted']['total_pending'] ?? '' // Se Debe
                ]);
            }
            
            // Total del cliente - colspan="7" para texto
            $rows->push([
                '', '', '', '', '', '', // 6 celdas vacías
                'TOTAL CLIENTE:', // Texto en posición 7
                $clientData['totals']['formatted']['total'] ?? '', // TOTAL
                '', // A cuenta vacío
                $clientData['totals']['formatted']['total_paid'] ?? '', // Importe
                $clientData['totals']['formatted']['total_pending'] ?? '' // Se Debe
            ]);
            
            $rows->push(['']); // Separador entre clientes
        }
        
        // TOTAL GENERAL al final del documento
        $rows->push([
            '', '', '', '', '', '', // 6 celdas vacías
            'TOTAL GENERAL:', // Texto en posición 7
            number_format($total_general, 2), // TOTAL
            '', // A cuenta vacío
            number_format($total_general_paid, 2), // Importe
            number_format($total_general_pending, 2) // Se Debe
        ]);
        
        return $rows;
    }

    public function headings(): array
    {
        return []; // Sin encabezados automáticos
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Título principal
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            // Nombre de la empresa
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            // Fechas
            4 => [
                'font' => ['size' => 10]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Fila 1: Paginación en las últimas 5 columnas (G1:K1)
                $sheet->mergeCells('G1:K1');
                
                // Combinar celdas para encabezados principales (11 columnas)
                $sheet->mergeCells('A2:K2'); // RELACIÓN DE VENTAS
                $sheet->mergeCells('A3:K3'); // Nombre empresa
                if ($this->establishment) {
                    $sheet->mergeCells('A4:K4'); // Establecimiento
                    $sheet->mergeCells('A5:I5'); // Período
                } else {
                    $sheet->mergeCells('A4:I4'); // Período
                }
                
                // Estilo para la paginación (fila 1, celdas G1:K1 combinadas)
                $sheet->getStyle('G1:K1')->applyFromArray([
                    'font' => ['size' => 8],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                
                // Aplicar estilos a encabezados principales
                $sheet->getStyle('A2:K2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->getStyle('A3:K3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                if ($this->establishment) {
                    $sheet->getStyle('A4:K4')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                
                // Aplicar estilos generales
                $sheet->getStyle('A1:K' . $highestRow)->applyFromArray([
                    'font' => ['size' => 9],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Auto-ajustar columnas
                foreach(range('A','K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Aplicar colores, merges y estilos específicos
                $this->applyCellMergesAndStyles($sheet, $highestRow);
                
                // Congelar las primeras 7 filas
                $sheet->freezePane('A8');
            }
        ];
    }

    private function applyCellMergesAndStyles($sheet, $highestRow)
    {
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValueA = $sheet->getCell('A' . $row)->getValue();
            $cellValueF = $sheet->getCell('F' . $row)->getValue();
            $cellValueG = $sheet->getCell('G' . $row)->getValue();
            
            // Mergear celdas según patrones del PDF
            
            // CLIENTE: - mergear A:G (colspan="7")
            if (strpos($cellValueA, 'CLIENTE:') !== false) {
                $sheet->mergeCells('A' . $row . ':G' . $row);
                $sheet->mergeCells('H' . $row . ':K' . $row); // CONTACTO (colspan="4")
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0000FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
            }
            
            // DIRECCIÓN: - mergear A:G (colspan="7")
            if (strpos($cellValueA, 'DIRECCIÓN:') !== false) {
                $sheet->mergeCells('A' . $row . ':G' . $row);
                $sheet->mergeCells('H' . $row . ':K' . $row); // TELEFONO (colspan="4")
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0000FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
            }
            
            // Fechas - mergear toda la fila (colspan="11")
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $cellValueA)) {
                $sheet->mergeCells('A' . $row . ':K' . $row);
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
            }
            
            // Descripción de items - mergear C:D (colspan="2")
            if ($cellValueA == '' && $cellValueF == '' && $cellValueG == '' && 
                $sheet->getCell('B' . $row)->getValue() != '' && 
                $sheet->getCell('C' . $row)->getValue() != '') {
                $sheet->mergeCells('C' . $row . ':D' . $row);
            }
            
            // Cancelaciones - rojo
            if (strpos($cellValueF, '-CANCELACION') !== false) {
                $sheet->mergeCells('G' . $row . ':H' . $row); // Descripción método pago (colspan="2")
                $sheet->getStyle('F' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => 'FF0000']],
                ]);
            }
            
            // Totales por documento - border-top-dashed
            if ($cellValueA == '' && $cellValueF == '' && $sheet->getCell('H' . $row)->getValue() != '' &&
                is_numeric(str_replace(',', '', $sheet->getCell('H' . $row)->getValue()))) {
                $sheet->getStyle('G' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_DASHED,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            }
            
            // SUBTOTAL por fecha
            if (strpos($cellValueG, 'SUBTOTAL') !== false) {
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                
                ]);
            }
            
            // TOTAL CLIENTE
            if (strpos($cellValueG, 'TOTAL CLIENTE') !== false) {
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    
                ]);
            }
            
            // TOTAL GENERAL
            if (strpos($cellValueG, 'TOTAL GENERAL') !== false) {
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'E8E8E8']
                    ],
                ]);
            }
            
            // Encabezados de tabla
            if ($cellValueA == 'DOCUMENTO' || strpos($cellValueA, 'Código') !== false) {
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F8F9FA']
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // Mergear primera fila de encabezados
                if ($cellValueA == 'DOCUMENTO') {
                    $sheet->mergeCells('D' . $row . ':K' . $row); // colspan="8"
                }
                
                // Mergear descripción en segunda fila
                if (strpos($cellValueA, 'Código') !== false) {
                    $sheet->mergeCells('C' . $row . ':D' . $row); // colspan="2"
                }
            }
        }
    }
}