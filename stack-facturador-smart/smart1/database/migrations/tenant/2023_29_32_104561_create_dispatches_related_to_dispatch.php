<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatchesRelatedToDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatches_related_to_dispatch', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dispatch_id');
            $table->string('serie_number');
            $table->string('company_number');
            $table->string('document_type')->default('09');
            $table->foreign('dispatch_id')->references('id')->on('dispatches')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatches_related_to_dispatch');
    }
}
