<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeOrdenItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orden_item', 'time')) {
            Schema::table('orden_item', function (Blueprint $table) {
                $table->time('time')->nullable()->after('status_orden_id');
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
        if (Schema::hasColumn('orden_item', 'time')) {
            Schema::table('orden_item', function (Blueprint $table) {
                $table->dropColumn('time');
            });
        }
    }
}
