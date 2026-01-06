<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204245_add_message_massive_message_detail

class AddMessageMassiveMessageDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('massive_message_detail', function (Blueprint $table) {
            $table->text('message')->nullable()->comment('Mensaje enviado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('massive_message_detail', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }
}
