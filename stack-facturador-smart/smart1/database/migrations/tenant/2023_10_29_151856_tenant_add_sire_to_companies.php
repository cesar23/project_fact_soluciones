<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TenantAddSireToCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //si ya existe la columna no la vuelva a crear
        if (!Schema::hasColumn('companies', 'sire_client_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('sire_client_id')->nullable();
            });
        }
        if (!Schema::hasColumn('companies', 'sire_client_secret')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('sire_client_secret')->nullable();
            });
        }
        if (!Schema::hasColumn('companies', 'sire_username')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('sire_username')->nullable();
            });
        }
        if (!Schema::hasColumn('companies', 'sire_password')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('sire_password')->nullable();
            });
        }

        // Schema::table('companies', function (Blueprint $table) {
        //     $table->string('sire_client_id')->nullable();
        //     $table->string('sire_client_secret')->nullable();
        //     $table->string('sire_username')->nullable();
        //     $table->string('sire_password')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('sire_client_id');
            $table->dropColumn('sire_client_secret');
            $table->dropColumn('sire_username');
            $table->dropColumn('sire_password');
        });
    }
};
