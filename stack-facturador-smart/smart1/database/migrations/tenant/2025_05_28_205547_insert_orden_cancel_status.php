<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_05_28_205547_insert_orden_cancel_status
class InsertOrdenCancelStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tenant_connection = DB::connection('tenant');
        $exists = $tenant_connection->table('status_orden')->where('id', 5)->exists();
        if (!$exists) {
            $tenant_connection->table('status_orden')->insert([
                'id' => 5,
                'description' => 'Cancelado',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
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
        
    }
}
