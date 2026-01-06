<?php

    namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use Illuminate\Http\Resources\Json\ResourceCollection;
    use App\Models\Tenant\Person;
    /**
     * Class SaleNoteCollection
     *
     * @package App\Http\Resources\Tenant
     */
    class SaleNoteHistoryCollection extends ResourceCollection {
        /**
         * Transform the resource collection into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array|\Illuminate\Support\Collection
         */
        public function toArray($request) {
            return $this->collection->transform(function ($row, $key)  {

                $total_paid = number_format($row->payments->sum('payment'), 2, ".", "");
                $total_pending_paid = number_format($row->total - $total_paid, 2, ".", "");

    
                $due_date = (!empty($row->due_date)) ? $row->due_date->format('Y-m-d') : null;
                $date_pay=$row->payments;
                $payment='';
                if (count($date_pay)>0) {
                    foreach ($date_pay as $pay) {
                        $payment=$pay->date_of_payment->format('Y-m-d');
                    }
                }
            
                $payments = $this->getTransformPayments($row);
            $number = $row->customer ? $row->customer->number : $row->supplier->number;
            $name = $row->customer ? $row->customer->name : $row->supplier->name;
            $email = $row->customer ? $row->customer->email : $row->supplier->email;
            $region = $row->customer ? $row->customer->department->description : $row->supplier->department->description;
                
                return [
        
                    'id'                           => $row->id,
                    'payments'                     => $payments,    
                    'date_of_issue'                => is_string($row->date_of_issue) ? $row->date_of_issue : $row->date_of_issue->format('Y-m-d'),
                    'time_of_issue'                => is_string($row->time_of_issue) ? $row->time_of_issue : $row->time_of_issue->format('H:i:s'),
                    'identifier'                   => $row->identifier,
                    'full_number'                  => $row->series.'-'.$row->number,
                    'customer_name'                => $name,
                    'customer_number'              => $number,
                    'customer_email'               => $email,
                    'customer_region'              => $region,
                    'currency_type_id'             => $row->currency_type_id,
                    'total_exportation'            => number_format($row->total_exportation, 2),
                    'total_free'                   => number_format($row->total_free, 2),
                    'total_unaffected'             => number_format($row->total_unaffected, 2),
                    'total_exonerated'             => number_format($row->total_exonerated, 2),
                    'total_taxed'                  => number_format($row->total_taxed, 2),
                    'total_igv'                    => number_format($row->total_igv, 2),
                    'total'                        => number_format($row->total, 2),
                    'state_type_id'                => $row->state_type_id,
                    'state_type_description'       => $row->state_type->description,
                    'observation'                 => $row->observation,
                    'created_at'                   => $row->created_at->format('Y-m-d H:i:s'),
                    'updated_at'                   => $row->updated_at->format('Y-m-d H:i:s'),
                    'paid'                         => (bool)$row->paid,
                    'total_canceled'               => (bool)$row->total_canceled,
                    'total_paid'                   => $total_paid,
                    'observation'                   => $row->observation,
                    'total_pending_paid'           => $total_pending_paid,
                    'user_name'                    => ($row->user) ? $row->user->name : '',
                    'quotation_number_full'        => ($row->quotation) ? $row->quotation->number_full : '',
                    'sale_opportunity_number_full' => isset($row->quotation->sale_opportunity) ? $row->quotation->sale_opportunity->number_full : '',
                    'number_full'                  => $row->number_full,
                    'print_a4'                     => url('')."/sale-notes/print/{$row->external_id}/a4",
                    'due_date'                     => $due_date,
                    'date_of_payment'              => $payment,
                    'exchange_rate_sale' => $row->exchange_rate_sale,

                ];
            });
        }

        public function getTransformPayments($row)
        {
            $payments = $row->payments()->get();
            return $payments->transform(function ($row, $key) {
                return [
                    'id' => $row->id,
                    'sale_note_id' => $row->sale_note_id ?? $row->purchase_id,
                    'date_of_payment' => $row->date_of_payment->format('Y-m-d'),
                    'payment_method_type_id' => $row->payment_method_type_id,
                    'has_card' => $row->has_card,
                    'card_brand_id' => $row->card_brand_id,
                    'reference' => $row->reference,
                    'payment' => $row->payment,
                    'payment_method_type' => $row->payment_method_type,
                    'payment_destination_id' => ($row->global_payment) ? ($row->global_payment->type_record == 'cash' ? 'cash' : $row->global_payment->destination_id) : null,
                    'payment_filename' => ($row->payment_file) ? $row->payment_file->filename : null,
                ];
            });
        }
    }
