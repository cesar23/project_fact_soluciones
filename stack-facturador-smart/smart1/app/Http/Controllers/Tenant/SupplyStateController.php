<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyStateCollection;
use App\Http\Resources\Tenant\SupplyStateResource;
use App\Models\Tenant\SupplyState;
use Illuminate\Http\Request;

class SupplyStateController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.states.index');
    }

    public function columns()
    {
        return [
            'description' => 'Descripción'
        ];
    }

    public function records(Request $request)
    {
        $records = SupplyState::latest();

        return new SupplyStateCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255'
        ]);

        $state = SupplyState::create($request->all());

        return response()->json($state, 201);
    }

    public function show($id)
    {
        $state = SupplyState::findOrFail($id);

        return new SupplyStateResource($state);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255'
        ]);

        $state = SupplyState::findOrFail($id);
        $state->update($request->all());

        return response()->json($state);
    }

    public function destroy($id)
    {
        $state = SupplyState::findOrFail($id);
        $state->delete();

        return response()->json(['message' => 'Estado de predio eliminado correctamente']);
    }
}