<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn("configurations", "quotation_projects")) {
            Schema::table("configurations", function (Blueprint $table) {
                $table->boolean('quotation_projects')->default(false);
            });
        }
 
       if(!Schema::hasTable("quotation_projects")){
        Schema::create('quotation_projects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quotation_id')->nullable();
            $table->string("project_name",70);
            $table->string("atention",70)->nullable();
            $table->string("direction",70)->nullable();
            $table->string("email",70)->nullable();
            $table->string("telephone",70)->nullable();
            $table->timestamps();
        });
       }

      if(!Schema::hasTable('quotation_project_items')){
        Schema::create('quotation_project_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quotation_item_id')->nullable();
            $table->string("disponibility",70)->nullable();
            $table->string("header",70);
       
            $table->timestamps();
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('quotation_projects');
        });
        Schema::dropIfExists('quotation_projects');
        Schema::dropIfExists('quotation_project_items');
   
    }
}
