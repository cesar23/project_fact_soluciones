<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_17_2024327_add_to_configuration_show_zone_instead_subject
class AddToConfigurationShowZoneInsteadSubject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('show_zone_instead_subject')->default(false);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('show_zone_instead_subject');
        });
    }
}
