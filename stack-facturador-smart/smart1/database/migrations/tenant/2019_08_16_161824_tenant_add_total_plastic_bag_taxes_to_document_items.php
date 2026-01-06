<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddTotalPlasticBagTaxesToDocumentItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('document_items', 'total_plastic_bag_taxes')) {
            Schema::table('document_items', function (Blueprint $table) {
                $table->decimal('total_plastic_bag_taxes', 6, 2)->default(0)->after('total_other_taxes');
                //
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
        Schema::table('document_items', function (Blueprint $table) {
            $table->dropColumn('total_plastic_bag_taxes');
        });
    }
}
