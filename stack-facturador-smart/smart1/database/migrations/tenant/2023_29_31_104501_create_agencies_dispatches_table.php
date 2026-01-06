<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgenciesDispatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::create('agencies_dispatches_table',function (Blueprint $table){

            $table->increments('id');
            $table->date('date_of_issue');
            $table->unsignedInteger('agency_id')->nullable();
            $table->unsignedInteger('dispatch_order_id');
            $table->string('reference')->nullable();
            $table->string('destination')->nullable();
            $table->string('dispatch_file')->nullable();
            $table->boolean('active')->default(true);
            $table->foreign('dispatch_order_id')->references('id')->on('dispatch_orders')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('agencies_dispatches_table');
    }
}
