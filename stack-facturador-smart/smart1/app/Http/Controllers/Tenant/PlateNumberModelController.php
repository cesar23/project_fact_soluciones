<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PlateNumberModel;
use Illuminate\Http\Request;

class PlateNumberModelController extends Controller
{
    public function index(Request $request)
    {
        $brand_id = $request->brand_id;
        if($brand_id){
            $models = PlateNumberModel::where('plate_number_brand_id', $brand_id)->get();
        }else{
            $models = PlateNumberModel::with('brand')->get();
        }
        return response()->json(['data' => $models], 200);
    }

    public function store(Request $request)
    {
        $model = PlateNumberModel::create($request->all());
        return response()->json(['data' => $model], 201);
    }

    public function update(Request $request, $id)
    {
        $model = PlateNumberModel::findOrFail($id);
        $model->update($request->all());
        return response()->json(['data' => $model], 200);
    }

    public function destroy($id)
    {
        $model = PlateNumberModel::findOrFail($id);
        $model->delete();
        return response()->json(null, 204);
    }
} 