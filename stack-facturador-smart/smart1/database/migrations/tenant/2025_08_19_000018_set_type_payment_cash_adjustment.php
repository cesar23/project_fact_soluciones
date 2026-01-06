<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SetTypePaymentCashAdjustment extends Migration
{
    public function up()
    {
        $search_to_replace = [
            [
                'search' => 'Modules\\SaleNote\\Models\\SaleNotePayment',
                'replace' => 'App\\Models\\Tenant\\SaleNotePayment',
            ],
            [
                'search' => 'Modules\\Document\\Models\\DocumentPayment',
                'replace' => 'App\\Models\\Tenant\\DocumentPayment',
            ],
            [
                'search' => 'Modules\\Purchase\\Models\\PurchasePayment',
                'replace' => 'App\\Models\\Tenant\\PurchasePayment',
            ],
        ];

        $connection = DB::connection('tenant');


        // Primero, veamos qué tipos de payment_type existen en la base de datos
        $existing_types = $connection->table('global_payments')
            ->select('payment_type')
            ->distinct()
            ->get();



        foreach ($search_to_replace as $item) {


            // Usar una consulta raw para ver exactamente qué está pasando
            $records = $connection->select(
                "SELECT COUNT(*) as count FROM global_payments WHERE payment_type = ?",
                [$item['search']]
            );

            $count = $records[0]->count;

            if ($count > 0) {
                $connection->update(
                    "UPDATE global_payments SET payment_type = ? WHERE payment_type = ?",
                    [$item['replace'], $item['search']]
                );
            }
        }
        $search_to_replace_destination = [
            [
                'search' => 'Modules\\Cash\\Models\\Cash',
                'replace' => 'App\\Models\\Tenant\\Cash',
            ]
        ];

        foreach ($search_to_replace_destination as $item) {
            $connection->update(
                "UPDATE global_payments SET destination_type = ? WHERE destination_type = ?",
                [$item['replace'], $item['search']]
            );
        }
    }

    public function down()
    {
        // No es necesario un rollback específico
    }
}
