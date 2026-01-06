<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyCollection;
use App\Http\Resources\Tenant\SupplyResource;
use App\Models\Tenant\Supply;
use App\Models\Tenant\SupplyVia;
use App\Models\Tenant\Sector;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.index');
    }

    public function columns()
    {
        return [
            'code' => 'Código',
            'person.name' => 'Persona',
            'supply_via.name' => 'Vía de Suministro',
            'sector.name' => 'Sector',
            'date_start' => 'Fecha Inicio',
            'supply_state.description' => 'Estado'
        ];
    }

    public function records(Request $request)
    {   

        $value = $request->value;
        $column = $request->column;
        $records = Supply::with(['person', 'supplyVia', 'sector', 'supplyState'])
            ->join('persons', 'supplies.person_id', '=', 'persons.id')
            ->orderBy('persons.name', 'asc')
            ->select('supplies.*');

            if($column){
                switch($column){
                    case 'person.name':
                        $records->whereHas('person', function($query) use($value){
                            $query->where('name', 'like', "%{$value}%");
                        });
                        break;
                    case 'supply_via.name':
                        $records->whereHas('supplyVia', function($query) use($value){
                            $query->where('name', 'like', "%{$value}%");
                        });
                        break;
                    case 'sector.name':
                        $records->whereHas('sector', function($query) use($value){
                            $query->where('name', 'like', "%{$value}%");
                        });
                        break;
                    case 'date_start':
                        $records->where('date_start', 'like', "%{$value}%");
                        break;
                    case 'supply_state.description':
                        $records->whereHas('supplyState', function($query) use($value){
                            $query->where('description', 'like', "%{$value}%");
                        });
                        break;
                }
            }

        return new SupplyCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'person_id' => 'required|exists:tenant.persons,id',
            'supply_via_id' => 'required|exists:tenant.supply_via,id',
            'date_start' => 'required|date',
        ]);
        
        $supplyVia = SupplyVia::find($request->supply_via_id);
        $code = $request->code;
        $request->merge(['cod_route' => $code]);
        $request->merge(['sector_id' => $supplyVia->sector_id]);
        $request->merge(['description' => $code]);
        $supply = Supply::create($request->all() + ['user_id' => auth()->id()]);

        return response()->json($supply, 201);
    }

    public function show($id)
    {
        $supply = Supply::with(['person', 'supplyVia', 'sector', 'supplyState'])
            ->findOrFail($id);

        return new SupplyResource($supply);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'person_id' => 'required|exists:tenant.persons,id',
            'supply_via_id' => 'required|exists:tenant.supply_via,id',
            'date_start' => 'required|date',
        ]);
        
        $supplyVia = SupplyVia::find($request->supply_via_id);
        $code = $request->code;
        $request->merge(['sector_id' => $supplyVia->sector_id]);
        $request->merge(['cod_route' => $code]);
        $request->merge(['description' => $code]);
        $supply = Supply::findOrFail($id);
        $supply->update($request->all());

        return response()->json($supply);
    }

    public function destroy($id)
    {
        $supply = Supply::findOrFail($id);
        $supply->delete();

        return response()->json(['message' => 'Predio eliminado correctamente']);
    }

    public function recordsByCustomerId($id)
    {
        $records = Supply::where('person_id', $id)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' =>  $row->old_code . ' - ' . $row->cod_route,
            ];
        });
        return compact('records');
        
    }

    public function generateCode(Request $request)
    {
        $request->validate([
            'supply_via_id' => 'required|exists:tenant.supply_via,id',
            'sector_id' => 'required|exists:tenant.sectors,id'
        ]);

        $supplyVia = SupplyVia::find($request->supply_via_id);
        $sector = Sector::find($request->sector_id);

        // Obtener los primeros 2 caracteres del código de vía de suministro y sector
        $supplyViaCode = strtoupper(substr($supplyVia->code ?: $supplyVia->name, 0, 2));
        $sectorCode = strtoupper(substr($sector->code ?: $sector->name, 0, 2));

        // Crear el prefijo del código
        $prefix = $supplyViaCode . $sectorCode;

        // Buscar el último predio con ese prefijo
        $lastSupply = Supply::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastSupply) {
            // Extraer los últimos 4 caracteres y sumarle 1
            $lastNumber = intval(substr($lastSupply->code, -4));
            $newNumber = $lastNumber + 1;
        } else {
            // Si no existe, empezar con 1
            $newNumber = 1;
        }

        // Formatear el número a 4 dígitos
        $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        // Crear el código completo
        $code = $prefix . $formattedNumber;

        return response()->json(['code' => $code]);
    }

    public function search(Request $request)
    {
        $input = $request->input('input');
        
        if (empty($input)) {
            return response()->json(['data' => []]);
        }

        $supplies = Supply::with(['person'])
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
}