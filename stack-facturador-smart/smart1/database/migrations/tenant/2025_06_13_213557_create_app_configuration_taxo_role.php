<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_06_13_213557_create_app_configuration_taxo_role
class CreateAppConfigurationTaxoRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_configuration_taxo_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_configuration_taxo_id')->constrained('app_configuration_taxo');
            $table->string('role_id');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        $types = ['admin', 'seller', 'integrator', 'client', 'superadmin'];
        $id_app_configuration_taxo = DB::connection('tenant')->table('app_configuration_taxo')->get()->pluck('id');
        foreach ($types as $type) {
            foreach ($id_app_configuration_taxo as $id) {
                DB::connection('tenant')->table('app_configuration_taxo_role')->insert([
                    'app_configuration_taxo_id' => $id,
                    'role_id' => $type,
                    'is_visible' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_configuration_taxo_role');
    }
}
