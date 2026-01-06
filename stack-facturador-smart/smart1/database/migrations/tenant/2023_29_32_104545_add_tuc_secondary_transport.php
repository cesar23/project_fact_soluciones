<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTucSecondaryTransport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transports',function (Blueprint $table){
            $table->string('tuc_secondary')->nullable()->after('tuc');
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
        Schema::table('transports',function (Blueprint $table){
            $table->dropColumn('tuc_secondary');
        });
     
      
       
    }
}
