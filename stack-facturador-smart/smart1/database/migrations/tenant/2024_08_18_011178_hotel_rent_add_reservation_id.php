<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 2024_08_18_011178_hotel_rent_add_reservation_id



class HotelRentAddReservationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('hotel_rents', 'reservation_id')) {
            Schema::table('hotel_rents', function (Blueprint $table) {
                $table->unsignedInteger('reservation_id')->nullable()->after('id')->comment('ID de la reserva');
                $table->foreign('reservation_id')->references('id')->on('hotel_reservations')->onDelete('set null');
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
        if (Schema::hasColumn('hotel_rents', 'reservation_id')) {
            Schema::table('hotel_rents', function (Blueprint $table) {
                $table->dropForeign(['reservation_id']);
                $table->dropColumn('reservation_id');
            });
        }
    }
}
