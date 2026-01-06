<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204238_add_fields_template_purchase_order
class AddFieldsTemplatePurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('purchase_orders', 'shipping_address')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->string('shipping_address')->nullable();
                $table->string('limit_date')->nullable(); 
                $table->string('purchase_quotation')->nullable();
                $table->string('mail_purchase_quotation')->nullable();
                $table->string('type_purchase_order')->nullable();
                $table->longText('work_description')->nullable();
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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('shipping_address');
            $table->dropColumn('limit_date');
            $table->dropColumn('purchase_quotation');
            $table->dropColumn('mail_purchase_quotation');
            $table->dropColumn('type_purchase_order');
            $table->dropColumn('work_description');
        });
    }
}
