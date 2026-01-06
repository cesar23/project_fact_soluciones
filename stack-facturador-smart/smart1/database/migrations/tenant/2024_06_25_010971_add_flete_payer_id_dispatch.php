<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFletePayerIdDispatch extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->unsignedInteger('flete_payer_id')->nullable();
            $table->foreign('flete_payer_id')->references('id')->on('persons');
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
            $table->dropForeign(['flete_payer_id']);
            $table->dropColumn('flete_payer_id');
        });
    }
}
