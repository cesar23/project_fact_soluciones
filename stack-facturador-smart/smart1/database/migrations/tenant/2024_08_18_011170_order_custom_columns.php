<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011170_order_custom_columns


class OrderCustomColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Valor producto
        // Precio producto
        // Valor descuento
        // Precio descuento
        // Valor unitario
        // Precio unitario
        // Valor total
        // Precio total
        $fields = [
            'item_value' => 1,
            'item_price' => 2,
            'discount_value' => 3,
            'discount' => 4,
            'unit_value' => 5,
            'unit_price' => 6,
            'total_value' => 7,
            'total_price' => 8,
        ];

        foreach ($fields as $key => $value) {
            DB::connection('tenant')
                ->table('document_columns')
                ->where('value', $key)
                ->update([
                    'order' => $value,
                ]);
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
