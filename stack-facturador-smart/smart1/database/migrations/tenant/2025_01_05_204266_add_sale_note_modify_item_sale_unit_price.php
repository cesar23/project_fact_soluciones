<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class AddSaleNoteModifyItemSaleUnitPrice extends Migration
{
    public function up()
    {

        
        Schema::table('sale_note_items', function (Blueprint $table) {
            $table->boolean('modify_sale_unit_price')->default(false);
        });

    


    }

    public function down()
    {
        Schema::table('sale_note_items', function (Blueprint $table) {
            $table->dropColumn('modify_sale_unit_price');
        });

    }


}