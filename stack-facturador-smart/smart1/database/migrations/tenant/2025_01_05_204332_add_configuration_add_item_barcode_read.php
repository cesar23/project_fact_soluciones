<?php

use App\Http\Controllers\Tenant\DocumentPaymentController;
use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204332_add_configuration_add_item_barcode_read
class AddConfigurationAddItemBarcodeRead extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('add_item_barcode_read')->default(false);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('add_item_barcode_read');
        });
    }
}
