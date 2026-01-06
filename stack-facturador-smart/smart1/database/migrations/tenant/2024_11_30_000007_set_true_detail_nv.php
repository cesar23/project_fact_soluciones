<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2024_11_30_000007_set_true_detail_nv
class SetTrueDetailNv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = DB::connection("tenant")
        ->table('configurations')
        ->update(['taxed_igv_visible_nv' => true]);

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No es necesario un rollback ya que los datos originales se perdieron
    }
}
