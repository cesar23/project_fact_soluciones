<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplyAdvancePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_advance_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supply_id');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->integer('year');
            $table->string('month');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supply_advance_payments');
    }
}