<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfigurationsDefaultOrderDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations',function (Blueprint $table){
            $table->enum('default_order_document',['01','03','80'])->default('80');
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('configurations',function (Blueprint $table){
            $table->dropColumn('default_order_document');
        });
       
    }
}
