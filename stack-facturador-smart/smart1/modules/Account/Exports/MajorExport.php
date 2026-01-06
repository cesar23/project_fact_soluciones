<?php

    namespace Modules\Account\Exports;

use Illuminate\Support\Collection as SupportCollection;
    use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

    /**
     * Class LedgerAccountExcelExport
     *
     * @package Modules\Account\Exports
     */
class MajorExport implements FromCollection, ShouldAutoSize, WithEvents
    {
        use Exportable;

        protected $accounts;
        protected $company;
    protected $period;
    protected $headerEndRow = 0;
    protected $tableRanges = [];
    protected $grandDebit = 0;
    protected $grandCredit = 0;
    protected $generalTotalsRow = null;

    public function __construct($accounts, $company, $period = null)
        {
            $this->accounts = $accounts;
            $this->company = $company;
        $this->period = $period;
        }

        public function collection()
        {
        // Cabecera general
        $companyName = $this->company->name ?? '';
        $companyNumber = $this->company->number ?? ($this->company->number_document ?? '');
        $period = $this->period ?? '';

        $rows = [];
        $currentRow = 0;
        $push = function (array $row) use (&$rows, &$currentRow) {
            $rows[] = $row;
            $currentRow++;
        };

        $push([ 'FORMATO 6.1: "LIBRO MAYOR"' ]);
        $push([ '' ]);
        $push([ 'PERÍODO:', $period ]);
        $push([ 'RUC:', $companyNumber ]);
        $push([ 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL:', $companyName ]);
        $this->headerEndRow = $currentRow;

        // Por cada cuenta (elemento del array general), agregar bloque
        foreach ($this->accounts as $account) {
            $code = $account['code'] ?? '';
            $description = $account['description'] ?? '';
            $items = $account['items'] ?? [];

            $push([ '' ]);
            $labelRow = $currentRow + 1; // se asignará tras push siguiente
            $push([ 'CÓDIGO Y/O DENOMINACIÓN DE LA CUENTA CONTABLE (1):', trim($code . ' ' . ($description ? '- ' . $description : '')) ]);
            // Cabecera de dos filas tipo SUNAT
            $headerRow = $currentRow + 1; // primera fila de cabecera
            $push([
                'FECHA DE',
                'NÚMERO CORRELATIVO',
                'DESCRIPCIÓN O GLOSA',
                'SALDOS Y MOVIMIENTOS',
                '',
            ]);
            $headerRow2 = $currentRow + 1; // segunda fila de cabecera
            $push([
                'LA OPERACIÓN',
                'DEL LIBRO DIARIO (2)',
                'DE LA OPERACIÓN',
                'DEUDOR',
                'ACREEDOR',
            ]);

            $debitTotal = 0; $creditTotal = 0;
            foreach ($items as $item) {
                $debit = (float)($item['debit_amount'] ?? 0);
                $credit = (float)($item['credit_amount'] ?? 0);
                $push([
                    $item['date'] ?? '',
                    $item['correlative_number'] ?? '',
                    $item['general_description'] ?? '',
                    $debit,
                    $credit,
                ]);
                $debitTotal += $debit;
                $creditTotal += $credit;
            }

            // Fila de totales por cuenta
            $push(['', '', 'TOTAL', $debitTotal, $creditTotal]);
            $totalsRow = $currentRow;
            $this->grandDebit += $debitTotal;
            $this->grandCredit += $creditTotal;

            // Registrar rangos para estilos
            $this->tableRanges[] = [
                'labelRow' => $labelRow,
                'headerRow' => $headerRow,
                'headerRow2' => $headerRow2,
                'bodyStart' => $headerRow + 2,
                'bodyEnd' => $totalsRow - 1,
                'totalsRow' => $totalsRow,
            ];
        }

        // Totales generales
        $push([ '' ]);
        $push(['', '', 'TOTAL GENERAL', $this->grandDebit, $this->grandCredit]);
        $this->generalTotalsRow = $currentRow;

        return new SupportCollection($rows);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Bordes
                $allBorders = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];

                // Cabecera general
                $headerRange = "A1:E{$this->headerEndRow}";
                $sheet->getStyle($headerRange)->applyFromArray($allBorders);
                $sheet->getStyle($headerRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle('A1')->getFont()->setBold(true);
                // Desactivar autosize y fijar anchos angostos (contenido)
                foreach (['A','B','C','D','E'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(false);
                }
                $sheet->getColumnDimension('A')->setWidth(14);
                $sheet->getColumnDimension('B')->setWidth(14);
                $sheet->getColumnDimension('C')->setWidth(60);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(12);

                // Combinar filas 1 a 7 (A:E) y unir textos de A+B donde aplique
                $firstLabelRow = $this->tableRanges[0]['labelRow'] ?? null;
                $maxHeaderRow = min(7, $sheet->getHighestRow());
                for ($r = 1; $r <= $maxHeaderRow; $r++) {
                    if (in_array($r, [3, 4, 5]) || ($firstLabelRow !== null && $r === (int)$firstLabelRow)) {
                        $a = (string) $sheet->getCell("A{$r}")->getValue();
                        $b = (string) $sheet->getCell("B{$r}")->getValue();
                        if ($b !== '') {
                            $sheet->setCellValue("A{$r}", trim($a . ' ' . $b));
                        }
                    }
                    $sheet->mergeCells("A{$r}:E{$r}");
                    $sheet->getStyle("A{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                    $sheet->getStyle("A{$r}:E{$r}")->applyFromArray($allBorders);
                }

                // Estilos por cada bloque de cuenta
                foreach ($this->tableRanges as $range) {
                    $labelRow = $range['labelRow'];
                    $headerRow = $range['headerRow'];
                    $headerRow2 = $range['headerRow2'];
                    $bodyStart = $range['bodyStart'];
                    $bodyEnd = $range['bodyEnd'];
                    $totalsRow = $range['totalsRow'];

                    // Etiqueta de cuenta
                    $labelRange = "A{$labelRow}:E{$labelRow}";
                    // Unir texto de A y B antes de combinar
                    $labelA = (string) $sheet->getCell("A{$labelRow}")->getValue();
                    $labelB = (string) $sheet->getCell("B{$labelRow}")->getValue();
                    if ($labelB !== '') {
                        $sheet->setCellValue("A{$labelRow}", trim($labelA . ' ' . $labelB));
                    }
                    $sheet->mergeCells($labelRange);
                    $sheet->getStyle($labelRange)->applyFromArray($allBorders);
                    $sheet->getStyle($labelRange)->getFont()->setBold(true);
                    $sheet->getStyle($labelRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
                    $sheet->getStyle($labelRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Cabecera de tabla de dos filas
                    $tableHeaderRange1 = "A{$headerRow}:E{$headerRow}";
                    $tableHeaderRange2 = "A{$headerRow2}:E{$headerRow2}";
                    $sheet->getStyle($tableHeaderRange1)->applyFromArray($allBorders);
                    $sheet->getStyle($tableHeaderRange2)->applyFromArray($allBorders);
                    $sheet->getStyle($tableHeaderRange1)->getFont()->setBold(true);
                    $sheet->getStyle($tableHeaderRange2)->getFont()->setBold(true);
                    $sheet->getStyle($tableHeaderRange1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');
                    $sheet->getStyle($tableHeaderRange2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');
                    $sheet->getStyle($tableHeaderRange1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                    $sheet->getStyle($tableHeaderRange2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

                    // Combinar celdas según diseño SUNAT
                    $sheet->mergeCells("A{$headerRow}:A{$headerRow2}");
                    $sheet->mergeCells("B{$headerRow}:B{$headerRow2}");
                    $sheet->mergeCells("C{$headerRow}:C{$headerRow2}");
                    $sheet->mergeCells("D{$headerRow}:E{$headerRow}");

                    // Cuerpo
                    if ($bodyEnd >= $bodyStart) {
                        $bodyRange = "A{$bodyStart}:E{$bodyEnd}";
                        $sheet->getStyle($bodyRange)->applyFromArray($allBorders);
                        $sheet->getStyle("A{$bodyStart}:B{$bodyEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle("C{$bodyStart}:C{$bodyEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                        $sheet->getStyle("D{$bodyStart}:E{$bodyEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("D{$bodyStart}:E{$bodyEnd}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    }

                    // Totales por cuenta
                    $totalsRange = "A{$totalsRow}:E{$totalsRow}";
                    $sheet->getStyle($totalsRange)->applyFromArray($allBorders);
                    $sheet->getStyle($totalsRange)->getFont()->setBold(true);
                    $sheet->getStyle("D{$totalsRow}:E{$totalsRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("D{$totalsRow}:E{$totalsRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                }

                // Total general
                if (!is_null($this->generalTotalsRow)) {
                    $g = $this->generalTotalsRow;
                    $gRange = "A{$g}:E{$g}";
                    $sheet->getStyle($gRange)->applyFromArray($allBorders);
                    $sheet->getStyle($gRange)->getFont()->setBold(true);
                    $sheet->getStyle("D{$g}:E{$g}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("D{$g}:E{$g}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                }
            }
        ];
    }

    protected function splitHeader(string $text): string
    {
        // Reemplaza espacios por saltos de línea para hacer el encabezado en vertical
        return preg_replace('/\s+/', "\n", trim($text));
    }
    }
