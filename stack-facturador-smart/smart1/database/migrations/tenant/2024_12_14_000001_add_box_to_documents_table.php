<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoxToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('documents', 'box')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('box')->nullable()->after('person_packer_id'); // Añade la columna "box" después de "person_packer_id"
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('box'); // Elimina la columna "box" si se hace rollback
        });
    }
}