<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemLabelColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('item_label_colors')) {
            Schema::create('item_label_colors', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('item_id');
                $table->unsignedBigInteger('label_color_id');
                $table->timestamps();

                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
                $table->foreign('label_color_id')->references('id')->on('label_colors')->onDelete('cascade');
                
                $table->unique(['item_id', 'label_color_id']);
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
        Schema::dropIfExists('item_label_colors');
    }
}