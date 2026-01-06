<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddIsTransportCategoryM1lToDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //if not exists
        if (!Schema::hasColumn('dispatches', 'is_transport_category_m1l')) {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->boolean('is_transport_category_m1l')->default(false);
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
        // Schema::table('dispatches', function (Blueprint $table) {
        //     $table->dropColumn('is_transport_category_m1l');
        // });

        if (Schema::hasColumn('dispatches', 'is_transport_category_m1l')) {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->dropColumn('is_transport_category_m1l');
            });
        }

    }
}
