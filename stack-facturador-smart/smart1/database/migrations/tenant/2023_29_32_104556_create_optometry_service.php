<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptometryService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('optometry_services',function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->char('soap_type_id', 2);
            $table->unsignedInteger('customer_id');
            $table->json('customer');
            $table->string('cellphone');
            $table->date('date_of_issue')->index();
            $table->time('time_of_issue');
            $table->text('description')->nullable();
            $table->string('filename')->nullable();
            $table->decimal('cost', 12, 2)->default(0); 
            $table->decimal('prepayment', 12, 2)->default(0); 
            $table->text('important_note')->nullable();
            $table->unsignedInteger('establishment_id')->default(0)->nullable();
            $table->json('establishment')->nullable();
            $table->text('currency_type_id')->nullable();
            $table->text('payment_condition_id')->nullable();
            $table->text('payment_method_type_id')->nullable();
            $table->unsignedInteger('seller_id')->default(0)->nullable();
            $table->decimal('exchange_rate_sale', 13, 3)->nullable()->default(0);

            $table->decimal('total_prepayment', 12)->nullable()->default(0);
            $table->decimal('total_charge', 12)->nullable()->default(0);
            $table->decimal('total_discount', 12)->nullable()->default(0);
            $table->decimal('total_exportation', 12)->nullable()->default(0);
            $table->decimal('total_free', 12)->nullable()->default(0);
            $table->decimal('total_taxed', 12)->nullable()->default(0);
            $table->decimal('total_unaffected', 12)->nullable()->default(0);
            $table->decimal('total_exonerated', 12)->nullable()->default(0);
            $table->decimal('total_igv', 12)->nullable()->default(0);
            $table->decimal('total_igv_free', 12)->nullable()->default(0);
            $table->decimal('total_base_isc', 12)->nullable()->default(0);
            $table->decimal('total_isc', 12)->nullable()->default(0);
            $table->decimal('total_base_other_taxes', 12)->nullable()->default(0);
            $table->decimal('total_other_taxes', 12)->nullable()->default(0);
            $table->decimal('total_plastic_bag_taxes', 6)->nullable()->default(0);
            $table->decimal('total_taxes', 12)->nullable()->default(0);
            $table->decimal('total_value', 12)->nullable()->default(0);
            $table->decimal('subtotal', 12)->nullable()->default(0);
            $table->decimal('total', 12)->nullable()->default(0);
            $table->unsignedTinyInteger('is_editable')->default(0)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('soap_type_id')->references('id')->on('soap_types');
            $table->foreign('customer_id')->references('id')->on('persons');
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('optometry_services');
        
       
    }
}
