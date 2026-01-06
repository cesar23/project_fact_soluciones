<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemBonus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::create('item_bonus', function(Blueprint $table){
                $table->increments('id');
                $table->unsignedInteger('item_id');
                $table->unsignedInteger('item_bonus_id');
                $table->decimal('quantity', 12, 2);
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
                $table->foreign('item_bonus_id')->references('id')->on('items')->onDelete('cascade');
            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
           
    
            Schema::dropIfExists('item_bonus');
       

      
    }
}
