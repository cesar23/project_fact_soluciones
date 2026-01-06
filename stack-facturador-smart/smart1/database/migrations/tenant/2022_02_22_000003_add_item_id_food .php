<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemIdFood extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('foods', 'item_id')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->unsignedInteger('item_id')->nullable()->after('id');
                $table->foreign('item_id')->references('id')->on('items');
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
        if (Schema::hasColumn('foods', 'item_id')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->dropColumn('item_id');
            });
        }
    }
}
