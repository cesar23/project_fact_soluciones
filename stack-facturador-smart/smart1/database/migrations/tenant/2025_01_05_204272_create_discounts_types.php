<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204272_create_discounts_types.php

class CreateDiscountsTypes extends Migration
{
    public function up()
    {

        Schema::create('discounts_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->decimal('discount_value', 12, 2);
            $table->string('image')->nullable();
            $table->string('type')->default('to_items');
            $table->boolean('is_percentage')->default(true);
            $table->boolean('apply_to_all_items')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

    


    }

    public function down()
    {
        Schema::dropIfExists('discounts_types');

    }


}