<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddAreaPrinterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('areas', 'printer')) {
            Schema::table('areas', function (Blueprint $table) {
                $table->string('printer')->nullable();
                $table->integer('copies')->nullable();
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
        if (Schema::hasColumn('areas', 'printer')) {
            Schema::table('areas', function(Blueprint $table) {
                $table->dropColumn('printer');
                $table->dropColumn('copies');
           });
        }
    }
}
