<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_01_01_111112_add_minute_to_validate

class AddMinuteToValidate extends Migration
{
    /**
     * Run the migrations.



     *
     * @return void
     */

    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->integer('minute_to_validate')->default(10);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('minute_to_validate');
        });



    }
}
