<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

//2025_09_08_083001_drop_index_unique_supply_plan_documents
class DropIndexUniqueSupplyPlanDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Obtener todas las restricciones de clave foránea que usan el índice único
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'supply_plan_documents' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        // Eliminar todas las restricciones de clave foránea
        foreach ($foreignKeys as $fk) {
            try {
                Schema::table('supply_plan_documents', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                });
            } catch (Exception $e) {
                // Ignorar errores si la restricción no existe
                echo "Warning: Could not drop foreign key {$fk->CONSTRAINT_NAME}: " . $e->getMessage() . "\n";
            }
        }
        
        // Ahora eliminar el índice único
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->dropUnique('unique_monthly_document');
        });
        
        // Recrear las restricciones de clave foránea
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->foreign('supply_plan_registered_id')->references('id')->on('supplies_plans_registered')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar todas las restricciones de clave foránea
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->dropForeign(['supply_plan_registered_id']);
            $table->dropForeign(['document_id']);
            $table->dropForeign(['user_id']);
        });
        
        // Recrear el índice único
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->unique(['supply_plan_registered_id', 'year', 'month'], 'unique_monthly_document');
        });
        
        // Recrear las restricciones de clave foránea
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->foreign('supply_plan_registered_id')->references('id')->on('supplies_plans_registered')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
}