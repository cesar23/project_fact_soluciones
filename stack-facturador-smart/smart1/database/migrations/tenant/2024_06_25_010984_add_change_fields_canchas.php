<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddChangeFieldsCanchas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('type_id')->nullable();
            $table->string('nombre')->nullable()->change();
            $table->string('numero')->nullable()->change();
            $table->string('ubicacion')->nullable()->change();
            $table->integer('capacidad')->nullable()->change();
            $table->string('reservante_nombre')->nullable()->change();
            $table->string('reservante_apellidos')->nullable()->change();
    


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropColumn('customer_id');
            $table->dropColumn('type_id');
    
        });
    
    }
}
