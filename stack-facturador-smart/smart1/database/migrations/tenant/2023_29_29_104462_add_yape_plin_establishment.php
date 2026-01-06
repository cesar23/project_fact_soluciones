<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYapePlinEstablishment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

        
            Schema::table("establishments", function (Blueprint $table)  {
                $table->string("yape_owner")->nullable();
                $table->string("yape_number",15)->nullable();
                $table->string("yape_logo")->nullable();
                $table->string("plin_owner")->nullable();
                $table->string("plin_number",15)->nullable();
                $table->string("plin_logo")->nullable();

            });
        
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table("establishments", function (Blueprint $table)  {
            $table->dropColumn("yape_owner");
            $table->dropColumn("yape_number");
            $table->dropColumn("yape_logo");
            $table->dropColumn("plin_owner");
            $table->dropColumn("plin_number");
            $table->dropColumn("plin_logo");
        });
    }
}
