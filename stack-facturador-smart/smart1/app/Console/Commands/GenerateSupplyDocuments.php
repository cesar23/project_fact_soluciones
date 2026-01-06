<?php

namespace App\Console\Commands;

use App\Models\Tenant\SupplyPlanRegistered;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSupplyDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supply:generate-documents {--force : Force generation even if not day 28}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate supply documents for active registrations on the 28th of each month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now();
        $isDay28 = $today->day === 28;
        $forceGeneration = $this->option('force');

        if (!$isDay28 && !$forceGeneration) {
            $this->info('Today is not the 28th. Use --force to generate anyway.');
            return 0;
        }

        $this->info('Starting supply document generation...');

        // Obtener todos los registros activos con auto-generación habilitada
        $activeRegisters = SupplyPlanRegistered::readyForGeneration()->get();

        if ($activeRegisters->isEmpty()) {
            $this->info('No active supply registrations found for document generation.');
            return 0;
        }

        $generated = 0;
        $errors = 0;

        foreach ($activeRegisters as $register) {
            try {
                DB::beginTransaction();

                // Calcular año y mes del siguiente documento
                $nextMonth = $today->copy()->addMonth();
                $year = $nextMonth->year;
                $month = $nextMonth->month;

                // Verificar si debe generar documento para este mes
                if ($register->shouldGenerateDocumentForMonth($year, $month)) {
                    $document = $register->createDocumentForMonth($year, $month);
                    
                    if ($document) {
                        $generated++;
                        $this->line("✓ Generated document for supply ID {$register->supply_id} - Period: {$document->period}");
                    }
                } else {
                    $this->line("- Skipped supply ID {$register->supply_id} - Document already exists or conditions not met");
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollback();
                $errors++;
                $this->error("✗ Error generating document for supply ID {$register->supply_id}: " . $e->getMessage());
            }
        }

        $this->info("\n=== Generation Summary ===");
        $this->info("Total active registrations: " . $activeRegisters->count());
        $this->info("Documents generated: {$generated}");
        $this->info("Errors: {$errors}");

        return 0;
    }
}