<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPinAreaUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin')->nullable();
            $table->unsignedInteger('worker_type_id')->nullable();
            $table->unsignedInteger('area_id')->nullable();
             $table->foreign('area_id')->references('id')->on('areas');
            $table->foreign('worker_type_id')->references('id')->on('workers_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('pin');
            $table->dropColumn('worker_type_id');
            $table->dropColumn('area_id');
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
}
