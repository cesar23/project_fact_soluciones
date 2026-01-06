<?php

namespace Modules\Item\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\ItemAttribute;
use Modules\Item\Models\IngredientAttributeItem;
use Modules\Item\Models\LineAttributeItem;
use App\Models\Tenant\Item;
use Exception;
use App\Traits\CacheTrait;

class ItemAttributeController extends Controller
{
    use CacheTrait;

    public function index()
    {
        return view('item::item_attributes.index');
    }

    public function columns()
    {
        return [
            'item_id' => 'ID Producto',
            'cat_line_id' => 'Línea',
            'cat_ingredient_id' => 'Ingrediente',
        ];
    }

    public function records(Request $request)
    {
        $records = ItemAttribute::with(['item', 'line', 'ingredient'])
            ->where($request->column, 'like', "%{$request->value}%")
            ->latest();

        return response()->json($records->paginate(config('tenant.items_per_page')));
    }

    public function record($id)
    {
        $record = ItemAttribute::with(['item', 'line', 'ingredient'])->findOrFail($id);

        return $record;
    }

    /**
     * Crea o edita un atributo de item.
     *
     * @param Request $request
     *
     * @return array
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'cat_line_id' => 'nullable|exists:cat_line,id',
            'cat_ingredient_id' => 'nullable|exists:cat_ingredient,id',
        ]);

        $id = (int)$request->input('id');
        $item_id = $request->input('item_id');

        $error = null;
        $itemAttribute = null;

        // Validar que no exista ya un registro para este item
        if (!empty($item_id)) {
            $itemAttribute = ItemAttribute::where('item_id', $item_id);
            if (empty($id)) {
                $itemAttribute = $itemAttribute->first();
                if (!empty($itemAttribute)) {
                    $error = 'Ya existe un atributo para este producto';
                }
            } else {
                $itemAttribute = $itemAttribute->where('id', '!=', $id)->first();
                if (!empty($itemAttribute)) {
                    $error = 'Ya existe un atributo para este producto en otro registro';
                }
            }
        }

        $data = [
            'success' => false,
            'message' => $error,
            'data' => $itemAttribute
        ];

        if (empty($error)) {
            $itemAttribute = ItemAttribute::firstOrNew(['id' => $id]);
            $itemAttribute->fill($request->all());
            $itemAttribute->save();

            $data = [
                'success' => true,
                'message' => ($id) ? 'Atributo de producto editado con éxito' : 'Atributo de producto registrado con éxito',
                'data' => $itemAttribute->load(['item', 'line', 'ingredient'])
            ];
        }

        return $data;
    }

    public function destroy($id)
    {
        try {
            $itemAttribute = ItemAttribute::findOrFail($id);
            $itemAttribute->delete();

            return [
                'success' => true,
                'message' => 'Atributo de producto eliminado con éxito'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Error inesperado, no se pudo eliminar el atributo"];
        }
    }

    /**
     * Obtiene los ingredientes activos para selección
     */
    public function getIngredients()
    {
        return IngredientAttributeItem::getIngredientsOrderByName();
    }

    /**
     * Obtiene las líneas activas para selección
     */
    public function getLines()
    {
        return LineAttributeItem::getLinesOrderByName();
    }

    /**
     * Obtiene los atributos de un item específico
     */
    public function getItemAttributes($item_id)
    {
        $attributes = ItemAttribute::with(['line', 'ingredient'])
            ->where('item_id', $item_id)
            ->first();

        return $attributes;
    }
}