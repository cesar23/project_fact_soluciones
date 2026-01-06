<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyContractCollection;
use App\Http\Resources\Tenant\SupplyContractResource;
use App\Models\Tenant\SupplyContract;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplyContractController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.contracts.index');
    }

    public function columns()
    {
        return [
            'person.id' => 'Contribuyente',
            'supply.id' => 'Suministro',
            'contract.id' => 'Contrato',
            'supply_solicitude.id' => 'Solicitud',
        ];
    }

    public function records(Request $request)
    {
        $column = $request->column;
        $value = $request->value;
        $records = SupplyContract::query();
        // $records->with(['supplySolicitude', 'person', 'supply']);
        if($column && $value){
            switch($column){
                case 'person.id':
                    $records->where('person_id', $value);
                    break;
                case 'supply.id':
                    $records->where('supply_id', $value);
                    break;
                case 'contract.id':
                    $records->where('id', $value);
                    break;
                case 'supply_solicitude.id':
                    $records->where('supply_solicitude_id', $value);
                    break;
            }
        }
        $records->orderBy('id', 'desc');


        return new SupplyContractCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supply_solicitude_id' => 'required|integer',
            'person_id' => 'required|integer',
            'supplie_plan_id' => 'required|integer',
            'supply_id' => 'required|integer',
            'path_solicitude' => 'nullable|file|mimes:pdf|max:2048',
            'supply_service_id' => 'required|integer',
            'address' => 'nullable|string|max:255',
            'install_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'finish_date' => 'nullable|date',
            'observation' => 'nullable|string'
        ]);
        $request->merge(['active' => 1]);

        $data = $request->all();

        // Manejar la subida del archivo PDF
        if ($request->hasFile('path_solicitude')) {
            $file = $request->file('path_solicitude');
            $filename = 'solicitud_' . $request->supply_solicitude_id . '.pdf';
            
            // Crear directorio si no existe
            $directory = storage_path('app/public/solicitudes');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Mover el archivo
            $file->move(storage_path('app/public/solicitudes'), $filename);
            $data['path_solicitude'] =$filename;
        }

        $contract = SupplyContract::create($data);

        return response()->json($contract, 201);
    }

    public function show($id)
    {
        $contract = SupplyContract::findOrFail($id);

        return new SupplyContractResource($contract);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supply_solicitude_id' => 'required|integer',
            'person_id' => 'required|integer',
            'supplie_plan_id' => 'required|integer',
            'supply_id' => 'required|integer',
            'path_solicitude' => 'nullable|file|mimes:pdf|max:2048',
            'supply_service_id' => 'required|integer',
            'address' => 'nullable|string|max:255',
            'install_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'finish_date' => 'nullable|date',
            'observation' => 'nullable|string'
        ]);

        $contract = SupplyContract::findOrFail($id);
        $request->merge(['active' => 1]);
        $data = $request->all();

        // Manejar la subida del archivo PDF
        if ($request->hasFile('path_solicitude')) {
            $file = $request->file('path_solicitude');
            $filename = 'solicitud_' . $request->supply_solicitude_id . '.pdf';
            
            // Crear directorio si no existe
            $directory = storage_path('app/public/solicitudes');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Eliminar archivo anterior si existe
            if ($contract->path_solicitude) {
                $oldPath = storage_path('app/public/solicitudes/' . $contract->path_solicitude);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Mover el archivo nuevo
            $file->move(storage_path('app/public/solicitudes'), $filename);
            $data['path_solicitude'] = $filename;
        } else {
            // Si no se envía archivo nuevo, mantener el existente
            unset($data['path_solicitude']);
        }

        $contract->update($data);

        return response()->json($contract);
    }

    public function destroy($id)
    {
        $contract = SupplyContract::findOrFail($id);
        $contract->delete();

        return response()->json(['message' => 'Contrato eliminado correctamente']);
    }

    public function printContract($id)
    {
        $contract = SupplyContract::with([
            'person',
            'supply.sector',
            'supplyService',
            'supplySolicitude'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('tenant.supplies.contracts.format', compact('contract'));
        
        // Configurar el PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('contrato-suministro-' . $contract->id . '.pdf');
    }
}