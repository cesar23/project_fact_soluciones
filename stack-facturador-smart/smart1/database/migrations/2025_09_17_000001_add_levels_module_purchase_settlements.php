<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2025_09_17_000001_add_levels_module_purchase_settlements
class AddLevelsModulePurchaseSettlements extends Migration
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
                    'value' => 'purchase_settlements',
                    'description' => 'Liquidaciones de compra',
                    'module_id' => 2,
                ],
                
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
        DB::table('module_levels')->where('value', 'purchase_settlements')->delete();
      
    }
}
