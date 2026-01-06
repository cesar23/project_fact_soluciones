<?php

namespace App\Http\Controllers\Tenant;

use App\Imports\ItemsImport;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\Tenant\ItemRequest;
use App\Http\Resources\Tenant\ItemCollection;
use App\Http\Resources\Tenant\ItemResource;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\ItemUnitType;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Modules\Account\Models\Account;
use App\Models\Tenant\ItemTag;
use App\Models\Tenant\Catalogs\Tag;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\HistoryReplaceSet;
use App\Models\Tenant\ItemSet;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\ItemWarehousePrice;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Helpers\UploadFileHelper;
use Modules\Item\Http\Resources\HistorySetReplaceCollection;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Modules\Item\Models\WebPlatform;


class ItemSetController extends Controller
{
    public function index()
    {
        return view('tenant.item_sets.index');
    }


    public function columns()
    {
        $to_return = [
            'description' => 'Nombre',
            'internal_id' => 'Código interno',
            'label_color' => 'Etiqueta',
            'model' => 'Modelo',
            'items' => 'Productos',
        ];
        $configuration = Configuration::getConfig();
        if(!$configuration->label_item_color){
            unset($to_return['label_color']);
        }

        return $to_return;  
    }

    public function records(Request $request)
    {
        $records = Item::whereTypeUser()
            ->whereIsSet();
        if ($request->column == 'items' && $request->value) {
            $records->whereHas('sets', function ($query) use ($request) {
                $query->whereHas('individual_item', function ($query) use ($request) {
                    $query->where('description', 'like', "%{$request->value}%")
                        ->orWhere('internal_id', 'like', "%{$request->value}%");
                });
            });
        }
        else if ($request->column == 'label_color' && $request->value) {
            $records->whereHas('labelColor', function ($query) use ($request) {
                $query->whereHas('labelColor', function ($query) use ($request) {
                    $query->where('description', 'like', "%{$request->value}%");
                });
            });
        }
        else if ($request->column !== 'items' && $request->value) {
            $records->where($request->column, 'like', "%{$request->value}%");
        }

        return new ItemCollection($records->orderBy('description')->paginate(config('tenant.items_per_page')));
    }

