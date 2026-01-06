<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->increments('id');

            // Identificación del registro auditado
            $table->string('auditable_type', 50); // 'document', 'sale_note', 'quotation', etc
            $table->unsignedInteger('auditable_id'); // ID del registro

            // Tipo de evento
            $table->string('event', 50); // 'created', 'updated', 'deleted', 'voided', 'generated_from', 'converted_to'

            // Detalles del cambio
            $table->string('field_name')->nullable(); // Campo que cambió (null si es evento general)
            $table->text('old_value')->nullable(); // Valor anterior
            $table->text('new_value')->nullable(); // Valor nuevo

            // Relación con otros documentos (para conversiones)
            $table->string('related_type', 50)->nullable(); // Tipo del documento relacionado
            $table->unsignedInteger('related_id')->nullable(); // ID del documento relacionado

            // Usuario que realizó el cambio
            $table->unsignedInteger('user_id')->nullable(); // NULL = proceso del sistema

            // Descripción y metadatos
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event');
            $table->index('user_id');
            $table->index(['related_type', 'related_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audits');
    }
}
