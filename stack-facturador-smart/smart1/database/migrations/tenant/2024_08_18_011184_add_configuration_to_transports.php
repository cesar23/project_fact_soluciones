<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2024_08_18_011184_add_configuration_to_transports

class AddConfigurationToTransports extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('transports', 'configuration')) {
            Schema::table('transports', function (Blueprint $table) {
                $table->string('configuration')->nullable();
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

        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn('configuration');
        });
    }
}
