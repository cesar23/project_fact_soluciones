<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204352_add_fields_to_hotel_reservations
class AddFieldsToHotelReservations extends Migration
{
    public function up()
    {
        Schema::table('hotel_reservations', function (Blueprint $table) {
            $table->time('departure_time')->nullable();
            $table->tinyInteger('duration_hours')->nullable();
            $table->string('custom_telephone')->nullable();

            
        });
    }

    public function down()
    {
        Schema::table('hotel_reservations', function (Blueprint $table) {
            $table->dropColumn('departure_time');
            $table->dropColumn('duration_hours');
            $table->dropColumn('custom_telephone');
        });
    }
}
