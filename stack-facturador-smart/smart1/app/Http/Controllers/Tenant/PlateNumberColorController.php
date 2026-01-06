<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PlateNumberColor;
use Illuminate\Http\Request;

class PlateNumberColorController extends Controller
{
    public function index()
    {
        $colors = PlateNumberColor::all();
        return response()->json(['data' => $colors], 200);
    }

    public function store(Request $request)
    {
        $color = PlateNumberColor::create($request->all());
        return response()->json(['data' => $color], 201);
    }

    public function update(Request $request, $id)
    {
        $color = PlateNumberColor::findOrFail($id);
        $color->update($request->all());
        return response()->json(['data' => $color], 200);
    }

    public function destroy($id)
    {
        $color = PlateNumberColor::findOrFail($id);
        $color->delete();
        return response()->json(null, 204);
    }
} 