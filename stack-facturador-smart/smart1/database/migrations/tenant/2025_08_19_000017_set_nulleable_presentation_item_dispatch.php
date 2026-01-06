<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000017_set_nulleable_presentation_item_dispatch
class SetNulleablePresentationItemDispatch extends Migration       
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('dispatch_items', 'presentation_name')) {
            Schema::table('dispatch_items', function (Blueprint $table) {
                $table->string('presentation_name')->nullable()->change();
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
