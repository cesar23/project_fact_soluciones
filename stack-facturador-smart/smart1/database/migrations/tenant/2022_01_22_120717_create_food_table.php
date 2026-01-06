<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('foods')) {
            Schema::create('foods', function (Blueprint $table) {
                $table->increments('id');
                $table->string('description');
                $table->string('code', 20)->nullable();
                $table->decimal('price', 12, 2);
                $table->boolean('active')->default(1);
                $table->unsignedInteger('category_food_id');
                $table->string('image')->nullable();
                $table->foreign('category_food_id')->references('id')->on('categories');
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
        Schema::dropIfExists('foods');
    }
}
