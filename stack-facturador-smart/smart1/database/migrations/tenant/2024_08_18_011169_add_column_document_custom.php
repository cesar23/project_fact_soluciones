<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011169_add_column_document_custom


class AddColumnDocumentCustom extends Migration
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
            ->where('value', 'discount')
            ->update([
                'name' => 'Precio descuento',
            ]);
        $columns = [
            [
                'name' => 'Valor descuento',
                'value' => 'discount_value',
                'is_visible' => false,
                'width' => 12,
                'order' => 0,
            ],
            [
                'name' => 'Valor producto', 
                'value' => 'item_value',
                'is_visible' => false,
                'width' => 12,
                'order' => 0,
            ],
            [
                'name' => 'Precio producto',
                'value' => 'item_price',
                'is_visible' => false,
                'width' => 12,
                'order' => 0,
            ]
        ];

        foreach($columns as $column) {
            $exists = DB::connection('tenant')
                        ->table('document_columns')
                        ->where('value', $column['value'])
                        ->exists();

            if (!$exists) {
                DB::connection('tenant')
                    ->table('document_columns')
                    ->insert($column);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('item_woocommerce');
    }
}
