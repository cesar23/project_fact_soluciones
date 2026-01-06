<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopCustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $includeNv;
    protected $rowIndex = 0;

    public function __construct($startDate, $endDate, $includeNv = false)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->includeNv = $includeNv;
    }

    public function collection()
    {
        // Consulta base para documentos
        $documentsQuery = DB::connection('tenant')->table('documents')
            ->join('persons', 'documents.customer_id', '=', 'persons.id')
            ->select(
                'persons.id',
                'persons.name',
                DB::raw('COUNT(documents.id) as purchases'),
                DB::raw('SUM(documents.total) as total')
            )
            ->whereBetween('documents.date_of_issue', [$this->startDate, $this->endDate])
            ->whereIn('documents.state_type_id', ['01', '03', '05', '07', '13'])
            ->groupBy('persons.id', 'persons.name');

        if ($this->includeNv) {
            $saleNotesQuery = DB::connection('tenant')->table('sale_notes')
                ->join('persons', 'sale_notes.customer_id', '=', 'persons.id')
                ->select(
                    'persons.id',
                    'persons.name',
                    DB::raw('COUNT(sale_notes.id) as purchases'),
                    DB::raw('SUM(sale_notes.total) as total')
                )
                ->whereBetween('sale_notes.date_of_issue', [$this->startDate, $this->endDate])
                ->where('sale_notes.state_type_id', '01')
                ->groupBy('persons.id', 'persons.name');

            $combinedQuery = $documentsQuery->unionAll($saleNotesQuery);

            $results = DB::connection('tenant')
                ->table(DB::raw("({$combinedQuery->toSql()}) as combined_sales"))
                ->mergeBindings($combinedQuery)
                ->select(
                    'id',
                    'name',
                    DB::raw('SUM(purchases) as purchases'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('id', 'name')
                ->orderBy('total', 'desc')
                ->get();
        } else {
            $results = $documentsQuery->orderBy('total', 'desc')->get();
        }

        // Reiniciar el índice para el mapeo
        $this->rowIndex = 0;
        return $results;
    }

    public function headings(): array
    {
        return [
            '#',
            'Cliente',
            'Cantidad de compras',
            'Total acumulado',
        ];
    }

    public function map($row): array
    {
        $this->rowIndex++;
        return [
            $this->rowIndex,
            $row->name,
            $row->purchases,
            number_format($row->total, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados en negrita y centrados
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal('center');
        // Bordes para toda la tabla
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:D' . $highestRow)
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Centrar columna índice
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal('center');
        // Formato de moneda para la columna total
        $sheet->getStyle('D2:D' . $highestRow)
            ->getNumberFormat()->setFormatCode('#,##0.00');
        return [];
    }
}
