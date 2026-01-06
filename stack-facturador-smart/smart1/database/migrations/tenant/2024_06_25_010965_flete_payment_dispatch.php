<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FletePaymentDispatch extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->string('flete_payment_number')->nullable();
            $table->string('flete_payment_name')->nullable();
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
            $table->dropColumn('flete_payment_number');
            $table->dropColumn('flete_payment_name');
        });
    }
}
