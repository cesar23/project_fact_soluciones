<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_05_28_205543_insert_status_tables_restaurant
class InsertStatusTablesRestaurant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::table('status_table')->delete();
        // DB::table('status_table')->insert([
        //     ['id' => 1, 'description' => 'Libre', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 2, 'description' => 'Ocupada', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 3, 'description' => 'Mantenimiento', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        //     ['id' => 4, 'description' => 'Reservada', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        // ]);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::table('status_table')->whereIn('description', ['Ocupada', 'Libre', 'Mantenimiento', 'Reservada'])->delete();
    }
}
