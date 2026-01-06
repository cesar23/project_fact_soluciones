<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddressTransportDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('document_transport_dispatches', function (Blueprint $table) {
            $table->json('origin_district_id')->nullable();
            $table->string('origin_address')->nullable();
            $table->json('destinatation_district_id')->nullable();
            $table->string('destinatation_address')->nullable();
        });

    
        Schema::table('sale_note_transport_dispatches', function (Blueprint $table) {
            $table->json('origin_district_id')->nullable();
            $table->string('origin_address')->nullable();
            $table->json('destinatation_district_id')->nullable();
            $table->string('destinatation_address')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
        Schema::table('document_transport_dispatches', function (Blueprint $table) {
            $table->dropColumn('origin_district_id');
            $table->dropColumn('origin_address');
            $table->dropColumn('destinatation_district_id');
            $table->dropColumn('destinatation_address');
        });

        Schema::table('sale_note_transport_dispatches', function (Blueprint $table) {
            $table->dropColumn('origin_district_id');
            $table->dropColumn('origin_address');
            $table->dropColumn('destinatation_district_id');
            $table->dropColumn('destinatation_address');
        });
    }
}
