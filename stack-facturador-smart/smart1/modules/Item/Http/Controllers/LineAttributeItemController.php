<?php

namespace Modules\Item\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\LineAttributeItem;
use Modules\Item\Http\Resources\BrandCollection;
use Modules\Item\Http\Resources\BrandResource;
use Modules\Item\Http\Requests\BrandRequest;
use Exception;
use App\Traits\CacheTrait;

class LineAttributeItemController extends Controller
{
    use CacheTrait;

    public function index()
    {
        return view('item::line_attributes.index');
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
        $records = LineAttributeItem::where($request->column, 'like', "%{$request->value}%")
            ->latest();

        return new BrandCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function record($id)
    {
        $record = LineAttributeItem::findOrFail($id);

        return $record;
    }

    /**
     * Crea o edita una nueva línea.
     * El nombre de línea debe ser único, por lo tanto se valida cuando el nombre existe.
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
        $line = null;
        if (!empty($name)) {
            $line = LineAttributeItem::where('name', $name);
            if (empty($id)) {
                $line = $line->first();
                if (!empty($line)) {
                    $error = 'El nombre de línea ya existe';
                }
            } else {
                $line = $line->where('id', '!=', $id)->first();
                if (!empty($line)) {
                    $error = 'El nombre de línea ya existe para otro registro';
                }
            }
        }
        $data = [
            'success' => false,
            'message' => $error,
            'data' => $line
        ];
        if (empty($error)) {
            $filename = null;

            if ($file) {
                $filename = uniqid() .'_'. $file->getClientOriginalName();
                $file->move(public_path('storage/uploads/lines'), $filename);
            }
            $line = LineAttributeItem::firstOrNew(['id' => $id]);
            $old_image = $line->image;
            if($old_image){
                $old_image_path = public_path($old_image);
                if(file_exists($old_image_path)){
                    unlink($old_image_path);
                }
            }
            $line->fill($request->all());
            if($filename){
                $path = 'storage/uploads/lines/'.$filename;
                $line->image = $path;
            }
            $line->save();
            $data = [
                'success' => true,
                'message' => ($id) ? 'Línea editada con éxito' : 'Línea registrada con éxito',
                'data' => $line
            ];
        }
        $this->clearCache('lines_order_by_name');
        return $data;
    }

    public function destroy($id)
    {
        try {
            $line = LineAttributeItem::findOrFail($id);

            // Remove line reference from all ItemAttributes in batch
            \Modules\Item\Models\ItemAttribute::where('cat_line_id', $id)
                ->update(['cat_line_id' => null]);

            // Delete ItemAttributes that have no ingredient and no line in batch
            \Modules\Item\Models\ItemAttribute::whereNull('cat_ingredient_id')
                ->whereNull('cat_line_id')
                ->delete();

            // Delete the line
            $line->delete();

            return [
                'success' => true,
                'message' => 'Línea eliminada con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => "La Línea esta siendo usada por otros registros, no puede eliminar"] : ['success' => false, 'message' => "Error inesperado, no se pudo eliminar la Línea"];
        }
    }
}