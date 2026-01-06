<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204323_update_prices_edit_config
class UpdatePricesEditConfig extends Migration
{
    public function up()
    {
        //default_price_change_item
        //default_purchase_price_change_item
        DB::connection('tenant')->table('configurations')->update([
            'default_price_change_item' => false,
            'default_purchase_price_change_item' => false,
        ]);
    }

    public function down()
    {
        DB::connection('tenant')->table('configurations')->update([
            'default_price_change_item' => true,
            'default_purchase_price_change_item' => true,
        ]);
    }
}
