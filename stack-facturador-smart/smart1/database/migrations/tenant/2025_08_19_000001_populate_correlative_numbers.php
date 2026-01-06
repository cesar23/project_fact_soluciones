<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class PopulateCorrelativeNumbers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Obtener todos los registros agrupados por código y mes
        $groups = DB::connection('tenant')
            ->table('account_sub_diaries')
            ->select('code', 'account_month_id')
            ->whereNotNull('account_month_id')
            ->groupBy('code', 'account_month_id')
            ->get();

        foreach ($groups as $group) {
            // Obtener registros del grupo ordenados por fecha y ID
            $records = DB::connection('tenant')
                ->table('account_sub_diaries')
                ->where('code', $group->code)
                ->where('account_month_id', $group->account_month_id)
                ->orderBy('date')
                ->orderBy('id')
                ->get(['id', 'is_manual', 'correlative_number']);

            // Separar manuales de automáticos
            $manuales = $records->where('is_manual', true)->where('correlative_number', '!=', null);
            $automaticos = $records->where('is_manual', false)->concat(
                $records->where('is_manual', true)->where('correlative_number', null)
            );

            // Obtener correlativos ocupados por manuales
            $ocupados = $manuales->pluck('correlative_number')->toArray();

            // Asignar correlativos a automáticos
            $correlativo_actual = 1;
            foreach ($automaticos as $record) {
                // Buscar el siguiente correlativo disponible
                while (in_array($correlativo_actual, $ocupados)) {
                    $correlativo_actual++;
                }

                // Actualizar el registro
                DB::connection('tenant')
                    ->table('account_sub_diaries')
                    ->where('id', $record->id)
                    ->update(['correlative_number' => $correlativo_actual]);

                // Marcar como ocupado
                $ocupados[] = $correlativo_actual;
                $correlativo_actual++;
            }
        }

        // También procesar registros sin account_month_id (usar fecha)
        $recordsWithoutMonth = DB::connection('tenant')
            ->table('account_sub_diaries')
            ->whereNull('account_month_id')
            ->whereNotNull('date')
            ->get();

        // Agrupar por código y mes de la fecha
        $groupedByDate = $recordsWithoutMonth->groupBy(function($item) {
            $date = \Carbon\Carbon::parse($item->date);
            return $item->code . '-' . $date->format('Y-m');
        });

        foreach ($groupedByDate as $key => $records) {
            // Ordenar por fecha y ID
            $sorted = $records->sortBy([
                ['date', 'asc'],
                ['id', 'asc']
            ]);

            // Separar manuales de automáticos
            $manuales = $sorted->where('is_manual', true)->where('correlative_number', '!=', null);
            $automaticos = $sorted->where('is_manual', false)->concat(
                $sorted->where('is_manual', true)->where('correlative_number', null)
            );

            // Obtener correlativos ocupados
            $ocupados = $manuales->pluck('correlative_number')->toArray();

            // Asignar correlativos
            $correlativo_actual = 1;
            foreach ($automaticos as $record) {
                while (in_array($correlativo_actual, $ocupados)) {
                    $correlativo_actual++;
                }

                DB::connection('tenant')
                    ->table('account_sub_diaries')
                    ->where('id', $record->id)
                    ->update(['correlative_number' => $correlativo_actual]);

                $ocupados[] = $correlativo_actual;
                $correlativo_actual++;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No hacer nada en el rollback para preservar datos
        // Si necesitas limpiar, puedes descomentar la siguiente línea:
        // DB::connection('tenant')->table('account_sub_diaries')->update(['correlative_number' => null]);
    }
}
