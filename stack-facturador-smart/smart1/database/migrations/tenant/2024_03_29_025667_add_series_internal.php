<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSeriesInternal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('series', function (Blueprint $table) {
            $table->boolean('internal')->default(false);
        });

        DB::connection('tenant')
            ->table('state_types')
            ->insert([
                ['id' => '55', 'description' => 'Interno']
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('series', 'internal')) {
            Schema::table('series', function (Blueprint $table) {
                $table->dropColumn('internal');
            });
        }

        DB::connection('tenant')
            ->table('state_types')
            ->where('id', '55')
            ->delete();
    }
}
