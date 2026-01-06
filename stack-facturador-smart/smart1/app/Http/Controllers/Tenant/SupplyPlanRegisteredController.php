<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Requests\Api\Transform\Common\PersonTransform;
use App\CoreFacturalo\Requests\Api\Transform\Functions;
use App\CoreFacturalo\Requests\Inputs\DocumentInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Resources\Tenant\SupplyPlanDocumentCollection;
use App\Http\Resources\Tenant\SupplyPlanRegisteredCollection;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Supply;
use App\Models\Tenant\SupplyPlanDocument;
use App\Models\Tenant\SupplyPlanRegistered;
use App\Models\Tenant\SupplyDebt;
use App\Models\Tenant\SupplyDebtDocument;
use App\Models\Tenant\SaleNote;
use App\Http\Requests\Tenant\SaleNoteRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Store\Http\Controllers\StoreController;

class SupplyPlanRegisteredController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.registers.index');
    }

    public function columns()
    {
        return [
            'person.id' => 'Contribuyente',
            'supply.id' => 'Predio',
        ];
    }

    public function records(Request $request)
    {
        $records = SupplyPlanRegistered::with(['supply.person', 'supply.supplyVia', 'supply.sector', 'supplyPlan']);
        $column = $request->column;
        $value = $request->value;
        if ($column && $value) {
            switch ($request->column) {
                case 'person.id':
                    $records->whereHas('supply.person', function ($query) use ($request) {
                        $query->where('id', $request->value);
                    });
                    break;
                case 'supply.id':
                    $records->where('supply_id', $request->value);
                    break;
            }
        }
        $records->join('supplies', 'supplies_plans_registered.supply_id', '=', 'supplies.id')
            ->orderBy('supplies.cod_route', 'asc')
            ->select('supplies_plans_registered.*');

        return new SupplyPlanRegisteredCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
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

    public function show($id)
    {
        $register = SupplyPlanRegistered::with(['supply', 'supplyPlan', 'user', 'supply.person', 'supply.supplyVia', 'supply.sector'])
            ->findOrFail($id);

        return response()->json($register);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supply_id' => 'required|exists:tenant.supplies,id',
            'supplie_plan_id' => 'required|exists:tenant.supplie_plans,id',
            'contract_number' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'active' => 'required|boolean'
        ]);

        $register = SupplyPlanRegistered::findOrFail($id);
        $register->update($request->all());

        return response()->json($register);
    }

    public function destroy($id)
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

    public function getDocuments(Request $request, $registerId)
    {
        try {
            $register = SupplyPlanRegistered::findOrFail($registerId);

            $query = $register->documents()
                ->with(['document', 'sale_note'])
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

    public function printDocument(Request $request, $documentId)
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
            $baseHeight += 80; // Encabezado empresa
            $baseHeight += 40; // Título
            $documentLines = 2;
            if ($planDocument->due_date) $documentLines++;
            if ($planDocument->full_document_number) $documentLines++;
            $baseHeight += $documentLines * 15;
            $baseHeight += 20; // Separador
            $supplyLines = 5;
            if ($data['person'] && $data['person']->number) $supplyLines++;
            $baseHeight += $supplyLines * 15;
            $baseHeight += 20; // Separador
            $baseHeight += 2 * 15; // Plan info
            $baseHeight += 80; // Tabla
            if ($planDocument->observations) {
                $obsLines = ceil(strlen($planDocument->observations) / 50);
                $baseHeight += 15 + ($obsLines * 12);
            }
            $baseHeight += 30; // Footer
            $baseHeight += 30; // Firmas
            $baseHeight += 20; // Nota final
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

    public function printDocumentById($id)
    {
        return $this->printDocument(request(), $id);
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
            'affectation_igv_type_id' => $register->supplyPlan->affectation_type_id,
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
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        return $months[$month] ?? 'Mes';
    }

    public function generatorDocument($info)
    {
        $igv = (new StoreController)->getIgv(new Request());
        $items = [];
        $plan_description = $info['name_description'];
        $plan_price = $info['price'];
        $customer_id = $info['customer_id'];

        $item = Item::checkIfExistBaseItem();
        if (!$item) {
            throw new \Exception('Item not found');
        }
        $presentation = null;

        $affectation_igv_type_id = $info['affectation_igv_type_id'];
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
            $document_inputs = DocumentInput::set($formatted);
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
            'establishment' => json_decode(Establishment::where('id', $establishment->id)->first(), true),
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
            'total_discount' => 0.00,
            'total_charge' => 0.00,
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
            'payments' => [
                [
                    'id' => null,
                    'date_of_payment' => $inputs['date_of_issue'],
                    'payment_method_type_id' => '01', // Efectivo
                    'payment_destination_id' => 'cash',
                    'reference' => null,
                    'payment' => Functions::valueKeyInArray($inputs, 'total'),
                ]
            ],
            'payment_condition_id' => '01',
            'total_detraction' => 0.00,
        ];

        return $inputs_transform;
    }

    /**
     * Generate document from selected debts with partial payment support
     */
    public function generateDebtDocument(Request $request)
    {
        try {
            $request->validate([
                'supply_id' => 'required|exists:tenant.supplies,id',
                'register_id' => 'required|exists:tenant.supplies_plans_registered,id',
                'debt_amounts' => 'required|array|min:1',
                'debt_amounts.*.debt_id' => 'required|exists:tenant.supply_debt,id',
                'debt_amounts.*.amount' => 'required|numeric|min:0.01',
                'debt_amounts.*.original_amount' => 'required|numeric|min:0.01',
                'document_type_id' => 'required|string',
                'emission_date' => 'required|date',
                'observations' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Get supply information
            $supply = Supply::with(['person', 'sector', 'supplyVia.supplyTypeVia'])->findOrFail($request->supply_id);

            // Extract debt IDs from debt_amounts array
            $debtIds = collect($request->debt_amounts)->pluck('debt_id')->toArray();

            // Get selected debts
            $debts = SupplyDebt::whereIn('id', $debtIds)
                ->where('active', false) // Only unpaid debts
                ->with(['supplyConcept', 'supplyTypeDebt'])
                ->get();

            if ($debts->isEmpty()) {
                throw new Exception('No se encontraron deudas válidas para procesar');
            }

            // Create lookup array for amounts
            $debtAmounts = collect($request->debt_amounts)->keyBy('debt_id');

            // Preserve original amounts and prepare items for document
            $items = [];
            $totalAmount = 0;

            foreach ($debts as $debt) {
                // Preserve original amount if not set
                $this->preserveOriginalAmount($debt);

                // Get amount data from request
                $amountData = $debtAmounts->get($debt->id);
                if (!$amountData) {
                    throw new Exception("No se encontró información de monto para la deuda {$debt->id}");
                }

                $amountToPay = floatval($amountData['amount']);
                $originalAmount = floatval($amountData['original_amount']);

                // Validate amounts
                if ($amountToPay > $debt->amount) {
                    throw new Exception("El monto a pagar para la deuda {$debt->id} excede el saldo pendiente");
                }

                if ($amountToPay <= 0) {
                    throw new Exception("El monto a pagar debe ser mayor a 0");
                }

                // Validate that original amount matches current debt amount (for first payment)
                if ($debt->original_amount === null && $originalAmount != $debt->amount) {
                    throw new Exception("El monto original no coincide con la deuda {$debt->id}");
                }

                // Generate description based on debt type
                $description = $this->generateDebtItemDescription($debt);

                // Add item for document generation
                $items[] = [
                    'debt_id' => $debt->id,
                    'description' => $description,
                    'amount' => $amountToPay,
                    'affectation_igv_type_id' => '20', // Exonerado by default for utility bills
                ];

                $totalAmount += $amountToPay;
            }

            // Generate the document
            $documentInfo = [
                'customer_id' => $supply->person_id,
                'document_type_id' => $request->document_type_id,
                'emission_date' => $request->emission_date,
                'observations' => $request->observations,
                'items' => $items,
                'total_amount' => $totalAmount,
            ];

            $documentResponse = $this->generateDebtPaymentDocument($documentInfo);

            if (!$documentResponse['success']) {
                throw new Exception($documentResponse['message'] ?? 'Error al generar el documento');
            }

            // Create SupplyPlanDocument record
            $planDocument = $this->createDebtPlanDocument(
                $request->document_type_id,
                $documentResponse,
                $request->observations,
                $totalAmount,
                $request->register_id
            );

            // Update debts and create relationships
            foreach ($debts as $debt) {
                $amountData = $debtAmounts->get($debt->id);
                $amountToPay = floatval($amountData['amount']);
                $debtAmountBefore = $debt->amount;

                // Update debt amount
                $newAmount = $debt->amount - $amountToPay;
                $paymentType = ($newAmount <= 0.01) ? 'full' : 'partial';
                if ($newAmount <= 0.01) { // Consider amounts less than 0.01 as fully paid
                    // Full payment - mark as paid
                    $debt->update([
                        'amount' => 0,
                        'active' => true,
                        'paid_amount' => ($debt->paid_amount ?? 0) + $amountToPay,
                        'last_payment_date' => now(),
                        'payment_count' => ($debt->payment_count ?? 0) + 1,
                    ]);
                } else {
                    // Partial payment - update remaining amount
                    $debt->update([
                        'amount' => round($newAmount, 2),
                        'paid_amount' => ($debt->paid_amount ?? 0) + $amountToPay,
                        'last_payment_date' => now(),
                        'payment_count' => ($debt->payment_count ?? 0) + 1,
                    ]);
                }

                // Create debt-document relationship with detailed tracking
                SupplyDebtDocument::create([
                    'debt_id' => $debt->id,
                    'supply_plan_document_id' => $planDocument->id,
                    'amount_paid' => $amountToPay,
                    'debt_amount_before' => $debtAmountBefore,
                    'debt_amount_after' => round($newAmount, 2),
                    'payment_type' => $paymentType,
                    'is_cancelled' => false,
                ]);
            }

            DB::commit();

            // Prepare response data
            $responseData = [
                'plan_document_id' => $planDocument->id,
                'document_number' => $planDocument->document_series . '-' . $planDocument->document_number,
                'total_amount' => $totalAmount,
                'processed_debts' => count($debts),
                'document_type' => $request->document_type_id,
            ];

            // Add appropriate document ID based on type
            if ($request->document_type_id === '80') {
                $responseData['sale_note_id'] = $documentResponse['data']['id'] ?? null;
            } else {
                $responseData['document_id'] = $documentResponse['document_id'] ?? null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Documento generado exitosamente',
                'data' => $responseData
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preserve original amount before payment
     */
    private function preserveOriginalAmount(SupplyDebt $debt)
    {
        if (is_null($debt->original_amount)) {
            $debt->update(['original_amount' => $debt->amount]);
        }
    }

    /**
     * Generate description for debt item in document
     */
    private function generateDebtItemDescription(SupplyDebt $debt): string
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        // If it's a concept type debt
        if ($debt->type == 'c' && $debt->supplyConcept) {
            return $debt->supplyConcept->name;
        }

        // If it's a manual debt without month
        if (empty($debt->month) && $debt->supply_type_debt_id == 1 && $debt->type == 'r') {
            return "Pago de deuda manual";
        }

        // For monthly debts
        if (!empty($debt->month)) {
            $mesNumero = (int)$debt->month;
            if ($mesNumero > 0 && $mesNumero <= 12) {
                $nombreMes = $meses[$mesNumero];
                $anio = $debt->year ? ' - ' . $debt->year : '';
                return "Pago consumo " . $nombreMes . $anio;
            }
        }

        return "Pago de deuda";
    }

    /**
     * Generate document using adapted logic from generatorDocument
     */
    private function generateDebtPaymentDocument($info)
    {
        try {
            $igv = (new StoreController)->getIgv(new Request());
            $items = [];
            $customer_id = $info['customer_id'];

            $baseItem = Item::checkIfExistBaseItem();
            if (!$baseItem) {
                throw new Exception('Item base no encontrado');
            }

            $totalAmount = 0;

            foreach ($info['items'] as $debtItem) {
                $description = $debtItem['description'];
                $amount = $debtItem['amount'];
                $affectation_igv_type_id = $debtItem['affectation_igv_type_id'];

                $price = $amount;
                $value = $amount;
                $has_igv = $baseItem->has_igv;

                if ($has_igv && $affectation_igv_type_id == "10") {
                    $value = $price / (1 + $igv);
                }

                $quantity = 1;
                $total_value = $value * $quantity;
                $total_igv = 0;

                if ($affectation_igv_type_id == "10") {
                    $total_igv = $total_value * $igv;
                }

                $total_taxes = $total_igv;
                $total = $total_value + $total_taxes;
                $totalAmount += $total;

                $items[] = [
                    'item_id' => $baseItem->id,
                    'item' => [
                        'has_perception' => false,
                        'percentage_perception' => null,
                        'can_edit_price' => false,
                        'video_url' => null,
                        'meter' => null,
                        'bonus_items' => [],
                        'disponibilidad' => null,
                        'header' => null,
                        'id' => $baseItem->id,
                        'item_code' => null,
                        'full_description' => $description,
                        'description' => $description,
                        'model' => null,
                        'brand' => null,
                        'brand_id' => null,
                        'category_id' => $baseItem->category_id,
                        'stock' => $baseItem->stock,
                        'internal_id' => $baseItem->internal_id,
                        'currency_type_id' => $baseItem->currency_type_id,
                        'has_igv' => $baseItem->has_igv,
                        'sale_unit_price' => $price,
                        'purchase_has_igv' => $baseItem->purchase_has_igv,
                        'purchase_unit_price' => $baseItem->purchase_unit_price,
                        'unit_type_id' => $baseItem->unit_type_id,
                        'sale_affectation_igv_type_id' => $baseItem->sale_affectation_igv_type_id,
                        'purchase_affectation_igv_type_id' => $baseItem->purchase_affectation_igv_type_id,
                        'calculate_quantity' => false,
                        'has_plastic_bag_taxes' => false,
                        'amount_plastic_bag_taxes' => '0.50',
                        'item_unit_types' => [],
                        'warehouses' => [],
                        'attributes' => [],
                        'lots_group' => [],
                        'lots' => [],
                        'is_set' => 0,
                        'barcode' => $baseItem->barcode,
                        'lots_enabled' => false,
                        'series_enabled' => false,
                        'unit_price' => $price,
                        'warehouse_id' => $baseItem->warehouse_id,
                        'presentation' => null,
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
                    'name_product_xml' => $description,
                    'name_product_pdf' => $description,
                    'total' => $total,
                    'warehouse_id' => $baseItem->warehouse_id
                ];
            }

            $inputs = $this->calculateTotal($items);
            $inputs['items'] = $items;
            $inputs['customer_id'] = $customer_id;
            $inputs['date_of_issue'] = $info['emission_date'];
            $type = $info['document_type_id'];
            // Check if it's a Sale Note (type 80) and use appropriate controller
            if ($info['document_type_id'] === '80') {
                $formatted = $this->formatDebtSaleNote($inputs, $info['document_type_id']);
                $response = (new \App\Http\Controllers\Tenant\SaleNoteController)->store(new SaleNoteRequest($formatted));
            } else {
                // Override document type
                $formatted = $this->formatDebtDocument($inputs, $info['document_type_id']);
                $document_inputs = DocumentInput::set($formatted);
                $response = (new DocumentController)->store(new DocumentRequest($document_inputs));
            }

            return [
                'success' => true,
                'type' => $type,
                'message' => 'Documento generado correctamente',
                'document_id' => $response['data']['id']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create SupplyPlanDocument record for debt payment
     */
    private function createDebtPlanDocument($type, $documentResponse, $observations, $totalAmount, $registerId)
    {
        $planDocumentData = [
            'supply_plan_registered_id' => $registerId,
            'year' => now()->year,
            'month' => now()->month,
            'amount' => $totalAmount,
            'status' => 'paid',
            'generation_date' => now(),
            'due_date' => now()->addMonth(),
            'user_id' => auth()->id(),
            'observations' => $observations ?: 'Documento generado por pago de deudas',
            'is_debt_payment' => true,
        ];

        // Check if it's a SaleNote (type 80) or regular Document
        if ($type == '80') {
            // For SaleNote
            $saleNote = SaleNote::find($documentResponse['document_id']);
            if ($saleNote) {
                $planDocumentData['sale_note_id'] = $saleNote->id;
                $planDocumentData['document_series'] = $saleNote->series;
                $planDocumentData['document_number'] = $saleNote->number;
            }
        } else {
            // For regular Document
            $document = Document::find($documentResponse['document_id']);
            if ($document) {
                $planDocumentData['document_id'] = $document->id;
                $planDocumentData['document_series'] = $document->series;
                $planDocumentData['document_number'] = $document->number;
            }
        }

        return SupplyPlanDocument::create($planDocumentData);
    }

    /**
     * Format document for debt payment with custom document type
     */
    private function formatDebtDocument($inputs, $documentTypeId)
    {
        $company = Company::first();
        $customer = Person::where('id', $inputs['customer_id'])->first();
        $establishment = Establishment::first();
        $series = Series::where('document_type_id', $documentTypeId)
            ->where('establishment_id', $establishment->id)
            ->first();

        if (!$series) {
            // Fallback to default series if specific type not found
            $series = Series::where('establishment_id', $establishment->id)->first();
        }

        $inputs_transform = [
            'user_id' => auth()->user()->id,
            'external_id' => Str::uuid()->toString(),
            'soap_type_id' => $company->soap_type_id,
            'establishment_id' => $establishment->id,
            'document_type_id' => $documentTypeId,
            'establishment' => json_decode(Establishment::where('id', $establishment->id)->first(), true),
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
            'additional_information' => null,
            'additional_data' => null,
            'payments' => [
                [
                    'id' => null,
                    'date_of_payment' => $inputs['date_of_issue'],
                    'payment_method_type_id' => '01', // Efectivo
                    'payment_destination_id' => 'cash',
                    'reference' => null,
                    'payment' => Functions::valueKeyInArray($inputs, 'total'),
                ]
            ],
            'payment_condition_id' => '01',
            'total_detraction' => 0.00,
        ];

        return $inputs_transform;
    }

    /**
     * Format document for debt payment as SaleNote (type 80)
     */
    private function formatDebtSaleNote($inputs, $documentTypeId)
    {
        $customer = Person::where('id', $inputs['customer_id'])->first();
        $establishment = Establishment::first();

        // Format items for SaleNote
        $saleNoteItems = $inputs['items'];

        $series = Series::where('document_type_id', $documentTypeId)->where('establishment_id', $establishment->id)->first();
        $inputs_transform = [
            'prefix' => 'NV',
            'series' => $series->number,
            'user_id' => auth()->user()->id,
            'external_id' => Str::uuid()->toString(),
            'customer_id' => $customer->id,
            'customer' => [
                'id' => $customer->id,
                'identity_document_type_id' => $customer->identity_document_type_id,
                'number' => $customer->number,
                'name' => $customer->name,
                'address' => $customer->address,
                'email' => $customer->email,
                'telephone' => $customer->telephone,
            ],
            'establishment_id' => $establishment->id,
            'date_of_issue' => Functions::valueKeyInArray($inputs, 'date_of_issue'),
            'time_of_issue' => Functions::valueKeyInArray($inputs, 'time_of_issue'),
            'currency_type_id' => Functions::valueKeyInArray($inputs, 'currency_type_id'),
            'exchange_rate_sale' => Functions::valueKeyInArray($inputs, 'exchange_rate_sale', 1),
            'total_prepayment' => 0.00,
            'total_discount' => 0.00,
            'total_charge' => 0.00,
            'total_exportation' => 0.00,
            'total_free' => 0.00,
            'total_taxed' => Functions::valueKeyInArray($inputs, 'total_taxed'),
            'total_unaffected' => Functions::valueKeyInArray($inputs, 'total_unaffected'),
            'total_exonerated' => Functions::valueKeyInArray($inputs, 'total_exonerated'),
            'total_igv' => Functions::valueKeyInArray($inputs, 'total_igv'),
            'total_igv_free' => Functions::valueKeyInArray($inputs, 'total_igv_free'),
            'total_taxes' => Functions::valueKeyInArray($inputs, 'total_taxes'),
            'total_value' => Functions::valueKeyInArray($inputs, 'total_value'),
            'subtotal' => (Functions::valueKeyInArray($inputs, 'total_value')) ? $inputs['total_value'] : $inputs['total'],
            'total' => Functions::valueKeyInArray($inputs, 'total'),
            'items' => $saleNoteItems,
            'payments' => [
                [
                    'id' => null,
                    'date_of_payment' => $inputs['date_of_issue'],
                    'payment_method_type_id' => '01', // Efectivo
                    'payment_destination_id' => null,
                    'reference' => null,
                    'payment' => Functions::valueKeyInArray($inputs, 'total'),
                ]
            ],
        ];

        return $inputs_transform;
    }

    /**
     * Reverse debt payments when document is cancelled
     * This method will be called by Document/SaleNote observers when state_type_id = 11
     */
    public function reverseDebtPayments($documentId, $documentType = 'document')
    {
        try {
            DB::beginTransaction();

            // Find the SupplyPlanDocument based on document type
            $planDocument = null;
            if ($documentType === 'sale_note') {
                $planDocument = SupplyPlanDocument::where('sale_note_id', $documentId)
                    ->where('is_debt_payment', true)
                    ->first();
            } else {
                $planDocument = SupplyPlanDocument::where('document_id', $documentId)
                    ->where('is_debt_payment', true)
                    ->first();
            }

            if (!$planDocument) {
                // Not a debt payment document, nothing to reverse
                DB::rollBack();
                return [
                    'success' => true,
                    'message' => 'Documento no relacionado con pagos de deuda'
                ];
            }

            // Check if already cancelled
            if ($planDocument->is_cancelled) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Los pagos de deuda ya fueron revertidos para este documento'
                ];
            }

            // Get all debt document relationships
            $debtDocuments = SupplyDebtDocument::where('supply_plan_document_id', $planDocument->id)
                ->where('is_cancelled', false)
                ->with('debt')
                ->get();

            if ($debtDocuments->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No se encontraron relaciones de deuda para revertir'
                ];
            }

            // Reverse each debt payment
            foreach ($debtDocuments as $debtDocument) {
                $debt = $debtDocument->debt;
                $amountToReverse = $debtDocument->amount_paid;

                // Update debt amounts
                $newAmount = $debt->amount + $amountToReverse;
                $newPaidAmount = ($debt->paid_amount ?? 0) - $amountToReverse;
                $newCancelledAmount = ($debt->cancelled_amount ?? 0) + $amountToReverse;
                $newPaymentCount = max(0, ($debt->payment_count ?? 1) - 1);

                // Determine new active status
                $newActiveStatus = false; // Back to unpaid

                $debt->update([
                    'amount' => round($newAmount, 2),
                    'paid_amount' => round(max(0, $newPaidAmount), 2),
                    'cancelled_amount' => round($newCancelledAmount, 2),
                    'payment_count' => $newPaymentCount,
                    'active' => $newActiveStatus,
                ]);

                // Mark debt document relationship as cancelled
                $debtDocument->update([
                    'is_cancelled' => true,
                    'cancelled_at' => now(),
                ]);
            }

            // Mark plan document as cancelled
            $planDocument->update([
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => 'Documento anulado - reversión automática de pagos',
                'original_status' => $planDocument->status,
                'status' => 'cancelled',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pagos de deuda revertidos exitosamente',
                'data' => [
                    'plan_document_id' => $planDocument->id,
                    'reversed_debts' => $debtDocuments->count(),
                    'total_amount_reversed' => $debtDocuments->sum('amount_paid'),
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al revertir pagos de deuda: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Manual cancellation endpoint for debt documents
     */
    public function cancelDebtDocument(Request $request, $planDocumentId)
    {
        try {
            $request->validate([
                'cancellation_reason' => 'required|string|max:500',
            ]);

            $planDocument = SupplyPlanDocument::where('id', $planDocumentId)
                ->where('is_debt_payment', true)
                ->firstOrFail();

            // Check permissions (you may want to add authorization here)

            // Check if already cancelled
            if ($planDocument->is_cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'El documento ya está anulado'
                ], 400);
            }

            // Get associated document to cancel
            $documentId = null;
            $documentType = 'document';

            if ($planDocument->sale_note_id) {
                $documentId = $planDocument->sale_note_id;
                $documentType = 'sale_note';
                // Cancel the SaleNote
                $saleNote = SaleNote::find($documentId);
                if ($saleNote && $saleNote->state_type_id !== '11') {
                    $saleNote->update(['state_type_id' => '11']);
                }
            } elseif ($planDocument->document_id) {
                $documentId = $planDocument->document_id;
                $documentType = 'document';
                // Cancel the Document
                $document = Document::find($documentId);
                if ($document && $document->state_type_id !== '11') {
                    $document->update(['state_type_id' => '11']);
                }
            }

            // Reverse debt payments
            $reverseResult = $this->reverseDebtPayments($documentId, $documentType);

            if (!$reverseResult['success']) {
                return response()->json($reverseResult, 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Documento anulado y pagos revertidos exitosamente',
                'data' => $reverseResult['data']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al anular documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete voided status for debt payment documents
     * Finds the voided/summary record and sends status query to complete cancellation
     */
    public function completeVoidedStatus($planDocumentId)
    {
        try {
            $planDocument = SupplyPlanDocument::with(['document'])->findOrFail($planDocumentId);

            if (!$planDocument->document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Determinar el tipo basado en group_id
            $type = ($planDocument->document->group_id === '01') ? 'voided' : 'summaries';

            // Buscar el registro de anulación más reciente para este documento
            $voidedRecord = null;
            if ($type === 'voided') {
                $voidedRecord = \App\Models\Tenant\Voided::whereHas('documents', function ($query) use ($planDocument) {
                    $query->where('document_id', $planDocument->document_id);
                })->where('state_type_id', '03')->orderBy('id', 'desc')->first();
            } else {
                $voidedRecord = \App\Models\Tenant\Summary::whereHas('documents', function ($query) use ($planDocument) {
                    $query->where('document_id', $planDocument->document_id);
                })->where('summary_status_type_id', '3')->orderBy('id', 'desc')->first();
            }

            if (!$voidedRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el registro de anulación pendiente'
                ], 404);
            }

            // Llamar al método status del controlador correspondiente
            if ($type === 'voided') {
                $controller = new \App\Http\Controllers\Tenant\VoidedController();
                $result = $controller->status($voidedRecord->id);
            } else {
                $controller = new \App\Http\Controllers\Tenant\SummaryController();
                $result = $controller->status($voidedRecord->id);
            }

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar la anulación: ' . $e->getMessage()
            ], 500);
        }
    }
}
