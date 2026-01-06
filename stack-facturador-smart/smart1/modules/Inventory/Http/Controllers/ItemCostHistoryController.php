<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Item;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Resources\ItemCostHistoryCollection;
use Modules\Inventory\Models\ItemCostHistory;
use Modules\Inventory\Models\PendingItemCostReset;
use Modules\Inventory\Services\ItemCostHistoryService;

class ItemCostHistoryController extends Controller
{

    public function index()
    {
        $configuration = Configuration::getPublicConfig();
        return view('inventory::item-cost-history.index', compact('configuration'));
    }

    public function searchItems(Request $request)
    {
        $input = $request->input('input');
        $warehouse_id = $request->input('warehouse_id');
        $items = Item::query()->whereNotIsSet()
            ->whereHas('warehouses', function ($query) use ($warehouse_id) {
                return $query->where('warehouse_id', $warehouse_id);
            })
            ->where(function ($query) use ($input) {
                $query->where('description', 'like', "%{$input}%")
                    ->orWhere('internal_id', 'like', "%{$input}%");
            })
            ->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);

        $items = $items->limit(10)->get()->transform(function ($row) {
            $full_description = $this->getFullDescription($row);
            return [
                'id' => $row->id,
                'full_description' => $full_description,
                'internal_id' => $row->internal_id,
                'description' => $row->description,
            ];
        });
        return response()->json(['items' => $items]);
    }
    public function excelGain(Request $request){
        $query = $this->getRecords($request)->whereIn('inventory_kardexable_type', [Document::class, SaleNote::class]);
        $records = $query->get();
        
        $export = new \Modules\Inventory\Exports\ItemCostHistoryGainExport();
        
        return $export
            ->records($records)
            ->download('Ganancias_'.date('YmdHis').'.xlsx');
    }
    public function excel(Request $request){
        $query = $this->getRecords($request);
        $records = $query->get();
        
        $export = new \Modules\Inventory\Exports\ItemCostHistoryExport();
        
        return $export
            ->records($records)
            ->download('Kardex_'.date('YmdHis').'.xlsx');
    }
    public function getRecords(Request $request){

        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id;
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $exist_history = ItemCostHistory::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->exists();
        if (!$exist_history) {
            $this->createHistoryByItemId($item_id, $warehouse_id);
        } else {
            $exist_pending_reset = PendingItemCostReset::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->exists();
            if ($exist_pending_reset) {
                $this->calculateHistoryByItemId($item_id, $warehouse_id);
            }
        }
        $records = ItemCostHistory::where('item_id', $item_id)->where('warehouse_id', $warehouse_id);
        if ($date_start && $date_end) {
            $records->whereBetween('date', [$date_start, $date_end]);
        } else if ($date_start) {
            $records->where('date', '>=', $date_start);
        } else if ($date_end) {
            $records->where('date', '<=', $date_end);
        }
        return $records;
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        $request->merge(['with_format' => true]);
        return new ItemCostHistoryCollection($records->paginate(20));
    }

    public function createHistoryByItemId($item_id, $warehouse_id)
    {
        $itemCostHistoryService = new ItemCostHistoryService();
        $itemCostHistoryService->createHistoryByItemId($item_id, $warehouse_id);
        return response()->json(['message' => 'Historial de costos creado correctamente']);
    }

    public function calculateHistoryByItemId($item_id, $warehouse_id)
    {
        $itemCostHistoryService = new ItemCostHistoryService();
        $itemCostHistoryService->calculateHistoryByItemId($item_id, $warehouse_id);
        return response()->json(['message' => 'Historial de costos calculado correctamente']);
    }

    public function filter()
    {
        $warehouses = [];
        $user = User::query()->find(auth()->id());

        $records = Warehouse::query();
        if(!in_array($user->type, ['admin', 'superadmin'])){
            $records->where('establishment_id', $user->establishment_id);
        }
        $records = $records->get();

        foreach ($records as $record) {
            $warehouses[] = [
                'id' => $record->id,
                'name' => $record->description,
            ];
        }

        return [
            'warehouses' => $warehouses
        ];
    }

    public function filterByWarehouse($warehouse_id)
    {
        $query = Item::query()->whereNotIsSet()
            ->with('warehouses')
            ->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);

        if ($warehouse_id !== 'all') {
            $query->whereHas('warehouses', function ($query) use ($warehouse_id) {
                return $query->where('warehouse_id', $warehouse_id);
            });
        }

        $items = $query->latest()
            ->limit(10)
            ->get()
            ->transform(function ($row) {
                $full_description = $this->getFullDescription($row);
                return [
                    'id' => $row->id,
                    'full_description' => $full_description,
                    'internal_id' => $row->internal_id,
                    'description' => $row->description,
                ];
            });

        return [
            'items' => $items
        ];
    }
    public function getFullDescription($row)
    {
        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }
}
