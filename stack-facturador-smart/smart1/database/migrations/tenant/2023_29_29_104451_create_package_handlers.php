<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageHandlers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table("configurations", function (Blueprint $table) {
                $table->boolean('package_handlers')->default(false);
            });
 
        Schema::create('package_handlers', function (Blueprint $table) {
            $table->increments('id');
            $table->string("series",15);
            $table->string("number",15);
            $table->unsignedInteger('establishment_id');
            $table->json('establishment');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('seller_id')->nullable();
            $table->unsignedInteger('sender_id');
            $table->json('sender');
            $table->unsignedInteger('issuer_id');
            $table->json('issuer');
            $table->unsignedInteger('driver_id')->nullable();
            $table->json('driver')->nullable();
            $table->string("license_plate",15)->nullable();
            $table->date("date_of_issue");
            $table->time("time_of_issue");
            $table->string("departure",70)->nullable();
            $table->string("arrival",70)->nullable();
            $table->string("observation")->nullable();
            $table->string('currency_type_id');
            $table->decimal('exchange_rate_sale', 12, 2);
            $table->decimal('total_prepayment', 12, 2)->default(0);
            $table->decimal('total_charge', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('total_exportation', 12, 2)->default(0);
            $table->decimal('total_free', 12, 2)->default(0);
            $table->decimal('total_taxed', 12, 2)->default(0);
            $table->decimal('total_unaffected', 12, 2)->default(0);
            $table->decimal('total_exonerated', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total_base_isc', 12, 2)->default(0);
            $table->decimal('total_isc', 12, 2)->default(0);
            $table->decimal('total_base_other_taxes', 12, 2)->default(0);
            $table->decimal('total_other_taxes', 12, 2)->default(0);
            $table->decimal('total_taxes', 12, 2)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->unsignedInteger('cash_id');
            $table->timestamps();
        });

        Schema::create('package_handler_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->json('item');
            $table->decimal('quantity',12,4);
            $table->decimal('unit_value', 12, 2);

            $table->string('affectation_igv_type_id');
            $table->decimal('total_base_igv', 12, 2);
            $table->decimal('percentage_igv', 12, 2);
            $table->decimal('total_igv', 12, 2);

            $table->string('system_isc_type_id')->nullable();
            $table->decimal('total_base_isc', 12, 2)->default(0);
            $table->decimal('percentage_isc', 12, 2)->default(0);
            $table->decimal('total_isc', 12, 2)->default(0);

            $table->decimal('total_base_other_taxes', 12, 2)->default(0);
            $table->decimal('percentage_other_taxes', 12, 2)->default(0);
            $table->decimal('total_other_taxes', 12, 2)->default(0);
            $table->decimal('total_taxes', 12, 2);

            $table->string('price_type_id');
            $table->decimal('unit_price', 12, 2);

            $table->decimal('total_value', 12, 2);
            $table->decimal('total_charge', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->json('attributes')->nullable();
            $table->unsignedInteger('package_handler_id');
            $table->timestamps();
        });
        Schema::create('package_handler_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('package_handler_id');
            $table->date('date_of_payment');
            $table->char('payment_method_type_id', 2);
            $table->boolean('has_card')->default(false);
            $table->char('card_brand_id', 2)->nullable();
            $table->string('reference')->nullable();
            $table->decimal('payment', 12, 2);

        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('package_handlers');
        });
        Schema::dropIfExists('package_handlers');
        Schema::dropIfExists('package_handler_items');
        Schema::dropIfExists('package_handler_payments');
   
    }
}
