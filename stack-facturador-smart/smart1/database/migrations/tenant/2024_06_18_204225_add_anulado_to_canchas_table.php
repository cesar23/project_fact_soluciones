<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnuladoToCanchasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->boolean('anulado')->default(0)->after('qr_code_path'); // Añadir el campo anulado con valor por defecto 0
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
            $table->dropColumn('anulado');
        });
    }
}

