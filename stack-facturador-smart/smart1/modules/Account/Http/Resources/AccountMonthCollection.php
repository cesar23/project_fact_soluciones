<?php

namespace Modules\Account\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AccountMonthCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            $month_name = optional($row->month)->format('F');
            $month_name_es = $this->translateMonth($month_name);
            
            return [
                'id' => $row->id,
                'account_period_id' => $row->account_period_id,
                'month' => optional($row->month)->format('Y-m'),
                'month_name' => $month_name_es,
                'total_debit' => number_format($row->total_debit, 2, '.', ''),
                'total_credit' => number_format($row->total_credit, 2, '.', ''),
                'balance' => number_format($row->balance, 2, '.', ''),
                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
    
    private function translateMonth($month)
    {
        $translations = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];
        
        return $translations[$month] ?? $month;
    }
} 