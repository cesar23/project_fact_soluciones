<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusOrdensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ordens', 'status')) {
            Schema::table('ordens', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->nullable()->after('status_orden_id');
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
        if (Schema::hasColumn('ordens', 'status')) {
            Schema::table('ordens', function(Blueprint $table) {
                $table->dropColumn('status');
           });
        }
    }
}
