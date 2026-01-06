<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_153352_add_note_credit_sale_note_type
class AddNoteCreditSaleNoteType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        DB::connection('tenant')->table('cat_note_credit_types')->insert([
            'id' => 'NV',
            'description' => 'Devolución parcial',
            'active' => true,
        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('cat_note_credit_types')->where('id', 'NV')->delete();
    }
}
