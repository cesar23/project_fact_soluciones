<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyReceiptCollection;
use App\Models\Tenant\SupplyDebt;
use App\Models\Tenant\Sector;
use App\Models\Tenant\SupplyVia;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use App\Http\Controllers\PdfUnionController;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class SupplyReceiptController extends Controller
{
    private const CACHE_DEBT_PDF_KEY = 'supply_debt_pdf_';
    
    // Configuración de optimización
    private const BATCH_SIZE = 50; // Procesar en lotes de 50
    private const CACHE_TTL = 3600; // 1 hora de caché
    private const PDF_CACHE_TTL = 1800; // 30 minutos para PDFs individuales
    public function index()
    {
        return view('tenant.supplies.receipts.index');
    }

    public function columns()
    {
        return [
            'supply.cod_route' => 'Código Predio',
            'person.name' => 'Cliente',
            'supply.sector.name' => 'Sector',
            'supply.supplyVia.name' => 'Vía',
            'amount' => 'Monto',
            'year' => 'Año',
            'month' => 'Mes',
            'generation_date' => 'Fecha Generación',
            'due_date' => 'Fecha Vencimiento',
            'active' => 'Estado'
        ];
    }

    public function records(Request $request)
    {
        $sector_ids = $request->sector_ids;
        $via_id = $request->via_id;
        $year = $request->year;
        $month = $request->month;
        $status = $request->status; // 'pending', 'paid', 'all'

        // Filtrar valores especiales como 'all' o 'todos'
        if (is_array($sector_ids)) {
            $sector_ids = array_filter($sector_ids, function($id) {
                return !in_array($id, ['all', 'todos']);
            });
            if (empty($sector_ids)) {
                $sector_ids = null;
            }
        } elseif (in_array($sector_ids, ['all', 'todos'])) {
            $sector_ids = null;
        }

        $records = SupplyDebt::with([
            'supply.sector',
            'supply.supplyVia',
            'supply.person',
            'supplyTypeDebt',
            'supplyConcept'
        ])
        ->when($sector_ids || $via_id, function($query) use ($sector_ids, $via_id) {
            $query->whereHas('supply', function($q) use ($sector_ids, $via_id) {
                $q->when($sector_ids, function($subQ) use ($sector_ids) {
                    $hasComma = $sector_ids && is_string($sector_ids) && strpos($sector_ids, ',') !== false;
                    if ($hasComma) {
                        $sector_ids = explode(',', $sector_ids);
                    }
                    if (is_array($sector_ids)) {
                        $subQ->whereIn('sector_id', $sector_ids);
                    } else {
                        $subQ->where('sector_id', $sector_ids);
                    }
                });
                $q->when($via_id, function($subQ) use ($via_id) {
                    $subQ->where('supply_via_id', $via_id);
                });
            });
        })
        ->when($year, function($query) use ($year) {
            $query->where('year', $year);
        })
        ->when($month, function($query) use ($month) {
            // Normalizar mes a entero para manejar "04" vs "4"
            $monthInt = (int)$month;
            $query->whereRaw('CAST(month AS UNSIGNED) = ?', [$monthInt]);
        })
        ->whereNotNull('serie_receipt');

        if($sector_ids){
            $records->join('supplies', 'supply_debt.supply_id', '=', 'supplies.id')
                   ->when($status === 'pending', function($query) {
                       $query->where('supply_debt.active', false);
                   })
                   ->when($status === 'paid', function($query) {
                       $query->where('supply_debt.active', true);
                   })
                   ->orderBy('supplies.sector_id', 'ASC')
                   ->select('supply_debt.*');
        }else{
            $records->when($status === 'pending', function($query) {
                $query->where('active', false);
            })
            ->when($status === 'paid', function($query) {
                $query->where('active', true);
            })
            ->orderByRaw('
            CASE
                WHEN month IS NULL THEN 1
                ELSE 0
            END,
            CAST(year AS UNSIGNED) ASC,
            CAST(month AS UNSIGNED) ASC,
            supply_id ASC
        ');
        }

        return new SupplyReceiptCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getSectors()
    {
        $sectors = Sector::orderBy('code')->get();
        return response()->json($sectors);
    }

    public function getSupplyVias()
    {
        $vias = SupplyVia::with('sector')->orderBy('name')->get();
        return response()->json($vias);
    }

    public function getSupplyViasBySector($sectorId)
    {
        $vias = SupplyVia::where('sector_id', $sectorId)
            ->with('sector')
            ->orderBy('name')
            ->get();
        return response()->json($vias);
    }

    public function exportToExcel(Request $request)
    {
        $sector_ids = $request->sector_ids;
        $via_id = $request->via_id;
        $year = $request->year;
        $month = $request->month;
        $status = $request->status;

        // Filtrar valores especiales como 'all' o 'todos'
        if (is_array($sector_ids)) {
            $sector_ids = array_filter($sector_ids, function($id) {
                return !in_array($id, ['all', 'todos']);
            });
            if (empty($sector_ids)) {
                $sector_ids = null;
            }
        } elseif (in_array($sector_ids, ['all', 'todos'])) {
            $sector_ids = null;
        }

        $records = SupplyDebt::with([
            'supply.sector',
            'supply.supplyVia',
            'supply.person',
            'supplyTypeDebt',
            'supplyConcept'
        ])
        ->when($sector_ids || $via_id, function($query) use ($sector_ids, $via_id) {
            $query->whereHas('supply', function($q) use ($sector_ids, $via_id) {
                $q->when($sector_ids, function($subQ) use ($sector_ids) {
                    $hasComma = $sector_ids && is_string($sector_ids) && strpos($sector_ids, ',') !== false;
                    if ($hasComma) {
                        $sector_ids = explode(',', $sector_ids);
                    }
                    if (is_array($sector_ids)) {
                        $subQ->whereIn('sector_id', $sector_ids);
                    } else {
                        $subQ->where('sector_id', $sector_ids);
                    }
                });
                $q->when($via_id, function($subQ) use ($via_id) {
                    $subQ->where('supply_via_id', $via_id);
                });
            });
        })
        ->when($year, function($query) use ($year) {
            $query->where('year', $year);
        })
        ->when($month, function($query) use ($month) {
            $query->where('month', $month);
        })
        ->whereNotNull('serie_receipt');

        if($sector_ids){
            $records->join('supplies', 'supply_debt.supply_id', '=', 'supplies.id')
                   ->when($status === 'pending', function($query) {
                       $query->where('supply_debt.active', false);
                   })
                   ->when($status === 'paid', function($query) {
                       $query->where('supply_debt.active', true);
                   })
                   ->orderBy('supplies.sector_id', 'ASC')
                   ->select('supply_debt.*');
        }else{
            $records->when($status === 'pending', function($query) {
                $query->where('active', false);
            })
            ->when($status === 'paid', function($query) {
                $query->where('active', true);
            })
            ->orderByRaw('
            CASE
                WHEN month IS NULL THEN 1
                ELSE 0
            END,
            year ASC,
            month ASC,
            supply_id ASC
        ');
        }

        $records = $records->get();

        $meses = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        $data = $records->map(function($row) use ($meses) {
            // Generar número de recibo
            if (empty($row->serie_receipt) && empty($row->correlative_receipt)) {
                $receipt = '-';
            } else {
                $receipt = $row->serie_receipt . ' - ' . $row->correlative_receipt;
            }

            // Generar descripción del período
            $period = '';
            if ($row->year) {
                $period = $row->year;
                if ($row->month) {
                    $monthName = $meses[$row->month - 1] ?? $row->month;
                    $period = $monthName . ' ' . $row->year;
                }
            }

            // Estado de la deuda
            $status = $row->active ? 'Pagado' : 'Pendiente';

            // Tipo de deuda
            $debtType = '';
            if ($row->type == 'c' && $row->supplyConcept) {
                $debtType = 'Colateral';
            } elseif ($row->type == 'a') {
                $debtType = 'Acumulada';
            } elseif ($row->type == 'r') {
                $debtType = 'Regular';
            } else {
                $debtType = 'Manual';
            }

            return [
                'Código Predio' => $row->supply->cod_route ?? '-',
                'Cliente' => $row->person->name ?? '-',
                'Sector' => $row->supply->sector->name ?? '-',
                'Vía' => $row->supply->supplyVia->name ?? '-',
                'Período' => $period,
                'Monto' => number_format($row->amount, 2),
                'Saldo' => number_format($row->remaining_amount, 2),
                'N° Recibo' => $receipt,
                'Estado' => $status,
                'Tipo' => $debtType,
                'Fecha Generación' => $row->generation_date ? $row->generation_date->format('d/m/Y') : '-',
                'Fecha Vencimiento' => $row->due_date ? $row->due_date->format('d/m/Y') : '-',
            ];
        });

        $filename = 'recibos_por_cobrar_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return response()->streamDownload(function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, array_keys($data->first() ?: []));
            
            // Data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Genera PDF masivo concatenado de recibos por cobrar
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function printMassiveReceipts(Request $request)
    {
        try {
            $sector_ids = $request->sector_ids;
            $via_id = $request->via_id;
            $year = $request->year;
            $month = $request->month;
            $status = $request->status;
            $limit = $request->limit; // Sin límite por defecto, solo si el usuario lo especifica

            // Debug: Log de parámetros recibidos
            Log::info('Parámetros recibidos:', [
                'sector_ids' => $sector_ids,
                'via_id' => $via_id,
                'year' => $year,
                'month' => $month,
                'status' => $status,
                'limit' => $limit
            ]);

            // Filtrar valores especiales como 'all' o 'todos'
            if (is_array($sector_ids)) {
                $sector_ids = array_filter($sector_ids, function($id) {
                    return !in_array($id, ['all', 'todos']);
                });
                if (empty($sector_ids)) {
                    $sector_ids = null;
                }
            } elseif (in_array($sector_ids, ['all', 'todos'])) {
                $sector_ids = null;
            }

            // Verificar si month es 'null' string y convertirlo a null
            if ($month === 'null' || $month === null) {
                $month = null;
            }

            // Verificar si via_id es 'null' string y convertirlo a null
            if ($via_id === 'null' || $via_id === null) {
                $via_id = null;
            }

            // Obtener deudas con filtros
            $query = SupplyDebt::with([
                'supply.sector',
                'supply.supplyVia',
                'supply.person',
                'supplyTypeDebt',
                'supplyConcept'
            ]);

            // Aplicar filtros de suministro
            if ($sector_ids || $via_id) {
                $query->whereHas('supply', function($q) use ($sector_ids, $via_id) {
                    if ($sector_ids) {
                        $hasComma = $sector_ids && is_string($sector_ids) && strpos($sector_ids, ',') !== false;
                        if ($hasComma) {
                            $sector_ids = explode(',', $sector_ids);
                        }
                        if (is_array($sector_ids)) {
                            $q->whereIn('sector_id', $sector_ids);
                        } else {
                            $q->where('sector_id', $sector_ids);
                        }
                    }
                    if ($via_id) {
                        $q->where('supply_via_id', $via_id);
                    }
                });
            }

            // Aplicar filtros de deuda
            if ($year) {
                $query->where('year', $year);
            }
            if ($month) {
                // Normalizar mes a entero para manejar "04" vs "4"
                $monthInt = (int)$month;
                $query->whereRaw('CAST(month AS UNSIGNED) = ?', [$monthInt]);
            }

            $query->where('type', 'r');

            // Aplicar filtros de estado y ordenamiento según si hay sectores
            if($sector_ids){
                $query->join('supplies', 'supply_debt.supply_id', '=', 'supplies.id');
                if ($status === 'pending') {
                    $query->where('supply_debt.active', false);
                } elseif ($status === 'paid') {
                    $query->where('supply_debt.active', true);
                }
                $query->orderBy('supplies.sector_id', 'ASC')
                      ->select('supply_debt.*');
            }else{
                if ($status === 'pending') {
                    $query->where('active', false);
                } elseif ($status === 'paid') {
                    $query->where('active', true);
                }
                $query->orderByRaw('
                CASE
                    WHEN month IS NULL THEN 1
                    ELSE 0
                END,
                year ASC,
                month ASC,
                supply_id ASC
            ');
            }

            // Aplicar límite solo si el usuario lo especifica
            if ($limit && $limit > 0) {
                $query->limit($limit);
            }

            // Debug: Log de la consulta SQL
            Log::info('Consulta SQL:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

            // Optimización: Eager loading mejorado y selección específica de campos
            // Verificar cantidad de registros antes de procesar
            $totalCount = $query->count();
            Log::info('Total de recibos encontrados:', ['count' => $totalCount]);

            // Debug adicional para sectores específicos
            if($sector_ids) {
                Log::info('Generando PDF para sectores específicos:', [
                    'sector_ids' => $sector_ids,
                    'total_count' => $totalCount
                ]);
            }

            // Validación: Si hay más de 500 registros y no se especifica sector, rechazar
            if ($totalCount > 500 && !$sector_ids) {
                return response()->json([
                    'success' => false,
                    'message' => 'Para generar PDFs de más de 500 recibos, debe filtrar por sector específico.',
                    'total_receipts' => $totalCount,
                    'max_allowed' => 500,
                    'suggestion' => 'Seleccione un sector específico para reducir la cantidad de recibos.'
                ], 400);
            }

            // Obtener los datos finales
            $debts = $query->with([
                'supply:id,old_code,sector_id,supply_via_id,person_id,optional_address',
                'supply.person:id,name,number',
                'supply.sector:id,name',
                'supply.supplyVia:id,name,supply_type_via_id',
                'supply.supplyVia.supplyTypeVia:id,short',
                'supplyConcept:id,name'
            ])->get();

            Log::info('Recibos cargados para procesamiento:', [
                'count' => $debts->count(),
                'sector_ids' => $sector_ids,
                'first_debt_sector' => $debts->first() ? $debts->first()->supply->sector_id ?? 'null' : 'no_data'
            ]);

            // Debug adicional: verificar distribución por sectores
            if($debts->count() > 0) {
                $sectorDistribution = $debts->groupBy(function($debt) {
                    return $debt->supply->sector_id ?? 'unknown';
                })->map(function($group) {
                    return $group->count();
                });
                Log::info('Distribución por sectores en PDF:', $sectorDistribution->toArray());
            }

            if ($debts->isEmpty()) {
                // Debug: Verificar si hay deudas sin filtros
                $totalDebts = SupplyDebt::count();
                $debtsWithSupply = SupplyDebt::whereHas('supply')->count();
                
                // Debug adicional por filtros
                $debtsBySector = 0;
                $debtsByVia = 0;
                $debtsByYear = 0;
                $debtsByStatus = 0;
                
                if ($sector_ids) {
                    $hasComma = $sector_ids && is_string($sector_ids) && strpos($sector_ids, ',') !== false;
                    if ($hasComma) {
                        $sector_ids = explode(',', $sector_ids);
                    }
                    if (is_array($sector_ids)) {
                        $debtsBySector = SupplyDebt::whereHas('supply', function($q) use ($sector_ids) {
                            $q->whereIn('sector_id', $sector_ids);
                        })->count();
                    } else {
                        $debtsBySector = SupplyDebt::whereHas('supply', function($q) use ($sector_ids) {
                            $q->where('sector_id', $sector_ids);
                        })->count();
                    }
                }
                
                if ($via_id) {
                    $debtsByVia = SupplyDebt::whereHas('supply', function($q) use ($via_id) {
                        $q->where('supply_via_id', $via_id);
                    })->count();
                }
                
                if ($year) {
                    $debtsByYear = SupplyDebt::where('year', $year)->count();
                }
                
                if ($status === 'pending') {
                    $debtsByStatus = SupplyDebt::where('active', false)->count();
                } elseif ($status === 'paid') {
                    $debtsByStatus = SupplyDebt::where('active', true)->count();
                }

                Log::info('Debug - Análisis detallado:', [
                    'total' => $totalDebts,
                    'con_suministro' => $debtsWithSupply,
                    'por_sector' => $debtsBySector,
                    'por_via' => $debtsByVia,
                    'por_año' => $debtsByYear,
                    'por_estado' => $debtsByStatus
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron recibos con los filtros aplicados',
                    'debug' => [
                        'total_debts' => $totalDebts,
                        'debts_with_supply' => $debtsWithSupply,
                        'debts_by_sector' => $debtsBySector,
                        'debts_by_via' => $debtsByVia,
                        'debts_by_year' => $debtsByYear,
                        'debts_by_status' => $debtsByStatus,
                        'filters_applied' => [
                            'sector_ids' => $sector_ids,
                            'via_id' => $via_id,
                            'year' => $year,
                            'month' => $month,
                            'status' => $status
                        ]
                    ]
                ], 404);
            }

            // Procesar en chunks para optimizar memoria y velocidad
            return $this->processReceiptsOptimized($debts, $limit);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF masivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa recibos de forma optimizada para grandes volúmenes
     */
    private function processReceiptsOptimized($debts, $limit)
    {
        // Obtener datos estáticos con caché una sola vez
        $staticData = $this->getCachedStaticData();
        $company = $staticData['company'];
        $establishment = $staticData['establishment'];
        $meses = $staticData['meses'];
        
        // Configurar tiempo límite para procesos largos
        set_time_limit(300); // 5 minutos
        ini_set('memory_limit', '512M');
        
        // Pre-calcular datos comunes una sola vez
        $currentDate = Carbon::now();
        $dateFormatted = $currentDate->format('d-m-Y');
        $timeFormatted = $currentDate->format('H:i:s');
        
        Log::info('Iniciando procesamiento optimizado:', [
            'total_debts' => $debts->count(),
            'memory_start' => memory_get_usage(true) / 1024 / 1024 . 'MB'
        ]);

        // Procesar en chunks para evitar problemas de memoria
        $chunkSize = $limit ? min(50, max(10, intval($limit / 4))) : 50; // Chunk dinámico o 50 por defecto
        $receiptsData = [];

        $debts->chunk($chunkSize)->each(function($chunk) use (&$receiptsData, $meses, $dateFormatted, $timeFormatted) {
            foreach ($chunk as $debt) {
                // Pre-verificar datos necesarios para evitar procesamiento innecesario
                if (!$debt->supply || !$debt->supply->person) {
                    continue;
                }

                // Buscar deudas anteriores del mismo suministro
                // Normalizar mes a entero para comparación correcta (convierte "04" a 4)
                $currentYear = (int)$debt->year;
                $currentMonth = (int)$debt->month;

                $previousDebts = SupplyDebt::where('supply_id', $debt->supply_id)
                    ->where('id', '!=', $debt->id)
                    ->where('active', false) // Solo deudas pendientes
                    ->where(function($query) use ($debt, $currentYear, $currentMonth) {
                        // Deudas anteriores por año/mes
                        if ($debt->year && $debt->month) {
                            $query->where(function($q) use ($currentYear, $currentMonth) {
                                $q->where('year', '<', $currentYear)
                                  ->orWhere(function($q2) use ($currentYear, $currentMonth) {
                                      $q2->where('year', '=', $currentYear)
                                         ->whereRaw('CAST(month AS UNSIGNED) < ?', [$currentMonth]);
                                  })
                                  ->orWhereNull('year')  // Incluir deudas sin año
                                  ->orWhereNull('month'); // Incluir deudas sin mes
                            });
                        } else {
                            // Deudas anteriores por fecha de generación
                            $query->where('generation_date', '<', $debt->generation_date);
                        }
                    })
                    ->with(['supplyConcept'])
                    ->orderByRaw('CAST(year AS UNSIGNED) ASC, CAST(month AS UNSIGNED) ASC')
                    ->get();

                // Calcular total incluyendo deudas anteriores
                $totalAmount = $debt->amount + $previousDebts->sum('amount');

                // Preparar resumen de deudas anteriores agrupadas
                $previousDebtsGrouped = [];
                if ($previousDebts->count() > 0) {
                    $mesesAbrev = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];

                    // Agrupar deudas por tipo y concepto
                    $grouped = $previousDebts->groupBy(function($prevDebt) {
                        // Si tiene supply_concept_id, agrupar por ese concepto
                        if ($prevDebt->supply_concept_id) {
                            return 'concept_' . $prevDebt->supply_concept_id;
                        }
                        // Si es consumo regular (tipo 'r'), agrupar todos juntos
                        if ($prevDebt->type === 'r') {
                            return 'consumo_regular';
                        }
                        // Otros tipos individuales
                        return 'other_' . $prevDebt->id;
                    });

                    // Procesar cada grupo
                    foreach ($grouped as $groupKey => $groupDebts) {
                        $firstDebt = $groupDebts->first();
                        $lastDebt = $groupDebts->last();

                        $firstMonth = $firstDebt->month ? ($mesesAbrev[$firstDebt->month - 1] ?? '') : '';
                        $lastMonth = $lastDebt->month ? ($mesesAbrev[$lastDebt->month - 1] ?? '') : '';

                        // Determinar descripción del grupo
                        $description = '';
                        if (strpos($groupKey, 'concept_') === 0 && $firstDebt->supplyConcept) {
                            $description = $firstDebt->supplyConcept->name;
                        } elseif ($groupKey === 'consumo_regular') {
                            $description = 'Consumo Mensual';
                        } else {
                            $description = $this->generateDebtDescriptionOptimized($firstDebt, $meses);
                        }

                        $previousDebtsGrouped[] = [
                            'description' => $description,
                            'firstMonth' => $firstMonth,
                            'firstYear' => $firstDebt->year,
                            'lastMonth' => $lastMonth,
                            'lastYear' => $lastDebt->year,
                            'totalAmount' => $groupDebts->sum('amount'),
                            'count' => $groupDebts->count(),
                            'isSingleDebt' => $groupDebts->count() === 1,
                        ];
                    }
                }

                // Optimización: calcular datos una sola vez
                $clientName = strtoupper($debt->supply->person->name ?? 'Cliente');
                $description = $this->generateDebtDescriptionOptimized($debt, $meses);
                $monthName = $debt->month ? ($meses[$debt->month - 1] ?? '') : '';
                $debtType = $this->getDebtTypeNameOptimized($debt);
                $clientNameClass = $this->getClientNameClassOptimized($clientName);
                $totalRows = $this->calculateTotalRowsOptimized($clientName, $description);

                $receiptsData[] = [
                    'debt' => $debt,
                    'supply' => $debt->supply,
                    'person' => $debt->supply->person,
                    'sector' => $debt->supply->sector,
                    'supplyVia' => $debt->supply->supplyVia,
                    'supplyConcept' => $debt->supplyConcept,
                    'description' => $description,
                    'monthName' => $monthName,
                    'debtType' => $debtType,
                    'date' => $dateFormatted,
                    'time' => $timeFormatted,
                    'clientNameClass' => $clientNameClass,
                    'totalRows' => $totalRows,
                    // Agregar grupos de deudas anteriores
                    'previousDebtsGrouped' => $previousDebtsGrouped,
                    'totalAmount' => $totalAmount,
                    'hasPreviousDebts' => $previousDebts->count() > 0,
                ];
            }
            
            // Liberar memoria después de cada chunk
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        });
        
        Log::info('Datos procesados:', [
            'receipts_count' => count($receiptsData),
            'memory_after_processing' => memory_get_usage(true) / 1024 / 1024 . 'MB'
        ]);

        // Generar PDF con la misma configuración que la original (que funcionaba)
        $pdf = PDF::loadView('tenant.supplies.documents.receipt_massive', [
            'company' => $company,
            'establishment' => $establishment,
            'receiptsData' => $receiptsData
        ])->setPaper('A5', 'portrait');
        
        $filename = 'recibos_masivos_' . date('Y-m-d_H-i-s') . '.pdf';
        
        Log::info('PDF generado:', [
            'memory_final' => memory_get_usage(true) / 1024 / 1024 . 'MB',
            'filename' => $filename
        ]);
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * Método de debug para verificar datos
     */
    public function debugData(Request $request)
    {
        $sector_ids = $request->sector_ids;
        $via_id = $request->via_id;
        $year = $request->year;
        $month = $request->month;
        $status = $request->status;

        // Verificar si month es 'null' string y convertirlo a null
        if ($month === 'null' || $month === null) {
            $month = null;
        }

        // Verificar si via_id es 'null' string y convertirlo a null
        if ($via_id === 'null' || $via_id === null) {
            $via_id = null;
        }

        $debug = [];

        // 1. Verificar total de deudas
        $debug['total_debts'] = SupplyDebt::count();

        // 2. Verificar deudas con suministro
        $debug['debts_with_supply'] = SupplyDebt::whereHas('supply')->count();

        // 3. Verificar suministros por sector
        if ($sector_ids) {
            if (is_array($sector_ids)) {
                $debug['supplies_by_sector'] = \App\Models\Tenant\Supply::whereIn('sector_id', $sector_ids)->count();
            } else {
                $debug['supplies_by_sector'] = \App\Models\Tenant\Supply::where('sector_id', $sector_ids)->count();
            }
        }

        // 4. Verificar suministros por vía
        if ($via_id) {
            $debug['supplies_by_via'] = \App\Models\Tenant\Supply::where('supply_via_id', $via_id)->count();
        }

        // 5. Verificar deudas por año
        if ($year) {
            $debug['debts_by_year'] = SupplyDebt::where('year', $year)->count();
        }

        // 6. Verificar deudas por mes
        if ($month) {
            $debug['debts_by_month'] = SupplyDebt::where('month', $month)->count();
        }

        // 7. Verificar deudas por estado
        if ($status === 'pending') {
            $debug['pending_debts'] = SupplyDebt::where('active', false)->count();
        } elseif ($status === 'paid') {
            $debug['paid_debts'] = SupplyDebt::where('active', true)->count();
        }

        // 8. Verificar combinación de filtros
        $query = SupplyDebt::query();
        if ($sector_ids || $via_id) {
            $query->whereHas('supply', function($q) use ($sector_ids, $via_id) {
                if ($sector_ids) {
                    if (is_array($sector_ids)) {
                        $q->whereIn('sector_id', $sector_ids);
                    } else {
                        $q->where('sector_id', $sector_ids);
                    }
                }
                if ($via_id) {
                    $q->where('supply_via_id', $via_id);
                }
            });
        }
        if ($year) {
            $query->where('year', $year);
        }
        if ($month) {
            $query->where('month', $month);
        }
        if ($status === 'pending') {
            $query->where('active', false);
        } elseif ($status === 'paid') {
            $query->where('active', true);
        }
        $query->whereNotNull('serie_receipt');

        $debug['filtered_debts'] = $query->count();
        $debug['sql_query'] = $query->toSql();
        $debug['bindings'] = $query->getBindings();

        return response()->json([
            'success' => true,
            'debug' => $debug,
            'filters' => [
                'sector_ids' => $sector_ids,
                'via_id' => $via_id,
                'year' => $year,
                'month' => $month,
                'status' => $status
            ]
        ]);
    }

    /**
     * Agrega un PDF a la concatenación
     *
     * @param Fpdi $fpdi
     * @param string $pdfPath
     */
    private function addPdfToConcatenated(Fpdi &$fpdi, $pdfPath)
    {
        if (file_exists($pdfPath)) {
            $pageCount = $fpdi->setSourceFile($pdfPath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pageId = $fpdi->ImportPage($pageNo);
                $s = $fpdi->getTemplatesize($pageId);
                $fpdi->AddPage($s['orientation'], $s);
                $fpdi->useImportedPage($pageId);
            }
        }
    }

    /**
     * Genera descripción de la deuda
     *
     * @param SupplyDebt $debt
     * @param array $meses
     * @return string
     */
    private function generateDebtDescription($debt, $meses)
    {
        if ($debt->type == 'c' && $debt->supplyConcept) {
            return $debt->supplyConcept->name;
        } elseif ($debt->type == 'a') {
            return 'Deuda Acumulada';
        } elseif ($debt->type == 'r') {
            if ($debt->month && $debt->year) {
                $monthName = $meses[$debt->month - 1] ?? $debt->month;
                return "Consumo Mensual - {$monthName} {$debt->year}";
            }
            return 'Consumo Mensual';
        } else {
            return 'Deuda Manual';
        }
    }

    /**
     * Obtiene el nombre del tipo de deuda
     *
     * @param SupplyDebt $debt
     * @return string
     */
    private function getDebtTypeName($debt)
    {
        if ($debt->type == 'c' && $debt->supplyConcept) {
            return 'Concepto Específico';
        } elseif ($debt->supply_type_debt_id == 1 && $debt->type == 'r') {
            return 'Deuda Manual';
        } else {
            return 'Consumo Mensual';
        }
    }

    /**
     * Determina la clase CSS para nombres de clientes largos
     *
     * @param string $clientName
     * @return string
     */
    private function getClientNameClass($clientName)
    {
        $nameLength = strlen($clientName);
        $hasLineBreaks = strpos($clientName, "\n") !== false || strpos($clientName, "\r") !== false;
        $lineCount = substr_count($clientName, "\n") + substr_count($clientName, "\r") + 1;
        
        // Detectar nombres muy largos (como CORPORACION PERUANA DE AEROPUERTOS...)
        if ($nameLength > 80 || $lineCount > 3 || ($nameLength > 60 && $hasLineBreaks)) {
            return 'client-name very-long';
        } elseif ($nameLength > 50 || $lineCount > 2 || ($nameLength > 40 && $hasLineBreaks)) {
            return 'client-name long';
        }
        
        return 'client-name';
    }

    /**
     * Obtiene datos estáticos con caché para optimización
     */
    private function getCachedStaticData()
    {
        return Cache::remember('supply_receipts_static_data_v2', self::CACHE_TTL, function () {
            // Optimización: seleccionar solo campos necesarios
            $company = Company::select(['id', 'name', 'number'])->first();
            $user = auth()->user();
            $establishment = Establishment::select([
                'id', 'address', 'district_id', 'province_id', 'department_id'
            ])->with([
                'district:id,description',
                'province:id,description', 
                'department:id,description'
            ])->where('id', $user->establishment_id)->first();
            
            if (!$establishment) {
                $establishment = Establishment::select([
                    'id', 'address', 'district_id', 'province_id', 'department_id'
                ])->with([
                    'district:id,description',
                    'province:id,description',
                    'department:id,description'
                ])->first();
            }
            
            $meses = [
                "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
            ];
            
            return [
                'company' => $company,
                'establishment' => $establishment,
                'meses' => $meses
            ];
        });
    }

    /**
     * Obtiene deudas con eager loading optimizado
     */
    private function getDebtsWithRelations($query)
    {
        return $query->with([
            'supply.person',
            'supply.sector', 
            'supply.supplyVia',
            'supplyConcept'
        ])->get();
    }

    /**
     * Genera PDF individual con caché
     */
    private function generateCachedIndividualPdf($debt, $staticData)
    {
        $cacheKey = self::CACHE_DEBT_PDF_KEY . $debt->id . '_' . $debt->updated_at->timestamp;
        
        return Cache::remember($cacheKey, self::PDF_CACHE_TTL, function () use ($debt, $staticData) {
            // Generar descripción de la deuda
            $description = $this->generateDebtDescription($debt, $staticData['meses']);

            // Obtener nombre del mes si existe
            $monthName = '';
            if ($debt->month) {
                $monthIndex = (int)$debt->month - 1;
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $monthName = $staticData['meses'][$monthIndex];
                }
            }

            // Determinar tipo de deuda
            $debtType = $this->getDebtTypeName($debt);

            // Preparar datos para la vista
            $clientName = strtoupper($debt->supply->person->name ?? 'Cliente');
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
                'time' => Carbon::now()->format('H:i:s'),
                'clientNameClass' => $this->getClientNameClass($clientName),
                'totalRows' => $this->calculateTotalRows($clientName, $description)
            ];
            $company = $staticData['company'];
            $establishment = $staticData['establishment'];
            // Generar PDF individual
            return PDF::loadView('tenant.supplies.documents.receipt', [
                'company' => $company,
                'establishment' => $establishment,
                'data' => $data
            ])->setPaper('A5', 'portrait')->output();
        });
    }

    /**
     * Procesa deudas en lotes para optimizar memoria
     */
    private function processDebtsInBatches($debts, $staticData)
    {
        $pdfj = new Fpdi();
        $tempFiles = [];
        
        try {
            // Procesar en lotes para optimizar memoria
            $debtChunks = $debts->chunk(self::BATCH_SIZE);
            
            foreach ($debtChunks as $chunk) {
                foreach ($chunk as $debt) {
                    try {
                        // Generar PDF individual con caché
                        $pdfContent = $this->generateCachedIndividualPdf($debt, $staticData);
                        
                        // Crear archivo temporal único
                        $tempPath = storage_path('app/temp_receipt_' . $debt->id . '_' . uniqid() . '.pdf');
                        file_put_contents($tempPath, $pdfContent);
                        $tempFiles[] = $tempPath;
                        
                        // Agregar al PDF concatenado
                        $this->addPdfToConcatenated($pdfj, $tempPath);
                        
                    } catch (\Exception $e) {
                        Log::error("Error generando recibo {$debt->id}: " . $e->getMessage());
                        continue;
                    }
                }
                
                // Limpiar memoria después de cada lote
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
            
            return $pdfj->Output('S');
            
        } finally {
            // Limpiar archivos temporales
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        }
    }

    /**
     * Calcula el número total de filas para la tabla de importes basándose en la longitud del contenido
     *
     * @param string $clientName
     * @param string $description
     * @return int
     */
    private function calculateTotalRows($clientName, $description)
    {
        $baseRows = 12; // Filas base por defecto
        
        // Calcular factor de reducción por nombre del cliente
        $clientNameLength = strlen($clientName);
        $clientLineBreaks = substr_count($clientName, "\n") + substr_count($clientName, "\r");
        $clientLines = $clientLineBreaks + 1;
        
        $clientReduction = 0;
        if ($clientNameLength > 80 || $clientLines > 3) {
            $clientReduction = 2; // Nombres muy largos reducen 2 filas
        } elseif ($clientNameLength > 50 || $clientLines > 2) {
            $clientReduction = 1; // Nombres largos reducen 1 fila
        }
        
        // Calcular factor de reducción por descripción del concepto
        $descriptionLength = strlen($description);
        $descriptionLineBreaks = substr_count($description, "\n") + substr_count($description, "\r");
        $descriptionLines = $descriptionLineBreaks + 1;
        
        $descriptionReduction = 0;
        if ($descriptionLength > 100 || $descriptionLines > 4) {
            $descriptionReduction = 3; // Descripciones muy largas reducen 3 filas
        } elseif ($descriptionLength > 70 || $descriptionLines > 3) {
            $descriptionReduction = 2; // Descripciones largas reducen 2 filas
        } elseif ($descriptionLength > 50 || $descriptionLines > 2) {
            $descriptionReduction = 1; // Descripciones medianas reducen 1 fila
        }
        
        // Calcular filas totales
        $totalRows = $baseRows - $clientReduction - $descriptionReduction;
        
        // Asegurar un mínimo de 8 filas
        return max(8, $totalRows);
    }

    /**
     * VERSIONES OPTIMIZADAS DE LOS MÉTODOS AUXILIARES
     */
    
    /**
     * Versión optimizada de generateDebtDescription
     */
    private function generateDebtDescriptionOptimized($debt, $meses)
    {
        if ($debt->type == 'c' && $debt->supplyConcept) {
            return $debt->supplyConcept->name;
        } elseif ($debt->type == 'a') {
            return 'Deuda Acumulada';
        } elseif ($debt->type == 'r') {
            if ($debt->month && $debt->year) {
                $monthName = $meses[$debt->month - 1] ?? $debt->month;
                return "Consumo Mensual - {$monthName} {$debt->year}";
            }
            return 'Consumo Mensual';
        } else {
            return 'Deuda Manual';
        }
    }

    /**
     * Versión optimizada de getDebtTypeName
     */
    private function getDebtTypeNameOptimized($debt)
    {
        if ($debt->type == 'c' && $debt->supplyConcept) {
            return 'Concepto Específico';
        } elseif ($debt->supply_type_debt_id == 1 && $debt->type == 'r') {
            return 'Deuda Manual';
        } else {
            return 'Consumo Mensual';
        }
    }

    /**
     * Versión optimizada de getClientNameClass con caché
     */
    private static $clientNameCache = [];
    private function getClientNameClassOptimized($clientName)
    {
        $cacheKey = md5($clientName);
        if (isset(self::$clientNameCache[$cacheKey])) {
            return self::$clientNameCache[$cacheKey];
        }
        
        $nameLength = strlen($clientName);
        $hasLineBreaks = strpos($clientName, "\n") !== false || strpos($clientName, "\r") !== false;
        $lineCount = substr_count($clientName, "\n") + substr_count($clientName, "\r") + 1;
        
        $result = 'client-name';
        if ($nameLength > 80 || $lineCount > 3 || ($nameLength > 60 && $hasLineBreaks)) {
            $result = 'client-name very-long';
        } elseif ($nameLength > 50 || $lineCount > 2 || ($nameLength > 40 && $hasLineBreaks)) {
            $result = 'client-name long';
        }
        
        self::$clientNameCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Versión optimizada de calculateTotalRows con caché
     */
    private static $totalRowsCache = [];
    private function calculateTotalRowsOptimized($clientName, $description)
    {
        $cacheKey = md5($clientName . '|' . $description);
        if (isset(self::$totalRowsCache[$cacheKey])) {
            return self::$totalRowsCache[$cacheKey];
        }
        
        $result = $this->calculateTotalRows($clientName, $description);
        self::$totalRowsCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Limpia caché de PDFs individuales y datos optimizados
     */
    public function clearPdfCache()
    {
        try {
            // Limpiar caché de datos estáticos (versiones antiguas y nuevas)
            Cache::forget('supply_receipts_static_data');
            Cache::forget('supply_receipts_static_data_v2');
            
            // Limpiar caché de PDFs individuales (patrón)
            $keys = Cache::getRedis()->keys('*' . self::CACHE_DEBT_PDF_KEY . '*');
            foreach ($keys as $key) {
                Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
            }
            
            // Limpiar cachés estáticos de la clase
            self::$clientNameCache = [];
            self::$totalRowsCache = [];
            
            return response()->json([
                'success' => true,
                'message' => 'Caché limpiado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar caché: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas de rendimiento del caché
     */
    public function getCacheStats()
    {
        try {
            $stats = [
                'static_data_cached' => Cache::has('supply_receipts_static_data'),
                'cache_driver' => config('cache.default'),
                'cache_prefix' => config('cache.prefix'),
                'batch_size' => self::BATCH_SIZE,
                'cache_ttl' => self::CACHE_TTL,
                'pdf_cache_ttl' => self::PDF_CACHE_TTL
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera PDF individual de un recibo por cobrar
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function printIndividualReceipt($id)
    {
        try {
            // Obtener la deuda específica
            $debt = SupplyDebt::with([
                'supply.sector',
                'supply.supplyVia',
                'supply.person',
                'supply.supplyVia.supplyTypeVia',
                'supplyTypeDebt',
                'supplyConcept'
            ])->findOrFail($id);

            // Obtener datos estáticos con caché
            $staticData = $this->getCachedStaticData();
            $company = $staticData['company'];
            $establishment = $staticData['establishment'];
            $meses = $staticData['meses'];

            // Pre-calcular datos comunes
            $currentDate = Carbon::now();
            $dateFormatted = $currentDate->format('d-m-Y');
            $timeFormatted = $currentDate->format('H:i:s');

            // Buscar deudas anteriores del mismo suministro
            $currentYear = (int)$debt->year;
            $currentMonth = (int)$debt->month;

            $previousDebts = SupplyDebt::where('supply_id', $debt->supply_id)
                ->where('id', '!=', $debt->id)
                ->where('active', false) // Solo deudas pendientes
                ->where(function($query) use ($debt, $currentYear, $currentMonth) {
                    // Deudas anteriores por año/mes
                    if ($debt->year && $debt->month) {
                        $query->where(function($q) use ($currentYear, $currentMonth) {
                            $q->where('year', '<', $currentYear)
                              ->orWhere(function($q2) use ($currentYear, $currentMonth) {
                                  $q2->where('year', '=', $currentYear)
                                     ->whereRaw('CAST(month AS UNSIGNED) < ?', [$currentMonth]);
                              })
                              ->orWhereNull('year')  // Incluir deudas sin año
                              ->orWhereNull('month'); // Incluir deudas sin mes
                        });
                    } else {
                        // Deudas anteriores por fecha de generación
                        $query->where('generation_date', '<', $debt->generation_date);
                    }
                })
                ->with(['supplyConcept'])
                ->orderByRaw('CAST(year AS UNSIGNED) ASC, CAST(month AS UNSIGNED) ASC')
                ->get();

            // Calcular total incluyendo deudas anteriores
            $totalAmount = $debt->amount + $previousDebts->sum('amount');

            // Preparar resumen de deudas anteriores agrupadas
            $previousDebtsGrouped = [];
            if ($previousDebts->count() > 0) {
                $mesesAbrev = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];

                // Agrupar deudas por tipo y concepto
                $grouped = $previousDebts->groupBy(function($prevDebt) {
                    // Si tiene supply_concept_id, agrupar por ese concepto
                    if ($prevDebt->supply_concept_id) {
                        return 'concept_' . $prevDebt->supply_concept_id;
                    }
                    // Si es consumo regular (tipo 'r'), agrupar todos juntos
                    if ($prevDebt->type === 'r') {
                        return 'consumo_regular';
                    }
                    // Otros tipos individuales
                    return 'other_' . $prevDebt->id;
                });

                // Procesar cada grupo
                foreach ($grouped as $groupKey => $groupDebts) {
                    $firstDebt = $groupDebts->first();
                    $lastDebt = $groupDebts->last();

                    $firstMonth = $firstDebt->month ? ($mesesAbrev[$firstDebt->month - 1] ?? '') : '';
                    $lastMonth = $lastDebt->month ? ($mesesAbrev[$lastDebt->month - 1] ?? '') : '';

                    // Determinar descripción del grupo
                    $description = '';
                    if (strpos($groupKey, 'concept_') === 0 && $firstDebt->supplyConcept) {
                        $description = $firstDebt->supplyConcept->name;
                    } elseif ($groupKey === 'consumo_regular') {
                        $description = 'Consumo Mensual';
                    } else {
                        $description = $this->generateDebtDescriptionOptimized($firstDebt, $meses);
                    }

                    $previousDebtsGrouped[] = [
                        'description' => $description,
                        'firstMonth' => $firstMonth,
                        'firstYear' => $firstDebt->year,
                        'lastMonth' => $lastMonth,
                        'lastYear' => $lastDebt->year,
                        'totalAmount' => $groupDebts->sum('amount'),
                        'count' => $groupDebts->count(),
                        'isSingleDebt' => $groupDebts->count() === 1,
                    ];
                }
            }

            // Optimización: calcular datos una sola vez
            $clientName = strtoupper($debt->supply->person->name ?? 'Cliente');
            $description = $this->generateDebtDescriptionOptimized($debt, $meses);
            $monthName = $debt->month ? ($meses[$debt->month - 1] ?? '') : '';
            $debtType = $this->getDebtTypeNameOptimized($debt);
            $clientNameClass = $this->getClientNameClassOptimized($clientName);
            $totalRows = $this->calculateTotalRowsOptimized($clientName, $description);

            // Preparar datos para la vista (usando el mismo formato que printMassiveReceipts)
            $receiptsData = [[
                'debt' => $debt,
                'supply' => $debt->supply,
                'person' => $debt->supply->person,
                'sector' => $debt->supply->sector,
                'supplyVia' => $debt->supply->supplyVia,
                'supplyConcept' => $debt->supplyConcept,
                'description' => $description,
                'monthName' => $monthName,
                'debtType' => $debtType,
                'date' => $dateFormatted,
                'time' => $timeFormatted,
                'clientNameClass' => $clientNameClass,
                'totalRows' => $totalRows,
                'previousDebtsGrouped' => $previousDebtsGrouped,
                'totalAmount' => $totalAmount,
                'hasPreviousDebts' => $previousDebts->count() > 0,
            ]];

            // Generar PDF usando la misma plantilla masiva
            $pdf = PDF::loadView('tenant.supplies.documents.receipt_massive', [
                'company' => $company,
                'establishment' => $establishment,
                'receiptsData' => $receiptsData
            ])->setPaper('A5', 'portrait');

            $filename = 'recibo_' . $debt->serie_receipt . '-' . str_pad($debt->correlative_receipt, 6, '0', STR_PAD_LEFT) . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF individual: ' . $e->getMessage()
            ], 500);
        }
    }
}