<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSurveysModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->transaction(
            function () {
                DB::connection('tenant')->table('modules')->insertGetId(
                    [
                        'value' => 'surveys',
                        'description' => 'Encuestas',
                        'order_menu' => 8,
                    ],

                );
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->transaction(
            function () {
                DB::connection('tenant')->table('modules')->where('value', 'surveys')->delete();
            }
        );
    }
}
