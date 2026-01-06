<?php

namespace Modules\Inventory\Exports;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\SaleNote;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Modules\Inventory\Models\Devolution;
use Modules\Inventory\Models\Inventory;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ItemCostHistoryGainExport implements FromCollection, ShouldAutoSize, WithHeadings, WithCustomStartCell, WithEvents
{
    use Exportable;

    protected $records;
    protected $company;
    protected $additionalData;

    public function records($records)
    {
        $this->records = $records;
        return $this;
    }

    public function company($company)
    {
        $this->company = $company;
        return $this;
    }

    public function additionalData($additionalData)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge cells for VENTA header
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->setCellValue('A1', 'VENTA');

                // Merge cells for COSTO DE VENTA header
                $event->sheet->mergeCells('E1:F1');
                $event->sheet->setCellValue('E1', 'COSTO DE VENTA');

                // Merge cells for ANALISIS DE MARGENES header
                $event->sheet->mergeCells('G1:I1');
                $event->sheet->setCellValue('G1', 'ANALISIS DE MARGENES');

                // Style the merged cells
                $event->sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            },
        ];
    }

    public function collection()
    {
        $chunks = $this->records->chunk(1000);
        $result = new Collection();
        $configuration = Configuration::select('stock_decimal', 'decimal_quantity')->first();
        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                $date = $row->date;
                $state_type_id = '05';
                $to_return = [
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'item_internal_id' => $row->item->internal_id,
                    'item_description' => $row->item->description,
                    'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                    'warehouse_description' => $row->warehouse->description,
                    'stock' => number_format($row->stock, $configuration->stock_decimal),
                    'average_cost' => number_format($row->average_cost, $configuration->decimal_quantity),
                    'unit_price' => number_format($row->unit_price, $configuration->decimal_quantity),
                    'date' => $date,
                    'quantity' => number_format(abs($row->quantity), $configuration->stock_decimal),
                    'balance' => number_format($row->stock, $configuration->stock_decimal),
                    'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
                ];
                if ($row->inventory_kardexable_type == Document::class || $row->inventory_kardexable_type == SaleNote::class) {
                    $state_type_id = $row->inventoryKardexable->state_type_id;
                }
                if (!in_array($state_type_id, ['11', '09'])) {
                    $to_return = array_merge($to_return, $this->formatData($row));
                    $total_sale = number_format($to_return['unit_price'] * ($to_return['quantity'] ?? 0), $configuration->decimal_quantity);
                    $total_sale_without_format = $to_return['unit_price'] * ($to_return['quantity'] ?? 0);
                    $total_cost = number_format($to_return['average_cost'] * ($to_return['quantity'] ?? 0), $configuration->decimal_quantity);
                    $total_cost_without_format = $to_return['average_cost'] * ($to_return['quantity'] ?? 0);
                    $total_gain_without_format = $total_sale_without_format - $total_cost_without_format;
                    $total_gain = number_format($total_gain_without_format, $configuration->decimal_quantity);
                    $gain_unit = number_format($to_return['unit_price'] - $to_return['average_cost'], $configuration->decimal_quantity);
                    $gain_percentage = number_format(($total_gain_without_format * 100) / $total_cost_without_format, 2);
                    $result->push([
                        'COMPROBANTE' => $to_return['number'],
                        'PRODUCTO' => $to_return['item_fulldescription'],
                        'CANTIDAD' => $to_return['quantity'],
                        'P.VENTA' => $to_return['unit_price'],
                        'TOTAL DE VENTA' => $total_sale,
                        'C/U' => $to_return['average_cost'],
                        'COSTO TOTAL' => $total_cost,
                        'UTILIDAD UNITARIA' => $gain_unit,
                        'UTILIDAD TOTAL' => $total_gain,
                        '%' => $gain_percentage,
                    ]);
                }
            }
        }

        return $result;
    }

    public function formatData($row)
    {
        $inventory_kardexable_type = $row->inventory_kardexable_type;
        $type = $row->type;
        $number = '-';
        $anulation = false;
        $type_transaction = null;
        switch ($inventory_kardexable_type) {
            case Document::class:
                $type_transaction = $type == 'in' ? 'Anulación de venta' : 'Venta';
                $anulation = $type == 'out';
                $number = $row->inventoryKardexable->number_full;
                break;
            case Purchase::class:
                $type_transaction = $type == 'in' ? 'Compra' : 'Anulación de compra';
                $anulation = $type == 'out';
                $number = $row->inventoryKardexable->number_full;
                break;
            case SaleNote::class:
                $type_transaction = $type == 'in' ? 'Anulación de venta' : 'Venta';
                $anulation = $type == 'out';
                $number = $row->inventoryKardexable->number_full;
                break;
            case Inventory::class:
                $type_transaction = $row->inventoryKardexable->description;
                break;

            case Devolution::class:
                $type_transaction = $type == 'in' ? 'Anulación de devolución' : 'Devolución';
                $number = $row->inventoryKardexable->prefix . ' ' . $row->inventoryKardexable->id;
                break;

            default:
                $type_transaction = 'Otros';
        }

        return [
            'type_transaction' => $type_transaction,
            'number' => $number,
            'anulation' => $anulation,
        ];
    }

    public function headings(): array
    {
        return [
            'COMPROBANTE',
            'PRODUCTO',
            'CANTIDAD',
            'P.VENTA',
            'TOTAL DE VENTA',
            'C/U',
            'COSTO TOTAL',
            'UTILIDAD UNITARIA',
            'UTILIDAD TOTAL',
            '%'
        ];
    }
}
