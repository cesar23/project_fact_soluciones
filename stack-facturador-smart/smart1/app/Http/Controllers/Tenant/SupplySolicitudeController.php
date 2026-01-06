<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplySolicitudeCollection;
use App\Http\Resources\Tenant\SupplySolicitudeNoContractCollection;
use App\Http\Resources\Tenant\SupplySolicitudeResource;
use App\Models\Tenant\Person;
use App\Models\Tenant\SupplyService;
use App\Models\Tenant\SupplySolicitude;
use App\Models\Tenant\User;
use App\Models\Tenant\SupplySolicitudeItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplySolicitudeController extends Controller
{
    public function index(Request $request)
    {
        $forOperators = $request->forOperators;
        return view('tenant.supplies.solicitudes.index', compact('forOperators'));
    }

    public function columns()
    {
        return [
            'person.name' => 'Persona',
            'supply.cod_route' => 'Suministro',
            'id' => 'N° Solicitud',
    
        
        ];
    }

    public function tables()
    {
        $services = SupplyService::get();
        $customers = Person::limit(20)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
            ];
        });
        $users = User::limit(20)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
            ];
        });

        return compact('services', 'users', 'customers');
    }
    public function noContractRecords(Request $request)
    {
        $records = SupplySolicitude::whereNull('supply_debt_id')
        ->where('active', '1')
        ->whereDoesntHave('supplyContract')
        ->orderBy('id', 'desc');
        return new SupplySolicitudeNoContractCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function records(Request $request)
    {
        $user = auth()->user();
        $user_id = $user->id;
        $type_user = $user->type;
        $type = $request->type;
        $type_operators = $request->type_operators;
        $column = $request->column;
        $value = $request->value;
        $records = SupplySolicitude::query();

        if($type == 'service'){
            $records->whereNotNull('supply_service_id');
        }
        if($type == 'connection'){
            $records->whereNull('supply_service_id');
        }
        if($type_operators){
            if(!in_array($type_user, ['admin', 'superadmin'])){
                $records->where('user_id', $user_id);
            }else{
                $records->whereNotNull('supply_service_id');
            }
        }
        if($type_operators == '0'){
            $records->where('review', '0');
        }
        if($type_operators == '1'){
            $records->where('review', '1');
        }
        if($type_operators == '2'){
            $records->where('review', '2');
        }
        if($column && $value){
            switch($column){
                case 'person.name':
                    $records->whereHas('person', function($query) use($value){
                        $query->where('name', 'like', "%{$value}%");
                    });
                    break;
                case 'supply.cod_route':
                    $records->whereHas('supply', function($query) use($value){
                        $query->where('cod_route', 'like', "%{$value}%");
                    });
                    break;
                case 'id':
                    $records->where('id', 'like', "%{$value}%");
                    break;
            }
        }
        $records->orderBy('id', 'desc');

        return new SupplySolicitudeCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'person_id' => 'required|integer',
            'supply_id' => 'required|integer',
            'user_id' => 'nullable|integer',
            'supply_service_id' => 'nullable|integer',
            'program_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'finish_date' => 'nullable|date',
            'use' => 'nullable|string|max:255',
            'active' => 'required|boolean',
            'review' => 'nullable|integer',
            'cod_tipo' => 'nullable|integer',
            'supply_debt_id' => 'nullable|integer',
            'observation' => 'nullable|string'
        ]);

        $solicitude = SupplySolicitude::create($request->all());

        return response()->json($solicitude, 201);
    }

    public function show($id)
    {
        $solicitude = SupplySolicitude::with([
            'person',
            'supply.sector',
            'supply.supplyVia',
            'supplyService',
            'user',
            'supplySolicitudeItems'
        ])->findOrFail($id);

        return new SupplySolicitudeResource($solicitude);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'person_id' => 'required|integer',
            'supply_id' => 'required|integer',
            'user_id' => 'nullable|integer',
            'supply_service_id' => 'nullable|integer',
            'program_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'finish_date' => 'nullable|date',
            'use' => 'nullable|string|max:255',
            'active' => 'required|boolean',
            'review' => 'nullable|integer',
            'cod_tipo' => 'nullable|integer',
            'supply_debt_id' => 'nullable|integer',
            'observation' => 'nullable|string'
        ]);

        $solicitude = SupplySolicitude::findOrFail($id);
        $solicitude->update($request->all());

        return response()->json($solicitude);
    }

    public function destroy($id)
    {
        $solicitude = SupplySolicitude::findOrFail($id);
        $solicitude->delete();

        return response()->json(['message' => 'Solicitud eliminada correctamente']);
    }

    public function printSolicitude($id)
    {
        $solicitude = SupplySolicitude::with([
            'person',
            'supply.sector',
            'supply.supplyVia',
            'supplyService',
            'user'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('tenant.supplies.solicitudes.format', compact('solicitude'));
        
        // Configurar el PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('solicitud-' . $solicitude->id . '.pdf');
    }

    public function printTicket($id)
    {
        $solicitude = SupplySolicitude::with([
            'person.identity_document_type',
            'supply.sector',
            'supplyService',
            'user'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('tenant.supplies.solicitudes.ticket', compact('solicitude'));
        
        // Configurar el PDF para ticket (80mm de ancho)
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm x 297mm
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('ticket-solicitud-' . $solicitude->id . '.pdf');
    }

    public function showDetail($id)
    {
        $solicitude = SupplySolicitude::with([
            'person',
            'supply.sector',
            'supply.supplyVia',
            'supplyService',
            'user',
            'supplySolicitudeItems'
        ])->findOrFail($id);

        return new SupplySolicitudeResource($solicitude);
    }

    public function storeDetail(Request $request)
    {
        $request->validate([
            'supply_solicitude_id' => 'required|integer|exists:supply_solicitude,id',
            'review_status' => 'nullable|integer',
            'pipe_diameter_water' => 'nullable|string',
            'property_type_water' => 'nullable|integer',
            'pipe_length_water' => 'nullable|string',
            'soil_type_water' => 'nullable|string',
            'pipe_diameter_drainage' => 'nullable|string',
            'property_type_drainage' => 'nullable|integer',
            'pipe_length_drainage' => 'nullable|string',
            'soil_type_drainage' => 'nullable|string',
            'water' => 'nullable|integer',
            'drainage' => 'nullable|integer',
            'connection_number_water' => 'nullable|string',
            'connection_number_drainage' => 'nullable|string',
            'connection_date' => 'nullable|date',
            'inspector_operator_id' => 'nullable|integer|exists:users,id',
            'installer_operator_id' => 'nullable|integer|exists:users,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Crear el item primero
        $itemData = $request->except(['photos']);
        $itemData['user_id'] = auth()->id();
        $item = SupplySolicitudeItem::create($itemData);

        // Procesar fotos si existen
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('supplies/solicitudes/' . $item->id, $filename, 'public');
                $photoPaths[] = $path;
            }
            
            // Guardar rutas de fotos en campo JSON o tabla separada
            $item->update(['photos' => json_encode($photoPaths)]);
        }

        return response()->json([
            'item' => $item,
            'photos' => $photoPaths
        ], 201);
    }

    public function updateDetail(Request $request, $id)
    {


        $request->validate([
            'supply_solicitude_id' => 'required|integer|exists:tenant.supply_solicitude,id',
            'review_status' => 'nullable|integer',
            'pipe_diameter_water' => 'nullable|string',
            'property_type_water' => 'nullable|integer',
            'pipe_length_water' => 'nullable|string',
            'soil_type_water' => 'nullable|string',
            'pipe_diameter_drainage' => 'nullable|string',
            'property_type_drainage' => 'nullable|integer',
            'pipe_length_drainage' => 'nullable|string',
            'soil_type_drainage' => 'nullable|string',
            'water' => 'nullable|integer',
            'drainage' => 'nullable|integer',
            'connection_number_water' => 'nullable|string',
            'connection_number_drainage' => 'nullable|string',
            'connection_date' => 'nullable|date',
            'inspector_operator_id' => 'nullable|integer|exists:tenant.users,id',
            'installer_operator_id' => 'nullable|integer|exists:tenant.users,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        $supply_solicitude_id = $request->supply_solicitude_id;
        $item = SupplySolicitudeItem::find($supply_solicitude_id);
        if(!$item){
            // $itemData['user_id'] = auth()->id();
            $request->merge(['user_id' => auth()->id()]);
            $item = SupplySolicitudeItem::create($request->all());
        }

        // Actualizar datos básicos
        $itemData = $request->except(['photos']);
        $itemData['user_id'] = auth()->id();

        $item->update($itemData);

        // Procesar nuevas fotos si existen
        if(is_array($request->photos)){
            $photoPaths = $request->photos;
        }else{
            $photoPaths = json_decode($item->photos ?? '[]', true);
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('supplies/solicitudes/' . $item->id, $filename, 'public');
                $photoPaths[] = $path;
            }

            $item->update(['photos' => json_encode($photoPaths)]);
        }

        return response()->json([
            'item' => $item,
            'photos' => $photoPaths
        ]);
    }

    public function consolidate(Request $request, $id)
    {
        $request->validate([
            'observation' => 'nullable|string'
        ]);

        $solicitude = SupplySolicitude::findOrFail($id);

        $solicitude->update([
            'consolidated' => true,
            'consolidated_at' => now(),
            'review' => 1,
            'observation' => $request->observation
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud consolidada correctamente',
            'solicitude' => $solicitude
        ]);
    }
}