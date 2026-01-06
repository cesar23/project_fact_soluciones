<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentPaymentRequest;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Resources\Tenant\DocumentPaymentCollection;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PaymentMethodType;
use App\Exports\DocumentPaymentExport;
use Exception, Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Carbon\Carbon;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Cash;
use App\Models\Tenant\CashDocument;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\Note;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\SupplyPlanDocument;
use App\Models\Tenant\User;
use Illuminate\Http\Request;

class DocumentPaymentController extends Controller
{
    use FinanceTrait, FilePaymentTrait;

    private function getSaleNotePayments($document_id)
    {
        $sale_note_payments = SaleNotePayment::where('document_prepayment_id', $document_id);

        return $sale_note_payments;
    }
    public function records($document_id)
    {
        $records = DocumentPayment::where('document_id', $document_id)->get();
        $sale_note_payments = $this->getSaleNotePayments($document_id)->get();
        $records = $records->union($sale_note_payments);
        return new DocumentPaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations(),
            'permissions' => auth()->user()->getPermissionsPayment()
        ];
    }

    public function records_fee($document_fee_id)
    {

        $records = DocumentPayment::where('document_fee_id', $document_fee_id)->get();
        return new DocumentPaymentCollection($records);
    }
    public function document_fee($document_fee_id)
    {
        $isCredit = false;
        $fee = [];
        $document_fee = DocumentFee::find($document_fee_id);
        if ($document_fee->original_amount == null) {
            $document_fee->original_amount = $document_fee->amount;
            $document_fee->save();
        }
        $total = $document_fee->original_amount;

        $total_paid = $document_fee->original_amount - $document_fee->amount;


        $total_difference = round($total - $total_paid, 2);

        return [
            'number_full' => $document_fee->document->number_full,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
            'currency_type_id' => $document_fee->document->currency_type_id,
            'exchange_rate_sale' => (float) $document_fee->document->exchange_rate_sale,
            'external_id' => $document_fee->document->external_id,
            'fee' => $fee,
            'is_credit' => $isCredit,
        ];
    }
    public function document($document_id)
    {
        $configuration = Configuration::first();
        $isCredit = false;
        $fee = [];
        $document = Document::find($document_id);
        if ($document->retention) {
            $total = $document->total - $document->retention->amount;
        } else {
            $total = $document->total;
            if($document->perception && $document->perception->amount > 0){
                $total = $document->total + $document->perception->amount;
            }
        }
        if ($configuration->bill_of_exchange_special) {
            $isCredit = $document->payment_condition_id !== '01';
            $fee = $isCredit ? $document->fee->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'date_of_payment' => $row->date->format('d/m/Y'),
                    'payment' => $row->amount,
                    'rest_amount' => $row->amount,
                    'is_canceled' => $row->is_canceled,
                    'original_amount' => $row->original_amount,
                    'payment_method_type_description' => optional($row->payment_method_type)->description,
                ];
            }) : [];
        }
        $total_paid = collect($document->payments)->sum('payment');

        $credit_notes_total = $document->getCreditNotesTotal();

        $total_difference = round($total - $total_paid - $credit_notes_total, 2);

        return [
            'number_full' => $document->number_full,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
            'currency_type_id' => $document->currency_type_id,
            'exchange_rate_sale' => (float) $document->exchange_rate_sale,
            'credit_notes_total' => $credit_notes_total,
            'external_id' => $document->external_id,
            'fee' => $fee,
            'is_credit' => $isCredit,
        ];
    }

    public function updateDocumentFees($document_id)
    {
        // Primero reseteamos todas las cuotas a su estado original
        DocumentFee::where('document_id', $document_id)
            ->update([
                'original_amount' => DB::raw('CASE WHEN original_amount IS NULL OR original_amount = 0 THEN amount ELSE original_amount END'),
                'amount' => DB::raw('CASE WHEN original_amount IS NULL OR original_amount = 0 THEN amount ELSE original_amount END'),
                'is_canceled' => false
            ]);

        // Obtenemos los pagos y las cuotas ordenadas por fecha
        $total_paid = DocumentPayment::where('document_id', $document_id)->sum('payment');
        $total_credit_notes = Note::where('notes.note_type', 'credit')
        ->where('notes.affected_document_id', $document_id)
        ->join('documents', 'notes.document_id', '=', 'documents.id')
        ->whereIn('documents.state_type_id', ['01', '03', '05', '07', '13'])
        ->sum('documents.total');
        $fees = DocumentFee::where('document_id', $document_id)
            ->orderBy('date', 'asc')
            ->get();

        $remaining_payment = $total_paid + $total_credit_notes;

        foreach ($fees as $fee) {
            if ($remaining_payment <= 0) {
                break;
            }

            // Calculamos cuánto se puede pagar de esta cuota
            $amount_to_pay = min($remaining_payment, $fee->amount);

            // Actualizamos el monto restante de la cuota
            $new_amount = $fee->amount - $amount_to_pay;

            $fee->amount = $new_amount;
            $fee->is_canceled = ($new_amount <= 0);
            $fee->save();

            // Reducimos el pago restante
            $remaining_payment -= $amount_to_pay;
        }
    }

    public function store_fee(DocumentPaymentRequest $request)
    {
        $id = $request->input('document_fee_id');
        $document_fee = DocumentFee::find($id);
        $document_id = $document_fee->document_id;
        $data =  DB::connection('tenant')->transaction(function () use ($id, $request, $document_id) {
            $record = DocumentPayment::firstOrNew(['id' => null]);
            $record->fill($request->all());
            $record->document_id = $document_id;
            $record->cash_id = User::getCashId();
            $record->document_fee_id = $id;
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->updateDocumentFees($document_id);
            $this->saveFiles($record, $request, 'documents');

            return $record;
        });




        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito',
            'id' => $data->id,
        ];
    }
    public function store(DocumentPaymentRequest $request)
    {
        // 

        $id = $request->input('id');

        $data =  DB::connection('tenant')->transaction(function () use ($id, $request) {
            $record = DocumentPayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->payment = str_replace(',', '', $request->payment);
            $record->cash_id = User::getCashId();
            $record->save();
            $record->document->auditPaymentAdded($record->payment, $record->payment_method_type->description);
            $this->createGlobalPayment($record, $request->all());
            $this->updateDocumentFees($record->document_id);
            $this->saveFiles($record, $request, 'documents');

            if(!$record->hasGlobalPayment()){
                $user_id = $record->document->user_id;
                $user_tmp = User::find($user_id);
                if($user_tmp->user_cash_id){
                    $user_id = $user_tmp->user_cash_id;
                }
                $custom_row = [
                    'user_id' => $user_id,
                    'payment_destination_id' => 'cash'
                ];
                $this->createGlobalPayment($record, $custom_row);
            }
            $document_id = $record->document_id;
            $supply_plan_document = SupplyPlanDocument::where('document_id', $document_id)->first();
            if($supply_plan_document){
                $document = Document::find($document_id);
                $total = $document->total;
                $total_payments = $document->payments->sum('payment');
                $balance = $total - $total_payments;
                if($balance <= 0){
                    $supply_plan_document->status = 'PAID';
                    $supply_plan_document->save();
                }
            }


            return $record;
        });

        $document_balance = (object)$this->document($request->document_id);

        if ($document_balance->total_difference < 0.01) {

            $credit = CashDocumentCredit::where([
                ['status', 'PENDING'],
                ['document_id', $request->document_id]
            ])->first();

            if ($credit) {

                $cash = Cash::where([
                    ['user_id', User::getUserCashId()],
                    ['state', true],
                ])->first();

                $credit->status = 'PROCESSED';
                $credit->cash_id_processed = $cash->id;
                $credit->save();

                $req = [
                    'document_id' => $request->document_id,
                    'sale_note_id' => null
                ];

                $cash->cash_documents()->updateOrCreate($req);
            }
        }

        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito',
            'id' => $data->id,
        ];
    }


    public function destroy($id)
    {
        $item = DocumentPayment::findOrFail($id);
        $document_id = $item->document_id;
        $item->delete();
        $this->updateDocumentFees($document_id);

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }

    public function initialize_balance()
    {

        DB::connection('tenant')->transaction(function () {

            $documents = Document::get();

            foreach ($documents as $document) {

                $total_payments = $document->payments->sum('payment');

                $balance = $document->total - $total_payments;

                if ($balance <= 0) {

                    $document->total_canceled = true;
                    $document->update();
                } else {

                    $document->total_canceled = false;
                    $document->update();
                }
            }
        });

        return [
            'success' => true,
            'message' => 'Acción realizada con éxito'
        ];
    }

    public function report($start, $end, $type = 'pdf')
    {
        $documents = DocumentPayment::whereBetween('date_of_payment', [$start, $end])->get();

        $records = collect($documents)->transform(function ($row) {
            return [
                'id' => $row->id,
                'date_of_payment' => $row->date_of_payment->format('d/m/Y'),
                'payment_method_type_description' => $row->payment_method_type->description,
                'destination_description' => ($row->global_payment) ? $row->global_payment->destination_description : null,
                'change' => $row->change,
                'payment' => $row->payment,
                'reference' => $row->reference,
                'customer' => $row->document->customer->name,
                'number' => $row->document->number_full,
                'total' => $row->document->total,
            ];
        });

        if ($type == 'pdf') {
            $pdf = PDF::loadView('tenant.document_payments.report', compact("records"));

            $filename = "Reporte_Pagos";

            return $pdf->stream($filename . '.pdf');
        } elseif ($type == 'excel') {
            $filename = "Reporte_Pagos";

            // $pdf = PDF::loadView('tenant.document_payments.report', compact("records"))->download($filename.'.xlsx');

            // return $pdf->stream($filename.'.xlsx');

            return (new DocumentPaymentExport)
                ->records($records)
                ->download($filename . Carbon::now() . '.xlsx');
        }
    }

    public function record($document_payment_id)
    {
        $document_payment = DocumentPayment::find($document_payment_id);
        return [
            'success' => true,
            'payment' => $document_payment
        ];
    }

    public function updateRecord(Request $request, $document_payment_id)
    {
        $document_payment = DocumentPayment::find($document_payment_id);
        $document_payment->fill($request->all());
        $document_payment->save();
        return [
            'success' => true,
            'message' => 'Pago actualizado con éxito'
        ];
    }

}
