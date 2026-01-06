<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldToNulleablesTransport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_note_transports', function (Blueprint $table) {

            $table->string('origin_district_id')->nullable()->change();
            $table->json('destinatation_district_id')->nullable()->change();
            $table->string('passenger_manifest')->nullable()->change();
        });

        Schema::table('document_transports', function (Blueprint $table) {
            $table->string('origin_district_id')->nullable()->change();
            $table->json('destinatation_district_id')->nullable()->change();
            $table->string('passenger_manifest')->nullable()->change();
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('sale_note_transports', function (Blueprint $table) {
        //     $table->string('origin_district_id')->nullable(false)->change();
        //     $table->json('destinatation_district_id')->nullable(false)->change();
        //     $table->string('passenger_manifest')->nullable(false)->change();
        // });
        

        
    }
}
