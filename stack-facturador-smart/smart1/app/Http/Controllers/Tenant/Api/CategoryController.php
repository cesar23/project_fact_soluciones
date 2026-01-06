<?php

namespace App\Http\Controllers\Tenant\Api;

use Exception;
use App\Models\Tenant\Item;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Tenant\CategoryItem;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Tenant\ItemCollection;
use App\Http\Resources\Tenant\CategoryResource;
use Modules\Item\Http\Requests\CategoryRequest;
use Modules\Item\Http\Resources\CategoryCollection;

class CategoryController extends Controller
{

    public function index()
    {
        return view('item::category.index');
    }

    public function search(Request $request)
    {
        $query = $request['query'];
        if ($query == null) {
            return [
                'success' => false,
            ];
        }
        $items = new ItemCollection(Item::where("description", "like", "%{$query}%")->take(15)->get());

        return [
            'success' => true,
            'items' => $items
        ];
    }
    public function productsByCategory(Request $request)
    {
        $category_id = $request->category_id;
        $items = Item::where('category_id', $category_id);

        return new ItemCollection($items->paginate(config('tenant.items_per_page')));
    }
    public function init()
    {
        $categories = CategoryItem::all();
        //  $categories = CategoryItem::get()->transform(function($row) {
        //     return [
        //         'id' => $row->id,
        //          'name' => $row->name,
        //          'icono' => url('') . "/storage/uploads/{$row->icono}",
        //          //($row->image_small !== 'imagen-no-disponible.jpg') ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'category'.DIRECTORY_SEPARATOR.$row->icono) : asset("/logo/{$row->icono}"),
        //     ];
        // });


        $items = [];
       
        foreach ($categories as $category) {
           
            $category_items = new ItemCollection(Item::where('category_id', $category->id)->take(5)->get());
            array_push($items, ['category' =>[
                               'id' => $category->id,
                               'name' => $category->name,
                                'icono' => url('') . "/storage/uploads/category/{$category->icono}",
                                ] , 'products' => $category_items]);
        }

        return [
            'success' => true,
            'items' => $items
        ];
    }
    public function columns()
    {
        return [
            'name' => 'Nombre',
        ];
    }

    public function records(Request $request)
    {
        $records = CategoryItem::where($request->column, 'like', "%{$request->value}%")
            ->orderBy('id', 'asc')
            ->latest();

        return new CategoryCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function record($id)
    {
        //    $record = CategoryItem::findOrFail($id);
        $record = new CategoryResource(CategoryItem::findOrFail($id));

        return $record;
    }
    public function uploads(Request $request)
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
    public function store(CategoryRequest $request)
    {
        $id = $request->input('id');
        $category = CategoryItem::firstOrNew(['id' => $id]);
        $category->fill($request->all());
        $temp_path = $request->input('temp_path');
        if ($temp_path) {
            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'category' . DIRECTORY_SEPARATOR;
            $file_name_old = $request->input('icono');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path);
            $datenow = date('YmdHis');
            $file_name = Str::slug($category->name) . '-' . $datenow . '.' . $file_name_old_array[1];
            Storage::put($directory . $file_name, $file_content);
            $category->icono = $file_name;
        } else if (!$request->input('image') && !$request->input('temp_path') && !$request->input('image_url')) {
            $category->icono = 'imagen-no-disponible.jpg';
        }
        $category->save();
        return [
            'success' => true,
            'message' => ($id) ? 'Categoría editada con éxito' : 'Categoría registrada con éxito',
            'data' => $category

        ];
    }

    public function destroy($id)
    {
        try {

            $category = CategoryItem::findOrFail($id);
            $category->delete();

            return [
                'success' => true,
                'message' => 'Categoría eliminada con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => "La categoría esta siendo usada por otros registros, no puede eliminar"] : ['success' => false, 'message' => "Error inesperado, no se pudo eliminar la categoría"];
        }
    }
}
