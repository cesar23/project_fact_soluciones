<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_27_010980_create_changed_quotations_to_document_services_not_services
class CreateChangedQuotationsToDocumentServicesNotServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_services_not_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('quotation_id');
            $table->unsignedInteger('document_service_id')->nullable();
            $table->unsignedInteger('document_not_service_id')->nullable();
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
        Schema::dropIfExists('quotation_services_not_services');
    }
}
