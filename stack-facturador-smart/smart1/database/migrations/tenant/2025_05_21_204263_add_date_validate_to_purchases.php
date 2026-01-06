<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204263_documents_change_date_validate_new
class AddDateValidateToPurchases extends Migration
{
    public function up()
    {
    

        Schema::table('purchases', function (Blueprint $table) {
            $table->datetime('date_validate')->nullable();
            $table->string('state_validate')->nullable();
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('date_validate');
            $table->dropColumn('state_validate');
        });
    }
}