<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyOfficeCollection;
use App\Http\Resources\Tenant\SupplyOfficeResource;
use App\Models\Tenant\SupplyOffice;
use Illuminate\Http\Request;

class SupplyOfficeController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.offices.index');
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'description' => 'Descripción'
        ];
    }

    public function records(Request $request)
    {
        $records = SupplyOffice::orderBy('name');

        return new SupplyOfficeCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $office = SupplyOffice::create($request->all());

        return response()->json($office, 201);
    }

    public function show($id)
    {
        $office = SupplyOffice::findOrFail($id);

        return new SupplyOfficeResource($office);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $office = SupplyOffice::findOrFail($id);
        $office->update($request->all());

        return response()->json($office);
    }

    public function destroy($id)
    {
        $office = SupplyOffice::findOrFail($id);
        $office->delete();

        return response()->json(['message' => 'Oficina eliminada correctamente']);
    }
}