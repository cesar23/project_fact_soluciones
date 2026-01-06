<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_111315_add_modules_certificates
class AddModulesCertificates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules')->insert(
            [
                [
                    'value' => 'certificates',
                    'description' => 'Certificados',
                    'sort' => 8,
                ],
            ]
        );


        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::table('modules')->where('value', 'certificates')->delete();
    }
}
