<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceByPaymentCondition extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_price_payment_condition', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_id');
            $table->string('payment_condition_id');
            $table->decimal('price', 12, 2);
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('payment_condition_id')->references('id')->on('payment_conditions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::dropIfExists('item_price_payment_condition');
    
    }
}
