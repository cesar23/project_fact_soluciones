<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCanceledBillOfExchange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

        
            Schema::table("bills_of_exchange", function (Blueprint $table)  {
                $table->boolean("total_canceled")->default(false);
            });
        
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table("bill_of_exchange", function (Blueprint $table)  {
            $table->dropColumn("total_canceled");
        });
    }
}
