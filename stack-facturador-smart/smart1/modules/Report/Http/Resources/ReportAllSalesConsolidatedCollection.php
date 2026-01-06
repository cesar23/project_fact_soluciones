<?php

    namespace Modules\Report\Http\Resources;

use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\PersonRegModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    use Illuminate\Support\Collection;
    use Modules\Report\Helpers\UserCommissionHelper;


    class ReportAllSalesConsolidatedCollection extends ResourceCollection
    {
        /**
         * Transform the resource collection into an array.
         *
         * @param Request $request
         *
         * @return Collection
         */
        public function toArray($request)
        {
            $payment_methods = PaymentMethodType::all();
            $person_reg = PersonRegModel::all();
            $data = $this->collection->transform(function ($row, $key) use ($payment_methods, $person_reg)  {    
                $detraction = 0;
                if($row->detraction) {
                    $detraction = json_decode($row->detraction);
                    $detraction = $detraction->amount;
                }
                $now = Carbon::now();
                $date_of_issue = Carbon::parse($row->date_of_issue);
                $days_of_delay = $now->diffInDays($date_of_issue);
                return [
                'id' => $row->id,
                'date_of_issue' => $row->date_of_issue,
                'year_of_issue' => Carbon::parse($row->date_of_issue)->year,
                'date_of_due' => $row->date_of_due,
                'customer_name' => $row->customer_name,
                'customer_number' => $row->customer_number,
                'customer_id' => $row->customer_id,
                'customer_reg' => $row->customer_reg_id ? $person_reg->find($row->customer_reg_id)->description : null,
                'document_type_id' => $row->document_type_id,
                'number_full' => $row->number_full,
                'total' => $row->total,
                'total_payment' => $row->total_payment,
                'total_credit_notes' => $row->total_credit_notes,
                'total_subtraction' => $row->total_subtraction,
                'type' => $row->type,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => $row->exchange_rate_sale,
                'user_id' => $row->user_id,
                'username' => $row->username,
                'total_discount' => $row->total_discount,
                'detraction' => number_format($detraction, 2, '.', ''),
                'observation' => $row->observation,
                'total_without_detraction' => number_format($row->total - $detraction, 2, '.', ''),
                'pending' => number_format($row->total - $row->total_payment, 2, '.', ''),
                'days_of_delay' => $days_of_delay,
                'status_due' => $row->total_subtraction > 0 ? $this->statusDue($days_of_delay) : null,
                'last_payment_date' => $row->last_payment_date,
                'last_payment_method_type_id' => $row->last_payment_method_type_id ? $payment_methods->find($row->last_payment_method_type_id)->description : null,
                'seller_name' => $row->seller_name ?? $row->username,
                'total_subtraction' => $row->total_subtraction,
                ];

            });

            return $data;
        }


        private function statusDue($days_of_delay) {
        if($days_of_delay >= 0 && $days_of_delay <= 15) {
            return "Por vencer";
        } else if($days_of_delay >= 16 && $days_of_delay <= 45) {
            return "Atrasado";
        } else if($days_of_delay > 45) {
            return "Muy atrasado";
        }
        }
    }
