<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011165_add_edit_customer_info_direct


class AddEditCustomerInfoDirect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'edit_customer_info_direct')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('edit_customer_info_direct')->default(false);
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('edit_customer_info_direct');
        });
    }
}
