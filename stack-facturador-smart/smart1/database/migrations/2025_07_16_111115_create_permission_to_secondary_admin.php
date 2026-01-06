<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_111115_create_permission_to_secondary_admin


class CreatePermissionToSecondaryAdmin extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {
        if (!Schema::hasTable('permission_to_secondary_admin')) {
            Schema::create('permission_to_secondary_admin', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('permission');
                $table->string('value');
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users');
            });
        }



    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_to_secondary_admin');
    }
}
