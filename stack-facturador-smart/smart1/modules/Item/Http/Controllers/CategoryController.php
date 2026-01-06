<?php

namespace Modules\Item\Http\Controllers;

use App\Traits\CacheTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\Category;
use Modules\Item\Http\Resources\CategoryCollection;
use Modules\Item\Http\Resources\CategoryResource;
use Modules\Item\Http\Requests\CategoryRequest;
use Exception;

class CategoryController extends Controller
{
    use CacheTrait;
    public function index()
    {
        return view('item::categories.index');
    }


    public function columns()
    {
        return [
            'name' => 'Nombre',
        ];
    }

    public function records(Request $request)
    {
        if($request->column){
            $records = Category::where($request->column, 'like', "%{$request->value}%")
            ->latest();

        }else{
            $records = Category::latest();
        }

        return new CategoryCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function record($id)
    {
        $record = Category::findOrFail($id);

        return $record;
    }

    /**
     * Crea o edita una nueva categoría.
     * El nombre de categoría debe ser único, por lo tanto se valida cuando el nombre existe.
     *
     * @param CategoryRequest $request
     *
     * @return array
     */
    public function store(CategoryRequest $request)
    {
        $id = (int)$request->input('id');
        $name = $request->input('name');
        $file = $request->file('image');
    
        $error = null;
        $category = null;
        if(!empty($name)){
            $category = Category::where('name', $name);
            if(empty($id)) {
                $category= $category->first();
                if (!empty($category)) {
                    $error = 'El nombre de categoría ya existe';
                }
            }else{
                $category = $category->where('id','!=',$id)->first();
                if (!empty($category)) {
                    $error = 'El nombre de categoría ya existe para otro registro';
                }
            }
        }
        $data = [
            'success' => false,
            'message' => $error,
            'data' => $category
        ];
        if(empty($error)){
            $filename = null;
        
            if ($file) {
                $filename = uniqid() .'_'. $file->getClientOriginalName();
                $file->move(public_path('storage/uploads/categories'), $filename);
            }
            $category = Category::firstOrNew(['id' => $id]);
            $old_image = $category->image;
            if($old_image){
                $old_image_path = public_path($old_image);
                if(file_exists($old_image_path)){
                    unlink($old_image_path);
                }
            }
            $category->fill($request->all());
            if($filename){
               
                $path = 'storage/uploads/categories/'.$filename;
                $category->image = $path;
            }
            $category->save();
            $data = [
                'success' => true,
                'message' => ($id)?'Categoría editada con éxito':'Categoría registrada con éxito',
                'data' => $category
            ];
        }
        $this->clearCache('categories_order_by_name');
        return $data;

    }

    public function destroy($id)
    {
        try {

            $category = Category::findOrFail($id);
            $category->delete();

            return [
                'success' => true,
                'message' => 'Categoría eliminada con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => "La categoría esta siendo usada por otros registros, no puede eliminar"] : ['success' => false,'message' => "Error inesperado, no se pudo eliminar la categoría"];

        }

    }




}
