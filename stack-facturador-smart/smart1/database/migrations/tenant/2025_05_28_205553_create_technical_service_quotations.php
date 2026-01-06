<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_05_28_205553_create_technical_service_quotations
class CreateTechnicalServiceQuotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_service_quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('technical_service_id');
            $table->foreign('technical_service_id')->references('id')->on('technical_services');
            $table->unsignedInteger('quotation_id');
            $table->foreign('quotation_id')->references('id')->on('quotations');
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
        Schema::dropIfExists('technical_service_quotations');
    }
}
