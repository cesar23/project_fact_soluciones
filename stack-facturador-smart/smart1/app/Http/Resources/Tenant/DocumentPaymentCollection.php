<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Cash;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentPaymentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            $type = get_class($row);
            $is_from_sale_note =  $type == 'App\Models\Tenant\SaleNotePayment';
            $sale_note_number = null;
            if($is_from_sale_note){
                $sale_note_number = $row->associated_record_payment->number_full;
            }
            $global_payment = $row->global_payment;
            $cash_id = null;
            if($global_payment){
                $destination_type = $global_payment->destination_type;
                if($destination_type == Cash::class){
                    $cash_id = $global_payment->destination_id;
                }
                if($cash_id == null){
                    $cash_id = $row->cash_id;
                }
                if($cash_id == null){
                    $cash_id = $row->document->cash_id;
                }
            }

    
            $detail = [];
            if($cash_id){
                $cash = Cash::find($cash_id);
                if($cash){
                    $detail = [
                        'user' => $cash->user->name,
                        'reference' => $cash->reference_number,
                        'date_opening' => $cash->date_opening,
                        'time_opening' => $cash->time_opening,
                        'date_closed' => $cash->date_closed,
                        'time_closed' => $cash->time_closed,
                        'url' => url('') . "/cash/report-a4/{$cash_id}?withBank=1"
                    ];
                }
            }
            $edit_payment = false;
            if(auth()->user()->edit_payment){
                $edit_payment = true;
            }
            return [
                'edit_payment' => $edit_payment,
                'detail' => $detail,
                'id' => $row->id,
                'is_from_sale_note' => $is_from_sale_note,
                'sale_note_number' => $sale_note_number,
                'date_of_payment' => $row->date_of_payment->format('d/m/Y'),
                'payment_method_type_description' => $row->payment_method_type->description,
                'destination_description' => ($row->global_payment) ? $row->global_payment->destination_description:null,
                'reference' => $row->reference,
                'glosa' => $row->glosa,
                'filename' => ($row->payment_file) ? $row->payment_file->filename:null,
                'payment' => $row->payment,
                'payment_received' => $row->payment_received,
                'payment_received_description' => $row->getPaymentReceivedDescription(),
            ];
        });
    }
}