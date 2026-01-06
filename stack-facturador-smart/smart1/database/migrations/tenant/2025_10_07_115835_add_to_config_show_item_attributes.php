<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_10_07_115835_add_to_config_show_item_attributes
class AddToConfigShowItemAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('show_item_attributes')->default(false);
            });
    }

    /**
     * Reverse the migrations.
     *p
     * @return void
     */
    public function down()
    {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('show_item_attributes');
            });
    }
}
