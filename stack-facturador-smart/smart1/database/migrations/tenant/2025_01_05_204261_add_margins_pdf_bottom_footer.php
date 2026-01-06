<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204261_add_margins_pdf_bottom_footer

class AddMarginsPdfBottomFooter extends Migration
{
    /**
     * Run the migrations.


     *
     * @return void
     */
    public function up()
    {


        Schema::table('configurations', function (Blueprint $table) {
            $table->string('add_margin_bottom')->default(0);
            $table->string('footer_margin')->default(2);
        });






    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('add_margin_bottom');
            $table->dropColumn('footer_margin');
        });


      


    }
}
