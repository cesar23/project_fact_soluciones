<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DiscountType;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use App\Imports\DiscountItemsImport;
use App\Models\Tenant\DiscountTypeItem;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class DiscountTypeController extends Controller
{
    public function index()
    {
        return view('tenant.discount_types.index');
    }

    public function columns()
    {
        return [
            'description' => 'Descripción',
            'discount_value' => 'Valor de descuento',
            'is_percentage' => 'Es porcentaje',
            'active' => 'Estado'
        ];
    }

    public function records()
    {
        $manual_records = DiscountType::where('type', 'manual')->get();
        $item_records = DiscountType::where('type','<>' ,'manual')->get();

        return [
            'manual' => collect($manual_records)->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'discount_value' => $row->discount_value,
                    'is_percentage' => $row->is_percentage ? 'Si' : 'No',
                    'active' => $row->active ? 'Activo' : 'Inactivo',
                    'image' => $row->image
                ];
            }),
            'items' => collect($item_records)->transform(function ($row) {
                $discount_type_items = $row->discount_type_items->first();
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'discount_value' => $row->discount_value,
                    'is_percentage' => $row->is_percentage ? 'Si' : 'No',
                    'discount_type_items' => $discount_type_items ? [
                        [
                            'brand_id' => $discount_type_items->brand_id,
                            'category_id' => $discount_type_items->category_id,
                            'item_id' => $discount_type_items->item_id
                        ]
                    ] : null,
                    'active' => $row->active ? 'Activo' : 'Inactivo'
                ];
            })
        ];
    }
    public function idExistsInDiscountTypeItems($id, $discount_type_id){
        $exists = DiscountTypeItem::whereRaw('discounts_type_id = ? AND item_id = ?', [$discount_type_id, $id])
                              ->exists();

        return response()->json(['exists' => $exists]);

    }
    public function record($id)
    {
        $record = DiscountType::with(['discount_type_items.brands:id,name', 'discount_type_items.categories:id,name'])
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
            'discount_value' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:category,brand,specific,all,manual',
            'apply_to' => 'nullable|in:all,specific,brand,category'
        ], [
            'description.required' => 'El campo descripción es obligatorio',
            'discount_value.required' => 'El campo valor de descuento es obligatorio',
            'discount_value.numeric' => 'El valor de descuento debe ser un número',
            'discount_value.min' => 'El valor de descuento no puede ser negativo',
            'discount_value.max' => 'El valor de descuento no puede ser mayor a 100'
        ]);

        $id = $request->input('id');
        $discount_type = DiscountType::firstOrNew(['id' => $id]);
        
        $discount_type->description = $request->description;
        $discount_type->discount_value = $request->discount_value;
        $discount_type->type = $request->type;
        $discount_type->is_percentage = true;
        $discount_type->active = true;

        // Manejar la imagen si existe
        if ($request->temp_file && $request->type === 'manual') {
            $temp_path = storage_path('app/temp/' . $request->temp_file);
            if (file_exists($temp_path)) {
                $new_filename = 'discount_' . time() . '.' . pathinfo($request->temp_file, PATHINFO_EXTENSION);
                Storage::move('temp/' . $request->temp_file, 'public/discounts/' . $new_filename);
                $discount_type->image = $new_filename;
            }
        }
        
        $discount_type->save();

        // Manejar las diferentes formas de aplicar el descuento
        if ($request->apply_to === 'all') {
            $discount_type->apply_to_all_items = true;
            $discount_type->save();
        } 
        elseif ($request->apply_to === 'specific' && $request->temp_file) {
            // Procesar archivo Excel
            try {
                $temp_path = storage_path('app/temp/' . $request->temp_file);
                if (file_exists($temp_path)) {
                    // Primero eliminamos los registros anteriores
                    $discount_type->discount_type_items()->delete();
                    
                    // Importar los items desde el Excel
                    $import = new DiscountItemsImport($discount_type->id);
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
                    
                    // Mover el archivo a su ubicación final
                    // $new_filename = 'products_' . time() . '.' . pathinfo($request->temp_file, PATHINFO_EXTENSION);
                    // Storage::move('temp/' . $request->temp_file, 'discounts/' . $new_filename);
                    // $discount_type->file = $new_filename;
                    $discount_type->save();
                    
                    return [
                        'success' => true,
                        'message' => 'Tipo de descuento registrado con éxito. ' . count($results['success']) . ' productos procesados.',
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
                $discount_type->discount_type_items()->delete();
                foreach ($selected_brands as $brand_id) {
                    $discount_type->discount_type_items()->create([
                        'brand_id' => $brand_id
                    ]);
                }
            }
        }
        elseif ($request->apply_to === 'category' && $request->has('selected_categories')) {
            $selected_categories = json_decode($request->selected_categories, true);
            if (!empty($selected_categories)) {
                $discount_type->discount_type_items()->delete();
                foreach ($selected_categories as $category_id) {
                    $discount_type->discount_type_items()->create([
                        'category_id' => $category_id
                    ]);
                }
            }
        }

        return [
            'success' => true,
            'message' => ($id) ? 'Tipo de descuento editado con éxito' : 'Tipo de descuento registrado con éxito'
        ];
    }

    public function destroy($id)
    {
        try {
            $discount_type = DiscountType::findOrFail($id);
            $discount_type->discount_type_items()->delete();
            $discount_type->delete();

            return [
                'success' => true,
                'message' => 'Tipo de descuento eliminado con éxito'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getDiscountItems($id)
    {
        $discount = DiscountType::findOrFail($id);
        $items = [];
        $type = '';

        if ($discount->apply_to_all_items) {
            return [
                'success' => true,
                'type' => 'all',
                'items' => []
            ];
        }

        // Obtener los items según el tipo
        $discount_items = $discount->discount_type_items;

        if ($discount_items->first()->brand_id) {
            $type = 'brand';
            $items = Brand::whereIn('id', $discount_items->pluck('brand_id'))
                ->select('id', 'name')
                ->get();
        } elseif ($discount_items->first()->category_id) {
            $type = 'category';
            $items = Category::whereIn('id', $discount_items->pluck('category_id'))
                ->select('id', 'name')
                ->get();
        } elseif ($discount_items->first()->item_id) {
            $type = 'specific';
            $items = Item::whereIn('id', $discount_items->pluck('item_id'))
                ->select('id', 'description as name', 'internal_id')
                ->get();
        }

        return [
            'success' => true,
            'type' => $type,
            'items' => $items
        ];
    }

    public function addDiscountItem(Request $request, $id)
    {
        $discount = DiscountType::findOrFail($id);
        
        // Verificar si ya existe el item según el tipo
        $exists = false;
        if ($request->type === 'brand') {
            $exists = $discount->discount_type_items()
                ->where('brand_id', $request->item_id)
                ->exists();
                
            if ($exists) {
                return [
                    'success' => false,
                    'message' => 'Esta marca ya está registrada en el descuento'
                ];
            }
            
            $discount->discount_type_items()->create([
                'brand_id' => $request->item_id
            ]);
        } elseif ($request->type === 'category') {
            $exists = $discount->discount_type_items()
                ->where('category_id', $request->item_id)
                ->exists();
                
            if ($exists) {
                return [
                    'success' => false,
                    'message' => 'Esta categoría ya está registrada en el descuento'
                ];
            }
            
            $discount->discount_type_items()->create([
                'category_id' => $request->item_id
            ]);
        } elseif ($request->type === 'specific') {
            $exists = $discount->discount_type_items()
                ->where('item_id', $request->item_id)
                ->exists();
                
            if ($exists) {
                return [
                    'success' => false,
                    'message' => 'Este producto ya está registrado en el descuento'
                ];
            }
            
            $discount->discount_type_items()->create([
                'item_id' => $request->item_id
            ]);
        }

        return [
            'success' => true,
            'message' => 'Item agregado correctamente'
        ];
    }

    public function removeDiscountItem(Request $request, $id)
    {
        $discount = DiscountType::findOrFail($id);

        if ($request->type === 'brand') {
            $discount->discount_type_items()
                ->where('brand_id', $request->item_id)
                ->delete();
        } elseif ($request->type === 'category') {
            $discount->discount_type_items()
                ->where('category_id', $request->item_id)
                ->delete();
        } elseif ($request->type === 'specific') {
            $discount->discount_type_items()
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
            $import = new DiscountItemsImport($id);
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
