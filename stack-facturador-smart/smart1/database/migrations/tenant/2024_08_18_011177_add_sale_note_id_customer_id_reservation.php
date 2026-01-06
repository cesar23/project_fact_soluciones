<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 2024_08_18_011177_add_sale_note_id_customer_id_reservation



class AddSaleNoteIdCustomerIdReservation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('hotel_reservations', 'customer_id') && !Schema::hasColumn('hotel_reservations', 'sale_note_id')) {
            Schema::table('hotel_reservations', function (Blueprint $table) {
                $table->unsignedInteger('customer_id')->nullable()->after('reservation_method')->comment('ID del cliente');
                $table->unsignedInteger('sale_note_id')->nullable()->after('customer_id')->comment('ID de la nota de venta');
                $table->foreign('customer_id')->references('id')->on('persons')->onDelete('set null');
                $table->foreign('sale_note_id')->references('id')->on('sale_notes')->onDelete('set null');
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
        if (Schema::hasColumn('hotel_reservations', 'customer_id')) {
            Schema::table('hotel_reservations', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }
        if (Schema::hasColumn('hotel_reservations', 'sale_note_id')) {
            Schema::table('hotel_reservations', function (Blueprint $table) {
                $table->dropForeign(['sale_note_id']);
                $table->dropColumn('sale_note_id');
            });
        }
    }
}
