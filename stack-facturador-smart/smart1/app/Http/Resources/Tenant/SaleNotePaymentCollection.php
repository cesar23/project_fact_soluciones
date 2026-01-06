<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\BankAccount;
use App\Models\Tenant\Cash;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SaleNotePaymentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $hasQuotation = $request->hasQuotation;
        $quotationNumber = $request->quotationNumber;
        $user = auth()->user();
        return $this->collection->transform(function($row, $key) use ($hasQuotation, $quotationNumber, $user) {
            $destination_description = ($row->global_payment) ? $row->global_payment->destination_description:null;
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
                    $cash_id = $row->sale_note->cash_id;
                }
            }

            if($hasQuotation && $destination_description == null){
                $destination_description = "Pago realizado por la cotización ".$quotationNumber;
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
            if($user->edit_payment){
                $edit_payment = true;
            }
            return [
                'edit_payment' => $edit_payment,
                'id' => $row->id,
                'detail' => $detail,
                'hasQuotation' => $hasQuotation,
                'selected' => false,
                'document_prepayment_id' => $row->document_prepayment_id,
                'sale_note_id' => $row->sale_note_id,
                'date_of_payment' => $row->date_of_payment->format('d/m/Y'),
                'payment_method_type_description' => $row->payment_method_type->description,
                'destination_description' => $destination_description,
                'reference' => $row->reference,
                'filename' => ($row->payment_file) ? $row->payment_file->filename:null,
                'payment' => $row->payment,
            ];
        });
    }
}