<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnToNulleableAndAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('food_dealer_auth', function (Blueprint $table) {
            if (Schema::hasColumn('food_dealer_auth', 'auth_user_id')) {
                $table->unsignedInteger('auth_user_id')->nullable()->change();
            }
            
            if (!Schema::hasColumn('food_dealer_auth', 'user_name')) {
                $table->string('user_name')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('food_dealer_auth', function (Blueprint $table) {
            $table->unsignedInteger('auth_user_id')->change();
            $table->dropColumn('user_name');
        });
        
    }
}
