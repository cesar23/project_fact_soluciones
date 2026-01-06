<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminSellerCash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
      

        Schema::table('configurations', function (Blueprint $table) {
            
            $table->boolean('admin_seller_cash')->default(false);
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
            $table->dropColumn('admin_seller_cash');
        });
    }
}
