<?php

use App\Models\Tenant\Cash;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\SaleNotePayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_06_000005_adjust_payments_to_global_payments
class AdjustPaymentsToGlobalPayments extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $cn = DB::connection('tenant');
        DB::beginTransaction();
    
        try {
            // DOCUMENT PAYMENTS
            $lastId = 0;
            do {
                $payments = $cn->table('document_payments as dp')
                    ->join('documents as d', 'd.id', '=', 'dp.document_id')
                    ->leftJoin('global_payments as gp', function($j) {
                        $j->on('gp.payment_id', '=', 'dp.id')
                          ->where('gp.payment_type', '=', DocumentPayment::class);
                    })
                    ->whereNull('gp.id')
                    ->whereNotNull('d.cash_id')
                    ->where('dp.id', '>', $lastId)         // <-- clave
                    ->orderBy('dp.id')
                    ->limit(500)
                    ->get(['dp.id','d.cash_id','d.user_id']);
    
                if ($payments->isEmpty()) break;
    
                $data = $payments->map(function($p){
                    return [
                    'soap_type_id'     => '02',
                    'destination_id'   => $p->cash_id,
                    'destination_type' => Cash::class,
                    'payment_id'       => $p->id,
                    'payment_type'     => DocumentPayment::class,
                    'user_id'          => $p->user_id,
                    'created_at'       => now(),
                    ];
                })->toArray();
    
                $cn->table('global_payments')->insert($data);
    
                $lastId = $payments->last()->id;          // <-- avanza por id
            } while (true);
    
            // SALE NOTE PAYMENTS
            $lastId = 0;
            do {
                $payments = $cn->table('sale_note_payments as snp')
                    ->join('sale_notes as sn', 'sn.id', '=', 'snp.sale_note_id')
                    ->leftJoin('global_payments as gp', function($j) {
                        $j->on('gp.payment_id', '=', 'snp.id')
                          ->where('gp.payment_type', '=', SaleNotePayment::class);
                    })
                    ->whereNull('gp.id')
                    ->whereNotNull('sn.cash_id')
                    ->where('snp.id', '>', $lastId)        // <-- clave
                    ->orderBy('snp.id')
                    ->limit(500)
                    ->get(['snp.id','sn.cash_id','sn.user_id']);
    
                if ($payments->isEmpty()) break;
    
                $data = $payments->map(function($p){
                    return [
                    'soap_type_id'     => '02',
                    'destination_id'   => $p->cash_id,
                    'destination_type' => Cash::class,
                    'payment_id'       => $p->id,
                    'payment_type'     => SaleNotePayment::class,
                    'user_id'          => $p->user_id,
                    'created_at'       => now(),
                    ];
                })->toArray();
    
                $cn->table('global_payments')->insert($data);
    
                $lastId = $payments->last()->id;          // <-- avanza por id
            } while (true);
    
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}