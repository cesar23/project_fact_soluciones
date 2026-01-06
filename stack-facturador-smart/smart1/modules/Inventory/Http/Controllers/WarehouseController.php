<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Http\Resources\WarehouseCollection;
use Modules\Inventory\Http\Resources\WarehouseResource;
use Modules\Inventory\Http\Requests\WarehouseRequest;
use Modules\Inventory\Models\Warehouse;

class WarehouseController extends Controller
{

    public function stockApi(Request $request)
    {
        $company = Company::first();
        $products = DB::connection('tenant')->table('items as i')
            ->leftJoin('item_warehouse as iw', 'i.id', '=', 'iw.item_id')
            ->select(
                'i.id',
                'i.internal_id as sku',
                'i.item_code',
                'i.barcode',
                'i.name',
                'i.description',
                'i.sale_unit_price as price',
                DB::raw('COALESCE(SUM(iw.stock), i.stock, 0) as stock_total')
            )
            ->where('i.active', 1)
            ->where('i.item_type_id', '01') // Solo productos
            ->groupBy(
                'i.id',
                'i.internal_id',
                'i.item_code',
                'i.barcode',
                'i.name',
                'i.description',
                'i.sale_unit_price',
                'i.stock'
            )
            ->get();

        return response()->json([
            'success' => true,
            'company' => $company->name,
            'total_products' => $products->count(),
            'data' => $products
        ]);
    }

    public function stockApiByInternalId($sku)
    {
        $product = DB::connection('tenant')->table('items as i')
            ->leftJoin('item_warehouse as iw', 'i.id', '=', 'iw.item_id')
            ->select(
                'i.id',
                'i.internal_id as sku',
                'i.item_code',
                'i.barcode',
                'i.name',
                'i.sale_unit_price as price',
                DB::raw('COALESCE(SUM(iw.stock), i.stock, 0) as stock_total')
            )
            ->where('i.active', 1)
            ->where(function ($query) use ($sku) {
                $query->where('i.internal_id', $sku)
                    ->orWhere('i.item_code', $sku)
                    ->orWhere('i.barcode', $sku);
            })
            ->groupBy(
                'i.id',
                'i.internal_id',
                'i.item_code',
                'i.barcode',
                'i.name',
                'i.sale_unit_price',
                'i.stock'
            )
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }
    public function index()
    {
        return view('inventory::warehouses.index');
    }

    public function columns()
    {
        return [
            'description' => 'Descripción'
        ];
    }

    public function allRecords()
    {
        $records = Warehouse::all();
        return new WarehouseCollection($records);
    }

    public function records(Request $request)
    {
        if ($request->column && $request->value) {
            $records = Warehouse::where($request->column, 'like', "%{$request->value}%");
        } else {
            $records = Warehouse::query();
        }

        return new WarehouseCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function record($id)
    {
        $record = new WarehouseResource(Warehouse::findOrFail($id));

        return $record;
    }

    public function store(WarehouseRequest $request)
    {
        $id = $request->input('id');
        if (!$id) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            if ($warehouse) {
                return [
                    'success' => false,
                    'message' => 'Solo es posible registrar un almacén por establecimiento.'
                ];
            }
        }

        $record = Warehouse::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        if (!$id) {
            $record->establishment_id = auth()->user()->establishment_id;
        }
        $record->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Almacén editado con éxito' : 'Almacén registrado con éxito',
            'id' => $record->id
        ];
    }
}
