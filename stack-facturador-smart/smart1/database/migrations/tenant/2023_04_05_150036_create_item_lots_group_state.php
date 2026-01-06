<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateItemLotsGroupState extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('item_lots_group_state')) return;
        Schema::create('item_lots_group_state', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->boolean('active')->default(1);
            $table->timestamps();
        });

        DB::connection('tenant')->table('item_lots_group_state')->insert([
            ['active' => 1, 'description' => 'APROBADO'],
            ['active' => 1, 'description' => 'RECHAZADO'],
            ['active' => 1, 'description' => 'DEVUELTO'],
            ['active' => 1, 'description' => 'CUARENTENA'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_lots_group_state');
    }
}
