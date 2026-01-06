<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMallConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('store_id')->nullable();
            $table->string('store_name')->nullable();
            $table->string('mall_id')->nullable();
            $table->string('store_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_config');
    }
}
