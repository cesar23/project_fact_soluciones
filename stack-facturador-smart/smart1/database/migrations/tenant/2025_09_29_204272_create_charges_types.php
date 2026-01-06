<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_29_204272_create_charges_types.php

class CreateChargesTypes extends Migration
{
    public function up()
    {

        Schema::create('charges_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->decimal('charge_value', 12, 2);
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
        Schema::dropIfExists('charges_types');

    }


}