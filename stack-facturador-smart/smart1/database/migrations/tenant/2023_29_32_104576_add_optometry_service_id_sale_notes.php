<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptometryServiceIdSaleNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->unsignedInteger('optometry_service_id')->nullable();
            $table->foreign('optometry_service_id')->references('id')->on('optometry_services')->onDelete('cascade');
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
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropForeign(['optometry_service_id']);
            $table->dropColumn('optometry_service_id');
        });

        
    }
}
