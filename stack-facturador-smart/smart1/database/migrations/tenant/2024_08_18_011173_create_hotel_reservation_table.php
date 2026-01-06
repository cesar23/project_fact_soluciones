<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011173_create_hotel_reservation_table


class CreateHotelReservationTable extends Migration
{


    public function up(): void
    {
        if (!Schema::hasTable('hotel_reservations')) {
            Schema::create('hotel_reservations', function (Blueprint $table) {
                $table->increments('id');
                $table->date('reservation_date');
                $table->string('reservation_method', 50);
                $table->string('name', 200);
                $table->string('document', 20)->nullable();
                $table->enum('sex', ['F', 'M'])->nullable();
                $table->integer('age')->nullable();
                $table->unsignedInteger('room_id');
                $table->integer('number_of_nights');
                $table->string('breakfast_type', 50)->nullable();
                $table->date('check_in_date');
                $table->date('check_out_date');
                $table->time('arrival_time')->nullable();
                $table->boolean('transfer_in')->default(false);
                $table->boolean('transfer_out')->default(false);
                $table->decimal('nightly_rate', 10, 2);
                $table->decimal('total_amount', 10, 2);
                $table->string('agency', 200)->nullable();
                $table->string('contact', 200)->nullable();
                $table->string('created_by', 100);
                $table->text('observations')->nullable();
                
                $table->timestamps();

            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservations');
    }
}
