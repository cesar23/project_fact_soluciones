<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateItemLotsGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('item_lots_group', 'state_id')) {
            Schema::table('item_lots_group', function (Blueprint $table) {
                $table->unsignedInteger('state_id')->nullable()->after('item_id');
                $table->foreign('state_id')->references('id')->on('item_lots_group_state');
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
        if (Schema::hasColumn('item_lots_group', 'state_id')) {
            Schema::table('item_lots_group', function (Blueprint $table) {
                $table->dropForeign(['state_id']);
                $table->dropColumn('state_id');
            });
        }
    }
}
