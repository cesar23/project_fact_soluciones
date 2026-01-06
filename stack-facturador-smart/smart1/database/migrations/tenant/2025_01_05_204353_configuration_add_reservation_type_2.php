<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204353_configuration_add_reservation_type_2
class ConfigurationAddReservationType2 extends Migration 
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->tinyInteger('hotel_reservation_type_2')->nullable();

            
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('hotel_reservation_type_2');
        });
    }
}
