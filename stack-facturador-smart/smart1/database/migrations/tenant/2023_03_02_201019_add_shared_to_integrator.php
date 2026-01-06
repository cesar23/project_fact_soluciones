<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSharedToIntegrator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $token = str_random(50);
        // DB::connection('tenant')->table('users')->insert([
        //     'name' => 'Administrador',
        //     'email' => "superadmin@admin.com",
        //     'password' => bcrypt("123456"),
        //     'api_token' => $token,
        //     'establishment_id' => 1,
        //     'type' => "integrator",
        //     'locked' => true,
        //     'permission_edit_cpe' => true,
        //     'last_password_update' => date('Y-m-d H:i:s'),
        // ]);

        // $user_id = DB::connection('tenant')->table('users')->insert([
        //     'name' => 'Administrador',
        //     'email' => "superadmin@admin.com",
        //     'password' => bcrypt("123456"),
        //     'api_token' => $token,
        //     'establishment_id' => 1,
        //     'type' =>  "integrator",
        //     'locked' => true,
        //     'permission_edit_cpe' => true,
        //     'last_password_update' => date('Y-m-d H:i:s'),
        // ]);

        // App\Models\System\User::create([
        //     'name' => 'SuperUser',
        //     'email' => 'superuser@gmail.com',
        //     'password' => bcrypt('123456'),
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integrator', function (Blueprint $table) {
            //
        });
    }
}
