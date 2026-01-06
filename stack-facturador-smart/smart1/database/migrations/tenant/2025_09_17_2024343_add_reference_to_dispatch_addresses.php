<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_17_2024343_add_reference_to_dispatch_addresses
class AddReferenceToDispatchAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatch_addresses', function (Blueprint $table) {
            $table->text('reference')->nullable();    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('dispatch_addresses', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
}
