<?php

namespace Modules\Finance\Http\Resources;

use App\Models\Tenant\BillOfExchange;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\UnpaidTrait;


class UnpaidCollection extends ResourceCollection
{

    use UnpaidTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {

            $total_to_pay = $this->getTotalToPay($row);
            // $total_to_pay = (float)$row->total - (float)$row->total_payment;

            $delay_payment = null;
            $date_of_due = null;
            $number_full = $row->number_full;
            if ($total_to_pay > 0) {
                if ($row->document_type_id) {
                    $invoice = Invoice::where('document_id', $row->id)->first();
                    if ($invoice) {
                        $due = Carbon::parse($invoice->date_of_due); // $invoice->date_of_due;
                        $date_of_due = $invoice->date_of_due->format('Y/m/d');
                        $now = Carbon::now();
                        if ($now > $due) {
                            $delay_payment = $now->diffInDays($due);
                        }
                    }
                }
            }
            $guides = null;
            $date_payment_last = '';
            if ($row->document_type_id) {
                $guides = Dispatch::where('reference_document_id', $row->id)->orderBy('series')->orderBy('number', 'desc')->get()->transform(function ($item) {
                    return [
                        'id' => $item->id,
                        'external_id' => $item->external_id,
                        'number' => $item->number_full,
                        'date_of_issue' => $item->date_of_issue->format('Y-m-d'),
                        'date_of_shipping' => $item->date_of_shipping->format('Y-m-d'),
                        'download_external_xml' => $item->download_external_xml,
                        'download_external_pdf' => $item->download_external_pdf,
                    ];
                });
                $date_payment_last = DocumentPayment::where('document_id', $row->id)->orderBy('date_of_payment', 'desc')->first();
            } else {
                $date_payment_last = SaleNotePayment::where('sale_note_id', $row->id)->orderBy('date_of_payment', 'desc')->first();
            }

            $purchase_order = null;
            $date_of_issue = $row->date_of_issue;
            if ($row->type == 'document') {
                $document = Document::find($row->id);
                $web_platforms = $document->getPlatformThroughItems();
                $purchase_order = $document->purchase_order;

            } elseif ($row->type == 'sale_note') {
                $document = SaleNote::find($row->id);
                $web_platforms = $document->getPlatformThroughItems();
                $purchase_order = $document->purchase_order;
                $now = Carbon::now();
                if ($document->due_date) {
                    $date_of_due = $document->due_date->format('Y/m/d');
                } else {
                    $date_of_due =  $document->date_of_issue->format('Y/m/d');
                }
                if ($now > Carbon::parse($date_of_due)) {
                    $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                }
            } elseif ($row->type == 'bill_of_exchange') {
                $document = BillOfExchange::find($row->id);
                $number_full = $document->number_full;

                $date_of_issue = $document->created_at->format('Y/m/d');
                $web_platforms = [];
                $purchase_order = null;
                $due = Carbon::parse($document->date_of_due);
                $now = Carbon::now();
                $date_of_due = $due->format('Y/m/d');
                if ($now > $due) {
                    $delay_payment = $now->diffInDays($due);
                }
            } elseif ($row->type == 'document_fee') {
                $delay_payment = 0;
                $now = Carbon::now();
                $document_fee = DocumentFee::find($row->id);
                $document_id = $document_fee->document_id;
                $document = Document::select('documents.id', 'documents.date_of_issue', 'documents.series', 'documents.number')->find($document_id);
                $note = Note::where('affected_document_id', $document_id)->first();
                $amount_to_rest = 0;
                if ($note) {
                    $amount_to_rest = $note->document->amount;
                }
                $date_of_issue = $document->date_of_issue->format('Y/m/d');
                $number_full = $document->series . '-' . $document->number;
                $date_of_due = $row->date_of_issue;
                $parse_date_of_due = Carbon::createFromFormat('Y/m/d', $date_of_due);
                if ($now > $parse_date_of_due) {
                    $delay_payment = $now->diffInDays($parse_date_of_due);
                }
                $web_platforms = [];
                $purchase_order = null;
            } else {
                $web_platforms = new \Illuminate\Database\Eloquent\Collection();
            }
            return [
                'id' => $row->id,
                'date_of_issue' => $date_of_issue,
                'customer_name' => $row->customer_name,
                'customer_internal_code' => isset($document->customer->internal_code) ? $document->customer->internal_code : null,
                'customer_trade_name' => isset($document->customer->trade_name) ? $document->customer->trade_name : null,
                'customer_id' => $row->customer_id,
                'number_full' => $number_full,
                'total' => number_format((float)$row->total, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'type' => $row->type,
                'guides' => $guides,
                'date_payment_last' => ($date_payment_last) ? $date_payment_last->date_of_payment->format('Y-m-d') : null,
                'delay_payment' => $delay_payment,
                'date_of_due' => $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                "user_id" => $row->user_id,
                "username" => $row->username,
                "total_subtraction" => $row->total_subtraction,
                "total_credit_notes" => $this->getTotalCreditNote($row),
                "total_payment" => $row->total_payment,
                "web_platforms" => $web_platforms,
                "purchase_order" => $purchase_order,
            ];
        });
    }
}
