<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FillColumnNumberOrderNoteQuotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('quotations')->whereNull('number')->update(['number' => DB::raw('id')]);
        DB::connection('tenant')->table('order_notes')->whereNull('number')->update(['number' => DB::raw('id')]);
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
