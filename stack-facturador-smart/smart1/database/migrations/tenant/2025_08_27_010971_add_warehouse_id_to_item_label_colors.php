<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_27_010971_add_warehouse_id_to_item_label_colors
class AddWarehouseIdToItemLabelColors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_label_colors', function (Blueprint $table) {
            $table->unsignedInteger('warehouse_id')->nullable()->after('label_color_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_label_colors', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
    }
}
