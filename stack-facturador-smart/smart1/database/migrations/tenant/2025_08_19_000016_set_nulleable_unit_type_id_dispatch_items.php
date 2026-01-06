<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000016_set_nulleable_unit_type_id_dispatch_items
class SetNulleableUnitTypeIdDispatchItems extends Migration       
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('dispatch_items', 'unit_type_id')) {
            Schema::table('dispatch_items', function (Blueprint $table) {
                $table->string('unit_type_id')->nullable()->change();
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
        
    }
}
