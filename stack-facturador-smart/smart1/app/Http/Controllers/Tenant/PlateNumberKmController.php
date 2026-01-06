<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PlateNumberKm;
use Illuminate\Http\Request;

class PlateNumberKmController extends Controller
{
    public function index()
    {
        $kms = PlateNumberKm::with('plateNumber')->get();
        return response()->json(['data' => $kms], 200);
    }

    public function store(Request $request)
    {
        $km = PlateNumberKm::create($request->all());
        return response()->json(['data' => $km], 201);
    }

    public function update(Request $request, $id)
    {
        $km = PlateNumberKm::findOrFail($id);
        $km->update($request->all());
        return response()->json(['data' => $km], 200);
    }

    public function destroy($id)
    {
        $km = PlateNumberKm::findOrFail($id);
        $km->delete();
        return response()->json(null, 204);
    }
} 