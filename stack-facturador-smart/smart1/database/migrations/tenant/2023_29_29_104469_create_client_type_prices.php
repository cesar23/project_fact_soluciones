<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTypePrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::create('client_type_prices', function(Blueprint $table){
                $table->increments('id');
                $table->unsignedInteger('person_type_id');
                $table->unsignedInteger('item_id');
                $table->decimal('price', 12, 2);
                $table->timestamps();

                $table->foreign('person_type_id')->references('id')->on('person_types')->onDelete('cascade');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
           
    
            Schema::dropIfExists('client_type_prices');
       

      
    }
}
