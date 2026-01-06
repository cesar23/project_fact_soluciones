<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyAdvancePaymentCollection;
use App\Http\Resources\Tenant\SupplyAdvancePaymentResource;
use App\Models\Tenant\SupplyAdvancePayment;
use App\Models\Tenant\Supply;
use App\Models\Tenant\SupplyDebt;
use App\Models\Tenant\SupplyDebtDocument;
use App\Models\Tenant\SupplyPlanRegistered;
use App\Models\Tenant\SupplyPlanDocument;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class SupplyAdvancePaymentController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.advance_payments.index');
    }

    public function columns()
    {
        return [
            'supply.id' => 'Suministro',
            'amount' => 'Monto',
            'payment_date' => 'Fecha de Pago',
            'year' => 'Año',
            'month' => 'Mes',
        ];
    }

    public function records(Request $request)
    {
        $supply_id = $request->supply_id;
        $year = $request->year;
        $month = $request->month;
        $column = $request->column;
        $value = $request->value;

        $records = SupplyAdvancePayment::with([
            'supply.person',
            'supply.sector',
            'supply.supplyVia'
        ])->orderBy('payment_date', 'desc');

        if ($supply_id) {
            $records->where('supply_id', $supply_id);
        }

        if ($year) {
            $records->where('year', $year);
        }

        if ($month) {
            $records->where('month', $month);
        }

        if ($column && $value) {
            switch ($column) {
                case 'supply.id':
                    $records->where('supply_id', $value);
                    break;
                case 'amount':
                    $records->where('amount', $value);
                    break;
                case 'payment_date':
                    $records->where('payment_date', $value);
                    break;
                case 'year':
                    $records->where('year', $value);
                    break;
                case 'month':
                    $records->where('month', 'like', "%{$value}%");
                    break;
            }
        }

        return new SupplyAdvancePaymentCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supply_id' => 'required|integer|exists:tenant.supplies,id',
            'periods' => 'required|array|min:1',
            'periods.*.year' => 'required|integer|min:1900|max:2100',
            'periods.*.month' => 'required|integer|min:1|max:12',
            'periods.*.amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'document_type_id' => 'required|string|in:03,80',
            'observations' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calcular monto total
            $totalAmount = collect($request->periods)->sum('amount');

            // Obtener información del suministro
            $supply = Supply::with(['person', 'sector', 'supplyVia.supplyTypeVia'])->findOrFail($request->supply_id);

            // Validar que no existan deudas para los períodos seleccionados
            foreach ($request->periods as $period) {
                $existingDebt = SupplyDebt::where('supply_id', $request->supply_id)
                    ->where('year', $period['year'])
                    ->where('month', $period['month'])
                    ->where('type', 'r')
                    ->exists();

                if ($existingDebt) {
                    throw new Exception("Ya existe una deuda para el período {$period['month']}/{$period['year']}");
                }
            }

            // Obtener o crear plan activo para el suministro
            $activePlanRegistered = SupplyPlanRegistered::where('supply_id', $request->supply_id)
                ->where('active', true)
                ->with('supplyPlan')
                ->first();

            if (!$activePlanRegistered) {
                throw new Exception('No se encontró un plan activo para este suministro');
            }

            // Preparar items para la generación del documento
            $items = [];
            foreach ($request->periods as $period) {
                $monthNames = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];

                $monthName = $monthNames[$period['month']] ?? 'Mes';
                $description = "Pago consumo {$monthName} - {$period['year']}";

                $items[] = [
                    'debt_id' => null, // No hay debt_id aún
                    'description' => $description,
                    'amount' => $period['amount'],
                    'affectation_igv_type_id' => '20', // Exonerado por defecto para servicios
                ];
            }

            // Generar el documento
            $documentInfo = [
                'customer_id' => $supply->person_id,
                'document_type_id' => $request->document_type_id,
                'emission_date' => $request->payment_date,
                'observations' => $request->observations ?? 'Pago adelantado generado automáticamente',
                'items' => $items,
                'total_amount' => $totalAmount,
            ];

            $documentResponse = $this->generateDebtPaymentDocument($documentInfo);

            if (!$documentResponse['success']) {
                throw new Exception($documentResponse['message'] ?? 'Error al generar el documento');
            }

            // Crear SupplyPlanDocument
            $planDocument = $this->createDebtPlanDocument(
                $request->document_type_id,
                $documentResponse,
                $request->observations ?? 'Pago adelantado generado automáticamente',
                $totalAmount,
                $activePlanRegistered->id
            );

            $documentId = ($request->document_type_id === '80') ? null : $documentResponse['document_id'];
            $saleNoteId = ($request->document_type_id === '80') ? $documentResponse['document_id'] : null;

            // Crear deudas para cada período y marcarlas como pagadas
            $createdDebts = [];
            foreach ($request->periods as $period) {
                // Crear deuda
                $debt = SupplyDebt::create([
                    'supply_contract_id' => null,
                    'person_id' => $supply->person_id,
                    'supply_id' => $request->supply_id,
                    'serie_receipt' => null,
                    'correlative_receipt' => null,
                    'amount' => 0, // Pagada inmediatamente
                    'original_amount' => $period['amount'],
                    'paid_amount' => $period['amount'],
                    'year' => $period['year'],
                    'month' => $period['month'],
                    'generation_date' => $request->payment_date,
                    'due_date' => null,
                    'active' => true, // Pagada
                    'type' => 'r',
                    'supply_type_debt_id' => 1,
                    'supply_concept_id' => null,
                    'last_payment_date' => now(),
                    'payment_count' => 1,
                ]);

                $createdDebts[] = $debt;

                // Si es un documento (no nota de venta), crear relación deuda-documento
                if ($documentId) {
                    SupplyDebtDocument::create([
                        'debt_id' => $debt->id,
                        'supply_plan_document_id' => $documentId,
                        'amount_paid' => $period['amount'],
                        'debt_amount_before' => $period['amount'],
                        'debt_amount_after' => 0,
                        'payment_type' => 'full',
                        'is_cancelled' => false,
                    ]);
                }
            }

            // Crear el registro de pago adelantado único
            $advancePayment = SupplyAdvancePayment::create([
                'supply_id' => $request->supply_id,
                'periods' => $request->periods,
                'total_amount' => $totalAmount,
                'payment_date' => $request->payment_date,
                'document_type_id' => $request->document_type_id,
                'document_id' => $documentId,
                'sale_note_id' => $saleNoteId,
                'active' => true,
                'is_used' => true, // Ya se usó para generar deudas y documento
                'used_in_document_id' => $documentId ?? $saleNoteId,
                'used_at' => now(),
                // Campos legacy (para compatibilidad)
                'amount' => $totalAmount,
                'year' => $request->periods[0]['year'], // Primer período
                'month' => $request->periods[0]['month'], // Primer período
            ]);

            DB::commit();

            $monthNames = [
                '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Pago adelantado procesado correctamente. Se generaron las deudas y el documento automáticamente.',
                'data' => [
                    'advance_payment_id' => $advancePayment->id,
                    'document_type' => $request->document_type_id === '03' ? 'BOLETA ELECTRÓNICA' : 'NOTA DE VENTA',
                    'document_id' => $documentId ?? $saleNoteId,
                    'total_amount' => $totalAmount,
                    'debts_created' => count($createdDebts),
                    'periods_processed' => collect($request->periods)->map(function($period) use ($monthNames) {
                        return $monthNames[$period['month']] . ' ' . $period['year'];
                    })->toArray()
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago adelantado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $advancePayment = SupplyAdvancePayment::with([
                'supply.person',
                'supply.sector',
                'supply.supplyVia'
            ])->findOrFail($id);

            return new SupplyAdvancePaymentResource($advancePayment);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Pago adelantado no encontrado'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supply_id' => 'required|integer|exists:tenant.supplies,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'year' => 'required|integer|min:1900|max:2100',
            'month' => 'required|string|max:20',
            'active' => 'boolean',
            'document_type_id' => 'required|string|in:03,80'
        ]);

        try {
            $advancePayment = SupplyAdvancePayment::findOrFail($id);
            $advancePayment->update($request->all());

            return response()->json([
                'message' => 'Pago adelantado actualizado correctamente',
                'data' => $advancePayment
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el pago adelantado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $advancePayment = SupplyAdvancePayment::findOrFail($id);
            $advancePayment->delete();

            return response()->json([
                'message' => 'Pago adelantado eliminado correctamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el pago adelantado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBySupply($supplyId)
    {
        try {
            $advancePayments = SupplyAdvancePayment::where('supply_id', $supplyId)
                ->where('active', true)
                ->orderBy('payment_date', 'desc')
                ->get();

            $totalAdvances = $advancePayments->sum('amount');

            return response()->json([
                'data' => [
                    'payments' => $advancePayments,
                    'total_advances' => $totalAdvances,
                    'count' => $advancePayments->count()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los pagos adelantados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByPeriod(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|string',
            'supply_id' => 'nullable|integer'
        ]);

        try {
            $query = SupplyAdvancePayment::with(['supply.person'])
                ->where('year', $request->year)
                ->where('month', $request->month)
                ->where('active', true);

            if ($request->supply_id) {
                $query->where('supply_id', $request->supply_id);
            }

            $advancePayments = $query->orderBy('payment_date', 'desc')->get();
            $totalAdvances = $advancePayments->sum('amount');

            return response()->json([
                'data' => [
                    'payments' => $advancePayments,
                    'total_advances' => $totalAdvances,
                    'count' => $advancePayments->count(),
                    'period' => [
                        'year' => $request->year,
                        'month' => $request->month
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los pagos del período: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deactivate($id)
    {
        try {
            $advancePayment = SupplyAdvancePayment::findOrFail($id);
            
            $advancePayment->update(['active' => false]);

            return response()->json([
                'message' => 'Pago adelantado desactivado correctamente',
                'data' => $advancePayment
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al desactivar el pago adelantado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate($id)
    {
        try {
            $advancePayment = SupplyAdvancePayment::findOrFail($id);
            
            $advancePayment->update(['active' => true]);

            return response()->json([
                'message' => 'Pago adelantado activado correctamente',
                'data' => $advancePayment
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al activar el pago adelantado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchSupplies(Request $request)
    {
        $input = $request->input('input');
        
        if (empty($input) || strlen($input) < 2) {
            return response()->json(['data' => []]);
        }

        $supplies = Supply::with([
            'person',
            'sector',
            'supplyVia'
        ])
        ->whereHas('suppliesPlansRegistered', function($query) {
            $query->where('active', true);
        })
        ->with(['suppliesPlansRegistered' => function($query) {
            $query->where('active', true)->with('supplyPlan');
        }])
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
            $activePlan = $supply->suppliesPlansRegistered->where('active', true)->first();
            $tariff = $activePlan ? $activePlan->supplyPlan->total : 0;

            // Obtener la última deuda con período (month no null)
            $lastDebt = SupplyDebt::where('supply_id', $supply->id)
                ->whereNotNull('month')
                ->where('type', 'r')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();

            // Obtener todas las deudas existentes con período para validación
            $existingDebts = SupplyDebt::where('supply_id', $supply->id)
                ->whereNotNull('month')
                ->where('type', 'r')
                ->select('year', 'month')
                ->get()
                ->map(function($debt) {
                    return [
                        'year' => (int)$debt->year,
                        'month' => (int)$debt->month
                    ];
                })
                ->toArray();
            
            return [
                'id' => $supply->id,
                'description' => ($supply->person ? $supply->person->name . ' - ' : '') . 
                               ($supply->cod_route ? $supply->cod_route : '') .
                               ($supply->old_code ? ' (' . $supply->old_code . ')' : ''),
                'person_name' => $supply->person ? $supply->person->name : '',
                'person_number' => $supply->person ? $supply->person->number : '',
                'supply_code' => ($supply->old_code ? $supply->old_code : '') . 
                               ($supply->cod_route ? ' - ' . $supply->cod_route : ''),
                'address' => $supply->optional_address,
                'sector_name' => $supply->sector ? $supply->sector->name : '',
                'via_name' => $supply->supplyVia ? $supply->supplyVia->name : '',
                'monthly_tariff' => floatval($tariff),
                'tariff_description' => $activePlan ? $activePlan->supplyPlan->description : 'Sin tarifa asignada',
                'last_debt' => $lastDebt ? [
                    'year' => (int)$lastDebt->year,
                    'month' => (int)$lastDebt->month,
                    'amount' => (float)$lastDebt->amount,
                    'active' => $lastDebt->active
                ] : null,
                'existing_debts' => $existingDebts
            ];
        });

        return response()->json(['data' => $supplies]);
    }

    /**
     * Generate document using adapted logic from SupplyPlanRegisteredController
     */
    private function generateDebtPaymentDocument($info)
    {
        try {
            $igv = (new \Modules\Store\Http\Controllers\StoreController)->getIgv(new Request());
            $items = [];
            $customer_id = $info['customer_id'];

            $baseItem = \App\Models\Tenant\Item::checkIfExistBaseItem();
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

            // Check if it's a Sale Note (type 80) and use appropriate controller
            if ($info['document_type_id'] === '80') {
                $formatted = $this->formatDebtSaleNote($inputs, $info['document_type_id']);
                $response = (new \App\Http\Controllers\Tenant\SaleNoteController)->store(new \App\Http\Requests\Tenant\SaleNoteRequest($formatted));
            } else {
                $formatted = $this->formatDebtDocument($inputs, $info['document_type_id']);
                $document_inputs = \App\CoreFacturalo\Requests\Inputs\DocumentInput::set($formatted);
                $response = (new \App\Http\Controllers\Tenant\DocumentController)->store(new \App\Http\Requests\Tenant\DocumentRequest($document_inputs));
            }

            return [
                'success' => true,
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
     * Calculate totals for document generation
     */
    private function calculateTotal($items)
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
        }

        return [
            'date_of_issue' => now()->format('Y-m-d'),
            'time_of_issue' => now()->format('H:i:s'),
            'currency_type_id' => 'PEN',
            'exchange_rate_sale' => 1,
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

    /**
     * Create SupplyPlanDocument record for advance payment
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
            'observations' => $observations ?: 'Pago adelantado procesado automáticamente',
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
            $document = \App\Models\Tenant\Document::find($documentResponse['document_id']);
            if ($document) {
                $planDocumentData['document_id'] = $document->id;
                $planDocumentData['document_series'] = $document->series;
                $planDocumentData['document_number'] = $document->number;
            }
        }

        return SupplyPlanDocument::create($planDocumentData);
    }

    /**
     * Format document for advance payment
     */
    private function formatDebtDocument($inputs, $documentTypeId)
    {
        $company = \App\Models\Tenant\Company::first();
        $customer = \App\Models\Tenant\Person::where('id', $inputs['customer_id'])->first();
        $establishment = \App\Models\Tenant\Establishment::first();
        $series = \App\Models\Tenant\Series::where('document_type_id', $documentTypeId)
            ->where('establishment_id', $establishment->id)
            ->first();

        if (!$series) {
            $series = \App\Models\Tenant\Series::where('establishment_id', $establishment->id)->first();
        }

        $inputs_transform = [
            'user_id' => auth()->user()->id,
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
            'soap_type_id' => $company->soap_type_id,
            'establishment_id' => $establishment->id,
            'document_type_id' => $documentTypeId,
            'establishment' => json_decode(\App\Models\Tenant\Establishment::where('id', $establishment->id)->first(), true),
            'customer_id' => $customer->id,
            'customer' => \App\CoreFacturalo\Requests\Api\Transform\Common\PersonTransform::transform_customer($customer),
            'state_type_id' => "01",
            'number' => "#",
            'operation_type_id' => "0101",
            'series' => $series->number,
            'date_of_issue' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'date_of_issue'),
            'date_of_due' => now()->addMonth()->format('Y-m-d'),
            'time_of_issue' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'time_of_issue'),
            'currency_type_id' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'currency_type_id'),
            'exchange_rate_sale' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'exchange_rate_sale', 1),
            'purchase_order' => null,
            'total_prepayment' => 0.00,
            'total_discount' => 0.00,
            'total_charge' => 0.00,
            'total_exportation' => 0.00,
            'total_free' => 0.00,
            'total_taxed' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_taxed'),
            'total_unaffected' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_unaffected'),
            'total_exonerated' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_exonerated'),
            'total_igv' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_igv'),
            'total_igv_free' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_igv_free'),
            'total_base_isc' => 0.00,
            'total_isc' => 0.00,
            'total_base_other_taxes' => 0.00,
            'total_other_taxes' => 0.00,
            'total_plastic_bag_taxes' => 0.00,
            'total_taxes' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_taxes'),
            'total_value' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_value'),
            'subtotal' => (\App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_value')) ? $inputs['total_value'] : $inputs['total'],
            'total' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total'),
            'total_pending_payment' => 0.00,
            'has_prepayment' => 0,
            'items' => $inputs['items'],
            'additional_information' => null,
            'additional_data' => null,
            'payments' => [],
            'payment_condition_id' => '01',
            'total_detraction' => 0.00,
        ];

        return $inputs_transform;
    }

    /**
     * Format document for advance payment as SaleNote (type 80)
     */
    private function formatDebtSaleNote($inputs, $documentTypeId)
    {
        $customer = \App\Models\Tenant\Person::where('id', $inputs['customer_id'])->first();
        $establishment = \App\Models\Tenant\Establishment::first();
        $series = \App\Models\Tenant\Series::where('document_type_id', $documentTypeId)->where('establishment_id', $establishment->id)->first();

        $inputs_transform = [
            'prefix' => 'NV',
            'series' => $series->number,
            'user_id' => auth()->user()->id,
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
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
            'date_of_issue' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'date_of_issue'),
            'time_of_issue' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'time_of_issue'),
            'currency_type_id' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'currency_type_id'),
            'exchange_rate_sale' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'exchange_rate_sale', 1),
            'total_prepayment' => 0.00,
            'total_discount' => 0.00,
            'total_charge' => 0.00,
            'total_exportation' => 0.00,
            'total_free' => 0.00,
            'total_taxed' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_taxed'),
            'total_unaffected' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_unaffected'),
            'total_exonerated' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_exonerated'),
            'total_igv' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_igv'),
            'total_igv_free' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_igv_free'),
            'total_taxes' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_taxes'),
            'total_value' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_value'),
            'subtotal' => (\App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total_value')) ? $inputs['total_value'] : $inputs['total'],
            'total' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total'),
            'items' => $inputs['items'],
            'payments' => [
                [
                    'id' => null,
                    'date_of_payment' => $inputs['date_of_issue'],
                    'payment_method_type_id' => '01', // Efectivo
                    'payment_destination_id' => null,
                    'reference' => null,
                    'payment' => \App\CoreFacturalo\Requests\Api\Transform\Functions::valueKeyInArray($inputs, 'total'),
                ]
            ],
        ];

        return $inputs_transform;
    }

}