<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204276_add_configuration_show_internal_id_list

class AddConfigurationShowInternalIdList extends Migration
{
    public function up()
    {

        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_internal_id_list')->default(false);
        });

    


    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('show_internal_id_list');
        });

    }


}