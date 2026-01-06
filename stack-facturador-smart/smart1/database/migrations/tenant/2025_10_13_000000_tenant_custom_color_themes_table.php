<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantCustomColorThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_color_themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('primary', 7); // HEX color #RRGGBB
            $table->string('secondary', 7);
            $table->string('tertiary', 7);
            $table->string('quaternary', 7);
            $table->boolean('is_light')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_color_themes');
    }
}
