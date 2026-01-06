<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CopyNameToDescriptionItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $chunkSize = 1000; // Procesar 1000 registros a la vez
        
        DB::table('items')
            ->whereNull('description')
            ->whereNotNull('name')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($items) {
                $ids = $items->pluck('id')->toArray();
                
                DB::table('items')
                    ->whereIn('id', $ids)
                    ->update(['description' => DB::raw('name')]);
            });
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
