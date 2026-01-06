<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204283_add_type_person_user_client

class AddTypePersonUserClient extends Migration 
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('person_type_id')->nullable();
            $table->foreign('person_type_id')->references('id')->on('person_types');
        });
    


    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('person_type_id');
        });
    }


}