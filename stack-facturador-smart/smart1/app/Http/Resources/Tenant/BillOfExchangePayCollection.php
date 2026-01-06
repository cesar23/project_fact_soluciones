<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\BillOfExchangeDocumentPay;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;

/**
 * Class SaleNoteCollection
 *
 * @package App\Http\Resources\Tenant
 */
class BillOfExchangePayCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            $documents = BillOfExchangeDocumentPay::where('bill_of_exchange_id', $row->id)->get()
            ->transform(function($row){
                $document = $row->document;
                return [
                    "number_full" => $document ? $document->number_full : "-", 
                ];
            })
            ;
            $payments = $row->payments->sum('payment');
            $total =  $row->total - $payments;
            return [
                'documents' => $documents,
                'id' => $row->id,
                'date_of_due' => Carbon::parse($row->date_of_due)->format('Y-m-d'),
                'number' => $row->number,
                'customer_name' => $row->supplier->name,
                'customer_number' => $row->supplier->number,
                'series' => $row->series,
                'full_number' => $row->series.'-'.$row->number,
                'establishment' => $row->establishment,
                'establishment_id' => $row->establishment_id,
                'user' => $row->user,
                'total' => $total,
                'exchange_rate_sale' => $row->exchange_rate_sale,
                'currency_type_id' => $row->currency_type_id,
            ];
        });
    }
}
