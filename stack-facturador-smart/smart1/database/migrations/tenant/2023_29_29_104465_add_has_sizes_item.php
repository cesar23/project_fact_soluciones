<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasSizesItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

        
            Schema::table("items", function (Blueprint $table)  {
               $table->boolean("has_sizes")->default(false);

            });

            Schema::table("item_sizes",function(Blueprint $table){
                $table->unsignedInteger('warehouse_id')->nullable()->after('id');
                //cambia la columna "establishment_id" de no nulo a nulo
                $table->unsignedInteger('establishment_id')->nullable(true)->change();
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            });
        
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table("items", function (Blueprint $table)  {
            $table->dropColumn("has_sizes");
        });

        Schema::table("item_sizes",function(Blueprint $table){
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
            $table->unsignedInteger('establishment_id')->nullable(false)->change();
        });
    }
}
