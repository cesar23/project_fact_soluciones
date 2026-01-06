<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFeeBillExchangeDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('bills_of_exchange_documents', function (Blueprint $table) {
            $table->boolean('is_fee')->default(false);
            $table->unsignedInteger('fee_id')->nullable();
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
        Schema::table('bills_of_exchange_documents', function (Blueprint $table) {
            $table->dropColumn('is_fee');
            $table->dropColumn('fee_id');
        });
      
    }
}
