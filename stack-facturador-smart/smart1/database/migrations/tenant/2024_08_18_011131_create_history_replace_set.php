<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryReplaceSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_replace_set', function (Blueprint $table) {
            $table->increments('id');
            $table->string('internal_id_item');
            $table->string('description_item');
            $table->unsignedInteger('item_id');
            $table->string('internal_id_replace');
            $table->string('description_replace');
            $table->unsignedInteger('replace_id');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_replace_set');
    }
}
