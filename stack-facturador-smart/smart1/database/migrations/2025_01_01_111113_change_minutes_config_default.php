<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_01_01_111113_change_minutes_config_default


class ChangeMinutesConfigDefault extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {
    
        DB::table('configurations')->where('id', 1)->update([
            'max_attempt_pse' => 2,
            'minute_to_validate' => 5,
            'minutes_verify_cdr' => 5,
        ]);



    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    


    }
}
