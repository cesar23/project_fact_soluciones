<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPseTokenCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'pse_url')) {
                $table->string('pse_url')->nullable();
            }
            if (!Schema::hasColumn('companies', 'pse_token')) {
                $table->string('pse_token')->nullable();
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
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'pse_url')) {
                $table->dropColumn('pse_url');
            }
            if (Schema::hasColumn('companies', 'pse_token')) {
                $table->dropColumn('pse_token');
            }
        });
        

    }
}
