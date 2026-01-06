<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigNoStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
      

        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_no_stock')->default(false);
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('show_no_stock');
        });
    }
}
