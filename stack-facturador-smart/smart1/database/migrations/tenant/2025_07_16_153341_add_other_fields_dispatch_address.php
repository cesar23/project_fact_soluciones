<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153341_add_other_fields_dispatch_address
class AddOtherFieldsDispatchAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatch_addresses', function (Blueprint $table) {
            $table->text('reason')->nullable();
            $table->text('google_location')->nullable();
            $table->string('agency')->nullable();
            $table->string('person')->nullable();
            $table->string('person_document', 15)->nullable();
            $table->string('person_telephone', 15)->nullable();
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
            $table->dropColumn('reason');
            $table->dropColumn('google_location');
            $table->dropColumn('agency');
            $table->dropColumn('person');
            $table->dropColumn('person_document');
            $table->dropColumn('person_telephone');
        });
    }
}
