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

class ItemCostHistoryExport implements FromCollection, ShouldAutoSize, WithHeadings
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

    public function collection()
    {
        $configuration = Configuration::select('stock_decimal', 'decimal_quantity')->first();
        $chunks = $this->records->chunk(1000);
        $result = new Collection();

        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                $date = $row->date;
                $unit_price = $row->inventory_kardexable_type == Inventory::class ? $row->average_cost : $row->unit_price;
                $to_return = [
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'item_internal_id' => $row->item->internal_id,
                    'item_description' => $row->item->description,
                    'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                    'warehouse_description' => $row->warehouse->description,
                    'unit_price' => $unit_price,
                    'stock' => number_format($row->stock, $configuration->stock_decimal),
                    'average_cost' => number_format($row->average_cost, $configuration->decimal_quantity),
                    'date' => $date,
                    'input' => $row->type == 'in' ? number_format($row->quantity, $configuration->stock_decimal) : null,
                    'output' => $row->type == 'out' ? number_format($row->quantity, $configuration->stock_decimal) : null,
                    'balance' => number_format($row->stock, $configuration->stock_decimal),
                    'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
                ];
                $to_return = array_merge($to_return, $this->formatData($row));
                $result->push([
                    'Fecha y hora transacción' => $to_return['created_at'],
                    'Tipo transacción' => $to_return['type_transaction'],
                    'Número' => $to_return['number'],
                    'Precio unitario' => $to_return['unit_price'],
                    'Fecha de emisión' => $to_return['date'],
                    'Entrada' => $to_return['input'],
                    'Salida' => $to_return['output'],
                    'Saldo' => $to_return['balance'],
                    'Costo promedio' => $to_return['average_cost'],
                ]);
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
            'Fecha y hora transacción',
            'Tipo transacción',
            'Número',
            'Precio unitario',
            'Fecha de emisión',
            'Entrada',
            'Salida',
            'Saldo',
            'Costo promedio'
        ];
    }
}
