<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGekawaByEstablishment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->string('gekawa_url')->nullable()->default("https://gekawa.com");
            $table->string('gekawa_1')->nullable();
            $table->string('gekawa_2')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('gekawa_url');
            $table->dropColumn('gekawa_1');
            $table->dropColumn('gekawa_2');
        });
    }
}
