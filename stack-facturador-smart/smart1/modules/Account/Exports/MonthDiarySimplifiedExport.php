<?php

namespace Modules\Account\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Log;

/**
 * Class MonthDiarySimplifiedExport
 *
 * @package Modules\Account\Exports
 */
class MonthDiarySimplifiedExport implements ShouldAutoSize, FromCollection, WithHeadings, WithMapping, WithEvents
{
    use Exportable;

    /** @var Collection */
    protected $records;
    protected $total_debit;
    protected $total_credit;
    protected $balance;
    protected $company;
    protected $month;
    protected $prefix;
    protected $period;
    protected $columnTotals; // Array para almacenar totales por columna


    /**
     * Constructor
     */
    public function __construct($records = null, $company = null, $month = null, $period = null)
    {
        $this->records = $records;
        $this->total_debit = $month->total_debit;
        $this->total_credit = $month->total_credit;
        $this->balance = $month->balance;
        $this->month = $month;
        $this->company = $company;
        $month_date = $month->month->format('m');
        $this->prefix = '05-' . $month_date;
        $this->period = $period;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        // Se devuelve una colección vacía. Toda la lógica de creación de la hoja
        // se ha movido al evento AfterSheet en registerEvents() para evitar
        // problemas de sincronización al escribir los datos.
        return collect();
    }

    /**
     * Método de depuración para verificar totales
     */
    private function debugTotals($totals)
    {
        // Solo ejecutar en desarrollo
        if (config('app.debug')) {
            Log::info('=== DEBUG TOTALES ===');
            Log::info('Total ACTIVO (col 2-30): ' . array_sum(array_slice($totals, 2, 29)));
            Log::info('Total PASIVO (col 31-40): ' . array_sum(array_slice($totals, 31, 10)));
            Log::info('Total PATRIMONIO (col 41-47): ' . array_sum(array_slice($totals, 41, 7)));
            Log::info('Total GASTOS (col 48-79): ' . array_sum(array_slice($totals, 48, 32)));
            Log::info('Total INGRESOS (col 80): ' . array_sum(array_slice($totals, 80, 1)));
            Log::info('Total GENERAL: ' . array_sum($totals));
            Log::info('=====================');
        }
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Los encabezados se generan dinámicamente en AfterSheet.
        return [];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        // No se utiliza, ya que la colección inicial está vacía.
        return [];
    }

