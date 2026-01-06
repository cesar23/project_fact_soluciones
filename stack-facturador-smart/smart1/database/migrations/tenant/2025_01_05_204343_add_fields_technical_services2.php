<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204343_add_fields_technical_services2
class AddFieldsTechnicalServices2 extends Migration
{
    public function up()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            $table->unsignedInteger('state_id')->nullable();
            $table->foreign('state_id')->references('id')->on('technical_services_states');
        });
    }

    public function down()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            $table->dropColumn('state_id');
        });
    }
}
