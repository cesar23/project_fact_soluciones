<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRowsToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('cat_unit_types')->insert([
            ['id' => 'DZN', 'active' => 1, 'description' => 'Docena HD  - Media docena'],
            ['id' => 'QD', 'active' => 1, 'description' => 'Cuarto de docena'],
            ['id' => 'PK', 'active' => 1, 'description' => 'Paquete'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            //
        });
    }
}
