<?php

use App\Models\System\Client;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConfigSystemEnvToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('clients')) {

            if (!Schema::hasColumn('clients', 'config_system_env')) {
                Schema::table('clients', function (Blueprint $table) {
                    $table->boolean('config_system_env')->default(false)->nullable()->after('end_billing_cycle');
                });
            }

            Client::query()->update([
                'config_system_env' => false,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            //
        });
    }
}
