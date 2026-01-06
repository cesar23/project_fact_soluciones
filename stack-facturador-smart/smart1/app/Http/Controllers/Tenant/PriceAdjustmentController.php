<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PriceAdjustment;
use App\Models\Tenant\PriceAdjustmentItem;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Illuminate\Support\Facades\DB;

class PriceAdjustmentController extends Controller
{
    public function index()
    {
        return view('tenant.price_adjustments.index');
    }

    public function records()
    {
        $records = PriceAdjustment::with(['applied_by_user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'data' => collect($records)->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'apply_to' => $this->getApplyToText($row->apply_to),
                    'adjustment_type' => $row->adjustment_type === 'percentage' ? 'Porcentaje' : 'Monto',
                    'operation' => $row->operation === 'increase' ? 'Aumento' : 'Disminución',
                    'value' => $row->value,
                    'applied' => $row->applied ? 'Si' : 'No',
                    'applied_at' => $row->applied_at ? $row->applied_at->format('d/m/Y H:i') : '-',
                    'applied_by' => $row->applied_by_user->name ?? '-',
                    'items_affected' => $row->items_affected,
                    'notes' => $row->notes ?? '-',
                    'date' => $row->applied_at ? $row->applied_at->format('d/m/Y H:i') : $row->created_at->format('d/m/Y H:i')
                ];
            })
        ];
    }

    private function getApplyToText($apply_to)
    {
        $texts = [
            'all' => 'Todos los productos',
            'category' => 'Por categoría',
            'brand' => 'Por marca',
            'specific' => 'Productos específicos'
        ];

        return $texts[$apply_to] ?? $apply_to;
    }

    public function record($id)
    {
        $record = PriceAdjustment::with(['price_adjustment_items.brand', 'price_adjustment_items.category'])
            ->findOrFail($id);

        return ['data' => $record];
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'apply_to' => 'required|in:all,category,brand,specific',
            'adjustment_type' => 'required|in:percentage,amount',
            'operation' => 'required|in:increase,decrease',
            'value' => 'required|numeric|min:0'
        ], [
            'description.required' => 'El campo descripción es obligatorio',
            'apply_to.required' => 'Debe seleccionar a qué aplicar el ajuste',
            'adjustment_type.required' => 'Debe seleccionar el tipo de ajuste',
            'operation.required' => 'Debe seleccionar la operación',
            'value.required' => 'El campo valor es obligatorio',
            'value.numeric' => 'El valor debe ser un número',
            'value.min' => 'El valor no puede ser negativo'
        ]);

        DB::beginTransaction();

        try {
            $id = $request->input('id');
            $priceAdjustment = PriceAdjustment::firstOrNew(['id' => $id]);

            $priceAdjustment->description = $request->description;
            $priceAdjustment->apply_to = $request->apply_to;
            $priceAdjustment->adjustment_type = $request->adjustment_type;
            $priceAdjustment->operation = $request->operation;
            $priceAdjustment->value = $request->value;
            $priceAdjustment->notes = $request->notes;
            $priceAdjustment->save();

            // Limpiar items anteriores si está editando
            if ($id) {
                $priceAdjustment->price_adjustment_items()->delete();
            }

            // Guardar los items según el tipo de aplicación
            if ($request->apply_to === 'brand' && $request->has('selected_brands')) {
                $selectedBrands = json_decode($request->selected_brands, true);
                foreach ($selectedBrands as $brandId) {
                    $priceAdjustment->price_adjustment_items()->create([
                        'brand_id' => $brandId
                    ]);
                }
            } elseif ($request->apply_to === 'category' && $request->has('selected_categories')) {
                $selectedCategories = json_decode($request->selected_categories, true);
                foreach ($selectedCategories as $categoryId) {
                    $priceAdjustment->price_adjustment_items()->create([
                        'category_id' => $categoryId
                    ]);
                }
            } elseif ($request->apply_to === 'specific' && $request->has('selected_items')) {
                $selectedItems = json_decode($request->selected_items, true);
                foreach ($selectedItems as $itemId) {
                    $priceAdjustment->price_adjustment_items()->create([
                        'item_id' => $itemId
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => ($id) ? 'Ajuste de precio editado con éxito' : 'Ajuste de precio registrado con éxito'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $priceAdjustment = PriceAdjustment::findOrFail($id);

            if ($priceAdjustment->applied) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar un ajuste que ya fue aplicado'
                ];
            }

            $priceAdjustment->price_adjustment_items()->delete();
            $priceAdjustment->delete();

            return [
                'success' => true,
                'message' => 'Ajuste de precio eliminado con éxito'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function apply($id)
    {
        try {
            $priceAdjustment = PriceAdjustment::findOrFail($id);
            $result = $priceAdjustment->apply();

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al aplicar ajuste: ' . $e->getMessage()
            ];
        }
    }

    public function preview($id)
    {
        try {
            $priceAdjustment = PriceAdjustment::findOrFail($id);

            // Construir la query base según el tipo de aplicación
            $query = Item::select('id', 'description', 'internal_id', 'sale_unit_price');

            if ($priceAdjustment->apply_to === 'category') {
                $categoryIds = $priceAdjustment->price_adjustment_items()
                    ->whereNotNull('category_id')
                    ->pluck('category_id')
                    ->toArray();
                $query->whereIn('category_id', $categoryIds);
            } elseif ($priceAdjustment->apply_to === 'brand') {
                $brandIds = $priceAdjustment->price_adjustment_items()
                    ->whereNotNull('brand_id')
                    ->pluck('brand_id')
                    ->toArray();
                $query->whereIn('brand_id', $brandIds);
            } elseif ($priceAdjustment->apply_to === 'specific') {
                $itemIds = $priceAdjustment->price_adjustment_items()
                    ->whereNotNull('item_id')
                    ->pluck('item_id')
                    ->toArray();
                $query->whereIn('id', $itemIds);
            }

            // Obtener solo los primeros 100 para la vista previa
            $items = $query->limit(100)->get();

            // Calcular los nuevos precios
            $preview = $items->map(function ($item) use ($priceAdjustment) {
                $oldPrice = $item->sale_unit_price;
                $newPrice = $this->calculateNewPrice($oldPrice, $priceAdjustment);

                return [
                    'internal_id' => $item->internal_id,
                    'description' => $item->description,
                    'old_price' => number_format($oldPrice, 2),
                    'new_price' => number_format($newPrice, 2),
                    'difference' => number_format($newPrice - $oldPrice, 2)
                ];
            });

            // Contar el total sin limit
            $totalQuery = Item::query();
            if ($priceAdjustment->apply_to === 'category') {
                $categoryIds = $priceAdjustment->price_adjustment_items()
                    ->whereNotNull('category_id')
                    ->pluck('category_id')
                    ->toArray();
                $totalQuery->whereIn('category_id', $categoryIds);
            } elseif ($priceAdjustment->apply_to === 'brand') {
                $brandIds = $priceAdjustment->price_adjustment_items()
                    ->whereNotNull('brand_id')
                    ->pluck('brand_id')
                    ->toArray();
                $totalQuery->whereIn('brand_id', $brandIds);
            } elseif ($priceAdjustment->apply_to === 'specific') {
                $itemIds = $priceAdjustment->price_adjustment_items()
                    ->whereNotNull('item_id')
                    ->pluck('item_id')
                    ->toArray();
                $totalQuery->whereIn('id', $itemIds);
            }
            $totalItems = $totalQuery->count();

            return [
                'success' => true,
                'data' => $preview,
                'total_items' => $totalItems
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar vista previa: ' . $e->getMessage()
            ];
        }
    }

    private function calculateNewPrice($oldPrice, $priceAdjustment)
    {
        if ($priceAdjustment->adjustment_type === 'percentage') {
            $adjustment = $oldPrice * ($priceAdjustment->value / 100);
        } else {
            $adjustment = $priceAdjustment->value;
        }

        if ($priceAdjustment->operation === 'increase') {
            return $oldPrice + $adjustment;
        } else {
            return max(0, $oldPrice - $adjustment);
        }
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

    public function searchItems(Request $request)
    {
        $items = Item::where('description', 'like', '%' . $request->input . '%')
            ->orWhere('internal_id', 'like', '%' . $request->input . '%')
            ->select('id', 'description', 'internal_id', 'sale_unit_price')
            ->limit(100)
            ->get();

        return compact('items');
    }
}