<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyDebtCollection;
use App\Http\Resources\Tenant\SupplyDebtResource;
use App\Models\Tenant\SupplyDebt;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SupplyDebtExport;

class SupplyDebtController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.debts.index');
    }

    public function excel(Request $request)
    {
        // Aplicar los mismos filtros que en el método records
        $sector_id = $request->sector_id;
        $via_id = $request->via_id;
        $column = $request->column;
        $value = $request->value;

        $records = SupplyDebt::with([
            'supplyTypeDebt',
            'supplyConcept',
            'person',
            'supply.sector',
            'supply.supplyVia.supplyTypeVia'
        ])->orderByRaw('
            CASE
                WHEN month IS NULL THEN 1
                ELSE 0
            END,
            year ASC,
            month ASC
        ');

        // Aplicar filtros de sector y vía
        if($sector_id || $via_id){
            $records->whereHas('supply', function($query) use ($sector_id, $via_id){
                $query->when($sector_id, function($query) use ($sector_id){
                    $query->where('sector_id', $sector_id);
                });
                $query->when($via_id, function($query) use ($via_id){
                    $query->where('supply_via_id', $via_id);
                });
            });
        }

        // Aplicar filtros de columna
        if($column && $value){
            switch($column){
                case 'person.id':
                    $records->where('person_id', $value);
                    break;
                case 'supply.id':
                    $records->where('supply_id', $value);
                    break;
                case 'correlative_receipt':
                    $records->where('correlative_receipt', 'like', "%{$value}%");
                    break;
            }
        }

        // No materializamos la colección para evitar uso excesivo de memoria.
        return Excel::download(
            new SupplyDebtExport($sector_id, $via_id, $column, $value),
            'deudas_suministros_' . Carbon::now()->format('Y_m_d_H_i_s') . '.xlsx'
        );
    }

    public function columns()
    {
        return [
            'person.id' => 'Contribuyente',
            'supply.id'=> 'Suministro',
            'correlative_receipt' => 'N° Recibo',
        ];
    }

    public function records(Request $request)
    {
        $sector_id = $request->sector_id;
        $via_id = $request->via_id;
        $column = $request->column;
        $value = $request->value;
        $records = SupplyDebt::with([
            'supplyTypeDebt',
            'supplyConcept',
            'person',
            'supply'
        ])->orderByRaw('
            CASE 
                WHEN month IS NULL THEN 1 
                ELSE 0 
            END,
            year ASC,
            month ASC
        ');
        if($sector_id || $via_id){
            $records->whereHas('supply', function($query) use ($sector_id, $via_id){
                $query->when($sector_id, function($query) use ($sector_id){
                    $query->where('sector_id', $sector_id);
                });
                $query->when($via_id, function($query) use ($via_id){
                    $query->where('supply_via_id', $via_id);
                });
                
            });
        }
        if($column && $value){
            switch($column){
                case 'person.id':
                    $records->where('person_id', $value);
                    break;
                case 'supply.id':
                    $records->where('supply_id', $value);
                    break;
                case 'correlative_receipt':
                    $records->where('correlative_receipt', 'like', "%{$value}%");
                    break;
            }
        }

        return new SupplyDebtCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supply_contract_id' => 'nullable|integer',
            'person_id' => 'nullable|integer',
            'supply_id' => 'nullable|integer',
            'serie_receipt' => 'nullable|string',
            'correlative_receipt' => 'nullable|integer',
            'amount' => 'required|numeric|min:0',
            'year' => 'nullable|string|max:4',
            'month' => 'nullable|string|max:20',
            'generation_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'active' => 'required|boolean',
            'type' => 'nullable|string',
            'supply_type_debt_id' => 'nullable|integer',
            'supply_concept_id' => 'nullable|integer'
        ]);

        $debt = SupplyDebt::create($request->all());

        return response()->json($debt, 201);
    }

    public function show($id)
    {
        $debt = SupplyDebt::findOrFail($id);

        return new SupplyDebtResource($debt);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supply_contract_id' => 'nullable|integer',
            'person_id' => 'nullable|integer',
            'supply_id' => 'nullable|integer',
            'serie_receipt' => 'nullable|string',
            'correlative_receipt' => 'nullable|integer',
            'amount' => 'required|numeric|min:0',
            'year' => 'nullable|string|max:4',
            'month' => 'nullable|string|max:20',
            'generation_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'active' => 'required|boolean',
            'type' => 'nullable|string',
            'supply_type_debt_id' => 'nullable|integer',
            'supply_concept_id' => 'nullable|integer'
        ]);

        $debt = SupplyDebt::findOrFail($id);
        $debt->update($request->all());

        return response()->json($debt);
    }

    public function destroy($id)
    {
        $debt = SupplyDebt::findOrFail($id);
        $debt->delete();

        return response()->json(['message' => 'Deuda eliminada correctamente']);
    }

    public function getDebtsBySupply($id)
    {
        $debts = SupplyDebt::with([
            'supplyTypeDebt',
            'supplyConcept',
            'person',
            'supply.supplyVia.supplyTypeVia',
            'supply.sector'
        ])
        ->where('supply_id', $id)
        ->where('active', false) // Solo deudas sin pagar
        ->orderByRaw('
            CASE 
                WHEN month IS NULL THEN 1 
                ELSE 0 
            END,
            year ASC,
            month ASC
        ')
        ->get();

        // Transform data similar to SupplyDebtCollection
        $meses = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        $transformedDebts = $debts->map(function($debt) use ($meses) {
            // Generate receipt number
            if (empty($debt->serie_receipt) && empty($debt->correlative_receipt)) {
                $receipt = '-';
            } else {
                $receipt = $debt->serie_receipt . ' - ' . $debt->correlative_receipt;
            }

            // Generate description
            $description = $this->generateDebtDescription($debt, $meses);

            // Get debt type
            $debtType = null;
            if ($debt->type == 'c' && $debt->supplyConcept) {
                $debtType = 'Concepto';
            } elseif ($debt->supply_type_debt_id == 1 && $debt->type == 'r') {
                $debtType = 'Manual';
            } else {
                $debtType = 'Consumo';
            }

            return [
                'id' => $debt->id,
                'generation_date' => $debt->generation_date ? $debt->generation_date->format('Y-m-d') : null,
                'description' => $description,
                'receipt' => $receipt,
                'debt_type' => $debtType,
                'amount' => floatval($debt->amount),
                'year' => $debt->year,
                'month' => $debt->month,
                'due_date' => $debt->due_date ? $debt->due_date->format('Y-m-d') : null,
                'type' => $debt->type,
                'supply_type_debt_id' => $debt->supply_type_debt_id,
                'supply_concept_id' => $debt->supply_concept_id,
            ];
        });

        return response()->json([
            'data' => $transformedDebts
        ]);
    }

    private function generateDebtDescription($debt, $meses)
    {
        // Si no hay mes y es tipo de deuda 1 y tipo 'r'
        if (empty($debt->month) && $debt->supply_type_debt_id == 1 && $debt->type == 'r') {
            return "Deuda generada manualmente";
        }
        
        // Si es tipo 'c' (concepto)
        if ($debt->type == 'c' && $debt->supplyConcept) {
            return $debt->supplyConcept->name;
        }
        
        // Para otros casos, generar descripción basada en mes y año
        if (!empty($debt->month)) {
            $mesNumero = (int)$debt->month;
            if ($mesNumero > 0 && $mesNumero <= 12) {
                $nombreMes = $meses[$mesNumero - 1];
                $anio = $debt->year ? ' - ' . $debt->year : '';
                return "Deuda del mes " . $nombreMes . $anio;
            }
        }
        
        return "Mes no válido";
    }

    /**
     * Generate and display the receipt PDF for a specific debt
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function printReceipt($id)
    {
        try {
            // Obtener la deuda con sus relaciones
            $debt = SupplyDebt::with([
                'supply.person',
                'supply.sector', 
                'supply.supplyVia',
                'supplyConcept',
                'supplyTypeDebt'
            ])->findOrFail($id);

            // Datos de la empresa y establecimiento
            $company = Company::first();
            $user = auth()->user();
            $establishment = Establishment::where('id', $user->establishment_id)->first();

            if (!$establishment) {
                $establishment = Establishment::first();
            }

            // Generar descripción de la deuda
            $description = $this->generateDebtDescription($debt, [
                "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
            ]);

            // Obtener nombre del mes si existe
            $monthName = '';
            if ($debt->month) {
                $meses = [
                    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
                ];
                $monthIndex = (int)$debt->month - 1;
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $monthName = $meses[$monthIndex];
                }
            }

            // Determinar tipo de deuda
            $debtType = $this->getDebtTypeName($debt);

            // Preparar datos para la vista
            $data = [
                'debt' => $debt,
                'supply' => $debt->supply,
                'person' => $debt->supply->person,
                'sector' => $debt->supply->sector,
                'supplyVia' => $debt->supply->supplyVia,
                'supplyConcept' => $debt->supplyConcept,
                'description' => $description,
                'monthName' => $monthName,
                'debtType' => $debtType,
                'date' => Carbon::now()->format('d-m-Y'),
                'time' => Carbon::now()->format('H:i:s')
            ];

            // Generar PDF
            $pdf = PDF::loadView('tenant.supplies.documents.receipt', compact(
                'company',
                'establishment', 
                'data'
            ))->setPaper('A5', 'portrait');

            return $pdf->stream("recibo_deuda_{$debt->serie_receipt}_{$debt->correlative_receipt}.pdf");

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el recibo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get debt type name for display
     *
     * @param SupplyDebt $debt
     * @return string
     */
    private function getDebtTypeName(SupplyDebt $debt): string
    {
        if ($debt->type == 'c' && $debt->supplyConcept) {
            return 'Concepto Específico';
        } elseif ($debt->supply_type_debt_id == 1 && $debt->type == 'r') {
            return 'Deuda Manual';
        } else {
            return 'Consumo Mensual';
        }
    }

    public function storeConsumptionPrevious(Request $request)
    {
        $request->validate([
            'supply_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'year' => 'required|string|max:4',
            'month' => 'required|string|max:20',
        ]);

        try {
            // Verificar si ya existe una deuda para este período
            $existingDebt = SupplyDebt::where('supply_id', $request->supply_id)
                ->where('year', $request->year)
                ->whereRaw('CAST(month AS UNSIGNED) = ?', [$request->month])  
                ->where('type', 'r')
                ->first();

            if ($existingDebt) {
                return response()->json([
                    'message' => 'Ya existe una deuda registrada para este predio en el período especificado'
                ], 422);
            }

            // Obtener información del suministro
            $supply = \App\Models\Tenant\Supply::with('person')->findOrFail($request->supply_id);

            // Crear la deuda
            $debt = SupplyDebt::create([
                'supply_contract_id' => null,
                'person_id' => $supply->person_id,
                'supply_id' => $request->supply_id,
                'serie_receipt' => null, // Sin número de recibo
                'correlative_receipt' => null, // Sin número de recibo
                'amount' => $request->amount,
                'original_amount' => $request->amount,
                'year' => $request->year,
                'month' => $request->month,
                'generation_date' => now(),
                'due_date' => null, // Sin fecha de vencimiento específica
                'active' => false, // false = pendiente de pago
                'type' => 'r', // r = regular/mensual
                'supply_type_debt_id' => 1, // Tipo de deuda estándar
                'supply_concept_id' => null,
            ]);

            return response()->json([
                'message' => 'Deuda de consumo anterior registrada correctamente',
                'data' => $debt
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al registrar la deuda: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function storeAccumulated(Request $request)
    {
        $request->validate([
            'supply_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            // Obtener información del suministro
            $supply = \App\Models\Tenant\Supply::with('person')->findOrFail($request->supply_id);
            $currentYear = now()->year;

            // Crear la deuda acumulada
            $debt = SupplyDebt::create([
                'supply_contract_id' => null,
                'person_id' => $supply->person_id,
                'supply_id' => $request->supply_id,
                'serie_receipt' => null, // Sin número de recibo
                'correlative_receipt' => null, // Sin número de recibo
                'amount' => $request->amount,
                'original_amount' => $request->amount,
                'year' => $currentYear,
                'month' => null, // Sin mes específico para deudas acumuladas
                'generation_date' => now(),
                'due_date' => null, // Sin fecha de vencimiento específica
                'active' => false, // false = pendiente de pago
                'type' => 'r', // r = acumulada
                'supply_type_debt_id' => 1, // Tipo de deuda estándar
                'supply_concept_id' => null,
            ]);

            return response()->json([
                'message' => 'Deuda acumulada registrada correctamente',
                'data' => $debt
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al registrar la deuda acumulada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeColateral(Request $request)
    {
        $request->validate([
            'supply_id' => 'required|integer',
            'services' => 'required|array|min:1',
            'services.*.supply_concept_id' => 'required|integer',
            'services.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            // Obtener información del suministro
            $supply = \App\Models\Tenant\Supply::with('person')->findOrFail($request->supply_id);
            $currentYear = now()->year;
            $currentMonth = now()->month;

            $createdDebts = [];

            // Crear una deuda por cada servicio
            foreach ($request->services as $service) {
                $debt = SupplyDebt::create([
                    'supply_contract_id' => null,
                    'person_id' => $supply->person_id,
                    'supply_id' => $request->supply_id,
                    'serie_receipt' => null, // Sin número de recibo
                    'correlative_receipt' => null, // Sin número de recibo
                    'amount' => $service['amount'],
                    'original_amount' => $service['amount'],
                    'year' => $currentYear,
                    'month' => $currentMonth,
                    'generation_date' => now(),
                    'due_date' => null, // Sin fecha de vencimiento específica
                    'active' => false, // false = pendiente de pago
                    'type' => 'c', // c = colateral
                    'supply_type_debt_id' => 1, // Tipo de deuda estándar
                    'supply_concept_id' => $service['supply_concept_id'],
                ]);

                $createdDebts[] = $debt;
            }

            return response()->json([
                'message' => 'Deudas colaterales registradas correctamente',
                'data' => $createdDebts,
                'count' => count($createdDebts)
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al registrar las deudas colaterales: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'supply_id' => 'required|integer',
            'year' => 'required|string|max:4',
            'month' => 'required|string|max:20',
            'exclude_id' => 'nullable|integer',
        ]);

        $query = SupplyDebt::where('supply_id', $request->supply_id)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->where('type', 'r');

        if ($request->exclude_id) {
            $query->where('id', '!=', $request->exclude_id);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }
}