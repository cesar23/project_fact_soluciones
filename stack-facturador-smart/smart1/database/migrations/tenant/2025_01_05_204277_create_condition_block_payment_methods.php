<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204277_create_condition_block_payment_methods

class CreateConditionBlockPaymentMethods extends Migration
{
    public function up()
    {

        Schema::create('condition_block_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('payment_condition_id');
            $table->string('payment_method_type');
            $table->timestamps();
        });

    


    }

    public function down()
    {
        Schema::dropIfExists('condition_block_payment_methods');

    }


}