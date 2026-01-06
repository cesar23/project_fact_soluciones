<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyReceiptCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function($row) {
                $meses = [
                    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
                ];

                // Generar número de recibo
                if (empty($row->serie_receipt) && empty($row->correlative_receipt)) {
                    $receipt = '-';
                } else {
                    $receipt = $row->serie_receipt . ' - ' . $row->correlative_receipt;
                }

                // Generar descripción del período
                $period = '';
                if ($row->year) {
                    $period = $row->year;
                    if ($row->month) {
                        $monthName = $meses[$row->month - 1] ?? $row->month;
                        $period = $monthName . ' ' . $row->year;
                    }
                }

                // Estado de la deuda
                $status = $row->active ? 'Pagado' : 'Pendiente';
                $statusClass = $row->active ? 'success' : 'warning';

                // Tipo de deuda
                $debtType = '';
                if ($row->type == 'c' && $row->supplyConcept) {
                    $debtType = 'Colateral';
                } elseif ($row->type == 'a') {
                    $debtType = 'Acumulada';
                } elseif ($row->type == 'r') {
                    $debtType = 'Regular';
                } else {
                    $debtType = 'Manual';
                }

                return [
                    'id' => $row->id,
                    'supply_id' => $row->supply_id,
                    'person_id' => $row->person_id,
                    'cod_route' => $row->supply->cod_route ?? '-',
                    'old_code' => $row->supply->old_code ?? '-',
                    'person_name' => $row->person->name ?? '-',
                    'sector_name' => $row->supply->sector->name ?? '-',
                    'via_name' => $row->supply->supplyVia->name ?? '-',
                    'amount' => number_format($row->amount, 2),
                    'original_amount' => number_format($row->original_amount ?? $row->amount, 2),
                    'remaining_amount' => number_format($row->remaining_amount, 2),
                    'year' => $row->year ?? '-',
                    'month' => $row->month ? ($meses[$row->month - 1] ?? $row->month) : '-',
                    'period' => $period,
                    'generation_date' => $row->generation_date ? $row->generation_date->format('d/m/Y') : '-',
                    'due_date' => $row->due_date ? $row->due_date->format('d/m/Y') : '-',
                    'receipt' => $receipt,
                    'status' => $status,
                    'status_class' => $statusClass,
                    'debt_type' => $debtType,
                    'type' => $row->type,
                    'active' => $row->active,
                    'is_paid' => $row->is_paid,
                    'has_payments' => $row->has_payments,
                ];
            })
        ];
    }
}
