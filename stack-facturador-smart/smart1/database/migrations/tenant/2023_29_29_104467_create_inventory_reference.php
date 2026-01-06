<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

              if (!Schema::hasTable('inventory_references')) {
                Schema::create('inventory_references', function(Blueprint $table){
                    $table->increments('id');
                    $table->string('code', 10);
                    $table->string('description');
                });
            }

            if (!Schema::hasColumn('inventories', 'inventory_reference_id')) {
                Schema::table("inventories", function (Blueprint $table)  {
                    $table->unsignedInteger("inventory_reference_id")->nullable()->after("warehouse_id");
                    $table->foreign("inventory_reference_id")->references("id")->on("inventory_references")->onDelete("cascade");
                });
            }

            if (!Schema::hasColumn('dispatches', 'inventory_reference_id')) {
                Schema::table("dispatches", function (Blueprint $table)  {
                    $table->unsignedInteger("inventory_reference_id")->nullable()->after("establishment_id");
                    $table->foreign("inventory_reference_id")->references("id")->on("inventory_references")->onDelete("cascade");
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
            
            Schema::table("inventories", function (Blueprint $table)  {
                $table->dropForeign(["inventory_reference_id"]);
                $table->dropColumn("inventory_reference_id");
            });
    
            Schema::table("dispatches", function (Blueprint $table)  {
                $table->dropForeign(["inventory_reference_id"]);
                $table->dropColumn("inventory_reference_id");
            });
    
            Schema::dropIfExists('inventory_references');
       

      
    }
}
