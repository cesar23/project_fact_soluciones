<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ChargeType;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use App\Imports\ChargeItemsImport;
use App\Models\Tenant\ChargeTypeItem;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ChargeTypeController extends Controller
{
    public function index()
    {
        return view('tenant.charge_types.index');
    }

    public function columns()
    {
        return [
            'description' => 'Descripción',
            'charge_value' => 'Valor de cargo',
            'is_percentage' => 'Es porcentaje',
            'active' => 'Estado'
        ];
    }

    public function records()
    {
        $manual_records = ChargeType::where('type', 'manual')->get();
        $item_records = ChargeType::where('type','<>' ,'manual')->get();

        return [
            'manual' => collect($manual_records)->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'charge_value' => $row->charge_value,
                    'is_percentage' => $row->is_percentage ? 'Si' : 'No',
                    'active' => $row->active ? 'Activo' : 'Inactivo',
                    'image' => $row->image
                ];
            }),
            'items' => collect($item_records)->transform(function ($row) {
                $charge_type_items = $row->charge_type_items->first();
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'charge_value' => $row->charge_value,
                    'is_percentage' => $row->is_percentage ? 'Si' : 'No',
                    'charge_type_items' => $charge_type_items ? [
                        [
                            'brand_id' => $charge_type_items->brand_id,
                            'category_id' => $charge_type_items->category_id,
                            'item_id' => $charge_type_items->item_id
                        ]
                    ] : null,
                    'active' => $row->active ? 'Activo' : 'Inactivo'
                ];
            })
        ];
    }

    public function idExistsInChargeTypeItems($id, $charge_type_id){
        $exists = ChargeTypeItem::whereRaw('charges_type_id = ? AND item_id = ?', [$charge_type_id, $id])
                              ->exists();

        return response()->json(['exists' => $exists]);

    }

    public function record($id)
    {
        $record = ChargeType::with(['charge_type_items.brands:id,name', 'charge_type_items.categories:id,name'])
            ->findOrFail($id);
        return ['data' => $record];
    }

    public function searchCategories(Request $request)
    {
        $categories = Category::select('id', 'name')
            ->where('name', 'like', '%' . $request->q . '%')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function searchBrands(Request $request)
    {
        $brands = Brand::select('id', 'name')
            ->where('name', 'like', '%' . $request->q . '%')
            ->orderBy('name')
            ->get();

        return response()->json($brands);
    }


    public function getCategories()
    {
        $categories = Category::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function getBrands()
    {
        $brands = Brand::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($brands);
    }

    public function uploadTempFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $filename = 'temp_' . time() . '.' . $ext;

            $file->storeAs('temp', $filename);

            return response()->json([
                'success' => true,
                'filename' => $filename
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró ningún archivo'
        ], 400);
    }

    public function uploadTempImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $filename = 'temp_' . time() . '.' . $ext;

            $file->storeAs('temp', $filename);

            return response()->json([
                'success' => true,
                'filename' => $filename
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró ningún archivo'
        ], 400);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'charge_value' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:category,brand,specific,all,manual',
            'apply_to' => 'nullable|in:all,specific,brand,category'
        ], [
            'description.required' => 'El campo descripción es obligatorio',
            'charge_value.required' => 'El campo valor de cargo es obligatorio',
            'charge_value.numeric' => 'El valor de cargo debe ser un número',
            'charge_value.min' => 'El valor de cargo no puede ser negativo',
            'charge_value.max' => 'El valor de cargo no puede ser mayor a 100'
        ]);

        $id = $request->input('id');
        $charge_type = ChargeType::firstOrNew(['id' => $id]);

        $charge_type->description = $request->description;
        $charge_type->charge_value = $request->charge_value;
        $charge_type->type = $request->type;
        $charge_type->is_percentage = true;
        $charge_type->active = true;

        // Manejar la imagen si existe
        if ($request->temp_file && $request->type === 'manual') {
            $temp_path = storage_path('app/temp/' . $request->temp_file);
            if (file_exists($temp_path)) {
                $new_filename = 'charge_' . time() . '.' . pathinfo($request->temp_file, PATHINFO_EXTENSION);
                Storage::move('temp/' . $request->temp_file, 'public/charges/' . $new_filename);
                $charge_type->image = $new_filename;
            }
        }

        $charge_type->save();

        // Manejar las diferentes formas de aplicar el cargo
        if ($request->apply_to === 'all') {
            $charge_type->apply_to_all_items = true;
            $charge_type->save();
        }
        elseif ($request->apply_to === 'specific' && $request->temp_file) {
            // Procesar archivo Excel
            try {
                $temp_path = storage_path('app/temp/' . $request->temp_file);
                if (file_exists($temp_path)) {
                    // Primero eliminamos los registros anteriores
                    $charge_type->charge_type_items()->delete();

                    // Importar los items desde el Excel
                    $import = new ChargeItemsImport($charge_type->id);
                    Excel::import($import, $temp_path);

                    $results = $import->getResults();

                    // Si no hay éxitos y solo hay errores, revertir
                    if (empty($results['success']) && (!empty($results['errors']) || !empty($results['duplicates']))) {
                        return [
                            'success' => false,
                            'message' => 'No se pudo procesar ningún producto del archivo',
                            'data' => $results
                        ];
                    }

                    $charge_type->save();

                    return [
                        'success' => true,
                        'message' => 'Tipo de cargo registrado con éxito. ' . count($results['success']) . ' productos procesados.',
                        'data' => $results
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error al procesar el archivo: ' . $e->getMessage()
                ];
            }
        }
        elseif ($request->apply_to === 'brand' && $request->has('selected_brands')) {
            $selected_brands = json_decode($request->selected_brands, true);
            if (!empty($selected_brands)) {
                $charge_type->charge_type_items()->delete();
                foreach ($selected_brands as $brand_id) {
                    $charge_type->charge_type_items()->create([
                        'brand_id' => $brand_id
                    ]);
                }
            }
        }
        elseif ($request->apply_to === 'category' && $request->has('selected_categories')) {
            $selected_categories = json_decode($request->selected_categories, true);
            if (!empty($selected_categories)) {
                $charge_type->charge_type_items()->delete();
                foreach ($selected_categories as $category_id) {
                    $charge_type->charge_type_items()->create([
                        'category_id' => $category_id
                    ]);
                }
            }
        }

        return [
            'success' => true,
            'message' => ($id) ? 'Tipo de cargo editado con éxito' : 'Tipo de cargo registrado con éxito'
        ];
    }

    public function destroy($id)
    {
        try {
            $charge_type = ChargeType::findOrFail($id);
            $charge_type->charge_type_items()->delete();
            $charge_type->delete();

            return [
                'success' => true,
                'message' => 'Tipo de cargo eliminado con éxito'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getChargeItems($id)
    {
        $charge = ChargeType::findOrFail($id);
        $items = [];
        $type = '';

        if ($charge->apply_to_all_items) {
            return [
                'success' => true,
                'type' => 'all',
                'items' => []
            ];
        }

        // Obtener los items según el tipo
        $charge_items = $charge->charge_type_items;

        if ($charge_items->first()->brand_id) {
            $type = 'brand';
            $items = Brand::whereIn('id', $charge_items->pluck('brand_id'))
                ->select('id', 'name')
                ->get();
        } elseif ($charge_items->first()->category_id) {
            $type = 'category';
            $items = Category::whereIn('id', $charge_items->pluck('category_id'))
                ->select('id', 'name')
                ->get();
        } elseif ($charge_items->first()->item_id) {
            $type = 'specific';
            $items = Item::whereIn('id', $charge_items->pluck('item_id'))
                ->select('id', 'description as name', 'internal_id')
                ->get();
        }

        return [
            'success' => true,
            'type' => $type,
            'items' => $items
        ];
    }

    public function addChargeItem(Request $request, $id)
    {
        $charge = ChargeType::findOrFail($id);

        // Verificar si ya existe el item según el tipo
        $exists = false;
        if ($request->type === 'brand') {
            $exists = $charge->charge_type_items()
                ->where('brand_id', $request->item_id)
                ->exists();

            if ($exists) {
                return [
                    'success' => false,
                    'message' => 'Esta marca ya está registrada en el cargo'
                ];
            }

            $charge->charge_type_items()->create([
                'brand_id' => $request->item_id
            ]);
        } elseif ($request->type === 'category') {
            $exists = $charge->charge_type_items()
                ->where('category_id', $request->item_id)
                ->exists();

            if ($exists) {
                return [
                    'success' => false,
                    'message' => 'Esta categoría ya está registrada en el cargo'
                ];
            }

            $charge->charge_type_items()->create([
                'category_id' => $request->item_id
            ]);
        } elseif ($request->type === 'specific') {
            $exists = $charge->charge_type_items()
                ->where('item_id', $request->item_id)
                ->exists();

            if ($exists) {
                return [
                    'success' => false,
                    'message' => 'Este producto ya está registrado en el cargo'
                ];
            }

            $charge->charge_type_items()->create([
                'item_id' => $request->item_id
            ]);
        }

        return [
            'success' => true,
            'message' => 'Item agregado correctamente'
        ];
    }

    public function removeChargeItem(Request $request, $id)
    {
        $charge = ChargeType::findOrFail($id);

        if ($request->type === 'brand') {
            $charge->charge_type_items()
                ->where('brand_id', $request->item_id)
                ->delete();
        } elseif ($request->type === 'category') {
            $charge->charge_type_items()
                ->where('category_id', $request->item_id)
                ->delete();
        } elseif ($request->type === 'specific') {
            $charge->charge_type_items()
                ->where('item_id', $request->item_id)
                ->delete();
        }

        return [
            'success' => true,
            'message' => 'Item eliminado correctamente'
        ];
    }

    public function importItems(Request $request, $id)
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún archivo'
            ], 400);
        }

        try {
            $import = new ChargeItemsImport($id);
            Excel::import($import, $request->file('file'));

            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => count($results['success']) . ' productos procesados correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchItems(Request $request)
    {
        $items = Item::where('description', 'like', '%' . $request->input . '%')
            ->orWhere('internal_id', 'like', '%' . $request->input . '%')
            ->select('id', 'description', 'internal_id')
            ->limit(20)
            ->get();

        return compact('items');
    }
}