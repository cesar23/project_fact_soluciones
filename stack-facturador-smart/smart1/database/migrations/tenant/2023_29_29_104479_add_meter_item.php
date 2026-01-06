<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMeterItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
       
        Schema::table('items', function (Blueprint $table) {
                $table->decimal('meter', 12, 2)->nullable()->after('unit_type_id');
        });
           
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
                
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('meter');
            });
            
    
      
    }
}
