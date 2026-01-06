<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2024_12_15_104446_add_levels_module_more
class AddLevelsModuleMore extends Migration
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
                    'value' => 'payroll',
                    'description' => 'Planilla',
                    'sort' => 10,
                ],
                [
                    'value' => 'exchange_currency',
                    'description' => 'Tipo de Cambio',
                    'sort' => 10,
                ],
                [
                    'value' => 'payment_list',
                    'description' => 'Mis Pagos',
                    'sort' => 10,
                ]
     
            ]
        );
        

        DB::table('module_levels')->insert(
            [
                [
                    'value' => 'documents_recurrence',
                    'description' => 'Documentos Recurrentes',
                    'module_id' => 1,
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
        DB::table('modules')->where('value', 'payroll')->delete();
        DB::table('modules')->where('value', 'exchange_currency')->delete();
        DB::table('modules')->where('value', 'payment_list')->delete();
        DB::table('module_levels')->where('value', 'documents_recurrence')->delete();
      
    }
}
