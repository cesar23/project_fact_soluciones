<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 2024_08_18_011180_add_active_hotel_reservation



class AddActiveHotelReservation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('hotel_reservations', 'active')) {
            Schema::table('hotel_reservations', function (Blueprint $table) {
                $table->boolean('active')->default(true)->after('observations');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotel_reservations', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
}
