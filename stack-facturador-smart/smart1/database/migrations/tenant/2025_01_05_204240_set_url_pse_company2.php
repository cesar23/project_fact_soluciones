<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


class SetUrlPseCompany2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('companies')->update([
            'pse_url' => 'https://consultaperu.pe'
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
