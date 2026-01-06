<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_06_26_204248_add_modules_certificate
class AddModulesCertificate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->transaction(function () {
            DB::connection('tenant')->table('modules')->insert(
                [
                    'value' => 'certificates',
                    'description' => 'Certificados',
                    'order_menu' => 8,
                ],

            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->transaction(function () {
            $moduleId = DB::connection('tenant')->table('modules')
                ->select('id')
                ->where('value', 'reservation')
                ->first()->id;

            DB::connection('tenant')->table('module_user')->where('module_id', $moduleId)->delete();
        });
    }
}
