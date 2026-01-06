<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpgradeBtnToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('configurations')->update([
            'show_ticket_80' => true,
            'show_ticket_58' =>true,
            'show_ticket_50' => true,
            'ticket_single_shipment' => true,
            'allow_edit_unit_price_to_seller' => true,
            'quotation_allow_seller_generate_sale' => true,
            'seller_can_create_product' => true,
            'seller_can_generate_sale_opportunities' => true,
            'select_available_price_list' => true,
            'amount_plastic_bag_taxes' => 0.5,
            'include_igv' => true,
            'shipping_time_days' => 4
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
            //
        });
    }
}
