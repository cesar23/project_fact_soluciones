<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSeriesInternalAnulate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
        DB::connection('tenant')
            ->table('state_types')
            ->insert([
                ['id' => '56', 'description' => 'Anulado']
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')
            ->table('state_types')
            ->where('id', '56')
            ->delete();
    }
}
