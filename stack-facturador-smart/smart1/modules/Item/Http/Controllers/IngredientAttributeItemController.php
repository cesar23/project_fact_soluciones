<?php

namespace Modules\Item\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\IngredientAttributeItem;
use Modules\Item\Http\Resources\BrandCollection;
use Modules\Item\Http\Resources\BrandResource;
use Modules\Item\Http\Requests\BrandRequest;
use Exception;
use App\Traits\CacheTrait;

class IngredientAttributeItemController extends Controller
{
    use CacheTrait;

    public function index()
    {
        return view('item::ingredient_attributes.index');
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'active' => 'Activo',
        ];
    }

    public function records(Request $request)
    {
        $records = IngredientAttributeItem::where($request->column, 'like', "%{$request->value}%")
            ->latest();

        return new BrandCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function record($id)
    {
        $record = IngredientAttributeItem::findOrFail($id);

        return $record;
    }

    /**
     * Crea o edita un nuevo ingrediente.
     * El nombre de ingrediente debe ser único, por lo tanto se valida cuando el nombre existe.
     *
     * @param BrandRequest $request
     *
     * @return array
     */
    public function store(BrandRequest $request)
    {
        $id = (int)$request->input('id');
        $name = $request->input('name');
        $file = $request->file('image');

        $error = null;
        $ingredient = null;
        if (!empty($name)) {
            $ingredient = IngredientAttributeItem::where('name', $name);
            if (empty($id)) {
                $ingredient = $ingredient->first();
                if (!empty($ingredient)) {
                    $error = 'El nombre de ingrediente ya existe';
                }
            } else {
                $ingredient = $ingredient->where('id', '!=', $id)->first();
                if (!empty($ingredient)) {
                    $error = 'El nombre de ingrediente ya existe para otro registro';
                }
            }
        }
        $data = [
            'success' => false,
            'message' => $error,
            'data' => $ingredient
        ];
        if (empty($error)) {
            $filename = null;

            if ($file) {
                $filename = uniqid() .'_'. $file->getClientOriginalName();
                $file->move(public_path('storage/uploads/ingredients'), $filename);
            }
            $ingredient = IngredientAttributeItem::firstOrNew(['id' => $id]);
            $old_image = $ingredient->image;
            if($old_image){
                $old_image_path = public_path($old_image);
                if(file_exists($old_image_path)){
                    unlink($old_image_path);
                }
            }
            $ingredient->fill($request->all());
            if($filename){
                $path = 'storage/uploads/ingredients/'.$filename;
                $ingredient->image = $path;
            }
            $ingredient->save();
            $data = [
                'success' => true,
                'message' => ($id) ? 'Ingrediente editado con éxito' : 'Ingrediente registrado con éxito',
                'data' => $ingredient
            ];
        }
        $this->clearCache('ingredients_order_by_name');
        return $data;
    }

    public function destroy($id)
    {
        try {
            $ingredient = IngredientAttributeItem::findOrFail($id);

            // Remove ingredient reference from all ItemAttributes in batch
            \Modules\Item\Models\ItemAttribute::where('cat_ingredient_id', $id)
                ->update(['cat_ingredient_id' => null]);

            // Delete ItemAttributes that have no ingredient and no line in batch
            \Modules\Item\Models\ItemAttribute::whereNull('cat_ingredient_id')
                ->whereNull('cat_line_id')
                ->delete();

            // Delete the ingredient
            $ingredient->delete();

            return [
                'success' => true,
                'message' => 'Ingrediente eliminado con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => "El Ingrediente esta siendo usado por otros registros, no puede eliminar"] : ['success' => false, 'message' => "Error inesperado, no se pudo eliminar el Ingrediente"];
        }
    }
}