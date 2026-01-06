<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_01_05_204241_insert_period_if_no_exists
class InsertPeriodIfNoExists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $periods = DB::table('cat_periods')->get();
        if ($periods->count() == 0) {
            DB::table('cat_periods')->insert([
                [
                    'period' => 'M',
                'name' => 'Mensual',
                'active' => true
            ],
            [
                'period' => 'Y',
                'name' => 'Anual',
                    'active' => true
                ]
            ]);
        }
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('cat_periods')->where('period', 'M')->delete();
        DB::table('cat_periods')->where('period', 'Y')->delete();
    }
}
