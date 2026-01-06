<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddMaxAttemptPseSystem extends Migration
{
    /**
     * Run the migrations.


     *
     * @return void
     */

    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->integer('max_attempt_pse')->default(5);
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
            $table->dropColumn('max_attempt_pse');
        });


    }
}
