<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetFalseConfigurationProperties extends Migration
{
    public function up()
    {
        DB::connection('tenant')
            ->table('configurations')
            ->update([
                'admin_seller_cash' => 0,
                'search_item_by_series' => 0,
            ]);
    }

    public function down()
    {
    }
}
