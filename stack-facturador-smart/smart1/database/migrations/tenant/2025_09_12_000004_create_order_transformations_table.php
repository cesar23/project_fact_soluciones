<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_transformations', function (Blueprint $table) {
            $table->id();
            $table->string('series', 10)->default('OT');
            $table->string('number', 20);
            $table->date('date_of_issue');
            $table->unsignedInteger('person_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedInteger('destination_warehouse_id');
            $table->string('condition', 50)->default('TRANSFORMACION DE PRODUCTOS');
            $table->string('status', 20)->default('pending');
            $table->date('prod_start_date')->nullable();
            $table->date('prod_start_time')->nullable();
            $table->date('prod_end_date')->nullable();
            $table->date('prod_end_time')->nullable();
            $table->string('prod_responsible')->nullable();
            $table->date('mix_start_date')->nullable();
            $table->date('mix_start_time')->nullable();
            $table->date('mix_end_date')->nullable();
            $table->date('mix_end_time')->nullable();
            $table->string('mix_responsible')->nullable();
            $table->text('observation')->nullable();
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
        Schema::dropIfExists('order_transformations');
    }
}