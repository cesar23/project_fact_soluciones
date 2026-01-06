<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class HiddeModuleRestaurant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $firstRecord = DB::connection('tenant')->table('modules')->where('value', 'restaurant_app')->first();
        if($firstRecord){
            $firstRecordId = $firstRecord->id;
            DB::connection('tenant')->table('module_user')->where('module_id', $firstRecordId)->delete();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $record =   DB::connection('tenant')->table('modules')->insertGetId(
            [
                'value' => 'restaurant_app',
                'description' => 'Restaurante',
                'order_menu' => 18,
            ],

        );
    }
}
