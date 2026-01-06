<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplyPaymentConsumptionController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.consumption.index');
    }

    public function columns()
    {
        return [
            'period' => 'Período',
            'consumption' => 'Consumo',
            'amount' => 'Monto',
            'date' => 'Fecha',
            'status' => 'Estado'
        ];
    }

    public function records(Request $request)
    {
        $value = $request->value;
        $column = $request->column;

        // Implementar lógica de consulta aquí
        $records = collect();

        return response()->json([
            'data' => $records,
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required|string|max:255',
            'consumption' => 'required|numeric',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        // Implementar lógica de creación aquí

        return response()->json(['message' => 'Consumo creado correctamente'], 201);
    }

    public function show($id)
    {
        // Implementar lógica para mostrar un consumo específico

        return response()->json(['data' => []]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'period' => 'required|string|max:255',
            'consumption' => 'required|numeric',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        // Implementar lógica de actualización aquí

        return response()->json(['message' => 'Consumo actualizado correctamente']);
    }

    public function destroy($id)
    {
        // Implementar lógica de eliminación aquí

        return response()->json(['message' => 'Consumo eliminado correctamente']);
    }
}
