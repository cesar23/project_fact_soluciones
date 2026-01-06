<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrateSystemUserLevelModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::create('integrate_user_levels_module', function(Blueprint $table){
                $table->unsignedInteger('integrate_user_type_id');
                $table->unsignedInteger('module_level_id');
                $table->foreign('integrate_user_type_id')->references('id')->on('integrate_user_type');
                $table->foreign('module_level_id')->references('id')->on('module_levels');

            });
     
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
            Schema::dropIfExists('integrate_user_levels_module');
       
      
    }
}
