<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDriverTransportNulleableProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'transports',
            function (Blueprint $table) {
                $table->string('brand')->nullable()->change(true);
                $table->string('model')->nullable()->change(true);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'transports',
            function (Blueprint $table) {
                $table->string('brand')->nullable()->change(false);
                $table->string('model')->nullable()->change(false);
            }
        );
    }
}
