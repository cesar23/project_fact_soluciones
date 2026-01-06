<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204275_add_configuration_type_discount

class AddConfigurationTypeDiscount extends Migration
{
    public function up()
    {

        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('type_discount')->default(false);
        });

    


    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('type_discount');
        });

    }


}