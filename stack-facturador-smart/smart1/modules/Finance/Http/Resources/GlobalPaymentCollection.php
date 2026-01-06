<?php

namespace Modules\Finance\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Modules\Expense\Models\ExpensePayment;

class GlobalPaymentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data_persons = $this->collection->pluck('data_person')->filter(function ($person) {
            return property_exists($person, 'number') && $person->number !== '';
        });
        $customers_id = DB::connection('tenant')->table('persons')->whereIn('number', $data_persons->pluck('number'))->pluck('id')->toArray();
        $customers_data = DB::connection('tenant')->table('persons')
            ->leftJoin('zones', 'zones.id', '=', 'persons.zone_id')
            ->select('persons.number', 'zones.name as zone_name')
            ->whereIn('persons.id', $customers_id)->get()->mapWithKeys(function ($item) {
                return [$item->number => $item->zone_name ?? null];
            });
        return $this->collection->transform(function (\Modules\Finance\Models\GlobalPayment $row, $key) use ($customers_data) {

            $data_person = $row->data_person;
            $document_type = '';
            $glosa = '';
            $payment = ($row->payment) ? $row->payment : null;
            if ($payment !== null) {
                $glosa = $payment->glosa;
            }
            if ($payment !== null && $row->payment->associated_record_payment !== null && $row->payment->associated_record_payment->document_type) {

                $document_type = $row->payment->associated_record_payment->document_type->description;
            } elseif ($row->instance_type == 'technical_service') {

                $document_type = 'ST';
            } elseif ($payment !== null && isset($row->payment->associated_record_payment->prefix)) {

                $document_type = $row->payment->associated_record_payment->prefix;
            }

            $cci = $row->getCciBankAcount();
            // $cci = $row->getCciAcoount();
            $personName = $data_person->name;
            if (!is_string($personName)) {
                if (property_exists($personName, 'description')) {
                    // Los bancos con transacciones
                    $personName = $personName->description;
                }
            }
            $reason = null;
            if ($row->instance_type == 'expense') {
                $payment_id = $row->payment_id;
                if ($payment_id) {
                    $payment = ExpensePayment::find($payment_id);
                    if ($payment) {
                        $expense = $payment->expense;
                        $reason = $expense->expense_reason->description;
                    }
                }
            }
            $date_of_issue = '';
            if (in_array($row->instance_type, ['sale_note', 'document'])) {
                $date_of_issue = $row->payment->associated_record_payment->date_of_issue->format('Y-m-d');
            }
            if ($row->instance_type == 'bill_of_exchange_payment') {
                $date_of_issue = $row->payment->bill_of_exchange->created_at->format('Y-m-d');
            }
            $zone_description = $customers_data[$data_person->number] ?? null;
            return [
            
                'glosa' => $glosa,
                'date_of_issue' => $date_of_issue,
                'reason' => $reason,
                'id' => $row->id,
                'destination_description' => $row->getDestinationDescriptionPayment(),
                'cci' => $cci,
                'date_of_payment' => ($payment !== null && $row->payment->date_of_payment !== null) ? $row->payment->date_of_payment->format('Y-m-d') : '',
                'payment_method_type_description' => $this->getPaymentMethodTypeDescription($row),
                'reference' => ($payment !== null) ?  $row->payment->reference : '',
                'total' => ($payment !== null) ? $row->payment->payment : 0,
                'number_full' => ($payment !== null && $row->payment->associated_record_payment) ? $row->payment->associated_record_payment->number_full : '',
                'currency_type_id' => ($payment !== null && $row->payment->associated_record_payment != null) ? $row->payment->associated_record_payment->currency_type_id : '',
                'document_type_description' => $document_type,
                'person_name' => $personName,
                'person_number' => $data_person->number,
                'zone_description' => $zone_description,
                'instance_type' => $row->instance_type,
                'instance_type_description' => $row->instance_type_description,
                'user_id' => $row->user_id,
                'user_name' => optional($row->user)->name,
            ];
        });
    }


    public function getPaymentMethodTypeDescription($row)
    {

        $payment_method_type_description = '';

        if ($row->payment !== null && $row->payment->payment_method_type) {

            $payment_method_type_description = $row->payment->payment_method_type->description;
        } else {
            $payment_method_type_description = ($row->payment !== null  && $row->payment->expense_method_type !== null) ? $row->payment->expense_method_type->description : '';
        }

        return $payment_method_type_description;
    }
}
