<?php

namespace App\Http\Resources\Tenant;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class CreditNoteReportCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $credit_notes_ids = $this->collection->pluck('id')->toArray();
        $connection = DB::connection('tenant');
        $notes_data = $connection->table('notes')->whereIn('notes.document_id', $credit_notes_ids)
            ->leftJoin('documents', 'documents.id', '=', 'notes.affected_document_id')
            ->leftJoin('sale_notes', 'sale_notes.id', '=', 'notes.affected_sale_note_id')
            ->select(
                'notes.id as note_id',
                'notes.*',
                'documents.series as document_series',
                'documents.number as document_number',
                'sale_notes.number as sale_note_number',
                'sale_notes.series as sale_note_series'
            )
            ->get()->keyBy('document_id');
        $notes_data_ids = $connection->table('notes')->whereIn('notes.document_id', $credit_notes_ids)
        ->pluck('id')->toArray();
    
        $used_data = $connection->table('payments_with_credit_note')->whereIn('note_id', $notes_data_ids)
        ->join('notes', 'notes.id', '=', 'payments_with_credit_note.note_id')
        ->leftJoin('document_payments', 'document_payments.id', '=', 'payments_with_credit_note.document_payment_id')
        ->leftJoin('documents', 'documents.id', '=', 'document_payments.document_id')
        ->leftjoin('sale_note_payments', 'sale_note_payments.id', '=', 'payments_with_credit_note.sale_note_payment_id')
        ->leftjoin('sale_notes', 'sale_notes.id', '=', 'sale_note_payments.sale_note_id')
        ->leftjoin('expense_payments', 'expense_payments.id', '=', 'payments_with_credit_note.expense_payment_id')
        ->leftjoin('expenses', 'expenses.id', '=', 'expense_payments.expense_id')
        ->select(
            'notes.id as note_id',
            'notes.document_id as note_document_id',
            'payments_with_credit_note.*',
            'document_payments.payment as document_payment_amount',
            'sale_note_payments.payment as sale_note_payment_amount',
            'expense_payments.payment as expense_payment_amount',
            'expenses.number as expense_number',
            DB::raw("'GASTO' as expense_series"),
            'documents.number as document_number',
            'documents.series as document_series',
            'sale_notes.number as sale_note_number',
            'sale_notes.series as sale_note_series'
        )
        ->get()->keyBy('note_document_id');
        $users_data = $connection->table('users')->whereIn('id', $this->collection->pluck('user_id')->toArray())->get()->keyBy('id');
        $states_internal = ["55", "56"];
        return $this->collection->transform(function ($row, $key) use ($states_internal, $notes_data, $used_data, $users_data) {
            $is_internal = in_array($row->state_type_id, $states_internal);
            
            // Usar get() para evitar errores de índice indefinido
            $document_affected = $notes_data->get($row->id, null);
            $document_affected_number = null;
            if($document_affected){
                if($document_affected->sale_note_number){
                    $document_affected_number = $document_affected->sale_note_series . '-' . $document_affected->sale_note_number;
                }else{
                    $document_affected_number = $document_affected->document_series . '-' . $document_affected->document_number;
                }
            }
            $used_data_item = $used_data->get($row->id, null);
            $used_affected_number = null;
            if($used_data_item){
                if($used_data_item->sale_note_number){
                    $used_affected_number = $used_data_item->sale_note_series . '-' . $used_data_item->sale_note_number;
                }else if($used_data_item->expense_number){
                    $used_affected_number = $used_data_item->expense_series . '-' . $used_data_item->expense_number;
                }else{
                    $used_affected_number = $used_data_item->document_series . '-' . $used_data_item->document_number;
                }
            }
            $date_of_issue = $row->date_of_issue;
            if(is_string($date_of_issue)){
                $date_of_issue = Carbon::parse($date_of_issue);
            }
            $date_of_issue = $date_of_issue->format('Y-m-d');
            return [
                'id' => $row->id,
                'date_of_issue' => $date_of_issue,
                'time_of_issue' => $row->time_of_issue,
                'user_name' => $users_data->get($row->user_id, null)->name,
                'customer_name' => $row->customer->name,
                'number_full' => $row->number_full,
                'internal' => (bool) $is_internal,
                'currency_type_id' => $row->currency_type_id,
                'region' => 'Lima',
                'document_affected' => $document_affected_number,
                'document_used' => $used_affected_number,
                'total' => number_format($row->total, 2, '.', ''),
            ];
        });
    }
}
