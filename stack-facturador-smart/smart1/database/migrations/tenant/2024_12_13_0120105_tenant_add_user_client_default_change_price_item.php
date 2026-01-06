<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2024_12_27_0120105_tenant_add_user_client_default_change_price_item

class TenantAddUserClientDefaultChangePriceItem extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'person_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('person_id')->nullable();
                $table->boolean('edit_price_pos')->default(true);
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('person_id');
            $table->dropColumn('edit_price_pos');
        });
    }
}
