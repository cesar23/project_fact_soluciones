<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddPlateNumberToDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //if not exists
        if (!Schema::hasColumn('dispatches', 'plate_number')) {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->string('plate_number',8)->nullable();
                
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('dispatches', 'plate_number')) {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->dropColumn('plate_number');
            });
        }
    
    }
}
