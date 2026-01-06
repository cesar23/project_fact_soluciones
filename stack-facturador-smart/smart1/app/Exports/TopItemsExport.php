<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TopItemsExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = DB::connection('tenant')->table('document_items')
            ->select('items.id', 'items.description', DB::raw('COUNT(document_items.id) as sales_count'), DB::raw('SUM(document_items.total) as total_sales'))
            ->join('items', 'document_items.item_id', '=', 'items.id')
            ->join('documents', 'document_items.document_id', '=', 'documents.id')
            ->groupBy('items.id', 'items.description')
            ->orderBy('sales_count', 'desc');

        // Aplicar el filtro de fechas si están presentes
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('documents.date_of_issue', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID Item',
            'Descripción',
            'Ventas',
            'Total'
        ];
    }
}
