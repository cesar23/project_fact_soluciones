<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SectorCollection;
use App\Models\Tenant\Sector;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.sectors.index');
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'code' => 'Código'
        ];
    }

    public function allRecords()
    {
        $sectors = Sector::all();
        return new SectorCollection($sectors);
    }

    public function records(Request $request)
    {
        $records = Sector::query();
        
        $limit = $request->get('limit');
        
        if ($limit) {
            return new SectorCollection($records->limit($limit)->get());
        }

        return new SectorCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        $sector = Sector::create($request->all());

        return response()->json($sector, 201);
    }

    public function show($id)
    {
        $sector = Sector::findOrFail($id);

        return $sector;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        $sector = Sector::findOrFail($id);
        $sector->update($request->all());

        return response()->json($sector);
    }

    public function destroy($id)
    {
        $sector = Sector::findOrFail($id);
        $sector->delete();

        return response()->json(['message' => 'Sector eliminado correctamente']);
    }

    public function search(Request $request)
    {
        $input = $request->get('input', '');
        
        $sectors = Sector::where('name', 'like', '%' . $input . '%')
                        ->orWhere('code', 'like', '%' . $input . '%')
                        ->limit(20)
                        ->get();

        return response()->json(['data' => $sectors]);
    }
}