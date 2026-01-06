<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShortcuts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->json('shortcuts')->nullable();
        });
        DB::table('configurations')->where('id', 1)->update([
            'shortcuts' =>
            [
                "1" => "/documents/create",
                "2" => "/pos",
                "3" => "/sale-notes",
                "4" => "/persons/customers",
                "5" => "/items"
            ]
        ]);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('shortcuts');
        });
    }
}
