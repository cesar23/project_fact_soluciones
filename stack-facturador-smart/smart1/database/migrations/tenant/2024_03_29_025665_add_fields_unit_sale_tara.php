<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsUnitSaleTara extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations',function(Blueprint $table){
            $table->boolean('count_unit_sale_note')->default(false);
            $table->boolean('discount_unit_document')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('configurations','count_unit_sale_note')){
            Schema::table('configurations',function(Blueprint $table){
                $table->dropColumn('count_unit_sale_note');
            });
        }
        if(Schema::hasColumn('configurations','discount_unit_document')){
            Schema::table('configurations',function(Blueprint $table){
                $table->dropColumn('discount_unit_document');
            });
        }
    }
}
