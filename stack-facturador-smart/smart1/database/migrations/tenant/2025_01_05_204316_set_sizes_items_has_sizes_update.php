<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetSizesItemsHasSizesUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('items')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('item_sizes')
                    ->whereColumn('item_sizes.item_id', 'items.id');
            })
            ->orderBy('id')
            ->chunk(1000, function ($items) {
                $ids = $items->pluck('id')->toArray();
                DB::connection('tenant')->table('items')
                    ->whereIn('id', $ids)
                    ->update(['has_sizes' => 1]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        
    }
}
