<?php

namespace Modules\Inventory\Services;

use App\Models\Tenant\Item;
use App\Models\Tenant\PurchaseItem;
use App\Traits\CacheTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\ItemCostHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ItemCostHistoryService
{
    use CacheTrait;
    protected $batch_size = 1000;
    protected $inputs_reasons = [];
    protected $outputs_reasons = [];
    protected $average_cost = 0;
    protected $stock = 0;

    private function removePendingReset($item_id, $warehouse_id)
    {
        DB::connection('tenant')->table('pending_item_cost_resets')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->delete();
    }

    private function applyFromDateFilter($query, $date_column, $pending_from_date)
    {

        if ($pending_from_date) {
            if ($date_column == 'created_at') {
                $query->where($date_column, '>=', $pending_from_date);
            } else {
                if ($pending_from_date instanceof Carbon) {

                    $query->where($date_column, '>=', $pending_from_date->format('Y-m-d'));
                } else {
                    $to_date = Carbon::parse($pending_from_date)->format('Y-m-d');
                    $query->where($date_column, '>=', $to_date);
                }
            }
        }
    }

    private function deleteHistoryByItemId($item_id, $warehouse_id, $pending_from_date)
    {
        $tenantDb = DB::connection('tenant');
        $tenantDb->table('item_cost_histories')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->where('date', '>=', $pending_from_date)
            ->delete();
    }
    private function getPurchaseUnitPrice($item_id, $warehouse_id)
    {
        $item = Item::select('purchase_unit_price')->find($item_id);
        return PurchaseItem::select('unit_price', 'id')
            ->where(function ($query) use ($item_id, $warehouse_id) {
                $query->where('item_id', $item_id)
                    ->where('warehouse_id', $warehouse_id);
            })
            ->union(
                PurchaseItem::select('unit_price', 'id')
                    ->where('item_id', $item_id)
            )
            ->orderByDesc('id')
            ->value('unit_price') ?? $item->purchase_unit_price;
    }
    public function calculateHistoryByItemId($item_id, $warehouse_id)
    {
        $tenantDb = DB::connection('tenant');

        $establishment_id = $this->getEstablishmentIdByWarehouseId($warehouse_id);
        $pending_reset = $this->getPendingResetByItemId($item_id, $warehouse_id);
        $pending_from_date = null;
        if ($pending_reset) {
            $pending_from_date = $pending_reset->pending_from_date;
            $this->deleteHistoryByItemId($item_id, $warehouse_id, $pending_from_date);
            $last_average_cost = $tenantDb->table('item_cost_histories')
                ->where('item_id', $item_id)
                ->where('warehouse_id', $warehouse_id)
                ->orderByDesc('id')
                ->first();
            if ($last_average_cost) {
                $this->average_cost = $last_average_cost->average_cost;
                $this->stock = $last_average_cost->stock;
            }
        }
        $movements = collect();

        $tenantDb->table('inventories')
            ->select('id', 'created_at', 'quantity', 'inventory_transaction_id', 'type','warehouse_destination_id')
            ->where(function ($query) use ($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id)
                    ->orWhere('warehouse_destination_id', $warehouse_id);
            })
            ->where('item_id', $item_id)
            ->orderBy('created_at')
            ->when($pending_from_date !== null, function ($query) use ($pending_from_date) {
                return $this->applyFromDateFilter($query, 'created_at', $pending_from_date);
            })
            ->chunk($this->batch_size, function ($rows) use (&$movements,$warehouse_id) {

                foreach ($rows as $row) {
                    $type = $this->getInventoryType($row,$warehouse_id);
                    $movements->push([
                        'id' => $row->id,
                        'date' => $row->created_at,
                        'quantity' => (float)$row->quantity,
                        'unit_price' => 0,
                        'type' => $type,
                        'model' => 'Modules\Inventory\Models\Inventory',
                    ]);
                }
            });

        $tenantDb->table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select(
                'purchases.id',
                'purchases.date_of_issue',
                'purchases.time_of_issue',
                'purchase_items.quantity',
                DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(sale_note_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),
                'purchase_items.unit_price'
            )
            ->where('purchases.state_type_id', '01')
            ->where(function ($query) use ($warehouse_id, $establishment_id) {
                $query->where(function ($q) use ($warehouse_id) {
                    $q->whereNotNull('purchase_items.warehouse_id')
                        ->where('purchase_items.warehouse_id', $warehouse_id);
                })
                    ->orWhere(function ($q) use ($establishment_id) {
                        $q->whereNull('purchase_items.warehouse_id')
                            ->where('purchases.establishment_id', $establishment_id);
                    });
            })
            ->when($pending_from_date !== null, function ($query) use ($pending_from_date) {
                return $this->applyFromDateFilter($query, 'purchases.date_of_issue', $pending_from_date);
            })
            ->chunk($this->batch_size, function ($rows) use (&$movements) {
                foreach ($rows as $row) {
                    $datetime = Carbon::parse($row->date_of_issue)->setTimeFromTimeString($row->time_of_issue ?? '00:00:00');
                    $quantity_unit = $row->quantity_unit;
                    if (empty($quantity_unit)) {
                        $quantity_unit = 1;
                    }
                    $quantity = $row->quantity * $quantity_unit;
                    $movements->push([
                        'id' => $row->id,
                        'date' => $datetime,
                        'quantity' => (float)$quantity,
                        'unit_price' => (float)$row->unit_price,
                        'type' => 'in',
                        'model' => 'App\Models\Tenant\Purchase',
                    ]);
                }
            });

        // CHUNKS: Sale Notes (Salidas)
        $tenantDb->table('sale_note_items')
            ->join('sale_notes', 'sale_note_items.sale_note_id', '=', 'sale_notes.id')
            ->select(
                'sale_notes.id',
                'sale_notes.date_of_issue',
                'sale_notes.time_of_issue',
                'sale_notes.state_type_id',
                'sale_note_items.quantity',
                DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(sale_note_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),
                'sale_note_items.unit_price'
            )
            ->whereNull('order_note_id')
            ->where(function ($query) use ($warehouse_id, $establishment_id) {
                $query->where(function ($q) use ($warehouse_id) {
                    $q->whereNotNull('sale_note_items.warehouse_id')
                        ->where('sale_note_items.warehouse_id', $warehouse_id);
                })
                    ->orWhere(function ($q) use ($establishment_id) {
                        $q->whereNull('sale_note_items.warehouse_id')
                            ->where('sale_notes.establishment_id', $establishment_id);
                    });
            })
            ->where('sale_note_items.item_id', $item_id)
            ->orderByRaw('sale_notes.date_of_issue, COALESCE(sale_notes.time_of_issue, "00:00:00")')
            ->when($pending_from_date !== null, function ($query) use ($pending_from_date) {
                return $this->applyFromDateFilter($query, 'sale_notes.date_of_issue', $pending_from_date);
            })

            ->chunk($this->batch_size, function ($rows) use (&$movements) {
                foreach ($rows as $row) {
                    $datetime = Carbon::parse($row->date_of_issue)->setTimeFromTimeString($row->time_of_issue ?? '00:00:00');
                    $quantity = (float)$row->quantity;
                    $quantity_unit = $row->quantity_unit;
                    if (empty($quantity_unit)) {
                        $quantity_unit = 1;
                    }
                    $quantity = $quantity * $quantity_unit;
                    $movements->push([
                        'id' => $row->id,
                        'date' => $datetime,
                        'quantity' => $quantity,
                        'unit_price' => (float)$row->unit_price,
                        'type' => 'out',
                        'model' => 'App\Models\Tenant\SaleNote',
                    ]);
                    if ($row->state_type_id == '11' || $row->state_type_id == '09') {
                        $movements->push([
                            'id' => $row->id,
                            'date' => $datetime,
                            'quantity' => $quantity,
                            'unit_price' => (float)$row->unit_price,
                            'type' => 'in',
                            'model' => 'App\Models\Tenant\SaleNote',
                        ]);
                    }
                }
            });

        // CHUNKS: Documents (Salidas)
        DB::connection('tenant')->table('documents')
            ->join('document_items', 'documents.id', '=', 'document_items.document_id')
            ->select(
                'documents.id',
                'documents.date_of_issue',
                'documents.state_type_id',
                'documents.time_of_issue',
                'document_items.quantity',
                'document_items.unit_price',
                DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(document_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),
                'documents.document_type_id'
            )
            ->whereNull('sale_note_id')
            ->whereNull('order_note_id')
            ->where(function ($query) use ($warehouse_id, $establishment_id) {
                $query->where(function ($q) use ($warehouse_id) {
                    $q->whereNotNull('document_items.warehouse_id')
                        ->where('document_items.warehouse_id', $warehouse_id);
                })
                    ->orWhere(function ($q) use ($establishment_id) {
                        $q->whereNull('document_items.warehouse_id')
                            ->where('documents.establishment_id', $establishment_id);
                    });
            })
            ->where('document_items.item_id', $item_id)
            ->orderByRaw('documents.date_of_issue, COALESCE(documents.time_of_issue, "00:00:00")')
            ->when($pending_from_date !== null, function ($query) use ($pending_from_date) {
                return $this->applyFromDateFilter($query, 'documents.date_of_issue', $pending_from_date);
            })
            ->chunk($this->batch_size, function ($rows) use (&$movements) {
                foreach ($rows as $row) {
                    $quantity = (float)$row->quantity;
                    $quantity_unit = $row->quantity_unit;
                    if (empty($quantity_unit)) {
                        $quantity_unit = 1;
                    }
                    $state_type_id = $row->state_type_id;
                    $quantity = $quantity * $quantity_unit;
                    $datetime = Carbon::parse($row->date_of_issue)->setTimeFromTimeString($row->time_of_issue ?? '00:00:00');
                    $type = $row->document_type_id == '07' ? 'in' : 'out';
                    $movements->push([
                        'id' => $row->id,
                        'date' => $datetime,
                        'quantity' => $quantity,
                        'unit_price' => (float)$row->unit_price,
                        'type' => $type,
                        'model' => 'App\Models\Tenant\Document',
                    ]);
                    if ($state_type_id == '11' || $state_type_id == '09') {
                        $movements->push([
                            'id' => $row->id,
                            'date' => $datetime,
                            'quantity' => $quantity,
                            'unit_price' => (float)$row->unit_price,
                            'type' => $type == 'in' ? 'out' : 'in',
                            'model' => 'App\Models\Tenant\Document',
                        ]);
                    }
                }
            });

        $movements = $movements->sortBy('date');

        $this->processMovements($movements, $item_id, $warehouse_id);
        $this->removePendingReset($item_id, $warehouse_id);
    }

    public function createHistoryByItemId($item_id, $warehouse_id)
    {
        $establishment_id = $this->getEstablishmentIdByWarehouseId($warehouse_id);

        $movements = collect();
        $tenantDb = DB::connection('tenant');

        $tenantDb->table('inventories')
            ->select('id', 'created_at', 'quantity', 'inventory_transaction_id', 'type','warehouse_destination_id')
            ->where(function ($query) use ($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id)
                    ->orWhere('warehouse_destination_id', $warehouse_id);
            })
            ->where('item_id', $item_id)
            ->orderBy('created_at')

            ->chunk($this->batch_size, function ($rows) use (&$movements,$warehouse_id) {

                foreach ($rows as $row) {
                    $type = $this->getInventoryType($row,$warehouse_id);
                    $movements->push([
                        'id' => $row->id,
                        'date' => $row->created_at,
                        'quantity' => (float)$row->quantity,
                        'unit_price' => 0,
                        'type' => $type,
                        'model' => 'Modules\Inventory\Models\Inventory',
                    ]);
                }
            });

        // CHUNKS: Compras (Entradas)
        $tenantDb->table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select(
                'purchases.id',
                'purchases.date_of_issue',
                'purchases.time_of_issue',
                'purchase_items.quantity',
                DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(purchase_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),
                'purchase_items.unit_price'
            )
            ->where('purchases.state_type_id', '01')
            ->where(function ($query) use ($warehouse_id, $establishment_id) {
                $query->where(function ($q) use ($warehouse_id) {
                    $q->whereNotNull('purchase_items.warehouse_id')
                        ->where('purchase_items.warehouse_id', $warehouse_id);
                })
                    ->orWhere(function ($q) use ($establishment_id) {
                        $q->whereNull('purchase_items.warehouse_id')
                            ->where('purchases.establishment_id', $establishment_id);
                    });
            })
            ->where('purchase_items.item_id', $item_id)
            ->orderByRaw('purchases.date_of_issue, COALESCE(purchases.time_of_issue, "00:00:00")')

            ->chunk($this->batch_size, function ($rows) use (&$movements) {
                foreach ($rows as $row) {
                    $datetime = Carbon::parse($row->date_of_issue)->setTimeFromTimeString($row->time_of_issue ?? '00:00:00');
                    $quantity_unit = $row->quantity_unit;
                    if (empty($quantity_unit)) {
                        $quantity_unit = 1;
                    }
                    $quantity = $row->quantity * $quantity_unit;
                    $movements->push([
                        'id' => $row->id,
                        'date' => $datetime,
                        'quantity' => (float)$quantity,
                        'unit_price' => (float)$row->unit_price,
                        'type' => 'in',
                        'model' => 'App\Models\Tenant\Purchase',
                    ]);
                }
            });

        // CHUNKS: Sale Notes (Salidas)
        $tenantDb->table('sale_note_items')
            ->join('sale_notes', 'sale_note_items.sale_note_id', '=', 'sale_notes.id')
            ->select(
                'sale_notes.id',
                'sale_notes.date_of_issue',
                'sale_notes.time_of_issue',
                'sale_note_items.quantity',
                DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(sale_note_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),
                'sale_note_items.unit_price'
            )
            ->whereNull('order_note_id')
            ->where(function ($query) use ($warehouse_id, $establishment_id) {
                $query->where(function ($q) use ($warehouse_id) {
                    $q->whereNotNull('sale_note_items.warehouse_id')
                        ->where('sale_note_items.warehouse_id', $warehouse_id);
                })
                    ->orWhere(function ($q) use ($establishment_id) {
                        $q->whereNull('sale_note_items.warehouse_id')
                            ->where('sale_notes.establishment_id', $establishment_id);
                    });
            })
            ->where('sale_note_items.item_id', $item_id)
            ->orderByRaw('sale_notes.date_of_issue, COALESCE(sale_notes.time_of_issue, "00:00:00")')


            ->chunk($this->batch_size, function ($rows) use (&$movements) {
                foreach ($rows as $row) {
                    $datetime = Carbon::parse($row->date_of_issue)->setTimeFromTimeString($row->time_of_issue ?? '00:00:00');
                    $quantity = (float)$row->quantity;
                    $quantity_unit = $row->quantity_unit;
                    if (empty($quantity_unit)) {
                        $quantity_unit = 1;
                    }
                    $quantity = $quantity * $quantity_unit;
                    $movements->push([
                        'id' => $row->id,
                        'date' => $datetime,
                        'quantity' => $quantity,
                        'unit_price' => (float)$row->unit_price,
                        'type' => 'out',
                        'model' => 'App\Models\Tenant\SaleNote',
                    ]);
                }
            });

        // CHUNKS: Documents (Salidas)
        DB::connection('tenant')->table('documents')
            ->join('document_items', 'documents.id', '=', 'document_items.document_id')
            ->select(
                'documents.id',
                'documents.date_of_issue',
                'documents.state_type_id',
                'documents.time_of_issue',
                'document_items.quantity',
                'document_items.unit_price',
                DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(document_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),
                'documents.document_type_id'
            )
            ->whereNull('sale_note_id')
            ->whereNull('order_note_id')
            ->where(function ($query) use ($warehouse_id, $establishment_id) {
                $query->where(function ($q) use ($warehouse_id) {
                    $q->whereNotNull('document_items.warehouse_id')
                        ->where('document_items.warehouse_id', $warehouse_id);
                })
                    ->orWhere(function ($q) use ($establishment_id) {
                        $q->whereNull('document_items.warehouse_id')
                            ->where('documents.establishment_id', $establishment_id);
                    });
            })
            ->where('document_items.item_id', $item_id)
            ->orderByRaw('documents.date_of_issue, COALESCE(documents.time_of_issue, "00:00:00")')
            ->chunk($this->batch_size, function ($rows) use (&$movements) {
                foreach ($rows as $row) {
                    $quantity = (float)$row->quantity;
                    $quantity_unit = $row->quantity_unit;
                    if (empty($quantity_unit)) {
                        $quantity_unit = 1;
                    }
                    $state_type_id = $row->state_type_id;
                    $quantity = $quantity * $quantity_unit;
                    $datetime = Carbon::parse($row->date_of_issue)->setTimeFromTimeString($row->time_of_issue ?? '00:00:00');
                    $type = $row->document_type_id == '07' ? 'in' : 'out';
                    $movements->push([
                        'id' => $row->id,
                        'date' => $datetime,
                        'quantity' => $quantity,
                        'unit_price' => (float)$row->unit_price,
                        'type' => $type,
                        'model' => 'App\Models\Tenant\Document',
                    ]);
                    if ($state_type_id == '11' || $state_type_id == '09') {
                        $movements->push([
                            'id' => $row->id,
                            'date' => $datetime,
                            'quantity' => $quantity,
                            'unit_price' => (float)$row->unit_price,
                            'type' => $type == 'in' ? 'out' : 'in',
                            'model' => 'App\Models\Tenant\Document',
                        ]);
                    }
                }
            });

        $movements = $movements->sortBy('date');

        $this->processMovements($movements, $item_id, $warehouse_id);
    }
    private function getPendingResetByItemId($item_id, $warehouse_id)
    {
        return DB::connection('tenant')->table('pending_item_cost_resets')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->first();
    }

    private function existsHistory($item_id, $warehouse_id)
    {
        return ItemCostHistory::where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->exists();
    }

    private function processMovements(Collection $movements, $item_id, $warehouse_id)
    {
        $average_cost = $this->average_cost;

        $stock = $this->stock;
        $batch = [];
        $purchase_unit_price = $this->getPurchaseUnitPrice($item_id, $warehouse_id);
        $exists_history = $this->existsHistory($item_id, $warehouse_id);
        if (!$exists_history) {
            //first movement is inventory
            $first_movement = $movements->first();
            if ($first_movement['model'] == 'Modules\Inventory\Models\Inventory') {
                $average_cost = $purchase_unit_price;
            }
        }
        //dump count movements
        Log::info('Total movements: ' . $movements->count());
        foreach ($movements as $movement) {
            $is_purchase = $movement['model'] == 'App\Models\Tenant\Purchase';
            $qty = $movement['quantity'];
            $unit_price = $movement['unit_price'];

            if ($movement['type'] === 'in') {
                // Entrada: se recalcula el costo promedio
                if ($is_purchase) {
                    $total_cost = $average_cost * $stock + $unit_price * $qty;
                    $stock += $qty;
                    $average_cost = $stock > 0 ? $total_cost / $stock : 0;
                } else {
                    $stock += $qty;
                }
            } else {
                // Salida: solo reduce el stock
                $stock -= $qty;
            }


            $batch[] = [
                'item_id' => $item_id,
                'warehouse_id' => $warehouse_id,
                'date' => $movement['date'],
                'quantity' => $movement['type'] === 'in' ? $qty : -$qty,
                'average_cost' => $average_cost,
                'stock' => $stock,
                'unit_price' => $unit_price,
                'inventory_kardexable_id' => $movement['id'],
                'inventory_kardexable_type' => $movement['model'],
                'type' => $movement['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($batch) >= 1000) {
                ItemCostHistory::insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            ItemCostHistory::insert($batch);
        }
    }

    private function getEstablishmentIdByWarehouseId($warehouse_id)
    {
        $warehouse = \App\Models\Tenant\Warehouse::findOrFail($warehouse_id);
        return $warehouse->establishment_id;
    }

    private function getReasons()
    {
        $reasonsKey = CacheTrait::getCacheKey('reasons');
        $reasons = CacheTrait::getCache($reasonsKey);
        if(!$reasons){
            $reasons = DB::connection('tenant')->table('inventory_transactions')->get();
            CacheTrait::storeCache($reasonsKey, $reasons);
        }
        return $reasons;
    }

    private function getInventoryTransactionType($inventory_transaction_id)
    {
        $reason = $this->getReasons()->where('id', $inventory_transaction_id)->first();
        return $reason->type == 'input' ? 'in' : 'out';
    }

    private function getInventoryType($row,$warehouse_id)
    {
        $type = 'in';
        $row_type = $row->type;
        $warehouse_destination_id = $row->warehouse_destination_id;
        $inventory_transaction_id = $row->inventory_transaction_id;
        if ($inventory_transaction_id) {
            $type = $this->getInventoryTransactionType($inventory_transaction_id);
            return $type;
        }
        if ($row_type == 2) {
            $type = 'out';
        }
        if ($warehouse_destination_id == $warehouse_id && $row_type == 2) {
            $type = 'in';
        }
        return $type;
    }

    public function calculateAverageCost($item_id, $warehouse_id)
    {
        $tenant = DB::connection('tenant');

        $has_cost_history = $tenant->table('item_cost_histories')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->exists();

        $has_pending_reset = $tenant->table('pending_item_cost_resets')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->exists();

        if ($has_cost_history && !$has_pending_reset) {
            return true;
        }

        return false;
    }

    public function insertItemMovement($item_id, $warehouse_id, $quantity, $unit_cost, $inventory_kardexable_id = null, $inventory_kardexable_type = null)
    {
        $tenant = DB::connection('tenant');
        $movement_type = $quantity > 0 ? 'in' : 'out';
        $history = $tenant->table('item_cost_histories')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->orderByDesc('date')
            ->first();

        $has_pending_reset = $tenant->table('pending_item_cost_resets')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->exists();

        if (!$history || $has_pending_reset) {
            return false;
        }

        $new_stock = $this->calculateNewStock($history->stock, $quantity);

        if ($movement_type === 'in' && $inventory_kardexable_type === 'App\Models\Tenant\Purchase') {
            $new_average_cost = $this->calculateNewAverageCost(
                $history->stock,
                $history->average_cost,
                $quantity,
                $unit_cost
            );
        } else {
            $new_average_cost = $history->average_cost;
        }

        DB::connection('tenant')->beginTransaction();

        try {
            // Insertamos el nuevo movimiento
            $tenant->table('item_cost_histories')->insert([
                'item_id' => $item_id,
                'warehouse_id' => $warehouse_id,
                'date' => now(),
                'quantity' => $quantity,
                'type' => $movement_type, // 'in' para compras, 'out' para ventas
                'average_cost' => $new_average_cost,
                'stock' => $new_stock,
                'unit_price' => $unit_cost,
                'inventory_kardex_id' => null, // Si es necesario, agrega el ID del Kardex
                'inventory_kardexable_id' => $inventory_kardexable_id, // Lo mismo para el ID del Kardexable
                'inventory_kardexable_type' => $inventory_kardexable_type, // Lo mismo para el tipo del Kardexable
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::connection('tenant')->commit();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            throw $e;
        }

        return true;
    }

    public function calculateNewStock($last_stock, $quantity)
    {
        return $last_stock + $quantity;
    }

    public function calculateNewAverageCost($last_stock, $last_average_cost, $quantity, $unit_cost)
    {
        $total_cost_before = $last_stock * $last_average_cost;
        $total_cost_new = $quantity * $unit_cost;

        $new_stock = $last_stock + $quantity;

        return ($total_cost_before + $total_cost_new) / $new_stock;
    }


    public function insertPendingItemCostReset($item_id, $warehouse_id, $date_of_issue)
    {
        $tenant = DB::connection('tenant');
        $exists_history = $this->existsHistory($item_id, $warehouse_id);
        if (!$exists_history) {
            return;
        }
        $date_of_issue = is_string($date_of_issue)
            ? Carbon::parse($date_of_issue)->startOfDay()
            : $date_of_issue->copy()->startOfDay();

        $last_pending_reset = $tenant->table('pending_item_cost_resets')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->orderByDesc('id')
            ->first();

        if ($last_pending_reset) {
            $last_pending_reset_date = Carbon::parse($last_pending_reset->pending_from_date)->startOfDay();
            if ($last_pending_reset_date->lt($date_of_issue)) {
                return;
            }
        }
        $tenant->table('pending_item_cost_resets')->where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->delete();



        $tenant->table('pending_item_cost_resets')->insert([
            'item_id' => $item_id,
            'warehouse_id' => $warehouse_id,
            'pending_from_date' => $date_of_issue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public  function shouldTriggerPendingReset($original, $updated)
    {
        if (
            $original->state_type_id !== $updated->state_type_id &&
            in_array($updated->state_type_id, ['09', '11'])
        ) {
            return true;
        }

        if ($this->itemsChanged($original->items, $updated->items)) {
            return true;
        }

        return false;
    }

    private function itemsChanged($originalItems, $updatedItems)
    {
        if (count($originalItems) !== count($updatedItems)) return true;

        foreach ($originalItems as $index => $item) {
            $updatedItem = $updatedItems[$index];

            if (
                $item['item_id'] !== $updatedItem['item_id'] ||
                (float) $item['quantity'] !== (float) $updatedItem['quantity'] ||
                (float) $item['unit_price'] !== (float) $updatedItem['unit_price']
            ) {
                return true;
            }
        }

        return false;
    }
}
