<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWebsiteIdTypeCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies',function (Blueprint $table){
            $table->unsignedInteger('website_id')->nullable()->change();
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('companies',function (Blueprint $table){
          $table->tinyInteger('website_id')->nullable()->change();
      });
      

    }
}
