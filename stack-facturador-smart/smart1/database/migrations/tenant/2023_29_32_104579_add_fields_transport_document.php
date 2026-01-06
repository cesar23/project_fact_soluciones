<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsTransportDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_transports', function (Blueprint $table) {
            $table->string('bus_number')->nullable()->after('passenger_fullname');
            $table->string('passenger_age')->nullable()->after('passenger_fullname');
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
            $table->dropColumn('bus_number');
            $table->dropColumn('passenger_age');
        });
        

        
    }
}
