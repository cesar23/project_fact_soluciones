<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_09_17_2024340_rus_can_emit_01_purchases
class RusCanEmit01Purchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $company = DB::connection('tenant')->table('companies')->where('is_rus', 1)->first();
        if($company){
            DB::connection('tenant')->table('cat_document_types')->where('id', '01')->update(['active' => true]);
            
        }
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
