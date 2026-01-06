<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoxToSaleNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_notes', 'box')) {
                $table->string('box')->nullable()->after('person_packer_id'); // Añade la columna "box" después de "person_packer_id"
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_notes', function (Blueprint $table) {
            if (Schema::hasColumn('sale_notes', 'box')) {
                $table->dropColumn('box'); // Elimina la columna "box" si se hace rollback
            }
        });
    }
}