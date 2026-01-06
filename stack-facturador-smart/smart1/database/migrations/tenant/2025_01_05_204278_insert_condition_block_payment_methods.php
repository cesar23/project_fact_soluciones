<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204278_insert_condition_block_payment_methods

class InsertConditionBlockPaymentMethods extends Migration
{
    public function up()
    {

        DB::table('condition_block_payment_methods')->insert([
            [
                'payment_condition_id' => '01',
                'payment_method_type' => 'is_cash'
            ],
            [
                'payment_condition_id' => '01',
                'payment_method_type' => 'is_digital'
            ],
            [
                'payment_condition_id' => '01',
                'payment_method_type' => 'is_bank'
            ],
            [
                'payment_condition_id' => '02',
                'payment_method_type' => 'is_credit'
            ],
        ]);

    


    }

    public function down()
    {
        DB::table('condition_block_payment_methods')->whereIn('payment_condition_id', ['01', '02'])->delete();  
    }


}