<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2024_12_15_104447_add_levels_module_more2
class AddLevelsModuleMore2 extends Migration
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
                    'value' => 'cupones',
                    'description' => 'Cupones',
                    'module_id' => 17,
                ],
                [
                    'value' => 'inventory_references',
                    'description' => 'Referencias',
                    'module_id' => 8,
                ],
                [
                    'value' => 'valued_kardex',
                    'description' => 'Reporte Kardex 13.1',
                    'module_id' => 8,
                ],
                [
                    'value' => 'inventory_review',
                    'description' => 'Revisión de Inventario',
                    'module_id' => 8,
                ],
                [
                    'value' => 'stock_date',
                    'description' => 'Stock histórico',
                    'module_id' => 8,
                ],
                [
                    'value' => 'kardexaverage',
                    'description' => 'Kardex promedio',
                    'module_id' => 8,
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
        DB::table('module_levels')->where('value', 'cupones')->delete();
        DB::table('module_levels')->where('value', 'inventory_references')->delete();
        DB::table('module_levels')->where('value', 'inventory_references')->delete();
        DB::table('module_levels')->where('value', 'valued_kardex')->delete();
        DB::table('module_levels')->where('value', 'inventory_review')->delete();
        DB::table('module_levels')->where('value', 'stock_date')->delete();
        DB::table('module_levels')->where('value', 'kardexaverage')->delete();
    }
}
