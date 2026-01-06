<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberQuotationOrderNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        if (!Schema::hasColumn("quotations", "number")) {
            Schema::table("quotations", function (Blueprint $table)  {
                $table->string("number")->nullable();
            });
        }
        
        if (!Schema::hasColumn("order_notes", "number")) {
            Schema::table("order_notes", function (Blueprint $table) {
                $table->string("number")->nullable();
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

     
        if (Schema::hasColumn("quotations", "number")) {
            Schema::table("quotations", function (Blueprint $table)  {
                $table->dropColumn("number");
            });
        }
        
        if(Schema::hasColumn("order_notes", "number")) {
            Schema::table("order_notes", function (Blueprint $table)  {
                $table->dropColumn("number");
            });
        }
    
    }
}
