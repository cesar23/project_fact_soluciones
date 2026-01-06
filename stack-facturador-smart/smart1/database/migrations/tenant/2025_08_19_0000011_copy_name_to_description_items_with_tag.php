<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_08_19_0000011_copy_name_to_description_items_with_tag
class CopyNameToDescriptionItemsWithTag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::connection('tenant')->beginTransaction();

            $chunkSize = 1000;
            $totalAffected = 0;

            DB::connection('tenant')->table('items')
                ->whereRaw('description LIKE ? AND description LIKE ?', ['%<%', '%>%'])
                ->whereNotNull('name')
                ->orderBy('id')
                ->chunkById($chunkSize, function ($items) use (&$totalAffected) {
                    $ids = $items->pluck('id')->toArray();

                    $affected = DB::table('items')
                        ->whereIn('id', $ids)
                        ->update(['description' => DB::raw('name')]);

                    $totalAffected += $affected;
                });

            DB::connection('tenant')->commit();
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No es necesario revertir ya que solo estamos copiando datos donde description es nulo
    }
}
