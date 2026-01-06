<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204280_add_properties_orders

class AddPropertiesOrders extends Migration 
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_delivery')->default(true);
            $table->string('location_delivery', 350)->nullable();
            $table->string('street', 350)->nullable();
            $table->string('number', 350)->nullable();
            $table->string('reference', 350)->nullable();
        });
    


    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_delivery');
            $table->dropColumn('location_delivery');
            $table->dropColumn('street');
            $table->dropColumn('number');
            $table->dropColumn('reference');
        });
    }


}