<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptometryServicePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('optometry_service_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('optometry_service_id');
            $table->date('date_of_payment');
            $table->char('payment_method_type_id', 2);
            $table->string('reference')->nullable();
            $table->decimal('change',12, 2)->default(0);
            $table->decimal('payment', 12, 2);

            $table->foreign('optometry_service_id')->references('id')->on('optometry_services')->onDelete('cascade');
            $table->foreign('payment_method_type_id')->references('id')->on('payment_method_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('optometry_service_payments');
    }
}
