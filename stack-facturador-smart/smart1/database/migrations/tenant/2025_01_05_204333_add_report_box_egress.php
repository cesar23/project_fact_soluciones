<?php

use App\Http\Controllers\Tenant\DocumentPaymentController;
use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204333_add_report_box_egress
class AddReportBoxEgress extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('report_box_egress')->default(true);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('report_box_egress');
        });
    }
}
