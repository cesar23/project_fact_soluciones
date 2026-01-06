<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_29_204273_create_charges_type_items.php

class CreateChargesTypeItems extends Migration
{
    public function up()
    {

        Schema::create('charges_type_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedInteger('brand_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('charges_type_id');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('charges_type_id')->references('id')->on('charges_types');
            $table->timestamps();
        });




    }

    public function down()
    {
        Schema::dropIfExists('charges_type_items');

    }


}