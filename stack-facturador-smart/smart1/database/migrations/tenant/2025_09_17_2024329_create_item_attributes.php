<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_17_2024329_create_item_.attributes
class CreateItemAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('item_attributes', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('item_id');
                $table->unsignedInteger('cat_line_id')->nullable();
                $table->unsignedInteger('cat_ingredient_id')->nullable();
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
        Schema::dropIfExists('item_attributes');
    }
}
