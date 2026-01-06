<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyTypeViaCollection;
use App\Http\Resources\Tenant\SupplyViaCollection;
use App\Models\Tenant\SupplyTypeVia;
use App\Models\Tenant\SupplyVia;
use Illuminate\Http\Request;

class SupplyViaController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.supply_vias.index');
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'code' => 'Código',
            'supply_type_via.name' => 'Tipo de Vía',
            'sector.name' => 'Sector'
        ];
    }
    
    public function allRecords(Request $request)
    {
        $records = SupplyVia::get();

        return response()->json($records);
    }

    public function records(Request $request)
    {
        $records = SupplyVia::select('supply_via.*', 'sectors.name as sector_name', 'sectors.code as sector_code')
            ->join('sectors', 'supply_via.sector_id', '=', 'sectors.id')
            ->orderBy('sector_code', 'asc');

        return new SupplyViaCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function supplyTypeViasAllRecords(Request $request)
    {
        $records = SupplyTypeVia::orderBy('description')->get()->transform(function($record){
            return [
                'id' => $record->id,
                'name' => $record->short ? $record->short . ' - ' . $record->description : $record->description,
                'description' => $record->description,
                'short' => $record->short,
                'code' => $record->code
            ];
        });

        return response()->json([
            'data' => $records
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'supply_type_via_id' => 'required|exists:tenant.supply_type_via,id',
            'sector_id' => 'required|exists:tenant.sectors,id',
            'obsevation' => 'nullable|string|max:500'
        ]);

        $supplyVia = SupplyVia::create($request->all());

        return response()->json($supplyVia, 201);
    }

    public function show($id)
    {
        $supplyVia = SupplyVia::findOrFail($id);

        return $supplyVia;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'supply_type_via_id' => 'required|exists:tenant.supply_type_via,id',
            'sector_id' => 'required|exists:tenant.sectors,id',
            'obsevation' => 'nullable|string|max:500'
        ]);

        $supplyVia = SupplyVia::findOrFail($id);
        $supplyVia->update($request->all());

        return response()->json($supplyVia);
    }

    public function destroy($id)
    {
        $supplyVia = SupplyVia::findOrFail($id);
        $supplyVia->delete();

        return response()->json(['message' => 'Vía de suministro eliminada correctamente']);
    }

    public function search(Request $request)
    {
        $input = $request->get('input', '');
        $sectorId = $request->get('sector_id');
        
        $query = SupplyVia::where('name', 'like', '%' . $input . '%')
                          ->orWhere('code', 'like', '%' . $input . '%');
        
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }
        
        $supplyVias = $query->limit(20)->get();

        return response()->json(['data' => $supplyVias]);
    }

    public function getBySector($sectorId)
    {
        $supplyVias = SupplyVia::where('sector_id', $sectorId)->get();
        
        return response()->json(['data' => $supplyVias]);
    }
}