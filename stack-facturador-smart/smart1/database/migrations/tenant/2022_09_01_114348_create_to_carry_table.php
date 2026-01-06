<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateToCarryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ordens', 'to_carry')) {
            Schema::table('ordens', function (Blueprint $table) {
                $table->boolean('to_carry')->default(false)->nullable();
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
        if (Schema::hasColumn('ordens', 'to_carry')) {
            Schema::table('ordens', function(Blueprint $table) {
                $table->dropColumn('to_carry');
           });
        }
    }
}
