<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_17_2024328_create_line_attribute_item
class CreateLineAttributeItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('cat_line', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('image')->nullable();
                $table->boolean('active')->default(true);
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
        Schema::dropIfExists('cat_line');
    }
}
