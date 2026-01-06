<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_09_17_000002_create_cat_document_types
class CreateCatDocumentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('cat_document_types')){
            return;
        }
        Schema::create('cat_document_types', function (Blueprint $table) {
            $table->string('id');
            $table->string('description');
            $table->string('short');
            $table->boolean('active');
        });

        DB::table('cat_document_types')
            ->insert([
                ['id' => '01', 'description' => 'FACTURA ELECTRÓNICA', 'short' => 'FT', 'active' => true],
                ['id' => '03', 'description' => 'BOLETA DE VENTA ELECTRÓNICA', 'short' => 'BV', 'active' => true],
                ['id' => '07', 'description' => 'NOTA DE CRÉDITO', 'short' => '', 'active' => true],
                ['id' => '08', 'description' => 'NOTA DE DÉBITO', 'short' => '', 'active' => true],
                ['id' => '09', 'description' => 'GUIA DE REMISIÓN REMITENTE', 'short' => '', 'active' => true],
                ['id' => '20', 'description' => 'COMPROBANTE DE RETENCIÓN ELECTRÓNICA', 'short' => '', 'active' => true],
                ['id' => '31', 'description' => 'Guía de remisión transportista', 'short' => '', 'active' => true],
                ['id' => '40', 'description' => 'COMPROBANTE DE PERCEPCIÓN ELECTRÓNICA', 'short' => '', 'active' => true],
                ['id' => '71', 'description' => 'Guia de remisión remitente complementaria', 'short' => '', 'active' => false],
                ['id' => '72', 'description' => 'Guia de remisión transportista complementaria', 'short' => '', 'active' => false],
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()

    {
        Schema::dropIfExists('cat_document_types');
      
    }
}
