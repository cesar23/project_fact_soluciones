<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseAffectationIgv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('configurations', function(Blueprint $table){
                $table->string('purchase_affectation_igv_type_id')->nullable();
                $table->foreign('purchase_affectation_igv_type_id')->references('id')->on('cat_affectation_igv_types');

            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
    
            Schema::table('configurations', function(Blueprint $table){
                $table->dropForeign(['purchase_affectation_igv_type_id']);
                $table->dropColumn('purchase_affectation_igv_type_id');

            });

      
    }
}
