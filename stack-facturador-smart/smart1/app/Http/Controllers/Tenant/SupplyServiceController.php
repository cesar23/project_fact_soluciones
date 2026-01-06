<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyServiceCollection;
use App\Http\Resources\Tenant\SupplyServiceResource;
use App\Models\Tenant\SupplyService;
use Illuminate\Http\Request;

class SupplyServiceController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.services.index');
    }

    public function columns()
    {
        return [
            'name' => 'Nombre'
        ];
    }

    public function records(Request $request)
    {
        $records = SupplyService::orderBy('name');

        return new SupplyServiceCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $service = SupplyService::create($request->all());

        return response()->json($service, 201);
    }

    public function show($id)
    {
        $service = SupplyService::findOrFail($id);

        return new SupplyServiceResource($service);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $service = SupplyService::findOrFail($id);
        $service->update($request->all());

        return response()->json($service);
    }

    public function destroy($id)
    {
        $service = SupplyService::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }
}