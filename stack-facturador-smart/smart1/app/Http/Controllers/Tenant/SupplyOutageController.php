<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyOutageCollection;
use App\Http\Resources\Tenant\SupplyOutageResource;
use App\Models\Tenant\Supply;
use App\Models\Tenant\SupplyContract;
use App\Models\Tenant\SupplyOutage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplyOutageController extends Controller
{
    public function index(Request $request)
    {
        $type_outage = $request->type_outage;
        return view('tenant.supplies.outages.index', compact('type_outage'));
    }

    public function columns()
    {
        return [
            'supply_contract_id' => 'Contrato',
            'person_id' => 'Persona',
            'state' => 'Estado',
            'type' => 'Tipo',
            'created_at' => 'Fecha Creación'
        ];
    }

    public function records(Request $request)
    {
        $type_outage = $request->type_outage;
        $column = $request->column;
        $value = $request->value;
        $records = SupplyOutage::query();

        if ($type_outage == 'reconnection') {
            $records->where('state', '<>', 1);
        }
        if ($type_outage == 'outage') {
            $records->where('state', '<>', 2);
        }

        if ($column && $value) {
            switch ($column) {
                case 'supply_contract_id':
                    $records->where('supply_contract_id', $value);
                    break;
                case 'person_id':
                    $records->where('person_id', $value);
                    break;
                case 'state':
                    $records->where('state', $value);
                    break;
                case 'type':
                    $records->where('type', $value);
                    break;
            }
        }

        $records->orderBy('id', 'asc');

        return new SupplyOutageCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function cut(Request $request, $id)
    {
        $request->validate([
            'date_of_outage' => 'nullable|date',
        ]);

        $state = 0;
        $observation = 'Cortado desde el sistema';
        $date_of_outage = $request->date_of_outage ?: now()->toDateString();

        $outage = SupplyOutage::findOrFail($id);
        $supply_contract_id = $outage->supply_contract_id;
        $supply_contract = SupplyContract::where('id', $supply_contract_id)->where('active', true)->first();
        if(!$supply_contract) {
            return response()->json(['message' => 'El contrato no está activo'], 404);
        }
        $exist_outage = SupplyOutage::where('supply_contract_id', $supply_contract_id)->where('state', 0)->first();
        if($exist_outage) {
            return response()->json(['message' => 'El contrato ya tiene un corte.'], 404);
        }
        $outages_with_state_1 = SupplyOutage::where('supply_contract_id', $supply_contract_id)->where('state', 1)->get();
        if($outages_with_state_1->count() > 0) {
            SupplyOutage::where('supply_contract_id', $supply_contract_id)->where('state', 1)->delete();
        }
        $outage->update(['state' => $state, 'observation' => $observation, 'date_of_outage' => $date_of_outage]);
        return response()->json(['success' => true, 'outage' => $outage]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'supply_id' => 'required|integer|exists:tenant.supplies,id',
                'type' => 'required|string|in:TEMPORAL,DEFINITIVO',
                'observation' => 'required|string|max:1000',
                'date_of_outage' => 'required|date',
                'supply_contract_id' => 'nullable|integer',
                'state' => 'nullable|string'
            ]);
            $contract = SupplyContract::where('supply_id', $request->supply_id)->where('active', true)->first();
            if (!$contract) {
                return response()->json(['message' => 'El suministro no tiene contrato activo'], 404);
            }
            $observation = $request->observation;
            $state = $request->state;
            $type = $request->type;
            $person_id = $contract->person_id;
            $contract_id = $contract->id;

            $exist_outage = SupplyOutage::where('supply_contract_id', $contract_id)->where('state', 0)->first();
            if ($exist_outage) {
                return response()->json(['message' => 'El suministro ya tiene un corte.'], 404);
            }
            $outages_with_state_1 = SupplyOutage::where('supply_contract_id', $contract_id)->where('state', 1)->get();
            if ($outages_with_state_1->count() > 0) {
                SupplyOutage::where('supply_contract_id', $contract_id)->where('state', 1)->delete();
            }
            $to_insert = [
                'supply_contract_id' => $contract_id,
                'observation' => $observation,
                'state' => $state,
                'type' => $type,
                'person_id' => $person_id,
                'date_of_outage' => $request->date_of_outage,
            ];

            $outage = SupplyOutage::create($to_insert);

            SupplyContract::where('id', $contract_id)->update(['active' => false]);

            DB::commit();

            return response()->json($outage, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar la interrupción: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $outage = SupplyOutage::with(['person', 'supplyContract'])->findOrFail($id);

        return new SupplyOutageResource($outage);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supply_id' => 'required|integer|exists:tenant.supplies,id',
            'type' => 'required|string|in:TEMPORAL,DEFINITIVO',
            'observation' => 'required|string|max:1000',
            'date_of_outage' => 'required|date',
            'supply_contract_id' => 'nullable|integer',
            'state' => 'nullable|string'
        ]);

        // Obtener el person_id del supply seleccionado
        $supply = Supply::findOrFail($request->supply_id);

        $data = $request->all();
        $data['person_id'] = $supply->person_id;

        $outage = SupplyOutage::findOrFail($id);
        $outage->update($data);

        return response()->json($outage);
    }

    public function reconnect($id)
    {
        try {
            DB::beginTransaction();
            
            // Buscar el corte con sus relaciones
            $outage = SupplyOutage::with(['supplyContract.person', 'supplyContract.supply'])->findOrFail($id);
            
            // Verificar que el corte esté en estado cortado (0)
            if ($outage->state != 0) {
                return response()->json(['message' => 'El suministro no está cortado'], 400);
            }
            
            // Buscar el concepto de reconexión (ID 4 según el código original)
            $reconnectionConcept = \App\Models\Tenant\SupplyConcept::where('id', 4)
                ->orWhere('code', 'RECONEXION')
                ->orWhere('name', 'like', '%RECONEX%')
                ->first();
                
            if (!$reconnectionConcept) {
                return response()->json(['message' => 'No se encontró el concepto de reconexión'], 404);
            }
            
            $reconnectionCost = $reconnectionConcept->cost;
            
            // Crear la deuda de reconexión
            $debt = \App\Models\Tenant\SupplyDebt::create([
                'supply_contract_id' => $outage->supply_contract_id,
                'person_id' => $outage->person_id,
                'supply_id' => $outage->supplyContract->supply_id ?? null,
                'amount' => $reconnectionCost,
                'original_amount' => $reconnectionCost,
                'year' => date('Y'),
                'month' => date('n'),
                'generation_date' => now(),
                'due_date' => now()->addDays(30),
                'active' => false, // false = pendiente de pago
                'type' => 'c', // c = concepto
                'supply_type_debt_id' => 2, // Tipo de deuda por reconexión
                'supply_concept_id' => $reconnectionConcept->id,
            ]);
            
            // Cambiar el estado del corte a reconectado (2)
            $outage->update([
                'state' => 2,
                'observation' => $outage->observation . ' - Se ha creado la deuda de reconexión'
            ]);
            
            // Reactivar el contrato
            if ($outage->supplyContract) {
                $outage->supplyContract->update(['active' => true]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Reconexión registrada exitosamente',
                'debt_amount' => $reconnectionCost,
                'debt_id' => $debt->id
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la reconexión: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchSupplies(Request $request)
    {
        $input = $request->input('input');
        
        if (empty($input)) {
            return response()->json(['data' => []]);
        }

        $supplies = \App\Models\Tenant\Supply::with(['person'])
            ->where(function($query) use ($input) {
                // Buscar por nombre o número de persona
                $query->whereHas('person', function($personQuery) use ($input) {
                    $personQuery->where('name', 'like', "%{$input}%")
                               ->orWhere('number', 'like', "%{$input}%");
                })
                // Buscar por código de ruta (cod_route)
                ->orWhere('cod_route', 'like', "%{$input}%")
                // Buscar por código anterior (old_code)  
                ->orWhere('old_code', 'like', "%{$input}%");
            })
            ->limit(10)
            ->get()
            ->map(function($supply) {
                return [
                    'id' => $supply->id,
                    'description' => ($supply->person ? $supply->person->name . ' - ' : '') . 
                                   ($supply->cod_route ? $supply->cod_route : '') .
                                   ($supply->old_code ? ' (' . $supply->old_code . ')' : '')
                ];
            });

        return response()->json(['data' => $supplies]);
    }

    public function destroy($id)
    {
        $outage = SupplyOutage::findOrFail($id);
        $outage->delete();

        return response()->json(['message' => 'Interrupción eliminada correctamente']);
    }
}
