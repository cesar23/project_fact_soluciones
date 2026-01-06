<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ordens')) {
            Schema::create('ordens', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('table_id');
                $table->foreign('table_id')->references('id')->on('tables');
                $table->unsignedInteger('status_orden_id');
                $table->foreign('status_orden_id')->references('id')->on('status_orden');
                $table->timestamps();
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
        Schema::dropIfExists('ordens');
    }
}
