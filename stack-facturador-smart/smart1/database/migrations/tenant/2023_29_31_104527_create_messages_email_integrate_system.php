<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMessagesEmailIntegrateSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages_email_integrate_system', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trigger_event');
            $table->string('comment');
            $table->longText('message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
                                               
        Schema::dropIfExists('messages_email_integrate_system');            
    }
}
