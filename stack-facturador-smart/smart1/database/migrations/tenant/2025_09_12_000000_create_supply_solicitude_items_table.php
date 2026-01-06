<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplySolicitudeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_solicitude_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('supply_concept_id');
            $table->unsignedBigInteger('supply_solicitude_id');
            $table->tinyInteger('review_status')->default(0);
            $table->string('pipe_diameter_water')->nullable();
            $table->tinyInteger('property_type_water')->nullable();
            $table->string('pipe_length_water')->nullable();
            $table->string('soil_type_water')->nullable();
            $table->string('pipe_diameter_drainage')->nullable();
            $table->tinyInteger('property_type_drainage')->nullable();
            $table->string('pipe_length_drainage')->nullable();
            $table->string('soil_type_drainage')->nullable();
            $table->tinyInteger('water')->nullable();
            $table->tinyInteger('drainage')->nullable();
            $table->string('connection_number_water')->nullable();
            $table->string('connection_number_drainage')->nullable();
            $table->date('connection_date')->nullable();
            $table->unsignedInteger('inspector_operator_id')->nullable();
            $table->unsignedInteger('installer_operator_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamp('modification_date')->useCurrent();
            $table->json('photos')->nullable();
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
        Schema::dropIfExists('supply_solicitude_items');
    }
}