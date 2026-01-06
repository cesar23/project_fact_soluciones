<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2025_06_26_104447_add_levels_module_more3
class AddLevelsModuleMore3 extends Migration
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
                    'value' => 'order-delivery',
                    'description' => 'Ordenes de entrega',
                    'module_id' => 3,
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
        DB::table('module_levels')->where('value', 'order-delivery')->delete();
    }
}
