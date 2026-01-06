<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204314_add_bill_of_exchange_endorsement

class AddBillOfExchangeEndorsement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('bills_of_exchange', function (Blueprint $table) {
            $table->string('endorsement_name')->nullable();
            $table->string('endorsement_number')->nullable();
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
        Schema::table('bills_of_exchange', function (Blueprint $table) {
            $table->dropColumn('endorsement_name');
            $table->dropColumn('endorsement_number');
        });
    }
}
