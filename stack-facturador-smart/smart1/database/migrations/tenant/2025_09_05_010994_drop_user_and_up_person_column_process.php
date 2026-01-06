<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUserAndUpPersonColumnProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_process', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('supply_process', function (Blueprint $table) {
            $table->unsignedInteger('person_id')->nullable()->after('record');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
    }

    

    
}
