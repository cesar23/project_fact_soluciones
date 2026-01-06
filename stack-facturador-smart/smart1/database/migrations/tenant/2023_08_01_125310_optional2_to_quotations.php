<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Optional2ToQuotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('quotations', 'quotations_optional')) {
            Schema::table('quotations', function (Blueprint $table) {
                 $table->string('quotations_optional')->nullable();
             });
        }
        if (!Schema::hasColumn('quotations', 'quotations_optional_value')) {
            Schema::table('quotations', function (Blueprint $table) {
                  $table->string('quotations_optional_value')->nullable();
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
        Schema::table('quotations', function (Blueprint $table) {
            //
        });
    }
}
