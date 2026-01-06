<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//2025_09_29_2024346_create_sale_note_orders_to_states
class CreateSaleNoteOrdersToStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_note_orders_states', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('state_sale_note_orders_id');
            $table->unsignedInteger('sale_note_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_note_orders_states');    
    }
}
