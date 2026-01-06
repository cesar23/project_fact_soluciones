<?php

namespace App\Console\Commands;

use App\Models\Tenant\Supply;
use App\Models\Tenant\SupplyDebt;
use App\Models\Tenant\SupplyPlanRegistered;
use App\Models\Tenant\Series;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Catalogs\DocumentType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class GenerateSupplyDebtsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supply:generate-debts {--force : Force generation even if not day 27} {--month= : Specific month to generate (1-12)} {--year= : Specific year to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly supply debts for active registrations on the 27th of each month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now();
        $isDay27 = $today->day === 27;
        $forceGeneration = $this->option('force') ;
        // $forceGeneration = true;;

        if (!$isDay27 && !$forceGeneration) {
            $this->info('Today is not the 27th. Use --force to generate anyway.');
            return 0;
        }

        // Determinar año y mes para generar deudas
        $targetYear = $this->option('year') ? (int)$this->option('year') : $today->year;
        $targetMonth = $this->option('month') ? (int)$this->option('month') : $today->month;
        // $targetMonth = 9;
        // $targetYear = 2025;

        // Validar mes válido
        if ($targetMonth < 1 || $targetMonth > 12) {
            $this->error('Invalid month. Must be between 1 and 12.');
            return 1;
        }

        $this->info("Starting supply debt generation for {$targetYear}-{$targetMonth}...");

        // Asegurar que existen el DocumentType y Series necesarios
        $this->initializeDocumentTypeAndSeries();

        // Obtener todos los registros activos de suministros
        /** @var \Illuminate\Database\Eloquent\Collection<SupplyPlanRegistered> $activeRegisters */
        $activeRegisters = SupplyPlanRegistered::with([
            'supply.person', 
            'supply.supplyVia', 
            'supply.sector',
            'supplyPlan'
        ])
        ->where('active', true)
        ->whereHas('supply', function($query) {
            $query->whereHas('person'); // Asegurar que tenga persona asociada
        })
        ->get();

        if ($activeRegisters->isEmpty()) {
            $this->info('No active supply registrations found for debt generation.');
            return 0;
        }

        $generated = 0;
        $skipped = 0;
        $errors = 0;

        $this->info("Processing {$activeRegisters->count()} active supply registrations...\n");

        /** @var SupplyPlanRegistered $register */
        foreach ($activeRegisters as $register) {
            try {
                DB::beginTransaction();

                $result = $this->generateDebtForSupply($register, $targetYear, $targetMonth);
                
                if ($result['success']) {
                    if ($result['generated']) {
                        $generated++;
                        $monthName = $this->getMonthName($targetMonth);
                        $customerName = $register->supply->person->name ?? 'N/A';
                        $supplyCode = $register->supply->cod_route ?? $register->supply_id;
                        $this->line("✓ Generated debt for Supply {$supplyCode} - Customer: {$customerName} - Period: {$monthName} {$targetYear} - Amount: S/ " . number_format($result['amount'], 2));
                    } else {
                        $skipped++;
                        $supplyCode = $register->supply->cod_route ?? $register->supply_id;
                        $this->line("- Skipped Supply {$supplyCode} - {$result['reason']}");
                    }
                } else {
                    $errors++;
                    $supplyCode = $register->supply->cod_route ?? $register->supply_id;
                    $this->error("✗ Error for Supply {$supplyCode}: {$result['message']}");
                }

                DB::commit();

            } catch (Exception $e) {
                DB::rollback();
                $errors++;
                $this->error("✗ Exception for Supply ID {$register->supply_id}: " . $e->getMessage());
            }
        }

        $this->info("\n=== Debt Generation Summary ===");
        $this->info("Target period: {$targetYear}-{$targetMonth}");
        $this->info("Total active registrations: " . $activeRegisters->count());
        $this->info("Debts generated: {$generated}");
        $this->info("Registrations skipped: {$skipped}");
        $this->info("Errors: {$errors}");

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Generate debt for a specific supply registration
     *
     * @param SupplyPlanRegistered $register
     * @param int $year
     * @param int $month
     * @return array
     */
    private function generateDebtForSupply(SupplyPlanRegistered $register, int $year, int $month): array
    {
        // Verificar si ya existe deuda para este período
        $existingDebt = SupplyDebt::where('supply_id', $register->supply_id)
            ->where('year', $year)
            ->whereRaw('CAST(month AS UNSIGNED) = ?', [$month])
            ->whereIn('type', ['r', 'a']) // Deuda regular mensual o adelanto
            ->first();

        if ($existingDebt) {
            return [
                'success' => true,
                'generated' => false,
                'reason' => 'Debt already exists for this period'
            ];
        }

        // Validar fecha de inicio del contrato
        $contractStartDate = $register->date_start ? Carbon::parse($register->date_start) : null;
        $currentPeriod = Carbon::createFromDate($year, $month, 1);

        if ($contractStartDate && $contractStartDate->gt($currentPeriod->endOfMonth())) {
            return [
                'success' => true,
                'generated' => false,
                'reason' => 'Contract starts after this period'
            ];
        }

        // Verificar que el suministro tenga plan activo
        if (!$register->supplyPlan) {
            return [
                'success' => false,
                'message' => 'No supply plan found'
            ];
        }

        // Calcular monto de la deuda
        $amount = $register->supplyPlan->total;

        // Calcular monto proporcional si es el mes de inicio del contrato
        if ($contractStartDate && 
            $contractStartDate->year == $year && 
            $contractStartDate->month == $month) {
            
            $amount = $this->calculateProportionalAmount($amount, $contractStartDate, $year, $month);
        }

        // Generar número de recibo
        $receiptData = $this->generateReceiptNumber();

        // Calcular fecha de vencimiento (30 días desde el final del mes)
        $dueDate = Carbon::createFromDate($year, $month, 1)
            ->endOfMonth()
            ->addDays(30);

        // Crear la deuda
        $debt = SupplyDebt::create([
            'supply_contract_id' => null, // Puede agregarse si hay relación con contratos
            'person_id' => $register->supply->person_id,
            'supply_id' => $register->supply_id,
            'serie_receipt' => $receiptData['serie'],
            'correlative_receipt' => $receiptData['correlative'],
            'amount' => round($amount, 2),
            'original_amount' => round($amount, 2),
            'year' => $year,
            'month' => $month,
            'generation_date' => now(),
            'due_date' => $dueDate,
            'active' => false, // false = pendiente de pago
            'type' => 'r', // r = regular/mensual
            'supply_type_debt_id' => 1, // Tipo de deuda estándar
            'supply_concept_id' => null,
        ]);

        return [
            'success' => true,
            'generated' => true,
            'amount' => $amount,
            'debt_id' => $debt->id
        ];
    }

    /**
     * Calculate proportional amount for contracts starting mid-month
     *
     * @param float $baseAmount
     * @param Carbon $startDate
     * @param int $year
     * @param int $month
     * @return float
     */
    private function calculateProportionalAmount(float $baseAmount, Carbon $startDate, int $year, int $month): float
    {
        $monthStart = Carbon::createFromDate($year, $month, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();
        $totalDaysInMonth = $monthEnd->day;

        // Días desde el inicio del contrato hasta el final del mes
        $daysToCharge = $monthEnd->diffInDays($startDate) + 1;

        // Calcular proporción
        $proportion = $daysToCharge / $totalDaysInMonth;
        
        return $baseAmount * $proportion;
    }

    /**
     * Initialize DocumentType and Series for public services receipts
     * This is called once at the beginning of the command execution
     *
     * @return void
     */
    private function initializeDocumentTypeAndSeries(): void
    {
        try {
            // Asegurar que existe el tipo de documento 14
            $documentType = DocumentType::find('14');
            
            if (!$documentType) {
                DocumentType::create([
                    'id' => '14',
                    'active' => true,
                    'short' => 'RC',
                    'description' => 'RECIBO DE SERVICIOS PUBLICOS'
                ]);
                
                $this->info('✓ Created DocumentType 14: RECIBO DE SERVICIOS PUBLICOS');
            }

            // Asegurar que existe la serie para el establecimiento
            $establishment = Establishment::first();
            
            if (!$establishment) {
                throw new Exception('No establishment found');
            }

            $series = Series::where('establishment_id', $establishment->id)
                ->where('document_type_id', '14')
                ->first();

            if (!$series) {
                Series::create([
                    'establishment_id' => $establishment->id,
                    'document_type_id' => '14',
                    'number' => '0002',
                    'contingency' => false,
                    'internal' => false,
                ]);
                
                $this->info('✓ Created Series 0002 for DocumentType 14 (Public Services Receipts)');
            } else {
                $this->info('✓ Series 0002 for public services receipts already exists');
            }

        } catch (Exception $e) {
            $this->error("Error initializing DocumentType and Series: " . $e->getMessage());
            throw $e; // Re-throw to stop execution if critical setup fails
        }
    }

    /**
     * Generate receipt serie and correlative number
     * Assumes DocumentType and Series are already initialized
     *
     * @return array
     */
    private function generateReceiptNumber(): array
    {
        try {
            $establishment = Establishment::first();
            
            if (!$establishment) {
                throw new Exception('No establishment found');
            }

            // La serie ya debería existir gracias a initializeDocumentTypeAndSeries()
            $series = Series::where('establishment_id', $establishment->id)
                ->where('document_type_id', '14')
                ->first();

            if (!$series) {
                // Esto no debería pasar si initializeDocumentTypeAndSeries() funcionó correctamente
                throw new Exception('Series not found. initializeDocumentTypeAndSeries() may have failed.');
            }

            // Obtener el último número correlativo para esta serie
            $lastDebt = SupplyDebt::where('serie_receipt', $series->number)
                ->orderBy('correlative_receipt', 'desc')
                ->first();

            $nextCorrelative = $lastDebt ? $lastDebt->correlative_receipt + 1 : 1;

            return [
                'serie' => $series->number,
                'correlative' => $nextCorrelative
            ];

        } catch (Exception $e) {
            // Fallback a sistema simple si falla
            $this->error("Error generating receipt number: " . $e->getMessage());
            
            $lastDebt = SupplyDebt::orderBy('correlative_receipt', 'desc')->first();
            $nextNumber = $lastDebt ? $lastDebt->correlative_receipt + 1 : 1;

            return [
                'serie' => '0002',
                'correlative' => $nextNumber
            ];
        }
    }

    /**
     * Get the month name in Spanish for logging purposes
     *
     * @param int $month
     * @return string
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $months[$month] ?? 'Mes desconocido';
    }
}