<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//2025_08_21_000016_adjust_services_to_warehouses
class AdjustServicesToWarehouses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $db_connection = DB::connection('tenant');
        $db_connection->table('items')->select('items.*')->where('unit_type_id', 'ZZ')

            ->leftJoin('item_warehouse', 'items.id', '=', 'item_warehouse.item_id')
            ->whereNull('item_warehouse.item_id')
            ->orderBy('items.id')
            ->chunk(1000, function ($items) use ($db_connection) {
                $to_insert = [];
                $created_at = now();

                foreach ($items as $item) {
                    $to_insert[] = [
                        'warehouse_id' => 1,
                        'stock' => 0,
                        'item_id' => $item->id,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                    ];
                }
                $db_connection->table('item_warehouse')->insert($to_insert);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
