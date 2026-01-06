<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204293_add_active_establishment
class AddActiveEstablishment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('establishments', function (Blueprint $table) {
            $table->boolean('active')->default(true);
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
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('active');
        });
      
    }
}
