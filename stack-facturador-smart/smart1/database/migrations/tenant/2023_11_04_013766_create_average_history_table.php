<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAverageHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('average_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_document')->nullable();
            $table->unsignedInteger('sale_note_id')->nullable();
            $table->unsignedInteger('id_purchase')->nullable();
            $table->decimal('purchase_cost', 12, 4);
            $table->decimal('total_purchase_cost', 12, 4);
            $table->decimal('price_balance', 12, 4);
            $table->decimal('input', 12, 4);
            $table->decimal('output', 12, 4);
            $table->decimal('balance', 12, 4);
            $table->decimal('sales_cost', 12, 4);
            $table->decimal('total_sales', 12, 4);
            $table->decimal('total_balance', 12, 4);
            $table->string('type_transaction');
            $table->string('serie')->nullable();
            $table->string('number')->nullable();
            $table->timestamps();
            $table->foreign('id_purchase')->references('id')->on('purchases');
            $table->foreign('id_document')->references('id')->on('documents');
            $table->foreign('sale_note_id')->references('id')->on('sale_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('average_history');
    }
}
