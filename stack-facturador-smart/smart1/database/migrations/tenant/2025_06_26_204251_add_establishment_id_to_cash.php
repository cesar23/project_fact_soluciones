<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204251_add_establishment_id_to_cash
class AddEstablishmentIdToCash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash', function (Blueprint $table) {
            $table->unsignedInteger('establishment_id')->nullable();
            $table->string('currency_type_id')->nullable();
            $table->foreign('establishment_id')->references('id')->on('establishments');
            $table->foreign('currency_type_id')->references('id')->on('cat_currency_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash', function (Blueprint $table) {
            $table->dropColumn('establishment_id');
            $table->dropColumn('currency_type_id');
            $table->dropForeign(['establishment_id']);
            $table->dropForeign(['currency_type_id']);
        });
    }
}
