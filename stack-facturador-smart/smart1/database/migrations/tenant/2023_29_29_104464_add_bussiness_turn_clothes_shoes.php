<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBussinessTurnClothesShoes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        DB::table('business_turns')->insert([
            'name' => 'Venta de ropa y calzado',
            'active' => false,
            'value' => 'clothes_shoes',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Schema::create('item_sizes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('establishment_id');
            $table->string('size', 50);
            $table->bigInteger('stock')->default(0);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('establishment_id')->references('id')->on('establishments');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::table('business_turns')->where('value', 'clothes_shoes')->delete();

        Schema::dropIfExists('item_sizes');
    }
}
