<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153345_add_info_to_automatic_items
class AddInfoToAutomaticItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_automatic_items', function (Blueprint $table) {
            $table->string('info')->nullable()->after('is_credit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_automatic_items', function (Blueprint $table) {
            $table->dropColumn('info');
        });
    }
}
