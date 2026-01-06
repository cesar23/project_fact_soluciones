<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNoteCreditToExpenseMethodType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        DB::connection('tenant')->table('expense_method_types')->insert([
            'description' => 'Nota Crédito',
            'has_card' => false,
        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('expense_method_types')->where('description', 'Nota Crédito')->delete();
    }
}
