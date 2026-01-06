<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandAndModelVehicleTechnicalService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technical_service_cars', function (Blueprint $table) {
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technical_service_cars', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('model');
        });
        
        
    }
}
