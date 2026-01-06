<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMtcMonthSunatPurchase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->string('tracto_carreta')->nullable();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->date('sunat_date')->nullable();
        });
        Schema::table('items', function (Blueprint $table) {
            $table->string('info_link')->nullable();
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
            $table->dropColumn('tracto_carreta');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('sunat_date');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('info_link');
        });
    }
}
