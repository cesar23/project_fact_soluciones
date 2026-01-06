<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponsibleLicensePurchase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('purchases', function(Blueprint $table){
                $table->unsignedInteger('license_id')->nullable();
                $table->unsignedInteger('responsible_id')->nullable();
                $table->foreign('license_id')->references('id')->on('purchase_licenses');
                $table->foreign('responsible_id')->references('id')->on('purchase_responsibles');
            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
    
            Schema::table('purchases', function(Blueprint $table){
                $table->dropForeign(['license_id']);
                $table->dropForeign(['responsible_id']);
                $table->dropColumn('license_id');
                $table->dropColumn('responsible_id');

            });

      
    }
}
