<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageHandlerResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $serie = Series::where('number', $this->series)->first();
        $customers = Person::whereIn('id',[$this->seller_id,
        $this->driver_id,
        $this->issuer_id,
        ])->get()->transform(function (Person $row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'seller' => $row->seller,
                'seller_id' => $row->seller_id,
                'name' => $row->name,
                'number' => $row->number,
                'is_driver' => $row->is_driver,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code
            ];
        });
        return [
            'customers' => $customers,
            'id' => $this->id,
            'establishment' => $this->establishment,
            'date_of_issue' => Carbon::parse($this->date_of_issue)->format('Y-m-d'),
            'number' => $this->number,
            'establishment_id' => $this->establishment_id,
            'series_id' => ($serie) ? $serie->id : null,
            'sender_id' => $this->sender_id,
            'issuer_id' => $this->issuer_id,
            'driver_id' => $this->driver_id,
            'seller_id' => $this->seller_id,
            'sender_name' => $this->sender->name,
            'sender_number' => $this->sender->number,
            'issuer_name' => $this->issuer->name,
            'issuer_number' => $this->issuer->number,
            'departure' => $this->departure,
            'arrival' => $this->arrival,
            'currency_type_id' => $this->currency_type_id,
            'total' => $this->total,
            'payments' => self::getTransformPayments($this->payments),
            'license_plate' => $this->license_plate,
            'print_ticket' => url("package-handler/ticket/{$this->id}"),
            'items' => $this->items,
        ];

    }

    public static function getTransformPayments($payments){
        
        return $payments->transform(function($row, $key){ 
            return [
                'id' => $row->id, 
                'sale_note_id' => $row->sale_note_id, 
                'date_of_payment' => $row->date_of_payment->format('Y-m-d'), 
                'payment_method_type_id' => $row->payment_method_type_id, 
                'has_card' => $row->has_card, 
                'card_brand_id' => $row->card_brand_id, 
                'reference' => $row->reference, 
                'payment' => $row->payment, 
                'payment_method_type' => $row->payment_method_type, 
                'payment_destination_id' => ($row->global_payment) ? ($row->global_payment->type_record == 'cash' ? 'cash':$row->global_payment->destination_id):null, 
                'payment_filename' => ($row->payment_file) ? $row->payment_file->filename:null, 
            ];
        }); 

    }
}
