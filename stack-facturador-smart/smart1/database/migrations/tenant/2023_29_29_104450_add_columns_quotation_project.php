<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsQuotationProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotation_projects', function (Blueprint $table) {
            $table->decimal('percentage')->after('telephone')->nullable();
            $table->string('limit_date')->after('telephone')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotation_projects', function (Blueprint $table) {
            $table->dropColumn('percentage');
            $table->dropColumn('limit_date');
        });

    }
}
