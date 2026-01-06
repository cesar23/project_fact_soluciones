<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyProcessCollection;
use App\Http\Resources\Tenant\SupplyProcessResource;
use App\Models\Tenant\SupplyProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplyProcessController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.processes.index');
    }

    public function columns()
    {
        return [
            'record' => 'Registro',
            'document' => 'Documento',
            'document_date' => 'Fecha Documento',
            'receive_date' => 'Fecha Recepción',
            'subject' => 'Asunto',
            'state' => 'Estado',
            'location' => 'Ubicación',
            'contact_person' => 'Persona Contacto'
        ];
    }

    public function records(Request $request)
    {
        $records = SupplyProcess::orderBy(DB::raw('CAST(record AS UNSIGNED)'), 'desc');

        return new SupplyProcessCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'record' => 'required|string|max:255',
            'person_id' => 'required|integer',
            'supply_id' => 'nullable|integer',
            'document' => 'nullable|string',
            'document_date' => 'required|date',
            'receive_date' => 'required|date',
            'assign_date' => 'nullable|date',
            'year' => 'nullable|string|max:4',
            'subject' => 'required|string',
            'supply_office_id' => 'nullable|integer',
            'state' => 'nullable|string',
            'location' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'observation_document' => 'nullable|string',
            'observation_finish' => 'nullable|string',
            'n_folios' => 'nullable|string'
        ]);

        $process = SupplyProcess::create($request->all());

        return response()->json($process, 201);
    }

    public function show($id)
    {
        $process = SupplyProcess::findOrFail($id);

        return new SupplyProcessResource($process);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'record' => 'required|string|max:255',
            'person_id' => 'required|integer',
            'supply_id' => 'nullable|integer',
            'document' => 'nullable|string',
            'document_date' => 'required|date',
            'receive_date' => 'required|date',
            'assign_date' => 'nullable|date',
            'year' => 'nullable|string|max:4',
            'subject' => 'required|string',
            'supply_office_id' => 'nullable|integer',
            'state' => 'nullable|string',
            'location' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'observation_document' => 'nullable|string',
            'observation_finish' => 'nullable|string',
            'n_folios' => 'nullable|string'
        ]);

        $process = SupplyProcess::findOrFail($id);
        $process->update($request->all());

        return response()->json($process);
    }

    public function destroy($id)
    {
        $process = SupplyProcess::findOrFail($id);
        $process->delete();

        return response()->json(['message' => 'Proceso eliminado correctamente']);
    }
}