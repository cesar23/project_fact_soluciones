<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PlateNumberBrand;
use Illuminate\Http\Request;

class PlateNumberBrandController extends Controller
{
    public function index()
    {
        $brands = PlateNumberBrand::with('models')->get();
        return response()->json(['data' => $brands], 200);
    }

    public function store(Request $request)
    {
        $brand = PlateNumberBrand::create($request->all());
        return response()->json(['data' => $brand], 201);
    }

    public function update(Request $request, $id)
    {
        $brand = PlateNumberBrand::findOrFail($id);
        $brand->update($request->all());
        return response()->json(['data' => $brand], 200);
    }

    public function destroy($id)
    {
        $brand = PlateNumberBrand::findOrFail($id);
        $brand->delete();
        return response()->json(null, 204);
    }
} 