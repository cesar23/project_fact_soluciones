<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_111119_update_sub_items_diaries_01_01


class UpdateSubItemsDiaries0101 extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {

        $table = DB::table('sub_diary_items');
        $table->where('sub_diary_code', '01')->delete();
        $toInsert = [
            [
                'sub_diary_code' => '01',
                'code' => '104101',
                'description' => 'BANCO DE CREDITO',
                'general_description' => 'Cobro a clientes',
                'correlative_number' => '01-000001',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '121201',
                'description' => 'FACTURAS POR COBRAR EMITIDAS CARTERA TERCEROS M.N.',
                'general_description' => 'Cobro a clientes',
                'correlative_number' => '01-000002',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '421201',
                'description' => 'FACTURAS EMITIDAS POR PAGAR M.N. TERCEROS',
                'general_description' => 'Pago a proveedores',
                'correlative_number' => '01-000003',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '104101',
                'description' => 'BANCO DE CREDITO',
                'general_description' => 'Pago a proveedores',
                'correlative_number' => '01-000004',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '411101',
                'description' => 'SUELDOS',
                'general_description' => 'Pago a trabajadores',
                'correlative_number' => '01-000005',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '104101',
                'description' => 'BANCO DE CREDITO',
                'general_description' => 'Pago a trabajadores',
                'correlative_number' => '01-000006',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '424101',
                'description' => 'HONORARIOS POR PAGAR M.N.',
                'general_description' => 'Pago de recibos por honorarios',
                'correlative_number' => '01-000007',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '104101',
                'description' => 'BANCO DE CREDITO',
                'general_description' => 'Pago de recibos por honorarios',
                'correlative_number' => '01-000008',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '639101',
                'description' => 'GASTOS BANCARIOS',
                'general_description' => 'Pago de gastos diversos',
                'correlative_number' => '01-000009',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '104101',
                'description' => 'BANCO DE CREDITO',
                'general_description' => 'Pago de gastos diversos',
                'correlative_number' => '01-000010',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '971101',
                'description' => 'GASTOS FINANCIEROS',
                'general_description' => 'Destino gastos',
                'correlative_number' => '01-000011',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '791101',
                'description' => 'CARGAS IMPUTABLES A CUENTAS DE COSTOS Y GASTOS',
                'general_description' => 'Destino gastos',
                'correlative_number' => '01-000012',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '401111',
                'description' => 'IGV - CUENTA PROPIA',
                'general_description' => 'Pago de impuestos',
                'correlative_number' => '01-000013',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '401711',
                'description' => 'RENTA DE TERCERA CATEGORIA',
                'general_description' => 'Pago de impuestos',
                'correlative_number' => '01-000014',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '104101',
                'description' => 'BANCO DE CREDITO',
                'general_description' => 'Pago de impuestos',
                'correlative_number' => '01-000015',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '401731',
                'description' => 'RENTA DE QUINTA CATEGORIA',
                'general_description' => 'Pago de planilla',
                'correlative_number' => '01-000016',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '417101',
                'description' => 'ADMINISTRADORAS DE FONDOS DE PENSIONES',
                'general_description' => 'Pago de planilla',
                'correlative_number' => '01-000017',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '403101',
                'description' => 'ESSALUD',
                'general_description' => 'Pago de planilla',
                'correlative_number' => '01-000018',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '403201',
                'description' => 'ONP',
                'general_description' => 'Pago de planilla',
                'correlative_number' => '01-000019',
                'debit' => 1,
                'credit' => 0,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ],
            [
                'sub_diary_code' => '01',
                'code' => '104101',
                'description' => 'BANCO DE CREDITO',
                'general_description' => 'Pago de planilla',
                'correlative_number' => '01-000020',
                'debit' => 0,
                'credit' => 1,
                'debit_amount' => 0.00,
                'credit_amount' => 0.00,
            ]
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
        $table = DB::table('sub_diary_items');
        $table->where('sub_diary_code', '01')->delete();
    }
}
