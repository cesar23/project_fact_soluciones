<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyTypeDebtCollection;
use App\Http\Resources\Tenant\SupplyTypeDebtResource;
use App\Models\Tenant\SupplyTypeDebt;
use Illuminate\Http\Request;

class SupplyTypeDebtController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.type_debts.index');
    }

    public function columns()
    {
        return [
            'description' => 'Descripción',
            'code' => 'Código'
        ];
    }

    public function records(Request $request)
    {
        $records = SupplyTypeDebt::orderBy('description');

        return new SupplyTypeDebtCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        $typeDebt = SupplyTypeDebt::create($request->all());

        return response()->json($typeDebt, 201);
    }

    public function show($id)
    {
        $typeDebt = SupplyTypeDebt::findOrFail($id);

        return new SupplyTypeDebtResource($typeDebt);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        $typeDebt = SupplyTypeDebt::findOrFail($id);
        $typeDebt->update($request->all());

        return response()->json($typeDebt);
    }

    public function destroy($id)
    {
        $typeDebt = SupplyTypeDebt::findOrFail($id);
        $typeDebt->delete();

        return response()->json(['message' => 'Tipo de deuda eliminado correctamente']);
    }
}