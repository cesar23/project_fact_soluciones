<?php

namespace Modules\Restaurant\Http\Controllers;


use Illuminate\Routing\Controller;
use Modules\Restaurant\Http\Requests\CategoryFoodRequest;
use Modules\Restaurant\Models\CategoryFood;

class CategoryFoodController extends Controller
{


    public function index()
    {
        $categories = CategoryFood::all();
        return view('restaurant::food.category_food', compact('categories'));
    }
    public function records()
    {
        $category_food = CategoryFood::all();

        return [
            'success' => true,
            'data' => $category_food
        ];
    }
    public function active($id)
    {
        $category_food = CategoryFood::find($id);
        $category_food->active = !$category_food->active;
        $category_food->save();
        return [
            'success' => true,
            'data' => $category_food,
            'message' => 'Categoría ' . ($category_food->active ? 'activada' : 'desactivada')
        ];
    }
    public function record($id)
    {
        $category_food = CategoryFood::find($id);

        return [
            'success' => true,
            'data' => $category_food
        ];
    }
    public function store(CategoryFoodRequest $request)
    {
        $id = $request->input('id');
        $category_food = CategoryFood::firstOrNew(['id' => $id]);
        $category_food->fill($request->all());
        $category_food->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Categoría actualizada con éxito' : 'Categoría creada con éxito'
        ];
    }
    public function destroy($id)
    {
        //
    }
}
