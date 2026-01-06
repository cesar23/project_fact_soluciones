<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_27_010982_create_quotations_technicians_quotation
class CreateQuotationsTechniciansQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_technicians_quotation', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('quotation_id');
            $table->unsignedInteger('quotation_technician_id');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_technicians_quotation');
    }
}
