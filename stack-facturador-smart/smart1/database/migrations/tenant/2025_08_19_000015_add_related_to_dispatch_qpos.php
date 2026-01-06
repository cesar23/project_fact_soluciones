<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000015_add_related_to_dispatch_qpos
class AddRelatedToDispatchQpos extends Migration      
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('dispatches', 'related')) {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->json('related')->nullable()->after('container_number')->comment('Numero de DAM');
            });
        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('dispatches', 'related')) {   
            Schema::table('dispatches', function (Blueprint $table) {
                $table->dropColumn('related');
            });
        }
    }
}
