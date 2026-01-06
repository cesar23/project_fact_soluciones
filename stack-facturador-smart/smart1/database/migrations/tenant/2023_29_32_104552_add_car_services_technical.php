<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCarServicesTechnical extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_service_cars', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('technical_service_id');
            $table->string('plate_number')->nullable();
            $table->string('km')->nullable();
            $table->string('color')->nullable();
            $table->string('year')->nullable();
            $table->string('chasis')->nullable();
            $table->date('date_soat_due')->nullable();
            $table->date('date_check_due')->nullable();
            $table->integer('quantity_front_lights')->nullable();
            $table->string('state_front_lights')->nullable();
            $table->integer('quantity_directional_lights_front')->nullable();
            $table->string('state_directional_lights_front')->nullable();
            $table->integer('quantity_directional_lights_back')->nullable();
            $table->string('state_directional_lights_back')->nullable();
            $table->integer('quantity_hazard_lights')->nullable();
            $table->string('state_hazard_lights')->nullable();
            $table->integer('quantity_wiper_washer_arm')->nullable();
            $table->string('state_wiper_washer_arm')->nullable();
            $table->integer('quantity_gasoil_cap')->nullable();
            $table->string('state_gasoil_cap')->nullable();
            $table->integer('quantity_radio_antenna')->nullable();
            $table->string('state_radio_antenna')->nullable();
            $table->integer('quantity_side_mirrors')->nullable();
            $table->string('state_side_mirrors')->nullable();
            $table->integer('quantity_test_handles')->nullable();
            $table->string('state_test_handles')->nullable();
            $table->integer('quantity_alarm')->nullable();
            $table->string('state_alarm')->nullable();
            $table->integer('quantity_booties')->nullable();
            $table->string('state_booties')->nullable();
            $table->integer('quantity_spare_tire')->nullable();
            $table->string('state_spare_tire')->nullable();
            $table->integer('quantity_wheel_nut')->nullable();
            $table->string('state_wheel_nut')->nullable();
            $table->integer('quantity_wheel_cup')->nullable();
            $table->string('state_wheel_cup')->nullable();
            $table->integer('quantity_ashtray')->nullable();
            $table->string('state_ashtray')->nullable();
            $table->integer('quantity_internal_rearview_mirror')->nullable();
            $table->string('state_internal_rearview_mirror')->nullable();
            $table->integer('quantity_car_radio')->nullable();
            $table->string('state_car_radio')->nullable();
            $table->integer('quantity_protection_mat')->nullable();
            $table->string('state_protection_mat')->nullable();
            $table->integer('quantity_rubber_floors')->nullable();
            $table->string('state_rubber_floors')->nullable();
            $table->integer('quantity_cup_holder')->nullable();
            $table->string('state_cup_holder')->nullable();
            $table->integer('quantity_vehicle_key')->nullable();
            $table->string('state_vehicle_key')->nullable();
            $table->integer('quantity_extinguisher')->nullable();
            $table->string('state_extinguisher')->nullable();
            $table->integer('quantity_jack_lever')->nullable();
            $table->string('state_jack_lever')->nullable();
            $table->integer('quantity_toolkit')->nullable();
            $table->string('state_toolkit')->nullable();
            $table->integer('quantity_property_card')->nullable();
            $table->string('state_property_card')->nullable();
            $table->integer('quantity_logbook')->nullable();
            $table->string('state_logbook')->nullable();
            $table->integer('quantity_owner_manual')->nullable();
            $table->string('state_owner_manual')->nullable();
            $table->integer('quantity_document_holder')->nullable();
            $table->string('state_document_holder')->nullable();
            $table->boolean('auth_drive')->default(false);
            $table->boolean('move_on')->default(false);
            $table->boolean('no_value_things')->default(false);
            $table->boolean('cost_for_days')->default(false);
            $table->float('gasoline_level')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->foreign('technical_service_id')->references('id')->on('technical_services')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('technical_service_cars');
    }
}
