<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorSupplyAdvancePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_advance_payments', function (Blueprint $table) {
            // Agregar nuevos campos para el sistema refactorizado
            $table->json('periods')->after('document_type_id'); // Array de períodos
            $table->decimal('total_amount', 10, 2)->after('periods'); // Monto total
            $table->unsignedInteger('document_id')->nullable()->after('total_amount'); // ID del documento generado
            $table->unsignedInteger('sale_note_id')->nullable()->after('document_id'); // ID de nota de venta si aplica
            
            // Hacer nullable los campos year y month (ya no se usan individualmente)
            $table->integer('year')->nullable()->change();
            $table->string('month', 20)->nullable()->change();
            $table->decimal('amount', 10, 2)->nullable()->change(); // También nullable ya que usamos total_amount
            
            // Índices para mejor rendimiento (verificar si ya existen)
            $table->index('document_id');
            $table->index('sale_note_id');
            // El índice supply_id, is_used ya existe de migración anterior
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_advance_payments', function (Blueprint $table) {
            // Eliminar índices (solo los que creamos en esta migración)
            $table->dropIndex(['document_id']);
            $table->dropIndex(['sale_note_id']);
            // No eliminar el índice supply_id, is_used ya que existía antes
            
            // Eliminar campos agregados
            $table->dropColumn([
                'periods',
                'total_amount', 
                'document_id',
                'sale_note_id'
            ]);
            
            // Restaurar campos como not nullable
            $table->integer('year')->nullable(false)->change();
            $table->string('month', 20)->nullable(false)->change();
            $table->decimal('amount', 10, 2)->nullable(false)->change();
        });
    }
}