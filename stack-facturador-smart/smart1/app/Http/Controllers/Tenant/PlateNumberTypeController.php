<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PlateNumberType;
use Illuminate\Http\Request;

class PlateNumberTypeController extends Controller
{
    public function index()
    {
        $types = PlateNumberType::all();
        return response()->json(['data' => $types], 200);
    }

    public function store(Request $request)
    {
        $type = PlateNumberType::create($request->all());
        return response()->json(['data' => $type], 201);
    }

    public function update(Request $request, $id)
    {
        $type = PlateNumberType::findOrFail($id);
        $type->update($request->all());
        return response()->json(['data' => $type], 200);
    }

    public function destroy($id)
    {
        $type = PlateNumberType::findOrFail($id);
        $type->delete();
        return response()->json(null, 204);
    }
} 