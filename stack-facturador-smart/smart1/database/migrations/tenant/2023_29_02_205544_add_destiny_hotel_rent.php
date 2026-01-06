<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDestinyHotelRent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_rents', function (Blueprint $table) {
            $table->string('destiny')->nullable()->after('notes');
            $table->unsignedInteger('document_id')->nullable()->after('notes');
            $table->unsignedInteger('sale_note_id')->nullable()->after('notes');
            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('sale_note_id')->references('id')->on('sale_notes');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotel_rents', function (Blueprint $table) {
            $table->dropColumn('destiny');
            $table->dropForeign(['document_id', 'sale_note_id']);
            $table->dropColumn('document_id');
            $table->dropColumn('sale_note_id');
        });
    }
}
