<?php

namespace Modules\Report\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportDocumentsPaidExport implements ShouldAutoSize, FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    use Exportable;
    protected $records;
    protected $company;
    protected $establishment;
    protected $filters = [];
    protected $rowIndex = 0;
    protected $total_commission_by_product = 0;
    protected $total_sale_by_major = 0;
    protected $total_commission_by_sale = 0;
    protected $total_paid = 0;
    protected $seller_name;

    public function records($records) {
        $this->records = $records;
        $this->rowIndex = 0;
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

    public function filters(array $filters) {
        $this->filters = $filters;
        return $this;
    }

    public function seller_name($seller_name) {
        $this->seller_name = $seller_name;
        return $this;
    }

    public function collection()
    {
        return $this->records;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                [$dateStart, $dateEnd] = $this->resolveDates();

                $companyName = $this->company->name ?? '';
                $companyNumber = $this->company->number ?? '';
                $establishment = $this->establishment->description ?? '';
                $seller = $this->seller_name;

                $sheet = $event->sheet->getDelegate();

                // Fila 1
                $sheet->setCellValue('A1', 'Empresa:');
                $sheet->setCellValue('B1', $companyName);
                $sheet->setCellValue('D1', 'Fecha INICIA');
                $sheet->setCellValue('E1', $dateStart);
                $sheet->setCellValue('F1', 'Fecha FINAL');
                $sheet->setCellValue('G1', $dateEnd);

                // Fila 2
                $sheet->setCellValue('A2', 'Ruc:');
                $sheet->setCellValue('B2', $companyNumber);
                $sheet->setCellValue('D2', 'Establecimiento:');
                $sheet->setCellValue('E2', $establishment);

                // Fila 3
                $sheet->setCellValue('A3', 'VENDEDOR');
                $sheet->setCellValue('B3', $seller);

                // Estilos simples (negrita cabeceras superiores)
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);
                $sheet->getStyle('D1:D2')->getFont()->setBold(true);
                $sheet->getStyle('F1')->getFont()->setBold(true);

                // Rango de encabezados de la tabla (fila 5)
                $headingsCount = count($this->headings());
                $lastCol = Coordinate::stringFromColumnIndex($headingsCount);
                $headersRange = "A5:".$lastCol."5";
                $sheet->getStyle($headersRange)->getFont()->setBold(true);
                $sheet->getStyle($headersRange)->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEFEFEF');
                $sheet->getStyle($headersRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Determinar última fila con datos
                $rows = max(0, $this->records ? $this->records->count() : 0);
                if ($rows > 0) {
                    $tableRange = "A5:".$lastCol.(5 + $rows);
                    // Bordes para toda la tabla
                    $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                    // Auto-filtro
                    $sheet->setAutoFilter($headersRange);
                } else {
                    // Si no hay filas, aplicar borde solo al header
                    $sheet->getStyle($headersRange)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // Congelar encabezados (encabezados + cabecera superior): dejar visible hasta fila 5
                $event->sheet->freezePane('A6');

                // Ajuste de texto para la última columna (PRODUCTOS)
                $sheet->getStyle($lastCol.':'.$lastCol)->getAlignment()->setWrapText(true);

                // Agregar 3 filas vacías sin bordes y tabla resumen centrada
                $rows = max(0, $this->records ? $this->records->count() : 0);
                $summaryStartRow = 5 + $rows + 4; // header + rows + 3 filas vacías + 1
                $summaryCols = 3; // [label, currency, amount]
                $startColIndex = intdiv($headingsCount - $summaryCols, 2) + 1; // centrar
                $col1 = Coordinate::stringFromColumnIndex($startColIndex);
                $col2 = Coordinate::stringFromColumnIndex($startColIndex + 1);
                $col3 = Coordinate::stringFromColumnIndex($startColIndex + 2);

                // Contenido de la tabla resumen (valores iniciales en 0.00)
                $sheet->setCellValue($col1.$summaryStartRow, 'COMISION POR PRODUCTO');
                $sheet->setCellValue($col2.$summaryStartRow, 'S/');
                $sheet->setCellValue($col3.$summaryStartRow, number_format($this->total_commission_by_product, 2));

                $sheet->setCellValue($col1.($summaryStartRow+1), 'VENTA POR MAYOR');
                $sheet->setCellValue($col2.($summaryStartRow+1), 'S/');
                $sheet->setCellValue($col3.($summaryStartRow+1), number_format($this->total_sale_by_major, 2));

                $sheet->setCellValue($col1.($summaryStartRow+2), 'COMISION POR VENTA TOTAL');
                $sheet->setCellValue($col2.($summaryStartRow+2), 'S/');
                $sheet->setCellValue($col3.($summaryStartRow+2), number_format(($this->total_paid - ($this->total_commission_by_product + $this->total_sale_by_major)), 2));

                // Estilos de la tabla resumen
                $summaryRange = $col1.$summaryStartRow.':'.$col3.($summaryStartRow+2);
                $sheet->getStyle($summaryRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($col1.$summaryStartRow.':'.$col1.($summaryStartRow+2))->getFont()->setBold(true);
                $sheet->getStyle($col2.$summaryStartRow.':'.$col2.($summaryStartRow+2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($col3.$summaryStartRow.':'.$col3.($summaryStartRow+2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }

    private function resolveDates(): array
    {
        $period = $this->filters['period'] ?? null;
        $date_start = $this->filters['date_start'] ?? null;
        $date_end = $this->filters['date_end'] ?? null;
        $month_start = $this->filters['month_start'] ?? null;
        $month_end = $this->filters['month_end'] ?? null;

        switch ($period) {
            case 'month':
                $dStart = $month_start ? Carbon::parse($month_start . '-01')->format('Y-m-d') : null;
                $dEnd = $month_start ? Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d') : null;
                break;
            case 'between_months':
                $dStart = $month_start ? Carbon::parse($month_start . '-01')->format('Y-m-d') : null;
                $dEnd = $month_end ? Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d') : null;
                break;
            case 'date':
                $dStart = $date_start;
                $dEnd = $date_start;
                break;
            case 'between_dates':
                $dStart = $date_start;
                $dEnd = $date_end;
                break;
            default:
                $dStart = $date_start ?? '';
                $dEnd = $date_end ?? '';
                break;
        }

        return [$dStart ?? '', $dEnd ?? ''];
    }

    public function headings(): array
    {
        return [
            '#',
            'USUARIO/VENDEDOR',
            'TIPO DOC',
            'NÚMERO',
            'FECHA EMISIÓN',
            'FECHA VENCIMIENTO',
            'GUIA',
            'CLIENTE',
            'RUC',
            'DIRECCIÓN',
            'N° DE CONTACTO',
            'ZONA',
            'ESTADO',
            'MONEDA',
            'FORMA DE PAGO',
            'NOTA DE CREDITO',
            'TOTAL CARGOS',
            'TOTAL EXONERADO',
            'TOTAL INAFECTO',
            'TOTAL GRATUITO',
            'TOTAL GRAVADO',
            'DESCUENTO TOTAL',
            'TOTAL IGV',
            'TOTAL ISC',
            'VALOR TOTAL DOCUMENTO',
            'TOTAL PAGADO',
            'TOTAL PRODUCTOS',
            'PRODUCTOS',
        ];
    }

    public function map($record): array
    {
        $document_type_id = $record->document_type_id ?: '80';
        $net_paid = $record->total_paid - $record->credit_note_total;
        $this->total_paid += $net_paid;
        $this->total_commission_by_product += $record->total_commission_items;
        $this->total_sale_by_major += $record->total_sale_by_major;
        $seller_name = '';
        if(isset($this->filters['user_type']) && $this->filters['user_type'] == 'VENDEDOR'){
            $seller_name = $record->seller_name;
        }else if(isset($this->filters['user_type']) && $this->filters['user_type'] == 'CREADOR'){
            $seller_name = $record->user_name;
        }
        return [
            ++$this->rowIndex,
            $seller_name,
            $document_type_id,
            $record->series . '-' . $record->number,
            $record->date_of_issue,
            $record->date_of_due,
            $record->dispatch_series ?? '',
            $record->customer_name ?? 'N/A',
            $record->customer_number ?? 'N/A',
            $record->customer_address ?? '-',
            $record->contact_phone ?? '-',
            $record->zone ?? '-',
            $record->state_type_description ?? 'N/A',
            $record->currency_type_id,
            $record->payment_method_types ?? '-',
            $record->credit_note_series ?? '-',
            number_format($record->total_charge, 2),
            number_format($record->total_exonerated, 2),
            number_format($record->total_unaffected, 2),
            number_format($record->total_free, 2),
            number_format($record->total_taxed, 2),
            number_format($record->total_discount, 2),
            number_format($record->total_igv, 2),
            number_format($record->total_isc, 2),
            number_format($record->total, 2),
            number_format($net_paid, 2),
            number_format($record->quantity_items, 2),
            $this->formatItems($record->items ?? [])
        ];
    }

    private function formatItems($items)
    {
        if (empty($items)) return 'Sin productos';
        
        $formattedItems = [];
        foreach ($items as $item) {
            Log::info(json_encode($item));
            $itemData = $item['item'];
            $description = $itemData['description'] ?? 'Sin descripción';
            $quantity = $item['quantity'] ?? 1;
            $formattedItems[] = "{$description} (x{$quantity})";
        }
        
        return implode(', ', $formattedItems);
    }
}