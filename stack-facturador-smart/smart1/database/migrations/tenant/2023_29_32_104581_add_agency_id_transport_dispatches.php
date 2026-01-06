<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgencyIdTransportDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_transports', function (Blueprint $table) {
            $table->unsignedInteger('agency_origin_id')->nullable();
            $table->unsignedInteger('agency_destination_id')->nullable();
        });

        Schema::table('document_transport_dispatches', function (Blueprint $table) {
            $table->unsignedInteger('agency_origin_id')->nullable();
            $table->unsignedInteger('agency_destination_id')->nullable();
        });

        Schema::table('sale_note_transports', function (Blueprint $table) {
            $table->unsignedInteger('agency_origin_id')->nullable();
            $table->unsignedInteger('agency_destination_id')->nullable();
        });
        Schema::table('sale_note_transport_dispatches', function (Blueprint $table) {
            $table->unsignedInteger('agency_origin_id')->nullable();
            $table->unsignedInteger('agency_destination_id')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_transports', function (Blueprint $table) {
            $table->dropColumn('agency_origin_id');
            $table->dropColumn('agency_destination_id');
        });

        Schema::table('document_transport_dispatches', function (Blueprint $table) {
            $table->dropColumn('agency_origin_id');
            $table->dropColumn('agency_destination_id');
        });

        Schema::table('sale_note_transports', function (Blueprint $table) {
            $table->dropColumn('agency_origin_id');
            $table->dropColumn('agency_destination_id');
        });

        Schema::table('sale_note_transport_dispatches', function (Blueprint $table) {
            $table->dropColumn('agency_origin_id');
            $table->dropColumn('agency_destination_id');
        });
        
    }
}
