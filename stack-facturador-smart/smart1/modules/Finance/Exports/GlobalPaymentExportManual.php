<?php

namespace Modules\Finance\Exports;

use App\Models\Tenant\Configuration;
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
use Modules\Finance\Http\Resources\GlobalPaymentCollection;

class GlobalPaymentExportManual implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithEvents
{
    use Exportable;

    public $records;
    public $company;
    public $establishment;
    public $configuration;


    public function __construct()
    {
        $this->configuration = Configuration::first();
    }
    
    public function records($records) {
        $this->records = $records;
        
        return $this;
    }
    
    public function company($company) {
        $this->company = $company;
        
        return $this;
    }
    
    public function establishment($establishment) {
        $this->establishment = $establishment;
        
        return $this;
    }
    
    public function collection(): Collection
    {
        // Usar la colección para obtener los datos transformados
        $collection = new GlobalPaymentCollection($this->records);
        return collect($collection->toArray(request()));
    }
    
    public function headings(): array
    {
        $motivo_or_zone = $this->configuration->show_zone_instead_subject ? 'Zona' : 'Motivo';

        return [
            '#',
            'Fecha de emisión',
            'Adquiriente',
            'N° Doc. Identidad',
            'Tipo documento',
            'Documento/Transacción',
            'Moneda',
            'Tipo',
            $motivo_or_zone,
            'Destino',
            'Cuenta/Caja',
            'F. Pago',
            'Método',
            'Referencia',
            'Soles',
            'Responsable',
            'Pago'
        ];
    }
    
    public function map($row): array
    {
        $document_type_description = $row['document_type_description'];
        if($document_type_description == 'FACTURA ELECTRÓNICA'){
            $document_type_description = 'FT';
        }
        if($document_type_description == 'BOLETA DE VENTA ELECTRÓNICA'){
            $document_type_description = 'BV';
        }

        $reason_or_zone = $this->configuration->show_zone_instead_subject ? ($row['zone_description'] ?? $row['reason']) : $row['reason'];

        return [
            $row['id'] ?? 1, // Usar índice + 1 en lugar del ID del registro
            $row['date_of_issue'],
            $row['person_name'],
            $row['person_number'],
            $document_type_description,
            $row['number_full'],
            $row['currency_type_id'],
            $row['instance_type_description'],
            $reason_or_zone,
            $row['destination_description'],
            $row['cci'],
            $row['date_of_payment'],
            $row['payment_method_type_description'],
            $row['reference'],
            $row['glosa'],
            $row['user_name'],
            $row['total']
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
        ];
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Insertar filas de encabezado
                $sheet->insertNewRowBefore(1, 4);
                
                // Fila 1: Título centrado con fecha
                $sheet->setCellValue('A1', 'REPORTE AL ' . date('d/m/Y'));
                $sheet->mergeCells('A1:Q1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                
                // Fila 2: Nombre de la empresa (solo 5 celdas)
                $sheet->setCellValue('A2', $this->company->name);
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->getFont()->setBold(true);
                
                // Fila 3: RUC de la empresa (solo 5 celdas)
                $sheet->setCellValue('A3', 'RUC: ' . $this->company->number);
                $sheet->mergeCells('A3:E3');
                $sheet->getStyle('A3')->getFont()->setBold(true);
                
                // Fila 4: Establecimiento (solo 5 celdas)
                $sheet->setCellValue('A4', 'Establecimiento: ' . $this->establishment->description);
                $sheet->mergeCells('A4:E4');
                $sheet->getStyle('A4')->getFont()->setBold(true);
                
                // Fila 5: Encabezados de la tabla (se mueven automáticamente)
                $sheet->getStyle('A5:Q5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A5:Q5')->getFont()->setBold(true);
                
                // Corregir la columna # para usar índice + 1
                $dataStartRow = 6; // La primera fila de datos después de los encabezados
                $highestRow = $sheet->getHighestRow();
                
                for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                    $index = $row - $dataStartRow + 1; // Índice + 1
                    $sheet->setCellValue('A' . $row, $index);
                    
                    // Quitar negrita de toda la fila de datos
                    $sheet->getStyle('A' . $row . ':Q' . $row)->getFont()->setBold(false);
                }
                
                // Agregar bordes a la tabla de datos (cabecera y cuerpo)
                $tableRange = 'A5:Q' . $highestRow;
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Agregar totales al final de las columnas O y Q
                $totalRow = $highestRow + 1;
                
                // Total columna O (glosa) - solo valores numéricos
                $totalGlosa = 0;
                for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                    $value = $sheet->getCell('O' . $row)->getValue();
                    if (is_numeric($value)) {
                        $totalGlosa += $value;
                    }
                }
                $sheet->setCellValue('O' . $totalRow, $totalGlosa);
                
                // Total columna Q (total) - solo valores numéricos
                $totalPayment = 0;
                for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                    $value = $sheet->getCell('Q' . $row)->getValue();
                    if (is_numeric($value)) {
                        $totalPayment += $value;
                    }
                }
                $sheet->setCellValue('Q' . $totalRow, $totalPayment);
                
                // Formatear fila de totales
                $sheet->getStyle('O' . $totalRow . ':Q' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('O' . $totalRow . ':Q' . $totalRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue('N' . $totalRow, 'TOTAL:');
                $sheet->getStyle('N' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('N' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
