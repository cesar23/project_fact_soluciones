<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyConceptCollection;
use App\Http\Resources\Tenant\SupplyConceptResource;
use App\Models\Tenant\SupplyConcept;
use Illuminate\Http\Request;

class SupplyConceptController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.concepts.index');
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'code' => 'Código',
            'cost' => 'Costo',
            'type' => 'Tipo',
            'active' => 'Estado'
        ];
    }

    public function tables()
    {
        return [];
    }

    public function allRecords()
    {
        $records = SupplyConcept::all();
        return new SupplyConceptCollection($records);
    }

    public function records(Request $request)
    {
        $column = $request->column;
        $value = $request->value;
        $records = SupplyConcept::query();

        if($column && $value){
            switch($column){
                case 'name':
                    $records->where('name', 'like', "%{$value}%");
                    break;
                case 'code':
                    $records->where('code', 'like', "%{$value}%");
                    break;
                case 'type':
                    $records->where('type', 'like', "%{$value}%");
                    break;
            }
        }
        $records->orderBy('code', 'asc');

        return new SupplyConceptCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'cost' => 'required|numeric',
            'type' => 'required|string|max:255',
            'active' => 'required|boolean'
        ]);

        $concept = SupplyConcept::create($request->all());

        return response()->json($concept, 201);
    }

    public function show($id)
    {
        $concept = SupplyConcept::findOrFail($id);

        return new SupplyConceptResource($concept);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'cost' => 'required|numeric',
            'type' => 'required|string|max:255',
            'active' => 'required|boolean'
        ]);

        $concept = SupplyConcept::findOrFail($id);
        $concept->update($request->all());

        return response()->json($concept);
    }

    public function destroy($id)
    {
        $concept = SupplyConcept::findOrFail($id);
        $concept->delete();

        return response()->json(['message' => 'Concepto eliminado correctamente']);
    }
}