<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseLicenseResp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::create('purchase_responsibles', function(Blueprint $table){
                $table->increments('id');
                $table->string('identity_document_type_id');
                $table->string('number');
                $table->string('name');
                $table->char('country_id', 2);
                $table->char('department_id', 2)->nullable();
                $table->char('province_id', 4)->nullable();
                $table->char('district_id', 6)->nullable();
                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->timestamps();
                $table->foreign('identity_document_type_id')->references('id')->on('cat_identity_document_types');
                $table->foreign('country_id')->references('id')->on('countries');
                $table->foreign('department_id')->references('id')->on('departments');
                $table->foreign('province_id')->references('id')->on('provinces');
                $table->foreign('district_id')->references('id')->on('districts');
                $table->boolean('active')->default(true);
            });
            Schema::create('purchase_licenses', function(Blueprint $table){
                $table->increments('id');
                $table->string('license');
                $table->boolean('active')->default(true);
                $table->timestamps();
           
            });
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
            Schema::dropIfExists('purchase_responsibles');
            Schema::dropIfExists('purchase_licenses');
      
    }
}
