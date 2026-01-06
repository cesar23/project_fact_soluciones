<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204263_documents_change_date_validate_new
class DocumentsChangeDateValidateNew extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Primero eliminamos la columna existente
            $table->dropColumn('date_validate');
        });

        Schema::table('documents', function (Blueprint $table) {
            // Creamos la nueva columna datetime
            $table->datetime('date_validate')->nullable();
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('date_validate');
            $table->date('date_validate')->nullable();
        });
    }
}