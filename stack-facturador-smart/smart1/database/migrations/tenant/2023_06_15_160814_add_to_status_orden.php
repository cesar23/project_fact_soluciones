<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToStatusOrden extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('status_orden')->insert([
            'id' => '1',
            'description' => "Solicitado",
         ]);
        DB::table('status_orden')->insert([
            'id' => '2',
            'description' => "Preparando",
         ]);
        DB::table('status_orden')->insert([
            'id' => '3',
            'description' => "Listo",
         ]);
        
        DB::table('status_orden')->insert([
            'id' => '4',
            'description' => "Atendido",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_orden', function (Blueprint $table) {
            //
        });
    }
}
