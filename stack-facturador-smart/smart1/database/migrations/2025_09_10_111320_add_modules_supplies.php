<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_09_10_111320_add_modules_supplies
class AddModulesSupplies extends Migration
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
                    'value' => 'supplies',
                    'description' => 'Suministros',
                    'sort' => 18,
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

        DB::table('modules')->where('value', 'supplies')->delete();
    }
}
