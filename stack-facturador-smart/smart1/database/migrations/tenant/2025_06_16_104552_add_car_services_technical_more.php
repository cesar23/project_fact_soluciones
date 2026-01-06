<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCarServicesTechnicalMore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technical_service_cars', function (Blueprint $table) {

            $table->integer('quantity_pick_and_shovel')->nullable(); // Pico y pala
            $table->string('state_pick_and_shovel')->nullable();
            $table->integer('quantity_pole')->nullable(); // Pertiga
            $table->string('state_pole')->nullable();
            $table->integer('quantity_beacon_light')->nullable(); // Circulina
            $table->string('state_beacon_light')->nullable();
            $table->integer('quantity_pirate_light')->nullable(); // Pirata
            $table->string('state_pirate_light')->nullable();
            $table->integer('quantity_fog_light')->nullable(); // Neblinero
            $table->string('state_fog_light')->nullable();
            $table->integer('quantity_spill_kit')->nullable(); // Kit antiderrame
            $table->string('state_spill_kit')->nullable();
            $table->integer('quantity_first_aid_kit')->nullable(); // Botiquin
            $table->string('state_first_aid_kit')->nullable();
            $table->integer('quantity_battery_cable')->nullable(); // Cable de bateria
            $table->string('state_battery_cable')->nullable();
            $table->integer('quantity_tow_cable')->nullable(); // Cable de remolque
            $table->string('state_tow_cable')->nullable();
            $table->integer('quantity_wheel_chocks')->nullable(); // Tacos
            $table->string('state_wheel_chocks')->nullable();
            $table->integer('quantity_traffic_cones')->nullable(); // Conos
            $table->string('state_traffic_cones')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technical_service_cars', function (Blueprint $table) {
            $table->dropColumn('quantity_pick_and_shovel');
            $table->dropColumn('state_pick_and_shovel');
            $table->dropColumn('quantity_pole');
            $table->dropColumn('state_pole');
            $table->dropColumn('quantity_beacon_light');
            $table->dropColumn('state_beacon_light');
            $table->dropColumn('quantity_pirate_light');
            $table->dropColumn('state_pirate_light');
            $table->dropColumn('quantity_fog_light');
            $table->dropColumn('state_fog_light');
            $table->dropColumn('quantity_spill_kit');
            $table->dropColumn('state_spill_kit');
            $table->dropColumn('quantity_first_aid_kit');
            $table->dropColumn('state_first_aid_kit');
            $table->dropColumn('quantity_battery_cable');
            $table->dropColumn('state_battery_cable');
            $table->dropColumn('quantity_tow_cable');
            $table->dropColumn('state_tow_cable');
            $table->dropColumn('quantity_wheel_chocks');
            $table->dropColumn('state_wheel_chocks');
            $table->dropColumn('quantity_traffic_cones');
            $table->dropColumn('state_traffic_cones');
        });
    }
}
