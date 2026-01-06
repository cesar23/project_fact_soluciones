<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\ItemWarehouse;
use Modules\Inventory\Http\Controllers\ReportKardexController;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdjustStockCommand extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'stock:adjust';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Ajusta el stock de todos los items en base a kardex';

    private $kardexController;
    private $adjustedCount = 0;

    public function __construct()
    {
        parent::__construct();
        $this->kardexController = new ReportKardexController();
    }

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('Iniciando ajuste de stock...');

        ItemWarehouse::chunk(100, function (Collection $itemWarehouses) {
            $this->processItemWarehouseBatch($itemWarehouses);
        });

        $this->info("Proceso completado. Se ajustaron {$this->adjustedCount} registros.");
    }

    private function processItemWarehouseBatch(Collection $itemWarehouses)
    {
        foreach ($itemWarehouses as $itemWarehouse) {
            $this->processItemWarehouse($itemWarehouse);
        }
    }

    private function processItemWarehouse(ItemWarehouse $itemWarehouse)
    {
        $adjustmentResult = $this->checkStockAdjustment($itemWarehouse);

        if (!$adjustmentResult['success'] && isset($adjustmentResult['correct_stock'])) {
            $this->adjustStock($itemWarehouse, $adjustmentResult['correct_stock']);
        }
    }

    private function checkStockAdjustment(ItemWarehouse $itemWarehouse): array
    {
        return $this->kardexController->item_adjustment(new Request([
            'item_id' => $itemWarehouse->item_id,
            'warehouse_id' => $itemWarehouse->warehouse_id
        ]));
    }

    private function adjustStock(ItemWarehouse $itemWarehouse, float $correctStock): void
    {
        $stockResult = $this->kardexController->stock_adjustment(new Request([
            'item_id' => $itemWarehouse->item_id,
            'warehouse_id' => $itemWarehouse->warehouse_id,
            'correct_stock' => $correctStock
        ]));

        if ($stockResult['success']) {
            $this->adjustedCount++;
        }
    }
} 