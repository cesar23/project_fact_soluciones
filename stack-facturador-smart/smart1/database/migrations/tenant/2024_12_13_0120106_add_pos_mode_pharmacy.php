<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2024_12_13_0120106_add_pos_mode_pharmacy

class AddPosModePharmacy extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'pos_mode_pharmacy')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('pos_mode_pharmacy')->default(false);
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('pos_mode_pharmacy');
        });
    }
}
