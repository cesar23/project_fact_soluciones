<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertLedgerAccountMovement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::table('ledger_account_movements')->insert([
            [
                'code' => '10',
                'name' => 'EFECTIVO Y EQUIVALENTES DE EFECTIVO',
                'debit_description' => 'Se debita por ingresos de efectivo en caja y reembolsos de caja chica, por depósitos bancarios y otras entradas de dinero. También se registran ajustes por tipo de cambio al alza y notas de abono.',
                'credit_description' => 'Se acredita por salidas de efectivo para pagos en caja o banco, transferencias, cheques u otros desembolsos. También se incluyen ajustes por tipo de cambio a la baja y notas de cargo.',
            ],
            [
                'code' => '12',
                'name' => 'CUENTAS POR COBRAR COMERCIALES - TERCEROS',
                'debit_description' => 'Se debita por las ventas de bienes o servicios, relacionados con el giro del negocio, que se realizan al crédito.',
                'credit_description' => 'Se acredita por el cobro total o parcial de las deudas, devoluciones de mercaderías o anulación de facturas.',
            ],
            [
                'code' => '14',
                'name' => 'CUENTAS POR COBRAR AL PERSONAL, ACCIONISTAS Y DIRECTORES',
                'debit_description' => 'Se debita por los préstamos otorgados al personal, accionistas, directores, gerentes y personal administrativo.',
                'credit_description' => 'Se acredita por los cobros de los préstamos otorgados al personal, accionistas, directores, gerentes y personal administrativo.',
            ],
            [
                'code' => '16',
                'name' => 'CUENTAS POR COBRAR DIVERSAS - TERCEROS',
                'debit_description' => 'Se debita por los préstamos otorgados, reclamaciones a terceros, depósitos en garantía, entregas a rendir cuenta y otros conceptos por cobrar.',
                'credit_description' => 'Se acredita por el cobro de los préstamos, recuperación de reclamos, devolución de garantías y rendición de cuentas.',
            ],
            [
                'code' => '18',
                'name' => 'SERVICIOS Y OTROS CONTRATADOS POR ANTICIPADO',
                'debit_description' => 'Se debita por los pagos anticipados de seguros, alquileres, intereses y otros servicios a devengarse en fechas posteriores.',
                'credit_description' => 'Se acredita por el devengo de los gastos pagados por anticipado.',
            ],
            [
                'code' => '20',
                'name' => 'MERCADERÍAS',
                'debit_description' => 'Se debita por el costo de las mercaderías adquiridas, devoluciones de ventas y otros ingresos de mercaderías al almacén.',
                'credit_description' => 'Se acredita por el costo de las mercaderías vendidas, devoluciones a proveedores y otros egresos de mercaderías.',
            ],
            [
                'code' => '33',
                'name' => 'INMUEBLES, MAQUINARIA Y EQUIPO',
                'debit_description' => 'Se debita por el costo de adquisición de terrenos, edificios, maquinarias, unidades de transporte y otros activos fijos.',
                'credit_description' => 'Se acredita por la venta, baja o transferencia de los activos fijos.',
            ],
            [
                'code' => '40',
                'name' => 'TRIBUTOS POR PAGAR',
                'debit_description' => 'Se debita por el pago de tributos, contribuciones y otros impuestos.',
                'credit_description' => 'Se acredita por el reconocimiento de la obligación tributaria por pagar.',
            ],
            [
                'code' => '41',
                'name' => 'REMUNERACIONES Y PARTICIPACIONES POR PAGAR',
                'debit_description' => 'Se debita por el pago de remuneraciones, participaciones y beneficios sociales a los trabajadores.',
                'credit_description' => 'Se acredita por las remuneraciones, participaciones y beneficios sociales por pagar a los trabajadores.',
            ],
            [
                'code' => '42',
                'name' => 'CUENTAS POR PAGAR COMERCIALES - TERCEROS',
                'debit_description' => 'Se debita por los pagos efectuados a los proveedores, las devoluciones de mercaderías y anulación de facturas.',
                'credit_description' => 'Se acredita por las compras de bienes y servicios al crédito.',
            ],
            [
                'code' => '46',
                'name' => 'CUENTAS POR PAGAR DIVERSAS - TERCEROS',
                'debit_description' => 'Se debita por el pago de las obligaciones por préstamos recibidos, reclamos y otras deudas.',
                'credit_description' => 'Se acredita por préstamos recibidos, reclamos de terceros y otras obligaciones contraídas.',
            ],
            [
                'code' => '50',
                'name' => 'CAPITAL',
                'debit_description' => 'Se debita por la reducción de capital, recompra de acciones y pérdidas acumuladas.',
                'credit_description' => 'Se acredita por el aporte inicial de capital, aumentos de capital y capitalización de utilidades.',
            ],
            [
                'code' => '59',
                'name' => 'RESULTADOS ACUMULADOS',
                'debit_description' => 'Se debita por las pérdidas acumuladas, distribución de utilidades y ajustes de ejercicios anteriores desfavorables.',
                'credit_description' => 'Se acredita por las utilidades acumuladas y ajustes favorables de ejercicios anteriores.',
            ],
            [
                'code' => '60',
                'name' => 'COMPRAS',
                'debit_description' => 'Se debita por el importe de las compras de mercaderías, materias primas, suministros y otros bienes.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '61',
                'name' => 'VARIACIÓN DE EXISTENCIAS',
                'debit_description' => 'Se debita por la disminución de existencias determinada por el inventario físico.',
                'credit_description' => 'Se acredita por el aumento de existencias determinado por el inventario físico.',
            ],
            [
                'code' => '62',
                'name' => 'GASTOS DE PERSONAL, DIRECTORES Y GERENTES',
                'debit_description' => 'Se debita por los gastos de personal, directores y gerentes devengados en el período.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '63',
                'name' => 'GASTOS DE SERVICIOS PRESTADOS POR TERCEROS',
                'debit_description' => 'Se debita por los servicios prestados por terceros devengados en el período.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '64',
                'name' => 'GASTOS POR TRIBUTOS',
                'debit_description' => 'Se debita por los tributos que representan gasto o costo para la empresa.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '65',
                'name' => 'OTROS GASTOS DE GESTIÓN',
                'debit_description' => 'Se debita por los gastos de gestión, como seguros, suscripciones, regalías y otros gastos diversos.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '67',
                'name' => 'GASTOS FINANCIEROS',
                'debit_description' => 'Se debita por los intereses, comisiones y otros gastos financieros devengados.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '68',
                'name' => 'VALUACIÓN Y DETERIORO DE ACTIVOS Y PROVISIONES',
                'debit_description' => 'Se debita por las depreciaciones, amortizaciones, estimaciones de deterioro y provisiones del ejercicio.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '69',
                'name' => 'COSTO DE VENTAS',
                'debit_description' => 'Se debita por el costo de las mercaderías, productos terminados o servicios vendidos.',
                'credit_description' => 'Se acredita por el traslado del saldo al cierre del ejercicio.',
            ],
            [
                'code' => '70',
                'name' => 'VENTAS',
                'debit_description' => 'Se debita por el traslado del saldo al cierre del ejercicio.',
                'credit_description' => 'Se acredita por las ventas de mercaderías, productos o servicios.',
            ],
            [
                'code' => '75',
                'name' => 'OTROS INGRESOS DE GESTIÓN',
                'debit_description' => 'Se debita por el traslado del saldo al cierre del ejercicio.',
                'credit_description' => 'Se acredita por otros ingresos de gestión como alquileres, comisiones y regalías.',
            ],
            [
                'code' => '77',
                'name' => 'INGRESOS FINANCIEROS',
                'debit_description' => 'Se debita por el traslado del saldo al cierre del ejercicio.',
                'credit_description' => 'Se acredita por los intereses y otros ingresos financieros devengados.',
            ],
            [
                'code' => '79',
                'name' => 'CARGAS IMPUTABLES A CUENTAS DE COSTOS Y GASTOS',
                'debit_description' => 'Se debita por el traslado de los gastos por naturaleza a los centros de costos correspondientes.',
                'credit_description' => 'Se acredita por la asignación de gastos a las cuentas de costos y gastos por destino.',
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('ledger_account_movements')->delete();
    }
}