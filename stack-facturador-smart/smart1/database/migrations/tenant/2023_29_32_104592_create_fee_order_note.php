<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeOrderNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_note_fee', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_note_id');
            $table->date('date');
            $table->string('currency_type_id');
            $table->decimal('amount', 12, 2);

            $table->foreign('order_note_id')->references('id')->on('order_notes');
            $table->foreign('currency_type_id')->references('id')->on('cat_currency_types');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_note_fee');
    }
}
