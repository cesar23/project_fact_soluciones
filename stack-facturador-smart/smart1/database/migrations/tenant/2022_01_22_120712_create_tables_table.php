<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tables')) {
            Schema::create('tables', function (Blueprint $table) {
                $table->increments('id');
                $table->string('number');
                $table->unsignedInteger('area_id');
                $table->foreign('area_id')->references('id')->on('areas');
                $table->unsignedInteger('status_table_id');
                $table->foreign('status_table_id')->references('id')->on('status_table');
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
        Schema::dropIfExists('tables');
    }
}
