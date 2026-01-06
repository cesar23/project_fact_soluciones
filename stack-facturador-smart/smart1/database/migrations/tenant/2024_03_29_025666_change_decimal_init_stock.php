<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDecimalInitStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('init_stock',function(Blueprint $table){
            $table->decimal('stock',12,4)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('init_stock','stock')){
            Schema::table('init_stock',function(Blueprint $table){
                $table->decimal('stock',8,2)->change();
            });
        }
        
    }
}
