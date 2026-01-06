<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeAllToGekawa2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('configurations')->update(['show_gekawa_mk' => 0]);
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
