<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReopenCashConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

        
            Schema::table("configurations", function (Blueprint $table)  {
               $table->boolean("reopen_cash")->default(false);

            });

            Schema::table("items_sellers", function (Blueprint $table)  {
                $table->unsignedInteger("quotation_item_id")->nullable()->after("sale_note_item_id");
                $table->foreign("quotation_item_id")->references("id")->on("quotation_items")->onDelete("cascade");

             });
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table("configurations", function (Blueprint $table)  {
            $table->dropColumn("reopen_cash");
        });

        Schema::table("items_sellers", function (Blueprint $table)  {
            
            $table->dropForeign(["quotation_item_id"]);
            $table->dropColumn("quotation_item_id");

        });

      
    }
}
