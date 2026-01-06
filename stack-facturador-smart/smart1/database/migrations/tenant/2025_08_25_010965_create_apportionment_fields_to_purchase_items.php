<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_25_010965_create_apportionment_fields_to_purchase_items
class CreateApportionmentFieldsToPurchaseItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_value_apportioned_affected', 10, 2)->nullable();
            $table->decimal('unit_price_apportioned_affected', 10, 2)->nullable();
            $table->decimal('stock_before_apportionment', 10, 2)->nullable();
            $table->decimal('unit_price_apportioned', 10, 2)->nullable();
            $table->decimal('total_apportioned', 10, 2)->nullable();
            $table->decimal('discount_apportioned', 10, 2)->nullable();
            $table->boolean('affected')->default(false);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('discount_apportioned', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('unit_value_apportioned_affected');
            $table->dropColumn('unit_price_apportioned_affected');
            $table->dropColumn('stock_before_apportionment');
            $table->dropColumn('unit_price_apportioned');
            $table->dropColumn('total_apportioned');
            $table->dropColumn('discount_apportioned');
            $table->dropColumn('affected');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('discount_apportioned');
        });
    }
}
