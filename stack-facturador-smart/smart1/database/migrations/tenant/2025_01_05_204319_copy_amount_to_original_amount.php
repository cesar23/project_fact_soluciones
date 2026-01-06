<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204319_copy_amount_to_original_amount
class CopyAmountToOriginalAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('document_fee')
            
            ->orderBy('id')
            ->chunk(1000, function ($fees) {
                $updates = [];
                $ids = [];

                foreach ($fees as $fee) {
                    $ids[] = $fee->id;
                    $updates[] = [
                        'id' => $fee->id,
                        'original_amount' => $fee->amount
                    ];
                }

                if (!empty($updates)) {
                    DB::connection('tenant')
                        ->table('document_fee')
                        ->whereIn('id', $ids)
                        ->update(['original_amount' => DB::raw('amount')]);
                }
            });
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
