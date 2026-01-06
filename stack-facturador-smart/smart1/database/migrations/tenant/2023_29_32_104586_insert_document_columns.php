<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertDocumentColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::connection('tenant')
            ->table('document_columns')
            ->insert([
                [
                    'value' => 'second_name',
                    'name' => 'Nombre secundario',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'description',
                    'name' => 'Descripción',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'category',
                    'name' => 'Categoría',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'model',
                    'name' => 'Modelo',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'lot',
                    'name' => 'Lote',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'serie',
                    'name' => 'Serie',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'date_of_due',
                    'name' => 'Fecha de vencimiento',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'barcode',
                    'name' => 'Código de barra',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'internal_code',
                    'name' => 'Código interno',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'factory_code',
                    'name' => 'Código de fábrica',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'cod_digemid',
                    'name' => 'Código Digemid',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'nom_prod',
                    'name' => 'Nombre Digemid',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'num_reg_san',
                    'name' => 'Registro sanitario',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'nom_titular',
                    'name' => 'Laboratorio',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'info_link',
                    'name' => 'Hipervínculo',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'discount',
                    'name' => 'Descuento',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'unit_value',
                    'name' => 'Valor unitario',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'total_value',
                    'name' => 'Valor total',
                    'width' => 12,
                    'is_visible' => false,
                ],
                [
                    'value' => 'unit_price',
                    'name' => 'Precio unitario',
                    'width' => 12,
                    'is_visible' => true,

                ],
                [
                    'value' => 'total_price',
                    'name' => 'Precio total',
                    'width' => 12,
                    'is_visible' => true,

                ],
            ]);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')
            ->table('document_columns')
            ->delete();
    }
}
