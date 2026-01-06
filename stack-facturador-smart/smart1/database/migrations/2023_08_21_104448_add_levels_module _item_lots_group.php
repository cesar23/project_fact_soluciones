<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLevelsModuleItemLotsGroup extends Migration
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
                    'value' => 'item_lots_group',
                    'description' => 'Lotes',
                    'module_id' => 17,
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
        DB::table('module_levels')->where('value', 'item_lots_group')->delete();
      
    }
}
