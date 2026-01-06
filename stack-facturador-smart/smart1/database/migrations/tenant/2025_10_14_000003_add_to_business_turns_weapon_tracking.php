<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//2025_10_14_000003_add_to_business_turns_weapon_tracking
class AddToBusinessTurnsWeaponTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('business_turns')->insert([
            'value' => 'weapon_tracking',
            'name' => 'Control de armas',
            'active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('business_turns')->where('value', 'weapon_tracking')->delete();
    
    }
}
