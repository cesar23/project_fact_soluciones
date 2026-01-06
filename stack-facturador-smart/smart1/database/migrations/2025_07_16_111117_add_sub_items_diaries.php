<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_111117_add_sub_items_diaries


class AddSubItemsDiaries extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {

        $table = DB::table('sub_diary_items');
        $toInsert = [
            [
                'sub_diary_code' => '11',
                'code' => '201111',
                'description' => 'MERCADERIAS - COSTO',
                'general_description' => 'Compras del mes',
                'document_number' => '11-000004',
                'correlative_number' => 0,
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],
            [
                'sub_diary_code' => '11',
                'code' => '611101',
                'description' => 'MERCADERIAS',
                'general_description' => 'Compras del mes',
                'document_number' => '11-000005',
                'correlative_number' => 0,
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],
            [
                'sub_diary_code' => '15',
                'code' => '941101',
                'description' => 'GASTOS DE ADMINISTRACION',
                'general_description' => 'Recibo por honorarios',
                'document_number' => '15-000004',
                'correlative_number' => 0,
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],
            [
                'sub_diary_code' => '15',
                'code' => '951101',
                'description' => 'GASTOS DE VENTAS',
                'general_description' => 'Recibo por honorarios',
                'document_number' => '15-000005',
                'correlative_number' => 0,
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],
            [
                'sub_diary_code' => '15',
                'code' => '791101',
                'description' => 'CARGAS IMPUTABLES A CUENTAS DE COSTOS Y GASTOS',
                'general_description' => 'Recibo por honorarios',
                'document_number' => '15-000006',
                'correlative_number' => 0,
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],

            [
                'sub_diary_code' => '35',
                'code' => '941101',
                'description' => 'GASTOS DE ADMINISTRACION',
                'general_description' => 'Planilla de sueldos',
                'document_number' => '31-000008',
                'correlative_number' => 0,
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],
            [
                'sub_diary_code' => '35',
                'code' => '951101',
                'description' => 'GASTOS DE VENTAS',
                'general_description' => 'Planilla de sueldos',
                'document_number' => '31-000009',
                'correlative_number' => 0,
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],
            [
                'sub_diary_code' => '35',
                'code' => '791101',
                'description' => 'CARGAS IMPUTABLES A CUENTAS DE COSTOS Y GASTOS',
                'general_description' => 'Planilla de sueldos',
                'document_number' => '31-000010',
                'correlative_number' => 0,
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
                'created_at' => '2025-08-17 12:17:19',
                'updated_at' => '2025-08-17 12:17:19'
            ],
        ];

        $table->insert($toInsert);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('sub_diary_items')->whereIn('created_at', ['2025-08-17 12:17:19'])->delete();
    }
}
