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

/**
 * Class MonthDiaryExport
 *
 * @package Modules\Account\Exports
 */
class MonthDiaryExport implements ShouldAutoSize, FromCollection, WithHeadings, WithMapping, WithEvents
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
        $this->prefix =  $month_date;
        $this->period = $period;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $records = $this->records ?? collect();

        $rows = collect();
        foreach ($records as $index => $record) {
            $record->record_index = $index;
            $items = $record->items ?? collect();
            $book_code = $record->is_manual ? $record->book_code : '';
            foreach ($items as $item) {
                $correlative_number = $this->getCorrelative($record->code, $record->correlative_number);
                $rows->push([
                    // A: correlativo
                    $correlative_number,
                    // B: fecha
                    $record->date ? $record->date->format('d/m/Y') : '',
                    // C: glosa
                    $record->description ?? '',
                    // D: código libro
                    $book_code,
                    // E: número correlativo (ref)
                    $record->correlative_number ?? '',
                    // F: número doc sust
                    $item->document_number ?? '',
                    // G: código cuenta
                    $item->code ?? '',
                    // H: denominación
                    $item->description ?? '',
                    // I: debe
                    $item->debit ? (float) $item->debit_amount : null,
                    // J: haber
                    $item->credit ? (float) $item->credit_amount : null,
                ]);
            }
            // fila separadora
            $rows->push(['', '', '', '', '', '', '', '', '', '']);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Encabezados hoja de datos (serán reemplazados y combinados en AfterSheet para el diseño 5.1)
        return [
            'NÚMERO CORRELATIVO DEL ASIENTO',
            'FECHA DE LA OPERACIÓN',
            'GLOSA O DESCRIPCIÓN DE LA OPERACIÓN',
            'CÓDIGO DEL LIBRO (TABLA 8)',
            'NÚMERO CORRELATIVO',
            'NÚMERO DEL DOCUMENTO SUSTENTATORIO',
            'CÓDIGO',
            'DENOMINACIÓN',
            'DEBE',
            'HABER',
        ];
    }

    /**
     * @param mixed $record
     * @return array
     */
    public function map($row): array
    {
        // Los registros ya vienen aplanados en collection()
        return $row;
    }

    public function getCorrelative($code,$index)
    {
        return $code . '-' . $this->prefix . '' . str_pad($index, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // Construcción de cabecera superior
                // Insertar 7 filas para desplazar encabezado generado por WithHeadings (fila 1)
                // y disponer de 2 filas para cabecera personalizada sin pisar la primera fila de datos
                $ws->insertNewRowBefore(1, 7);
                $ws->setCellValue('A1', 'FORMATO 5.1: "LIBRO DIARIO"');
                $ws->mergeCells('A1:J1');
                $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $ws->getStyle('A1')->getAlignment()->setHorizontal('left');

                $periodText = $this->period ?? '';
                $ruc = $this->company->number ?? '';
                $companyName = $this->company->name ?? '';

                $ws->setCellValue('A3', 'PERÍODO:');
                $ws->setCellValue('B3', $periodText);
                $ws->mergeCells('B3:J3');
                $ws->getStyle('B3')->getAlignment()->setHorizontal('left');

                $ws->setCellValue('A4', 'RUC:');
                $ws->setCellValue('B4', $ruc);
                $ws->mergeCells('B4:J4');
                $ws->getStyle('B4')->getAlignment()->setHorizontal('left');

                $ws->setCellValue('A5', 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL:');
                $ws->setCellValue('B5', $companyName);
                $ws->mergeCells('B5:J5');

                // Cabecera de 2 niveles
                $h1 = 7; $h2 = 8;
                $ws->setCellValue("A{$h1}", 'NÚMERO CORRELATIVO');
                $ws->setCellValue("B{$h1}", 'FECHA');
                $ws->setCellValue("C{$h1}", 'GLOSA O DESCRIPCIÓN DE LA OPERACIÓN');
                $ws->setCellValue("D{$h1}", 'REFERENCIA DE LA OPERACIÓN');
                $ws->setCellValue("G{$h1}", 'CUENTA CONTABLE ASOCIADA A LA OPERACIÓN');
                $ws->setCellValue("I{$h1}", 'MOVIMIENTO');

                $ws->setCellValue("A{$h2}", 'DEL ASIENTO O CÓDIGO ÚNICO DE LA OPERACIÓN');
                $ws->setCellValue("B{$h2}", 'DE LA OPERACIÓN');
                $ws->setCellValue("C{$h2}", 'DESCRIPCIÓN DE LA OPERACIÓN');
                $ws->setCellValue("D{$h2}", 'CÓDIGO DEL LIBRO O REGISTRO (TABLA 8)');
                $ws->setCellValue("E{$h2}", 'NÚMERO CORRELATIVO');
                $ws->setCellValue("F{$h2}", 'NÚMERO DEL DOCUMENTO SUSTENTATORIO');
                $ws->setCellValue("G{$h2}", 'CÓDIGO');
                $ws->setCellValue("H{$h2}", 'DENOMINACIÓN');
                $ws->setCellValue("I{$h2}", 'DEBE');
                $ws->setCellValue("J{$h2}", 'HABER');

                $ws->mergeCells("A{$h1}:A{$h2}");
                $ws->mergeCells("B{$h1}:B{$h2}");
                $ws->mergeCells("C{$h1}:C{$h2}");
                $ws->mergeCells("D{$h1}:F{$h1}");
                $ws->mergeCells("G{$h1}:H{$h1}");
                $ws->mergeCells("I{$h1}:J{$h1}");

                $ws->getStyle("A{$h1}:J{$h2}")->getFont()->setBold(true);
                $ws->getStyle("A{$h1}:J{$h2}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
                $ws->getStyle("A{$h1}:J{$h2}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Anchos
                foreach (range('A','J') as $col) { $ws->getColumnDimension($col)->setAutoSize(false); }
                $ws->getColumnDimension('A')->setWidth(18);
                $ws->getColumnDimension('B')->setWidth(12);
                $ws->getColumnDimension('C')->setWidth(40);
                $ws->getColumnDimension('D')->setWidth(18);
                $ws->getColumnDimension('E')->setWidth(18);
                $ws->getColumnDimension('F')->setWidth(24);
                $ws->getColumnDimension('G')->setWidth(14);
                $ws->getColumnDimension('H')->setWidth(28);
                $ws->getColumnDimension('I')->setWidth(14);
                $ws->getColumnDimension('J')->setWidth(14);

                // Formato numérico
                $lastRow = $ws->getHighestRow();
                $dataStart = $h2 + 1; // inicio de datos (fila 9)
                // Formatos numéricos
                $ws->getStyle("I{$dataStart}:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle("J{$dataStart}:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                // Bordes para el cuerpo de la tabla
                if ($lastRow >= $dataStart) {
                    $ws->getStyle("A{$dataStart}:J{$lastRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }

                // Total general al final
                $totalRow = $lastRow + 2;
                $ws->setCellValue("A{$totalRow}", 'TOTAL GENERAL');
                $ws->mergeCells("A{$totalRow}:H{$totalRow}");
                $ws->getStyle("A{$totalRow}")->getAlignment()->setHorizontal('right');
                $ws->setCellValue("I{$totalRow}", $this->total_debit);
                $ws->setCellValue("J{$totalRow}", $this->total_credit);
                $ws->getStyle("A{$totalRow}:J{$totalRow}")->getFont()->setBold(true);
                $ws->getStyle("I{$totalRow}:J{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle("I{$totalRow}:J{$totalRow}")->getAlignment()->setHorizontal('right');
                $ws->getStyle("A{$totalRow}:J{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        ];
    }

    public function title(): string
    {
        return 'Diario mensual';
    }
}
