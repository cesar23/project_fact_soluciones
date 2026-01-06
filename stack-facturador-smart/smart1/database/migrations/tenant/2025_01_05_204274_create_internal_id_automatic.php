<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204274_create_internal_id_automatic

class CreateInternalIdAutomatic extends Migration
{
    public function up()
    {
        DB::table('inventory_configurations')->where('id', 1)->update(['generate_internal_id' => true]);

        $items = DB::table('items')->whereNull('internal_id')
            ->select('id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'internal_id' => strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10))
                ];
            });

        foreach (array_chunk($items->toArray(), 1000) as $chunk) {
            foreach ($chunk as $item) {
                DB::table('items')
                    ->where('id', $item['id'])
                    ->update(['internal_id' => $item['internal_id']]);
            }
        }



    


        
    }

    public function down()
    {
        

    }


}