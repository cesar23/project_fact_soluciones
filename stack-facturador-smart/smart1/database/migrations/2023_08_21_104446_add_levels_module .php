<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLevelsModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules')->insert(
            [
                [
                    'value' => 'whatsapp',
                    'description' => 'Whatsapp',
                    'sort' => 1,
                ],
                [
                    'value' => 'suppliers',
                    'description' => 'Proveedores',
                    'sort' => 6,
                ]
     
            ]
        );
        

        DB::table('module_levels')->insert(
            [
                [
                    'value' => 'summaries',
                    'description' => 'Resúmenes',
                    'module_id' => 1,
                ],
                [
                    'value'=>'voided',
                    'description'=>'Anulaciones',
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
        DB::table('modules')->where('value', 'whatsapp')->delete();
        DB::table('module_levels')->where('value', 'summaries')->delete();
      
    }
}
