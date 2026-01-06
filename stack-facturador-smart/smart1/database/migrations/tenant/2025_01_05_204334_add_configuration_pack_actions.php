<?php

use App\Http\Controllers\Tenant\DocumentPaymentController;
use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204334_add_configuration_pack_actions
class AddConfigurationPackActions extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('purchase_orden_in_item_set')->default(false);
            $table->boolean('items_delivery_states')->default(false);
            $table->boolean('show_platform_description')->default(false);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('purchase_orden_in_item_set');
            $table->dropColumn('items_delivery_states');
            $table->dropColumn('show_platform_description');
        });
    }
}
