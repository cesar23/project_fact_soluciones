<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSaleSunatPurchase extends Migration
{
    
    public function up()
    {

        Schema::create('sunat_purchase_sale', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('sunat_sale', 12, 2)->default(0);
            $table->decimal('internal_sale', 12, 2)->default(0);
            $table->decimal('purchase_expense', 12, 2)->default(0);
            $table->date('period');
            $table->boolean('show');
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('sunat_purchase_sale');
    }
}
