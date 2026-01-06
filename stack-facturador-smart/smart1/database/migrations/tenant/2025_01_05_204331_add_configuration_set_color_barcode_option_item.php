<?php

use App\Http\Controllers\Tenant\DocumentPaymentController;
use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204331_add_configuration_set_color_barcode_option_item
class AddConfigurationSetColorBarcodeOptionItem extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_set_color')->default(false);
            $table->boolean('add_quantity_in_read_barcode')->default(false);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('show_set_color');
            $table->dropColumn('add_quantity_in_read_barcode');
        });
    }
}
