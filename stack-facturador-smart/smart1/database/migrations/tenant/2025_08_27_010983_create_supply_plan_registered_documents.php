<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_27_010983_create_supply_plan_registered_documents
class CreateSupplyPlanRegisteredDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_plan_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supply_plan_registered_id'); // FK al registro del plan
            $table->unsignedInteger('document_id')->nullable(); // FK al documento emitido (factura,     boleta, etc.)
            $table->year('year'); // Año del documento
            $table->tinyInteger('month'); // Mes (1-12)
            $table->date('generation_date'); // Fecha en que se generó el documento
            $table->date('due_date')->nullable(); // Fecha de vencimiento/cobro
            $table->enum('status', [
                'pending', 'generated', 'sent', 'paid',
                'cancelled'
            ])->default('pending');
            $table->decimal('amount', 10, 2)->nullable(); // Monto del documento
            $table->string('document_series', 10)->nullable(); // Serie del documento
            $table->string('document_number', 20)->nullable(); // Número del documento
            $table->text('observations')->nullable(); // Observaciones adicionales
            $table->unsignedInteger('user_id')->nullable(); // Usuario que generó
            $table->timestamps();

            // Índices y llaves foráneas
            $table->foreign('supply_plan_registered_id')->references('id')->on('supplies_plans_registered')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Índice único para evitar duplicados por mes
            $table->unique(['supply_plan_registered_id', 'year', 'month'], 'unique_monthly_document');

            // Índices para consultas frecuentes
            $table->index(['year', 'month']);
            $table->index('status');
            $table->index('generation_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supply_plan_documents');
    }
}
