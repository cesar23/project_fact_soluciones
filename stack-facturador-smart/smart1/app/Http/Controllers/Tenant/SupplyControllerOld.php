<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Requests\Api\Transform\Common\PersonTransform;
use App\CoreFacturalo\Requests\Api\Transform\Functions;
use App\CoreFacturalo\Requests\Inputs\DocumentInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Resources\Tenant\SectorCollection;
use App\Models\Tenant\Supply;
use App\Models\Tenant\SupplyPlan;
use App\Models\Tenant\SupplyPlanRegistered;
use App\Models\Tenant\SupplyState;
use App\Models\Tenant\Person;
use App\Models\Tenant\SupplyVia;
use App\Models\Tenant\Sector;
use App\Models\Tenant\User;
use App\Http\Resources\Tenant\SupplyCollection;
use App\Http\Resources\Tenant\SupplyResource;
use App\Http\Resources\Tenant\SupplyPlanCollection;
use App\Http\Resources\Tenant\SupplyPlanRegisteredCollection;
use App\Http\Resources\Tenant\SupplyPlanResource;
use App\Http\Resources\Tenant\SupplyPlanDocumentCollection;
use App\Http\Resources\Tenant\SupplyStateCollection;
use App\Http\Resources\Tenant\SupplyStateResource;
use App\Http\Resources\Tenant\SupplyViaCollection;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\Series;
use App\Models\Tenant\SupplyPlanDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Store\Http\Controllers\StoreController;

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
            'description' => 'Descripción',
            'person.name' => 'Persona',
            'supply_via.name' => 'Vía de Suministro',
            'sector.name' => 'Sector',
            'date_start' => 'Fecha Inicio',
            'supply_state.description' => 'Estado'
        ];
    }

    public function records(Request $request)
    {
        $records = Supply::with(['person', 'supplyVia', 'sector', 'supplyState'])
            ->join('persons', 'supplies.person_id', '=', 'persons.id')
            ->orderBy('persons.name', 'asc')
            ->select('supplies.*');

        return new SupplyCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'description' => 'required|string',
            'person_id' => 'required|exists:tenant.persons,id',
            'supply_via_id' => 'required|exists:tenant.supply_via,id',
            'date_start' => 'required|date',
            'state_supply_id' => 'required|exists:tenant.supplies_states,id'
        ]);
        $supplyVia = SupplyVia::find($request->supply_via_id);
        $request->merge(['sector_id' => $supplyVia->sector_id]);
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
            'description' => 'required|string',
            'person_id' => 'required|exists:tenant.persons,id',
            'supply_via_id' => 'required|exists:tenant.supply_via,id',
            'date_start' => 'required|date',
            'state_supply_id' => 'required|exists:tenant.supplies_states,id'
        ]);
        $supplyVia = SupplyVia::find($request->supply_via_id);

        $request->merge(['sector_id' => $supplyVia->sector_id]);
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

    // Supply Plans Methods
    public function plans()
    {
        return view('tenant.supplies.plans.index');
    }

    public function plansColumns()
    {
        return [
            'description' => 'Descripción',
            'type_zone' => 'Tipo Zona',
            'type_plan' => 'Tipo Plan',
            'price_c_m' => 'Precio C/M',
            'price_s_m' => 'Precio S/M',
            'price_alc' => 'Precio Alc',
            'total' => 'Total',
            'active' => 'Estado'
        ];
    }
    public function plansTables()
    {
        $configuration = Configuration::first();
        $affectation_igv_type_id = $configuration->affectation_igv_type_id;
        $affectation_types = AffectationIgvType::all();
        return response()->json([
            'affectation_types' => $affectation_types,
            'affectation_igv_type_id' => $affectation_igv_type_id
        ]);
    }

    public function plansRecords(Request $request)
    {
        $records = SupplyPlan::latest();

        return new SupplyPlanCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'type_zone' => 'required|in:URBANO,RURAL',
            'type_plan' => 'required|in:DOMICILIARIO,COMERCIAL',
            'price_c_m' => 'required|numeric|min:0',
            'price_s_m' => 'required|numeric|min:0',
            'price_alc' => 'required|numeric|min:0',
            'observation' => 'nullable|string',
            'active' => 'required|boolean'
        ]);

        // Calculate total as sum of all prices
        $total = $request->price_c_m + $request->price_s_m + $request->price_alc;
        $request->merge(['total' => $total]);

        $plan = SupplyPlan::create($request->all());

        return response()->json($plan, 201);
    }

    public function showPlan($id)
    {
        $plan = SupplyPlan::findOrFail($id);

        return new SupplyPlanResource($plan);
    }

    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'type_zone' => 'required|in:URBANO,RURAL',
            'type_plan' => 'required|in:DOMICILIARIO,COMERCIAL',
            'price_c_m' => 'required|numeric|min:0',
            'price_s_m' => 'required|numeric|min:0',
            'price_alc' => 'required|numeric|min:0',
            'observation' => 'nullable|string',
            'active' => 'required|boolean'
        ]);

        // Calculate total as sum of all prices
        $total = $request->price_c_m + $request->price_s_m + $request->price_alc;
        $request->merge(['total' => $total]);

        $plan = SupplyPlan::findOrFail($id);
        $plan->update($request->all());

        return response()->json($plan);
    }

    public function destroyPlan($id)
    {
        $plan = SupplyPlan::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Tarifa de predio eliminado correctamente']);
    }

    // Supply States Methods
    public function states()
    {
        return view('tenant.supplies.states.index');
    }

    public function statesColumns()
    {
        return [
            'description' => 'Descripción'
        ];
    }

    public function statesRecords(Request $request)
    {
        $records = SupplyState::latest();

        return new SupplyStateCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function storeState(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255'
        ]);

        $state = SupplyState::create($request->all());

        return response()->json($state, 201);
    }

    public function showState($id)
    {
        $state = SupplyState::findOrFail($id);

        return new SupplyStateResource($state);
    }

    public function updateState(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255'
        ]);

        $state = SupplyState::findOrFail($id);
        $state->update($request->all());

        return response()->json($state);
    }

    public function destroyState($id)
    {
        $state = SupplyState::findOrFail($id);
        $state->delete();

        return response()->json(['message' => 'Estado de predio eliminado correctamente']);
    }

    // Supply Via Methods
    public function supplyVias()
    {
        return view('tenant.supplies.supply_vias.index');
    }

    public function supplyViasColumns()
    {
        return [
            'name' => 'Nombre',
            'code' => 'Código',
            'supply_type_via.name' => 'Tipo de Vía',
            'sector.name' => 'Sector'
        ];
    }

    public function supplyViasAllRecords(Request $request)
    {
        $records = SupplyVia::with(['supplyTypeVia', 'sector'])->get();

        return response()->json($records);
    }

    public function supplyViasRecords(Request $request)
    {
        $records = SupplyVia::query();

        return new SupplyViaCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function storeSupplyVia(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'supply_type_via_id' => 'required|exists:tenant.supply_type_via,id',
            'sector_id' => 'required|exists:tenant.sectors,id'
        ]);

        $supplyVia = SupplyVia::create($request->all());

        return response()->json($supplyVia, 201);
    }

    public function showSupplyVia($id)
    {
        $supplyVia = SupplyVia::with(['supplyTypeVia', 'sector'])->findOrFail($id);

        return $supplyVia;
    }

    public function updateSupplyVia(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'supply_type_via_id' => 'required|exists:tenant.supply_type_via,id',
            'sector_id' => 'required|exists:tenant.sectors,id'
        ]);

        $supplyVia = SupplyVia::findOrFail($id);
        $supplyVia->update($request->all());

        return response()->json($supplyVia);
    }

    public function destroySupplyVia($id)
    {
        $supplyVia = SupplyVia::findOrFail($id);
        $supplyVia->delete();

        return response()->json(['message' => 'Vía de suministro eliminada correctamente']);
    }

    // Sectors Methods
    public function sectors()
    {
        return view('tenant.supplies.sectors.index');
    }

    public function sectorsColumns()
    {
        return [
            'name' => 'Nombre',
            'code' => 'Código'
        ];
    }

    public function allSectorsRecords()
    {
        $sectors = Sector::all();
        return new SectorCollection($sectors);
    }

    public function sectorsRecords(Request $request)
    {
        $records = Sector::query();

        return new SectorCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function storeSector(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        $sector = Sector::create($request->all());

        return response()->json($sector, 201);
    }

    public function showSector($id)
    {
        $sector = Sector::findOrFail($id);

        return $sector;
    }

    public function updateSector(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255'
        ]);

        $sector = Sector::findOrFail($id);
        $sector->update($request->all());

        return response()->json($sector);
    }

    public function destroySector($id)
    {
        $sector = Sector::findOrFail($id);
        $sector->delete();

        return response()->json(['message' => 'Sector eliminado correctamente']);
    }

    // Supply Plan Registered Methods
    public function registers()
    {
        return view('tenant.supplies.registers.index');
    }

    public function registersColumns()
    {
        return [
            'supply.code' => 'Código Predio',
            'supply.description' => 'Predio',
            'supply_plan.description' => 'Tarifa',
            'contract_number' => 'Número de Contrato',
            'observation' => 'Observación',
            'active' => 'Estado'
        ];
    }

    public function registersRecords(Request $request)
    {
        $records = SupplyPlanRegistered::with(['supply.person', 'supply.supplyVia', 'supply.sector', 'supplyPlan'])
            ->latest();

        return new SupplyPlanRegisteredCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function storeRegister(Request $request)
    {
        $request->validate([
            'supply_id' => 'required|exists:tenant.supplies,id',
            'supplie_plan_id' => 'required|exists:tenant.supplie_plans,id',
            'contract_number' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'active' => 'required|boolean'
        ]);

        $register = SupplyPlanRegistered::create($request->all() + ['user_id' => auth()->id()]);

        return response()->json($register, 201);
    }

    public function showRegister($id)
    {
        $register = SupplyPlanRegistered::with(['supply', 'supplyPlan', 'user', 'supply.person', 'supply.supplyVia', 'supply.sector'])
            ->findOrFail($id);

        return response()->json($register);
    }

    public function updateRegister(Request $request, $id)
    {
        $request->validate([
            'supply_id' => 'required|exists:tenant.supplies,id',
            'supplie_plan_id' => 'required|exists:tenant.supplies_plans,id',
            'contract_number' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'active' => 'required|boolean'
        ]);

        $register = SupplyPlanRegistered::findOrFail($id);
        $register->update($request->all());

        return response()->json($register);
    }

    public function destroyRegister($id)
    {
        $register = SupplyPlanRegistered::findOrFail($id);
        $register->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    public function changePlan(Request $request, $registerId)
    {
        try {
            $request->validate([
                'new_plan_id' => 'required|exists:tenant.supplie_plans,id',
                'change_reason' => 'required|string|max:500',
                'change_date' => 'required|date',
                'new_contract_number' => 'nullable|string|max:255',
                'new_observation' => 'nullable|string'
            ]);

            $currentRegister = SupplyPlanRegistered::findOrFail($registerId);

            // Verificar que el registro esté activo
            if (!$currentRegister->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El registro no está activo y no se puede cambiar de tarifa'
                ], 400);
            }

            // Verificar que el nuevo plan sea diferente
            if ($currentRegister->supplie_plan_id == $request->new_plan_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El tarifa seleccionado es el mismo que el actual'
                ], 400);
            }

            DB::beginTransaction();

            // 1. Desactivar el registro actual con fecha de fin
            $currentRegister->update([
                'active' => false,
                'date_end' => $request->change_date,
                'observation' => ($currentRegister->observation ?? '') . "\n" .
                    "Cambiado el {$request->change_date}. Razón: {$request->change_reason}"
            ]);

            // 2. Crear nuevo registro con el nuevo plan
            $newRegister = SupplyPlanRegistered::create([
                'supply_id' => $currentRegister->supply_id,
                'supplie_plan_id' => $request->new_plan_id,
                'user_id' => auth()->id(),
                'contract_number' => $request->new_contract_number ?? $currentRegister->contract_number,
                'observation' => $request->new_observation,
                'active' => true,
                'date_start' => $request->change_date,
                'change_reason' => $request->change_reason,
                'previous_plan_registered_id' => $currentRegister->id,
                'generation_day' => $currentRegister->generation_day,
                'auto_generate' => $currentRegister->auto_generate,
                'start_generation_date' => $request->change_date,
                'end_generation_date' => $currentRegister->end_generation_date
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarifa cambiado exitosamente',
                'data' => [
                    'old_register_id' => $currentRegister->id,
                    'new_register_id' => $newRegister->id,
                    'change_date' => $request->change_date,
                    'old_plan' => $currentRegister->supplyPlan->description,
                    'new_plan' => $newRegister->supplyPlan->description
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la tarifa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPlanHistory($supplyId)
    {
        try {
            $supply = Supply::with(['person', 'supplyVia', 'sector'])->findOrFail($supplyId);

            $history = SupplyPlanRegistered::where('supply_id', $supplyId)
                ->with(['supplyPlan', 'user'])
                ->orderBy('date_start', 'desc')
                ->get()
                ->map(function ($register) {
                    return [
                        'id' => $register->id,
                        'plan' => $register->supplyPlan,
                        'contract_number' => $register->contract_number,
                        'date_start' => $register->date_start,
                        'date_end' => $register->date_end,
                        'change_reason' => $register->change_reason,
                        'active' => $register->active,
                        'user' => $register->user ? $register->user->name : null,
                        'observation' => $register->observation,
                        'documents_count' => $register->documents()->count(),
                        'total_generated' => $register->documents()->sum('amount')
                    ];
                });

            return response()->json([
                'success' => true,
                'supply_id' => $supplyId,
                'supply' => $supply,
                'history' => $history
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRegisterDocuments(Request $request, $registerId)
    {
        try {
            $register = SupplyPlanRegistered::findOrFail($registerId);

            $query = $register->documents()
                ->with(['document'])
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc');

            // Filtro por fecha de generación
            if ($request->filled('generation_date')) {
                $query->whereDate('generation_date', $request->generation_date);
            }

            // Paginación de 10 elementos por página
            $documents = $query->paginate(10);

            return new SupplyPlanDocumentCollection($documents);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los documentos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printSupplyPlanDocument(Request $request, $documentId)
    {
        try {
            // Obtener el documento de plan de predio
            $planDocument = SupplyPlanDocument::with([
                'supplyPlanRegistered.supply.person',
                'supplyPlanRegistered.supply.supplyVia',
                'supplyPlanRegistered.supply.sector',
                'supplyPlanRegistered.supplyPlan',
                'document',
                'user'
            ])->findOrFail($documentId);

            // Datos de la empresa y establecimiento
            $company = Company::first();
            $user = auth()->user();
            $establishment = Establishment::where('id', $user->establishment_id)->first();

            if (!$establishment) {
                $establishment = Establishment::first();
            }

            // Preparar datos para la vista
            $data = [
                'planDocument' => $planDocument,
                'supply' => $planDocument->supplyPlanRegistered->supply,
                'plan' => $planDocument->supplyPlanRegistered->supplyPlan,
                'person' => $planDocument->supplyPlanRegistered->supply->person,
                'supplyVia' => $planDocument->supplyPlanRegistered->supply->supplyVia,
                'sector' => $planDocument->supplyPlanRegistered->supply->sector,
                'contract' => $planDocument->supplyPlanRegistered->contract_number,
                'date' => Carbon::now()->format('d-m-Y'),
                'time' => Carbon::now()->format('H:i:s')
            ];

            // Calcular altura del PDF basado en contenido real
            $baseHeight = 0;

            // Encabezado empresa (logo, nombre, dirección, RUC, etc.)
            $baseHeight += 80;

            // Título "RECIBO - PLAN DE SUMINISTRO" + período + contrato
            $baseHeight += 40;

            // Información del documento (fecha emisión, vencimiento, estado, documento)
            $documentLines = 2; // Mínimo fecha emisión y estado
            if ($planDocument->due_date) $documentLines++;
            if ($planDocument->full_document_number) $documentLines++;
            $baseHeight += $documentLines * 15;

            // Separador + título "INFORMACIÓN DEL SUMINISTRO"
            $baseHeight += 20;

            // Información del predio (código, descripción, cliente, documento, zona, sector)
            $supplyLines = 5; // código, descripción, cliente, zona, sector
            if ($data['person'] && $data['person']->number) $supplyLines++; // documento persona
            $baseHeight += $supplyLines * 15;

            // Separador + título "PLAN DE SUMINISTRO" 
            $baseHeight += 20;

            // Plan info (plan, estado)
            $baseHeight += 2 * 15;

            // Tabla de conceptos (header + 1 fila + separadores + total)
            $baseHeight += 80;

            // Observaciones si existen
            if ($planDocument->observations) {
                $obsLines = ceil(strlen($planDocument->observations) / 50); // ~50 caracteres por línea
                $baseHeight += 15 + ($obsLines * 12); // título + líneas
            }

            // Footer (generado por, fecha/hora)
            $baseHeight += 30;

            // Firmas
            $baseHeight += 30;

            // Nota final
            $baseHeight += 20;

            // Agregar margen de seguridad
            $totalHeight = $baseHeight + 50;

            // Generar PDF en formato A5 portrait
            $pdf = PDF::loadView('tenant.supplies.documents.ticket_a5', compact(
                'company',
                'establishment',
                'data'
            ))->setPaper('A5', 'portrait');

            return $pdf->stream("recibo_predio_{$planDocument->id}.pdf");
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el recibo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printSupplyPlanDocumentById($id)
    {
        return $this->printSupplyPlanDocument(request(), $id);
    }

    public function generateNextDocument($registerId)
    {
        try {
            $register = SupplyPlanRegistered::with(['supply', 'supplyPlan'])
                ->findOrFail($registerId);

            // Verificar que el registro esté activo
            if (!$register->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El registro no está activo'
                ], 400);
            }

            // Calcular el siguiente mes para generar
            $currentMonth = now()->month;
            $currentYear = now()->year;

            // Buscar el último documento generado para determinar el siguiente período
            $lastDocument = $register->documents()
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();

            if ($lastDocument) {
                // Calcular siguiente mes basado en el último documento
                $nextMonth = $lastDocument->month + 1;
                $nextYear = $lastDocument->year;

                if ($nextMonth > 12) {
                    $nextMonth = 1;
                    $nextYear++;
                }
            } else {
                // Si no hay documentos previos, usar el mes actual
                $nextMonth = $currentMonth;
                $nextYear = $currentYear;
            }

            // Verificar que no exista ya un documento para este período
            $existingDocument = $register->documents()
                ->where('year', $nextYear)
                ->where('month', $nextMonth)
                ->first();

            if ($existingDocument) {
                return response()->json([
                    'success' => false,
                    'message' => "Ya existe un documento para " . $this->getMonthName($nextMonth) . " " . $nextYear
                ], 400);
            }

            // Crear el nuevo documento
            $document = $this->createSupplyPlanDocument($register, $nextYear, $nextMonth);

            return response()->json([
                'success' => true,
                'message' => "Documento generado para " . $this->getMonthName($nextMonth) . " " . $nextYear,
                'document' => [
                    'id' => $document->id,
                    'period' => $document->period,
                    'year' => $document->year,
                    'month' => $document->month,
                    'amount' => $document->amount,
                    'status' => $document->status,
                    'due_date' => $document->due_date->format('Y-m-d')
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createSupplyPlanDocument($register, $year, $month)
    {
        // Calcular fecha de vencimiento
        $generationDay = $register->generation_day ?? 15; // Día 15 por defecto
        $dueDate = Carbon::createFromDate($year, $month, 1)
            ->addMonth()
            ->setDay(min($generationDay, Carbon::createFromDate($year, $month + 1, 1)->daysInMonth));




        $info = [
            'name_description' => $register->supplyPlan->description,
            'price' => $register->supplyPlan->total,
            'customer_id' => $register->supply->person_id,
        ];
        $response = $this->generatorDocument($info);
        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'Error al generar el documento'
            ];
        }
        $document_id = null;
        $document_number = null;
        $document_series = null;
        if ($response['success']) {
            $document_id = $response['document_id'];
            $document = Document::find($document_id);
            $document_number = $document->number;
            $document_series = $document->series;
        }

        return SupplyPlanDocument::create([
            'supply_plan_registered_id' => $register->id,
            'year' => $year,
            'month' => $month,
            'period' => $this->getMonthName($month) . ' ' . $year,
            'amount' => $register->supplyPlan->total,
            'status' => 'pending',
            'generation_date' => now(),
            'due_date' => $dueDate,
            'document_series' => $document_series,
            'document_number' => $document_number,
            'document_id' => $document_id,
            'user_id' => auth()->id(),
            'observations' => 'Documento generado manualmente'
        ]);
    }

    private function getMonthName($month)
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $months[$month] ?? 'Mes';
    }

    public function getNextDocumentInfo($registerId)
    {
        try {
            $register = SupplyPlanRegistered::with(['supply', 'supplyPlan'])
                ->findOrFail($registerId);

            if (!$register->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El registro no está activo'
                ]);
            }

            // Calcular información del siguiente documento
            $lastDocument = $register->documents()
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();

            if ($lastDocument) {
                $nextMonth = $lastDocument->month + 1;
                $nextYear = $lastDocument->year;

                if ($nextMonth > 12) {
                    $nextMonth = 1;
                    $nextYear++;
                }
            } else {
                $nextMonth = now()->month;
                $nextYear = now()->year;
            }

            // Verificar si ya existe
            $existingDocument = $register->documents()
                ->where('year', $nextYear)
                ->where('month', $nextMonth)
                ->exists();

            $generationDay = $register->generation_day ?? 15;
            $suggestedDueDate = Carbon::createFromDate($nextYear, $nextMonth, 1)
                ->addMonth()
                ->setDay(min($generationDay, Carbon::createFromDate($nextYear, $nextMonth + 1, 1)->daysInMonth));

            return response()->json([
                'success' => true,
                'can_generate' => !$existingDocument,
                'next_period' => $this->getMonthName($nextMonth) . ' ' . $nextYear,
                'next_month' => $nextMonth,
                'next_year' => $nextYear,
                'amount' => $register->supplyPlan->total,
                'suggested_due_date' => $suggestedDueDate->format('Y-m-d'),
                'existing_document' => $existingDocument
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información: ' . $e->getMessage()
            ], 500);
        }
    }




    public function generatorDocument($info)
    {
        $igv = (new StoreController)->getIgv(new Request());
        $items = [];
        $plan_description = $info['name_description'];
        $plan_price = $info['price'];
        $customer_id = $info['customer_id'];
        $item = Item::checkIfExistBaseItemService();
        if (!$item) {
            throw new \Exception('Item not found');
        }
        $presentation = null;

        $affectation_igv_type_id = $item->sale_affectation_igv_type_id;
        $price = $plan_price;
        $value = $plan_price;
        $has_igv = $item->has_igv;
        if ($has_igv && $affectation_igv_type_id == "10") {
            $value = $price / (1 + $igv);
        }
        $quantity = 1;
        $total_value = $value * $quantity;
        $total_igv = 0;

        if ($affectation_igv_type_id == "10") {
            $total_igv = $total_value * $igv;
        }
        if ($affectation_igv_type_id == "20") {
            $total_igv = 0;
        }
        if ($affectation_igv_type_id == "30") {
            $total_igv = 0;
        }
        $total_taxes = $total_igv;
        $total = $total_value + $total_taxes;



        $items[] = [
            'item_id' => $item->id,
            'item' => [
                'has_perception' => false,
                'percentage_perception' => null,
                'can_edit_price' => false,
                'video_url' => null,
                'meter' => null,
                'bonus_items' => [],
                'disponibilidad' => null,
                'header' => null,
                'id' => $item->id,
                'item_code' => null,
                'full_description' => $plan_description,
                'description' => $plan_description,
                'model' => null,
                'brand' => null,
                'brand_id' => null,
                'category_id' => $item->category_id,
                'stock' => $item->stock,
                'internal_id' => $item->internal_id,
                'currency_type_id' => $item->currency_type_id,
                'has_igv' => $item->has_igv,
                'sale_unit_price' => $price,
                'purchase_has_igv' => $item->purchase_has_igv,
                'purchase_unit_price' => $item->purchase_unit_price,
                'unit_type_id' => $item->unit_type_id,
                'sale_affectation_igv_type_id' => $item->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $item->purchase_affectation_igv_type_id,
                'calculate_quantity' => false,
                'has_plastic_bag_taxes' => false,
                'amount_plastic_bag_taxes' => '0.50',
                'item_unit_types' => [],
                'warehouses' => [],
                'attributes' => [],
                'lots_group' => [],
                'lots' => [],
                'is_set' => 0,
                'barcode' => $item->barcode,
                'lots_enabled' => false,
                'series_enabled' => false,
                'unit_price' => $price,
                'warehouse_id' => $item->warehouse_id,
                'presentation' => $presentation,
                'used_points_for_exchange' => 0,
            ],
            'quantity' => $quantity,
            'unit_value' => $value,
            'price_type_id' => '01',
            'unit_price' => $price,
            'affectation_igv_type_id' => $affectation_igv_type_id,
            'total_base_igv' => $total_value,
            'percentage_igv' => $igv * 100,
            'total_igv' => $total_igv,
            'total_taxes' => $total_taxes,
            'total_value' => $total_value,
            'name_product_xml' => $plan_description,
            'name_product_pdf' => $plan_description,
            'total' => $total,
            'attributes' => [],
            'discounts' => [],
            'charges' => [],
            'warehouse_id' => $item->warehouse_id
        ];



        $inputs = $this->calculateTotal($items);
        if (count($items) > 0) {
            $inputs['items'] = $items;
            $inputs['customer_id'] = $customer_id;
            $formatted = $this->format($inputs);
            $document_inputs =  DocumentInput::set($formatted);
            $response = (new DocumentController)->store(new DocumentRequest($document_inputs));
            return [
                'success' => true,
                'message' => 'Documento generado correctamente',
                'document_id' => $response['data']['id']
            ];
        }
        return [
            'success' => false,

        ];
    }

    public function calculateTotal($items)
    {
        $total_discount = 0;
        $total_exportation = 0;
        $total_taxed = 0;
        $total_exonerated = 0;
        $total_unaffected = 0;
        $total_free = 0;
        $total_igv = 0;
        $total_value = 0;
        $total = 0;
        $total_igv_free = 0;

        foreach ($items as $row) {


            if ($row['affectation_igv_type_id'] === "10") {
                $total_taxed += floatval($row['total_value']);
            }
            if ($row['affectation_igv_type_id'] === "20") {
                $total_exonerated += floatval($row['total_value']);
            }
            if ($row['affectation_igv_type_id'] === "30") {
                $total_unaffected += floatval($row['total_value']);
            }
            if ($row['affectation_igv_type_id'] === "40") {
                $total_exportation += floatval($row['total_value']);
            }
            if (!in_array($row['affectation_igv_type_id'], ["10", "20", "30", "40"])) {
                $total_free += floatval($row['total_value']);
            }
            if (in_array($row['affectation_igv_type_id'], ["10", "20", "30", "40"])) {
                $total_igv += floatval($row['total_igv']);
                $total += floatval($row['total']);
            }
            $total_value += floatval($row['total_value']);

            if (in_array($row['affectation_igv_type_id'], ["11", "12", "13", "14", "15", "16"])) {
                $unit_value = $row['total_value'] / $row['quantity'];
                $total_value_partial = $unit_value * $row['quantity'];
                $row['total_taxes'] = $row['total_value'] - $total_value_partial;
                $row['total_igv'] = $total_value_partial * ($row['percentage_igv'] / 100);
                $row['total_base_igv'] = $total_value_partial;
                $total_value -= $row['total_value'];
                $total_igv_free += $row['total_igv'];
            }
        }

        return [
            'date_of_issue' => now()->format('Y-m-d'),
            'time_of_issue' => now()->format('H:i:s'),
            'currency_type_id' => 'PEN',
            'exchange_rate_sale' => 1,
            'total_igv_free' => round($total_igv_free, 2),
            'total_discount' => round($total_discount, 2),
            'total_exportation' => round($total_exportation, 2),
            'total_taxed' => round($total_taxed, 2),
            'total_exonerated' => round($total_exonerated, 2),
            'total_unaffected' => round($total_unaffected, 2),
            'total_free' => round($total_free, 2),
            'total_igv_free' => 0.00,
            'total_igv' => round($total_igv, 2),
            'total_value' => round($total_value, 2),
            'total_taxes' => round($total_igv, 2),
            'total' => round($total, 2)
        ];
    }

    public function format($inputs)
    {

        $company = Company::first();

        $customer = Person::where('id', $inputs['customer_id'])->first();

        $establishment = Establishment::first();
        $series = Series::where('document_type_id', "03")->where('establishment_id', $establishment->id)->first();

        $inputs_transform = [
            'user_id' => auth()->user()->id,
            'external_id' => Str::uuid()->toString(),
            'soap_type_id' => $company->soap_type_id,
            'establishment_id' => $establishment->id,
            'document_type_id' => "03",
            'establishment' =>  json_decode(Establishment::where('id', $establishment->id)->first(), true),
            'customer_id' => $customer->id,
            'customer' => PersonTransform::transform_customer($customer),
            'state_type_id' => "01",
            'number' => "#",
            'operation_type_id' => "0101",
            'series' => $series->number,
            'date_of_issue' => Functions::valueKeyInArray($inputs, 'date_of_issue'),
            'date_of_due' => now()->addMonth()->format('Y-m-d'),
            'time_of_issue' => Functions::valueKeyInArray($inputs, 'time_of_issue'),
            'currency_type_id' => Functions::valueKeyInArray($inputs, 'currency_type_id'),
            'exchange_rate_sale' => Functions::valueKeyInArray($inputs, 'exchange_rate_sale', 1),
            'purchase_order' => null,
            'total_prepayment' => 0.00,
            'total_discount' => 0.00,
            'total_charge' => 0.00,
            'total_exportation' => 0.00,
            'total_free' => 0.00,
            'total_prepayment' => 0.00,
            'total_discount'   => 0.00,
            'total_charge'     => 0.00,
            'total_taxed' => Functions::valueKeyInArray($inputs, 'total_taxed'),
            'total_unaffected' => Functions::valueKeyInArray($inputs, 'total_unaffected'),
            'total_exonerated' => Functions::valueKeyInArray($inputs, 'total_exonerated'),
            'total_igv' => Functions::valueKeyInArray($inputs, 'total_igv'),
            'total_igv_free' => Functions::valueKeyInArray($inputs, 'total_igv_free'),
            'total_base_isc' => 0.00,
            'total_isc' => 0.00,
            'total_base_other_taxes' => 0.00,
            'total_other_taxes' => 0.00,
            'total_plastic_bag_taxes' => 0.00,
            'total_taxes' => Functions::valueKeyInArray($inputs, 'total_taxes'),
            'total_value' => Functions::valueKeyInArray($inputs, 'total_value'),
            'subtotal' => (Functions::valueKeyInArray($inputs, 'total_value')) ? $inputs['total_value'] : $inputs['total'],
            'total' => Functions::valueKeyInArray($inputs, 'total'),
            'total_pending_payment' => 0.00,
            'has_prepayment' => 0,
            'items' => $inputs['items'],
            'additional_information' => Functions::valueKeyInArray($inputs, 'informacion_adicional'),
            'additional_data' => Functions::valueKeyInArray($inputs, 'dato_adicional'),
            'payments' => [],
            'payment_condition_id' => '01',
            'total_detraction' => 0.00,
        ];


        return $inputs_transform;
    }
}
