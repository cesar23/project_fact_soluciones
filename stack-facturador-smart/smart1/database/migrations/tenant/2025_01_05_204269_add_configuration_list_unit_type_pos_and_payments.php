<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204269_add_configuration_list_unit_type_pos_and_payments

class AddConfigurationListUnitTypePosAndPayments extends Migration
{
    public function up()
    {

        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('list_unit_type_pos')->default(false);
            $table->boolean('list_payments_pos')->default(false);
        });

    


    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('list_unit_type_pos');
            $table->dropColumn('list_payments_pos');
        });

    }


}