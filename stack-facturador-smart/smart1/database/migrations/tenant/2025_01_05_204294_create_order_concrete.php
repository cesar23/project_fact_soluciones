<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204294_create_order_concrete

class CreateOrderConcrete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('order_concretes', function (Blueprint $table) {
            $table->increments('id');
        
            // Datos del cliente
            $table->string('series');
            $table->string('number');
            
            // Ubicación y obra
            $table->string('establishment_code');
            $table->string('address',500);
            $table->unsignedInteger('master_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('establishment_id');
            
            // Requerimiento
            $table->date('date');
            $table->time('hour');
            $table->string('electro')->nullable();
            $table->string('volume')->nullable();
            $table->string('mix_kg_cm2')->nullable();
            $table->string('type_cement')->nullable();
            $table->string('pump')->nullable();
            $table->string('other')->nullable();
            
            $table->text('observations')->nullable();

            
    
            // Revisiones y aprobaciones
            $table->string('treasury_reviewed_name')->nullable();
            $table->date('treasury_reviewed_date')->nullable();
            $table->date('treasury_reviewed_signature')->nullable();

            $table->string('plant_manager_reviewed_name')->nullable();
            $table->date('plant_manager_reviewed_date')->nullable();
            $table->date('plant_manager_reviewed_signature')->nullable();

            $table->string('plant_operator_reviewed_name')->nullable();
            $table->date('plant_operator_reviewed_date')->nullable();
            $table->date('plant_operator_reviewed_signature')->nullable();

            $table->string('manager_approved_name')->nullable();
            $table->date('manager_approved_date')->nullable();
            $table->date('manager_approved_signature')->nullable();
            $table->unsignedInteger('document_id')->nullable();
            $table->unsignedInteger('sale_note_id')->nullable();
            
            $table->foreign('master_id')->references('id')->on('persons');
            $table->foreign('customer_id')->references('id')->on('persons'); 
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('establishment_id')->references('id')->on('establishments');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('sale_note_id')->references('id')->on('sale_notes')->onDelete('cascade');

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
        //
        Schema::dropIfExists('order_concretes');
    }
}
