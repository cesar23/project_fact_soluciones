<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_153353_add_note_credit_to_payment_method
class AddNoteCreditToPaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        DB::connection('tenant')->table('payment_method_types')->insert([
            'id' => 'NC',
            'description' => 'Nota Crédito',
            'has_card' => false,
            'is_bank' => false,
            'is_credit' => false,
            'is_digital' => false,
            'is_cash' => true,

        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('payment_method_types')->where('id', 'NC')->delete();
    }
}