    public function create()
    {
        // return view('tenant.items.form');
        return view('tenant.item_sets.index');
    }
    public function recordsIndividualsNotSet(Request $request)
    {
        $input = $request->input;
        $records = Item::where(function ($query) use ($input) {
            $query->where('description', 'like', "%{$input}%")
                ->orWhere('internal_id', 'like', "%{$input}%");
        })->whereNotIsSet()
            ->limit(20)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'full_description' => $row->internal_id . "-" . $row->description,
                    'internal_id' => $row->internal_id,
                ];
            });

        return compact('records');
    }
    public function recordsIndividuals(Request $request)
    {
        $input = $request->input;
        $records = Item::where(function ($query) use ($input) {
            $query->where('description', 'like', "%{$input}%")
                ->orWhere('internal_id', 'like', "%{$input}%");
        })->whereHas('sets_individual')
            ->limit(20)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'full_description' => $row->internal_id . "-" . $row->description,
                    'internal_id' => $row->internal_id,
                ];
            });

        return compact('records');
    }
    public function replace($item_id, $item_replace_id)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $item = Item::find($item_id);
            $item_replace = Item::find($item_replace_id);
            $count_item = ItemSet::where('individual_item_id', $item_id)->count();
            $message = 'El producto ' . $item->internal_id . ' - ' . $item->description . ' no esta siendo usado en ningun registro.';
            if ($count_item == 0) {
                return [
                    'success' => false,
                    'message' => $message,
                ];
            }
            ItemSet::where('individual_item_id', $item_id)->update(['individual_item_id' => $item_replace_id]);
            HistoryReplaceSet::create([
                'internal_id_item' => $item->internal_id,
                'description_item' => $item->description,
                'item_id' => $item_id,
                'internal_id_replace' => $item_replace->internal_id,
                'description_replace' => $item_replace->description,
                'replace_id' => $item_replace_id,
                'quantity' => $count_item,
                'user_id' => auth()->user()->id,
            ]);
            $message = 'Producto reemplazado con éxito en ' . $count_item . ' registro(s)';
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => $message,
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function tables()
    {
        $unit_types = UnitType::whereActive()->orderByDescription()->get();
        $currency_types = CurrencyType::whereActive()->orderByDescription()->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $system_isc_types = SystemIscType::whereActive()->orderByDescription()->get();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $web_platforms = WebPlatform::get();
        $categories = Category::all();
        $brands = Brand::all();
        $warehouse_id =null;
        $user = auth()->user();
        $establishment_id = $user->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        if($warehouse){
            $warehouse_id = $warehouse->id;
        }
        // $accounts = Account::all();
        // $tags = Tag::all();
        $user = auth()->user();
        $warehouses = [];
        if ($user->type == 'admin') {
            $warehouses = Warehouse::where('active', true)->get();
        } else {
            $establishment_id = $user->establishment_id;
            $warehouses = Warehouse::where('establishment_id', $establishment_id)->get();
        }

        return compact(
            'unit_types',
            'currency_types',
            'brands',
            'categories',
            'attribute_types',
            'system_isc_types',
            'affectation_igv_types',
            'web_platforms',
            'warehouses',
            'warehouse_id'
        );
    }


    public function item_tables(Request $request)
    {
        $establishment_id = auth()->user()->establishment_id;
        $warehouse_id = $request->warehouse_id ?? Warehouse::where('establishment_id', $establishment_id)->first()->id;
        // $warehouse_id = $request->warehouse_id ?? Warehouse::first()->id;
        $individual_items = Item::whereHas('warehouses', function ($query) use ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        })->
            // whereWarehouse()->
            whereTypeUser()->whereNotIsSet()->whereIsActive()->get()->transform(function ($row) {
                $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
                return [
                    'id' => $row->id,
                    'full_description' => $full_description,
                    'internal_id' => $row->internal_id,
                    'description' => $row->description,
                    'sale_unit_price' => $row->sale_unit_price,
                    'purchase_unit_price' => $row->purchase_unit_price,
                ];
            });

        return compact('individual_items');
    }


    public function record($id)
    {
        $record = new ItemResource(Item::findOrFail($id));

        return $record;
    }

    public function store(ItemRequest $request)
    {

        $id = $request->input('id');

        $record =  DB::connection('tenant')->transaction(function () use ($request, $id) {

            $configuration = Configuration::first();
            $item = Item::firstOrNew(['id' => $id]);
            $item->item_type_id = '01';
            $item->fill($request->all());

            $temp_path = $request->input('temp_path');
            if ($temp_path) {

                UploadFileHelper::checkIfValidFile($request->input('image'), $temp_path, true);

                $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR;

                $file_name_old = $request->input('image');
                $file_name_old_array = explode('.', $file_name_old);
                $file_content = file_get_contents($temp_path);
                $datenow = date('YmdHis');
                $file_name = Str::slug($item->description) . '-' . $datenow . '.' . $file_name_old_array[1];
                Storage::put($directory . $file_name, $file_content);
                $item->image = $file_name;
            } else if (!$request->input('image') && !$request->input('temp_path') && !$request->input('image_url')) {
                $item->image = 'imagen-no-disponible.jpg';
            }
            $label_color_id = $request->label_color_id;
            $warehouse_id = $request->warehouse_id ?? 1;
    

            $item->save();

            $id = $item->id;

            $item->sets()->delete();

            foreach ($request->individual_items as $row) {

                $item->sets()->create([
                    'individual_item_id' => $row['individual_item_id'],
                    'quantity' => $row['quantity'],
                ]);
            }

            $item->update();
            $individual_items = $item->sets()->get();
            $warehousesSelected = $request->warehousesSelected;
            if ($warehousesSelected && is_iterable($warehousesSelected)) {
                foreach ($warehousesSelected as $row) {
                    foreach ($individual_items as $item) {
                        if (!$item->individual_item->warehouses()->where('warehouse_id', $row)->exists()) {
                            $item->individual_item->warehouses()->create([
                                'warehouse_id' => $row,
                                'stock' => 0,
                            ]);
                        }
                    }
                    if (!$item->item->warehouses()->where('warehouse_id', $row)->exists()) {
                        $item->item->warehouses()->create([
                            'warehouse_id' => $row,
                            'stock' => 0,
                        ]);
                    }
                }
            } else {
                $warehouse_id = $item->warehouse_id;
                if ($warehouse_id) {
                    $item_wh = ItemWarehouse::where('warehouse_id', $warehouse_id)->where('item_id', $item->id)->first();
                    if (!$item_wh) {
                        $item->warehouses()->create([
                            'warehouse_id' => $warehouse_id,
                            'stock' => 0,
                        ]);
                    }
                    $item->warehouses()->where('warehouse_id', '!=', $warehouse_id)->delete();
                }
            }


            if ($configuration->item_set_warehouse_price) {
                ItemWarehousePrice::where('item_id', $item->id)->delete();
                foreach ($request->item_warehouse_prices as $row) {
                    ItemWarehousePrice::create([
                        'item_id' => $id,
                        'warehouse_id' => $row['warehouse_id'],
                        'price' => $row['price'],
                    ]);
                }
            }
            if($label_color_id){
                $item->labelColor()->where('warehouse_id', $warehouse_id)->delete();
                $item->labelColor()->create([
                    'label_color_id' => $label_color_id,
                    'warehouse_id' => $warehouse_id
                ]);
            }


            return $item;
        });


        return [
            'success' => true,
            'message' => ($id) ? 'Producto compuesto editado con éxito' : 'Producto compuesto registrado con éxito',
            'id' => $record->id
        ];
    }

    public function destroy($id)
    {
        try {

            $item = Item::findOrFail($id);
            $this->deleteRecordInitialKardex($item);
            $item->delete();

            return [
                'success' => true,
                'message' => 'Producto compuesto eliminado con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => 'El producto compuesto esta siendo usado por otros registros, no puede eliminar'] : ['success' => false, 'message' => 'Error inesperado, no se pudo eliminar el producto compuesto'];
        }
    }

    public function destroyItemUnitType($id)
    {
        $item_unit_type = ItemUnitType::findOrFail($id);
        $item_unit_type->delete();

        return [
            'success' => true,
            'message' => 'Registro eliminado con éxito'
        ];
    }


    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function history(Request $request)
    {
        $records = HistoryReplaceSet::query();

        return new HistorySetReplaceCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function upload(Request $request)
    {

        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg');

        if (!$validate_upload['success']) {
            return $validate_upload;
        }

        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];

            return $this->upload_image($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    function upload_image($request)
    {
        $file = $request['file'];
        $type = $request['type'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }

    private function deleteRecordInitialKardex($item)
    {

        if ($item->kardex->count() == 1) {
            ($item->kardex[0]->type == null) ? $item->kardex[0]->delete() : false;
        }
    }


    public function visibleStore(Request $request)
    {
        $item = Item::find($request->id);
        $visible = $request->apply_store == true ? 1 : 0;
        $item->apply_store = $visible;
        $item->save();

        return [
            'success' => true,
            'message' => ($visible > 0) ? 'El Producto ya es visible en tienda virtual' : 'El Producto ya no es visible en tienda virtual',
            'id' => $request->id
        ];
    }
}
