<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanceledPurchaseFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('purchase_fee', function (Blueprint $table) {
            $table->boolean('is_canceled')->default(false);
            $table->decimal('original_amount', 12, 2)->nullable()->after('amount');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('purchase_fee', function (Blueprint $table) {
            $table->dropColumn('is_canceled');
            $table->dropColumn('original_amount');
        });
      
    }
}
