<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLevelsModuleProductionDispatchOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        

        DB::table('module_levels')->insert(
            [
                [
                    'value' => 'production_orders',
                    'description' => 'Ordenes de producción',
                    'module_id' => 1,
                ],
                [
                    'value'=>'dispatch_orders',
                    'description'=>'Ordenes de despacho',
                    'module_id'=>1,
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()

    {
        DB::table('module_levels')->where('value', 'production_orders')->delete();
        DB::table('module_levels')->where('value', 'dispatch_orders')->delete();
      
    }
}
