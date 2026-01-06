<?php

namespace Modules\Item\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemMigrationExportV2 implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    use Exportable;
    
    protected $records;
    protected $warehouse_id;

    public function records($records)
    {
        $this->records = $records;
        return $this;
    }

    public function warehouse_id($warehouse_id)
    {
        $this->warehouse_id = $warehouse_id;
        return $this;
    }

    public function headings(): array
    {
        return [
            'Código Interno',
            'Nombre',
            'Nombre secundario',
            'Modelo',
            'Código Tipo de Unidad',
            'TIPO DE MONEDA',
            'Precio Unitario Venta',
            'Stock',
            'Categoría',
            'Marca',
            'Descripcion',
            'VISUALIZAR LOTE (LOTE)',
            'Fec. Vencimiento',
            'Cód barras'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Obtener la última columna (N para el código de barras)
        $lastColumn = 'N';
        $lastRow = $sheet->getHighestRow();
        
        return [
            // Alinear a la derecha y formato texto para la columna del código de barras
            $lastColumn . '1:' . $lastColumn . $lastRow => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '@'  // Formato texto
                ]
            ],
        ];
    }

    public function collection()
    {
        return collect($this->records)->transform(function($value) {
            if ($value->lot_code === null && $value->date_of_due === null) {
                $lots_group = $value->lots_group->where('warehouse_id', $this->warehouse_id);
                $first_lot = $lots_group->first();
            } else {
                $first_lot = (object)[
                    'lote_code' => $value->lot_code,
                    'date_of_due' => $value->date_of_due
                ];
            }

            return [
                'internal_id' => $value->internal_id,
                'description' => $value->description,
                'second_name' => $value->second_name,
                'model' => $value->model,
                'unit_type_id' => $value->unit_type_id,
                'currency_type_id' => $value->currency_type_id,
                'sale_unit_price' => $value->sale_unit_price,
                'stock' => $value->stock,
                'category' => $value->category ? $value->category->name : '-',
                'brand' => $value->brand ? $value->brand->name : '-',
                'name' => $value->name,
                'lot_code' => $first_lot ? $first_lot->lote_code : '',
                'date_of_due' => $first_lot ? Carbon::parse($first_lot->date_of_due)->format('d/m/Y') : null,
                'barcode' => ' ' . $value->barcode  // Agregamos espacio en blanco al inicio
            ];
        })->map(function($row) {
            return array_values($row);
        });
    }
}
