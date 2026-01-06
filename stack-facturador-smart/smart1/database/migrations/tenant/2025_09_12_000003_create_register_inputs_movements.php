<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_12_000003_create_register_inputs_movements
class CreateRegisterInputsMovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('register_inputs_movements', function (Blueprint $table) {
            $table->id();
            $table->date('date_of_issue');
            $table->unsignedInteger('person_id')->nullable();
            $table->unsignedInteger('item_id');
            $table->decimal('quantity', 12, 4);
            $table->unsignedInteger('warehouse_id');
            $table->string('lot_code')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('register_inputs_movements');
    }
}