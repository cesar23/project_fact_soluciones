<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeConfigurationTransportPdf extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('info_customer_pdf')->default(true);
            $table->boolean('date_of_due_pdf')->default(true);
            $table->boolean('qr_payments_pdf')->default(true);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('info_customer_pdf');
            $table->dropColumn('date_of_due_pdf');
            $table->dropColumn('qr_payments_pdf');
        });
    }
}
