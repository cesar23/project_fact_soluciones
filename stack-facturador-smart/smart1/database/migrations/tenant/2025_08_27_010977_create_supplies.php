<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_27_010976_create_supplies
class CreateSupplies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('zone_id');
            $table->unsignedInteger('sector_id');
            $table->string('optional_address')->nullable();
            $table->date('date_start');
            $table->date('date_end')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('state_supply_id');
            $table->timestamps();
            $table->index(['zone_id', 'sector_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplies');
    }
}