    public function getCorrelative($index)
    {
        return $this->prefix . '-' . str_pad($index, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // 1. GENERAR DATOS Y TOTALES
                $records = $this->records ?? collect();
                $dataRows = [];
                // Se ajusta el total de columnas a 73 (A, B + 71 cuentas)
                $totals = array_fill(0, 73, 0);

                // Definir el nuevo orden de cuentas y el mapeo de columnas
                $codes_activo = ['10', '11', '12', '13', '14', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39'];
                $codes_pasivo = ['40', '41', '42', '43', '44', '45', '46', '47', '48', '49'];
                $codes_patrimonio = ['50', '51', '52', '56', '57', '58', '59'];
                // CORREGIDO: Bloque GASTOS antes que INGRESOS, y sin cuentas de la clase 8
                $codes_gastos = ['60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '90', '91', '94', '95', '97'];
                $codes_ingresos = ['70', '71', '72', '73', '74', '75', '76', '77', '78', '79'];
                
                // CORREGIDO: Orden de merge para que GASTOS aparezca antes que INGRESOS
                $all_codes = array_merge($codes_activo, $codes_pasivo, $codes_patrimonio, $codes_gastos, $codes_ingresos);
                
                $codeMapping = [];
                foreach($all_codes as $idx => $code) {
                    $codeMapping[$code] = $idx + 2; // C es la columna 3 (índice 2)
                }

                foreach ($records as $index => $record) {
                    $items = $record->items ?? collect();
                    $row = array_fill(0, 73, null); // Ajustado a 73 columnas
                    $row[0] = $record->date ? $record->date->format('d/m/Y') : '';
                    $row[1] = $record->description ?? '';
                    
                    $groupedItems = [];
                    foreach ($items as $item) {
                        $code = substr($item->code, 0, 2);
                        if (empty($code)) continue;

                        $amount = 0;
                        if ($item->debit && !empty($item->debit_amount)) {
                            $amount = (float) $item->debit_amount;
                        } elseif ($item->credit && !empty($item->credit_amount)) {
                            $amount = -(float) $item->credit_amount;
                        }

                        if ($amount == 0) continue;

                        if (!isset($groupedItems[$code])) {
                            $groupedItems[$code] = 0;
                        }
                        $groupedItems[$code] += $amount;
                    }
                    
                    foreach ($groupedItems as $code => $totalAmount) {
                        if (isset($codeMapping[$code])) {
                            $columnIndex = $codeMapping[$code];
                            $row[$columnIndex] = $totalAmount;
                            $totals[$columnIndex] += $totalAmount;
                        }
                    }
                    $dataRows[] = $row;
                }
                
                // 2. ESCRIBIR ENCABEZADOS Y TÍTULOS (rangos ajustados a BU)
                $ws->setCellValue('A1', 'FORMATO 5.2: "LIBRO DIARIO - FORMATO SIMPLIFICADO"');
                $ws->mergeCells('A1:BU1');
                $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $ws->getStyle('A1')->getAlignment()->setHorizontal('left');
                
                $periodText = $this->period ?? '';
                $ruc = $this->company->number ?? '';
                $companyName = $this->company->name ?? '';

                $ws->setCellValue('A3', 'PERÍODO:');
                $ws->setCellValue('B3', $periodText);
                $ws->mergeCells('B3:BU3');
                $ws->getStyle('B3')->getAlignment()->setHorizontal('left');

                $ws->setCellValue('A4', 'RUC:');
                $ws->setCellValue('B4', $ruc);
                $ws->mergeCells('B4:BU4');
                $ws->getStyle('B4')->getAlignment()->setHorizontal('left');

                $ws->setCellValue('A5', 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL:');
                $ws->setCellValue('B5', $companyName);
                $ws->mergeCells('B5:BU5');

                $h1 = 7; $h2 = 8;
                $ws->setCellValue("A{$h1}", 'Fecha o Período')->mergeCells("A{$h1}:A{$h2}");
                $ws->setCellValue("B{$h1}", 'Operación mensual')->mergeCells("B{$h1}:B{$h2}");
                $ws->setCellValue("C{$h1}", 'ACTIVO')->mergeCells("C{$h1}:AE{$h1}");
                $ws->setCellValue("AF{$h1}", 'PASIVO')->mergeCells("AF{$h1}:AO{$h1}");
                $ws->setCellValue("AP{$h1}", 'PATRIMONIO')->mergeCells("AP{$h1}:AV{$h1}");
                // CORREGIDO: Nuevos rangos y orden para GASTOS e INGRESOS
                $ws->setCellValue("AW{$h1}", 'GASTOS')->mergeCells("AW{$h1}:BK{$h1}");   // Cuentas 6x, 9x (15 columnas)
                $ws->setCellValue("BL{$h1}", 'INGRESOS')->mergeCells("BL{$h1}:BU{$h1}"); // Cuentas 70-79 (10 columnas)

                $col = 'C';
                foreach ($all_codes as $code) {
                    $ws->setCellValue("{$col}{$h2}", $code);
                    $col++;
                }
                
                // 3. ESCRIBIR FILAS DE DATOS
                $dataStart = 9;
                foreach ($dataRows as $index => $rowData) {
                    $targetRow = $dataStart + $index;
                    foreach ($rowData as $colIndex => $value) {
                        if ($value !== null) {
                            $ws->setCellValueByColumnAndRow($colIndex + 1, $targetRow, $value);
                        }
                    }
                }
                
                // 4. APLICAR ESTILOS Y FORMATOS (rangos ajustados a BU)
                $lastRow = $ws->getHighestRow();
                $ws->getStyle("A{$h1}:BU{$h2}")->getFont()->setBold(true);
                $ws->getStyle("A{$h1}:BU{$h2}")->getAlignment()->setHorizontal('center')->setVertical('center');
                $ws->getStyle("A{$h1}:BU{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                $ws->getColumnDimension('A')->setWidth(12);
                $ws->getColumnDimension('B')->setWidth(40);
                for ($col = 'C'; $col <= 'BU'; $col++) {
                    $ws->getColumnDimension($col)->setWidth(12);
                }
                
                if ($lastRow >= $dataStart) {
                    $ws->getStyle("C{$dataStart}:BU{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // 5. ESCRIBIR TOTALES (rangos ajustados a BU)
                $totalRow = $lastRow + 2;
                $ws->setCellValue("A{$totalRow}", 'TOTAL GENERAL');
                $ws->mergeCells("A{$totalRow}:B{$totalRow}");
                $ws->getStyle("A{$totalRow}")->getAlignment()->setHorizontal('right');
                
                $this->columnTotals = $totals;
                $this->addColumnTotals($ws, $dataStart, $lastRow, $totalRow);
                
                $ws->getStyle("A{$totalRow}:BU{$totalRow}")->getFont()->setBold(true);
                $ws->getStyle("A{$totalRow}:BU{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        ];
    }

    /**
     * Agregar totales por columna
     */
    private function addColumnTotals($ws, $dataStart, $lastRow, $totalRow)
    {
        $col = 'C';
        // El bucle ahora es dinámico y se basa en el tamaño del array de totales
        for ($index = 2; $index < count($this->columnTotals); $index++) {
            $total = $this->columnTotals[$index] ?? 0;
            $ws->setCellValue("{$col}{$totalRow}", $total);
            $ws->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->getStyle("{$col}{$totalRow}")->getAlignment()->setHorizontal('right');
            $col++;
        }
    }

    public function title(): string
    {
        return 'Diario mensual simplificado';
    }
}
