<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SecondaryDriverTransportDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->unsignedInteger('secondary_driver_id')->nullable()->after('driver_id');
            $table->unsignedInteger('secondary_transport_id')->nullable()->after('transport_id');
            $table->json('secondary_driver')->nullable();
            $table->json('secondary_transport_data')->nullable();
            $table->foreign('secondary_driver_id')->references('id')->on('drivers');
            $table->foreign('secondary_transport_id')->references('id')->on('transports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn('secondary_driver');
            $table->dropColumn('secondary_transport_data');
            $table->dropForeign(['secondary_driver_id']);
            $table->dropForeign(['secondary_transport_id']);
            $table->dropColumn('secondary_driver_id');
            $table->dropColumn('secondary_transport_id');
        });
    
    }
}
