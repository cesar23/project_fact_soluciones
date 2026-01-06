<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYapePlinConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

        
            Schema::table("configurations", function (Blueprint $table)  {
               $table->boolean("yape_qr_documents")->default(false);
               $table->boolean("yape_qr_sale_notes")->default(false);
               $table->boolean("yape_qr_quotations")->default(false);
               $table->boolean("plin_qr_documents")->default(false);
               $table->boolean("plin_qr_sale_notes")->default(false);
               $table->boolean("plin_qr_quotations")->default(false);

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
            $table->dropColumn("yape_qr_documents");
            $table->dropColumn("yape_qr_sale_notes");
            $table->dropColumn("yape_qr_quotations");
            $table->dropColumn("plin_qr_documents");
            $table->dropColumn("plin_qr_sale_notes");
            $table->dropColumn("plin_qr_quotations");
         
        });
    }
}
