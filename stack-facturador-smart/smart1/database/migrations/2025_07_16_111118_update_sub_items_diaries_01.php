<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_111118_update_sub_items_diaries_01


class UpdateSubItemsDiaries01 extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {

        $table = DB::table('sub_diary_items');
        $table->where('sub_diary_code', '01')->where('code', '101101')->update([
            'code' => '104101',
            'description' => 'BANCO DE CREDITO',
            
        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('sub_diary_items')->whereIn('created_at', ['2025-08-17 12:17:19'])->delete();
    }
}
