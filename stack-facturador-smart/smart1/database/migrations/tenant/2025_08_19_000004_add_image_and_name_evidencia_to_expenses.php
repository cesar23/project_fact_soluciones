<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageAndNameEvidenciaToExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'expenses';
        if (!Schema::hasColumn($tableName, 'image')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('image')->nullable()->after('filename');
            });
        }

        if (!Schema::hasColumn($tableName, 'name_evidencia')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('name_evidencia')->nullable()->after('image');
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
        $tableName = 'expenses';
        if (Schema::hasColumn($tableName, 'name_evidencia')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('name_evidencia');
            });
        }

        if (Schema::hasColumn($tableName, 'image')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
}

