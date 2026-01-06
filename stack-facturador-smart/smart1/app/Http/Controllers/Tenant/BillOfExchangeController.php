<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\BillOfExchangeCollection;
use App\Http\Resources\Tenant\BillOfExchangePaymentCollection;
use App\Http\Resources\Tenant\BillOfExchangeResource;
use App\Models\Tenant\BillOfExchange;
use App\Models\Tenant\BillOfExchangeDocument;
use App\Models\Tenant\BillOfExchangePayment;
use App\Models\Tenant\Cash;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\PaymentMethodType;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FilePaymentTrait;
use Modules\Finance\Traits\FinanceTrait;
use App\Exports\BillOfExchangeExport;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\User;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BillOfExchangeController  extends Controller
{

    use StorageDocument, FinanceTrait, FilePaymentTrait;

    /**
     * EmailController constructor.
     */
    public function __construct()
    {
    }
    public function pdf($id)
    {
        $document = BillOfExchange::find($id);
        $company = Company::active();

        $configuration = Configuration::first();
        $template = $configuration->bill_of_exchange_template;

        $pdf = Pdf::loadView('tenant.bill_of_exchange.' . $template, compact(
            "company",
            "document"
        ))
            ->setPaper([0, 0, 370, 700], 'landscape');
        $filename = "Letra_de_cambio_{$document->series}-{$document->number}";

        return $pdf->stream($filename . '.pdf');
    }
    public function delete_payment($id)
    {
        $bill_of_exchange_payment = BillOfExchangePayment::find($id);

        $bill_of_exchange_payment->delete();
        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }
    public function delete($id)
    {
        $bill_of_exchange = BillOfExchange::find($id);
        $documents = BillOfExchangeDocument::where('bill_of_exchange_id', $id)->get();
        foreach ($documents as $document) {
            if ($document->document) {
                $document->document->bill_of_exchange_id = null;
                $document->document->total_pending_payment = $document->document->total;
                $document->document->total_canceled = 0;
                $document->document->save();
            }
            $document->delete();
        }
        $bill_of_exchange->delete();

        return [
            'success' => true,
            'message' => 'Letra de cambio eliminada con éxito'
        ];
    }
    public function store_payment(Request $request)
    {
        $id = $request->input('id');

        DB::connection('tenant')->transaction(function () use ($id, $request) {

            $record = BillOfExchangePayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->cash_id = User::getCashId();
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->saveFiles($record, $request, 'bill_of_exchange');
        });

        if ($request->paid == true) {
            $bill_of_exchange_payment = BillOfExchange::find($request->bill_of_exchange_id);
            $bill_of_exchange_payment->total_canceled = true;
            $bill_of_exchange_payment->save();
            $cash = Cash::where([
                ['user_id', User::getUserCashId()],
                ['state', true],
            ])->first();
            $req = [
                'document_id' => null,
                'bill_of_exchange_id' => $request->bill_of_exchange_id
            ];

            $cash->cash_documents()->updateOrCreate($req);

            // }

        }

        // $this->createPdf($request->input('sale_note_id'));

        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito'
        ];
    }
    public function document($id)
    {
        $bill_of_exchange = BillOfExchange::find($id);

        $total_paid = round(collect($bill_of_exchange->payments)->sum('payment'), 2);
        $total = $bill_of_exchange->total;
        $total_difference = round($total - $total_paid, 2);

        if ($total_difference < 0.01) {
            $bill_of_exchange->total_canceled = true;
            $bill_of_exchange->save();
        }

        return [
            'identifier' => "{$bill_of_exchange->series}-{$bill_of_exchange->number}",
            'full_number' =>  "{$bill_of_exchange->series}-{$bill_of_exchange->number}",
            'number_full' => "{$bill_of_exchange->series}-{$bill_of_exchange->number}",
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
            'paid' => (bool) $bill_of_exchange->total_canceled,
            'external_id' => $bill_of_exchange->id,
        ];
    }

    public function payments($bill_of_exchange_id)
    {
        $records = BillOfExchangePayment::where('bill_of_exchange_id', $bill_of_exchange_id)->get();

        return new BillOfExchangePaymentCollection($records);
    }
    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }
    public function documentsCreditByDocument($id)
    {
        try {
            // Obtener el documento base sin relaciones innecesarias
            $document = Document::without(['user', 'soap_type', 'state_type', 'currency_type', 'items', 'invoice', 'payments'])
                ->select(
                    'id',
                    'series',
                    'number',
                    'date_of_issue',
                    'total',
                    'currency_type_id',
                    'exchange_rate_sale',
                    'payment_condition_id',
                    'customer_id'
                )
                ->selectRaw('(SELECT SUM(payment) FROM document_payments WHERE document_id = documents.id) AS total_payment')
                ->selectRaw('total - IFNULL((SELECT SUM(payment) FROM document_payments WHERE document_id = documents.id), 0) AS pending_amount')
                ->where('id', $id)
                ->whereIn('document_type_id', ['01', '03', '08'])
                ->whereIn('state_type_id', ['05'])
                ->where('total_canceled', 0)
                ->where('total', '>', 0)
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado o no válido para generar letra'
                ], 404);
            }

            $processedRecords = [];
            $sum_total = 0;

            // Verificar si es documento al crédito
            if ($document->payment_condition_id == '02') {
                // Buscar cuotas pendientes
                $fees = DocumentFee::where('document_id', $document->id)
                    ->where('is_canceled', false)
                    ->get();

                if ($fees->count() > 0) {
                    // Si tiene cuotas, procesar cada una
                    foreach ($fees as $fee) {
                        $documentCopy = $document->replicate();
                        $documentCopy->id = $document->id;
                        $documentCopy->total = $fee->amount;
                        $documentCopy->date_of_due = $fee->date;
                        $documentCopy->is_fee = true;
                        $documentCopy->fee_id = $fee->id;
                        $documentCopy->payment_method_type_id = $fee->payment_method_type_id;
                        $documentCopy->payment_method_name = $fee->getStringPaymentMethodType();

                        $processedRecords[] = $documentCopy;
                        $sum_total += $fee->amount;
                    }
                }
            }

            // Si no tiene cuotas o no es crédito, agregar el documento original
            if (empty($processedRecords)) {
                $document->is_fee = false;
                $processedRecords[] = $document;
                $sum_total += $document->pending_amount;
            }

            return response()->json([
                'success' => true,
                'data' => $processedRecords,
                'sum_total' => $sum_total,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], 500);
        }
    }
    public function documentsCreditByClient(Request $request)
    {
        $request->validate([
            'client_id' => 'required|numeric|min:1',
        ]);
        $clientId = $request->client_id;

        // Primero obtenemos los documentos base
        $records = Document::without(['user', 'soap_type', 'state_type', 'currency_type', 'items', 'invoice', 'payments', 'fee', 'quotation'])
            ->select('series', 'number', 'id', 'date_of_issue', 'total', 'currency_type_id', 'exchange_rate_sale', 'payment_condition_id')
            ->selectRaw('(SELECT SUM(payment) FROM document_payments WHERE document_id = documents.id) AS total_payment')
            ->selectRaw('documents.total - IFNULL((SELECT SUM(payment) FROM document_payments WHERE document_id = documents.id), 0) AS pending_amount')
            ->where('customer_id', $clientId)
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->whereIn('state_type_id', ['05'])
            ->where('total_canceled', 0)
            ->where('total', '>', 0)
            ->orderBy('number', 'desc');

        $dateOfIssue = $request->date_of_issue;
        $dateOfDue = $request->date_of_due;
        if ($dateOfIssue && !$dateOfDue) {
            $records = $records->where('date_of_issue', $dateOfIssue);
        }

        if ($dateOfIssue && $dateOfDue) {
            $records = $records->whereBetween('date_of_issue', [$dateOfIssue, $dateOfDue]);
        }

        $records = $records->take(20)->get();

        // Procesamos los documentos para manejar las cuotas
        $processedRecords = [];
        $sum_total = 0;

        foreach ($records as $document) {
            // Si es documento al crédito (payment_condition_id = '02')
            if ($document->payment_condition_id == '02') {
                // Obtenemos las cuotas del documento que no estén canceladas
                $fees = DocumentFee::where('document_id', $document->id)
                    ->where('is_canceled', false)
                    ->get();

                if ($fees->count() > 0) {
                    foreach ($fees as $fee) {
                        // Creamos una copia del documento para cada cuota
                        $documentCopy = $document->replicate();
                        $documentCopy->id = $document->id; // Asegurarse de que la cuota tenga el mismo ID que el documento
                        $documentCopy->total = $fee->amount;
                        $documentCopy->date_of_due = $fee->date;
                        $documentCopy->is_fee = true; // Indicador de que es una cuota
                        $documentCopy->fee_id = $fee->id;
                        $documentCopy->payment_method_type_id = $fee->payment_method_type_id;
                        $documentCopy->payment_method_name = $fee->getStringPaymentMethodType();

                        $processedRecords[] = $documentCopy;
                        $sum_total += $fee->amount;
                    }
                } else {
                    // Si no tiene cuotas pero es crédito, lo agregamos como está
                    $document->is_fee = false;
                    $processedRecords[] = $document;
                    $sum_total += $document->pending_amount;
                }
            } else {
                // Si es documento al contado (payment_condition_id = '01')
                $document->is_fee = false;
                $processedRecords[] = $document;
                $sum_total += $document->pending_amount;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $processedRecords,
            'sum_total' => $sum_total,
        ], 200);
    }
    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();

            $documents_id = $request->input('documents_id');
            $combine_documents = $request->input('combine_documents');
            $documents_data = $request->input('documents_data');
            $customer_id = $request->input('customer_id');
            $general_date_of_due = $request->input('date_of_due');
            $general_code = $request->input('code');

            $last_number = BillOfExchange::query()
                ->select('number')
                ->orderByRaw('number * 1 DESC')
                ->lockForUpdate()
                ->first()
                ->number ?? 0;

            $current_number = intval($last_number);

            $created_bills = [];
            $combine_date_of_due = date('Y-m-d', strtotime('+1 month'));
            if ($combine_documents) {
                // Crear una sola letra de cambio combinando todos los documentos
                $total_combined = 0;
                $documents_to_process = [];

                foreach ($documents_data as $id => $doc_data) {
                    if (!isset($doc_data['id'])) {
                        continue;
                    }
                    

                    $document = Document::find($doc_data['id']);
                    if (!$document) {
                        continue;
                    }
                    $current_currency_type_id = $document->currency_type_id;
                    $exchange_rate_sale = $document->exchange_rate_sale;
                    $is_fee = isset($doc_data['is_fee']) && $doc_data['is_fee'];
                    $fee_id = $is_fee ? $doc_data['fee_id'] : null;
                    if($id == 0){
                        if($is_fee){
                            $combine_date_of_due = DocumentFee::find($fee_id)->date;
                        }else{
                            $combine_date_of_due = $document->invoice->date_of_due;
                        }
                    }
                    if ($is_fee) {
                        $fee = DocumentFee::find($fee_id);
                        if ($fee) {
                            $amount = isset($doc_data['editable_amount']) && $doc_data['editable_amount'] > 0
                                ? $doc_data['editable_amount']
                                : $fee->amount;

                            if ($amount > $fee->amount) {
                                throw new Exception("El monto no puede exceder el valor de la cuota");
                            }

                            $total_combined += $amount;
                            $documents_to_process[] = [
                                'document' => $document,
                                'is_fee' => true,
                                'fee_id' => $fee_id,
                                'amount' => $amount,
                                'payment_amount' => $amount,
                                'fee' => $fee
                            ];
                        }
                    } else {
                        $total_payments = DocumentPayment::where('document_id', $document->id)->sum('payment');
                        $pending_amount = $document->total - $total_payments;

                        $amount = isset($doc_data['editable_amount']) && $doc_data['editable_amount'] > 0
                            ? $doc_data['editable_amount']
                            : $pending_amount;

                        $total_combined += $amount;
                        $documents_to_process[] = [
                            'document' => $document,
                            'is_fee' => false,
                            'fee_id' => null,
                            'amount' => $amount,
                            'payment_amount' => $amount,
                            'fee' => null
                        ];
                    }
                }

                if (empty($documents_to_process)) {
                    throw new Exception("No hay documentos válidos para procesar");
                }

                // Verificar y normalizar monedas a PEN si es necesario
                $first_currency = $documents_to_process[0]['document']->currency_type_id;
                $has_mixed_currencies = false;
                $current_currency_type_id = $first_currency;
                $exchange_rate_sale = $documents_to_process[0]['document']->exchange_rate_sale;
                foreach ($documents_to_process as $doc) {
                    if ($doc['document']->currency_type_id !== $first_currency) {
                        $has_mixed_currencies = true;
                        break;
                    }
                }

                if ($has_mixed_currencies) {
                    $total_combined = 0;
                    foreach ($documents_to_process as &$doc) {
                        if ($doc['document']->currency_type_id === 'USD') {
                            // Convertir USD a PEN
                            $doc['amount'] = $doc['amount'] * $doc['document']->exchange_rate_sale;
                            $total_combined += $doc['amount'];
                        } else {
                            $total_combined += $doc['amount'];
                        }
                    }
                    // Establecer moneda final como PEN
                    $current_currency_type_id = 'PEN';
                    $exchange_rate_sale = 1;
                }

                // Crear una sola letra de cambio
                $current_number++;
                $bill_of_exchange = new BillOfExchange;
                $bill_of_exchange->series = "LC01";
                $bill_of_exchange->number = $current_number;
                $bill_of_exchange->endorsement_name = $documents_data[0]['endorsement_name'] ?? null;
                $bill_of_exchange->endorsement_number = $documents_data[0]['endorsement_number'] ?? null;
                $bill_of_exchange->date_of_due = $combine_date_of_due;
                $bill_of_exchange->total = $total_combined;
                $bill_of_exchange->establishment_id = auth()->user()->establishment_id;
                $bill_of_exchange->customer_id = $customer_id;
                $bill_of_exchange->user_id = auth()->id();
                $bill_of_exchange->code = $general_code;
                $bill_of_exchange->currency_type_id = $current_currency_type_id;
                $bill_of_exchange->exchange_rate_sale = $exchange_rate_sale;
                $bill_of_exchange->save();

                $created_bills[] = $bill_of_exchange->id;

                // Procesar cada documento/cuota
                foreach ($documents_to_process as $doc_process) {
                    $document = $doc_process['document'];
                    $is_fee = $doc_process['is_fee'];
                    $fee_id = $doc_process['fee_id'];
                    $payment_amount = $doc_process['payment_amount'];
                    $amount = $doc_process['amount'];
                    $fee = $doc_process['fee'];

                    $bill_of_exchange_document = new BillOfExchangeDocument;
                    $bill_of_exchange_document->bill_of_exchange_id = $bill_of_exchange->id;
                    $bill_of_exchange_document->document_id = $document->id;
                    $bill_of_exchange_document->total = $amount;

                    if ($is_fee) {
                        $bill_of_exchange_document->is_fee = true;
                        $bill_of_exchange_document->fee_id = $fee_id;
                    }

                    $bill_of_exchange_document->save();

                    // Crear el pago del documento
                    $document_payment = new DocumentPayment();
                    $document_payment->document_id = $document->id;
                    $document_payment->date_of_payment = date('Y-m-d');
                    $document_payment->payment_method_type_id = '09';
                    $document_payment->payment = $payment_amount;
                    $document_payment->glosa = "Pago con letra de cambio combinada {$bill_of_exchange->series}-{$bill_of_exchange->number}";

                    if ($is_fee) {
                        $fee->amount = $fee->amount - $amount;

                        if ($fee->amount <= 0) {
                            $fee->is_canceled = true;
                        }
                        $fee->save();

                        $pending_fees = DocumentFee::where('document_id', $document->id)
                            ->where('is_canceled', false)
                            ->count();

                        if ($pending_fees == 0) {
                            $document->total_canceled = 1;
                            $document_payment->glosa = "Pago total con letra de cambio combinada {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                        } else {
                            $document_payment->glosa = "Pago parcial con letra de cambio combinada {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                        }
                    } else {
                        $total_payments = DocumentPayment::where('document_id', $document->id)->sum('payment');
                        $is_total_payment = ($total_payments + $amount) >= $document->total;

                        if ($is_total_payment) {
                            $document->total_canceled = 1;
                            $document_payment->glosa = "Pago total con letra de cambio combinada {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                        } else {
                            $document_payment->glosa = "Pago parcial con letra de cambio combinada {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                        }
                    }

                    $document_payment->save();
                    $document->save();
                }
            } else {
                foreach ($documents_data as $doc_data) {
                    if (!isset($doc_data['id'])) {
                        continue;
                    }

                    $document = Document::find($doc_data['id']);
                    if (!$document) {
                        continue;
                    }

                    $code = !empty($doc_data['code']) ? $doc_data['code'] : $general_code;
                    $date_of_due = isset($doc_data['date_of_due']) && $doc_data['date_of_due']
                        ? $doc_data['date_of_due']
                        : $general_date_of_due;

                    if (!$date_of_due) {
                        $date_of_due = date('Y-m-d', strtotime('+1 month'));
                    }

                    $is_fee = isset($doc_data['is_fee']) && $doc_data['is_fee'];
                    $fee_id = $is_fee ? $doc_data['fee_id'] : null;

                    if ($is_fee) {
                        $fee = DocumentFee::find($fee_id);
                        if ($fee) {
                            $amount = isset($doc_data['editable_amount']) && $doc_data['editable_amount'] > 0
                                ? $doc_data['editable_amount']
                                : $fee->amount;

                            if ($amount > $fee->amount) {
                                throw new Exception("El monto no puede exceder el valor de la cuota");
                            }

                            $current_number++;

                            $bill_of_exchange = new BillOfExchange;
                            $bill_of_exchange->series = "LC01";
                            $bill_of_exchange->number = $current_number;
                            $bill_of_exchange->endorsement_name = $doc_data['endorsement_name'];
                            $bill_of_exchange->endorsement_number = $doc_data['endorsement_number'];
                            $bill_of_exchange->date_of_due = $date_of_due;
                            $bill_of_exchange->total = $amount;
                            $bill_of_exchange->establishment_id = auth()->user()->establishment_id;
                            $bill_of_exchange->customer_id = $customer_id;
                            $bill_of_exchange->user_id = auth()->id();
                            $bill_of_exchange->code = $code;
                            $bill_of_exchange->currency_type_id = $document->currency_type_id;
                            $bill_of_exchange->exchange_rate_sale = $document->exchange_rate_sale;
                            $bill_of_exchange->save();

                            $created_bills[] = $bill_of_exchange->id;

                            $bill_of_exchange_document = new BillOfExchangeDocument;
                            $bill_of_exchange_document->bill_of_exchange_id = $bill_of_exchange->id;
                            $bill_of_exchange_document->document_id = $document->id;
                            $bill_of_exchange_document->total = $amount;
                            $bill_of_exchange_document->is_fee = true;
                            $bill_of_exchange_document->fee_id = $fee_id;
                            $bill_of_exchange_document->save();

                            $document_payment = new DocumentPayment();
                            $document_payment->document_id = $document->id;
                            $document_payment->date_of_payment = date('Y-m-d');
                            $document_payment->payment_method_type_id = '09';
                            $document_payment->payment = $amount;
                            $document_payment->glosa = "Pago parcial con letra de cambio {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                            $document_payment->save();

                            $fee->amount = $fee->amount - $amount;

                            if ($fee->amount <= 0) {
                                $fee->is_canceled = true;
                            }
                            $fee->save();

                            $pending_fees = DocumentFee::where('document_id', $document->id)
                                ->where('is_canceled', false)
                                ->count();

                            if ($pending_fees == 0) {
                                $document->total_canceled = 1;
                            }
                            $document->save();
                        }

                        continue;
                    } else {
                        $total_payments = DocumentPayment::where('document_id', $document->id)->sum('payment');
                        $pending_amount = $document->total - $total_payments;

                        $amount = isset($doc_data['editable_amount']) && $doc_data['editable_amount'] > 0
                            ? $doc_data['editable_amount']
                            : $pending_amount;

                        $current_number++;

                        $bill_of_exchange = new BillOfExchange;
                        $bill_of_exchange->series = "LC01";
                        $bill_of_exchange->number = $current_number;
                        $bill_of_exchange->endorsement_name = $doc_data['endorsement_name'];
                        $bill_of_exchange->endorsement_number = $doc_data['endorsement_number'];
                        $bill_of_exchange->date_of_due = $date_of_due;
                        $bill_of_exchange->total = $amount;
                        $bill_of_exchange->establishment_id = auth()->user()->establishment_id;
                        $bill_of_exchange->customer_id = $customer_id;
                        $bill_of_exchange->user_id = auth()->id();
                        $bill_of_exchange->code = $code;
                        $bill_of_exchange->currency_type_id = $document->currency_type_id;
                        $bill_of_exchange->exchange_rate_sale = $document->exchange_rate_sale;
                        $bill_of_exchange->save();

                        $created_bills[] = $bill_of_exchange->id;

                        $bill_of_exchange_document = new BillOfExchangeDocument;
                        $bill_of_exchange_document->bill_of_exchange_id = $bill_of_exchange->id;
                        $bill_of_exchange_document->document_id = $document->id;
                        $bill_of_exchange_document->total = $amount;

                        if ($is_fee) {
                            $bill_of_exchange_document->is_fee = true;
                            $bill_of_exchange_document->fee_id = $fee_id;
                        }

                        $bill_of_exchange_document->save();

                        $document->bill_of_exchange_id = $bill_of_exchange->id;

                        if ($is_fee) {
                            $fee = DocumentFee::find($fee_id);
                            if ($fee) {
                                $fee->is_canceled = true;
                                $fee->save();
                            }
                            $document_payment = new DocumentPayment();
                            $document_payment->document_id = $document->id;
                            $document_payment->date_of_payment = date('Y-m-d');
                            $document_payment->payment_method_type_id = '09';
                            $document_payment->payment = $amount;
                            $document_payment->glosa = "Pago con letra de cambio {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                            $is_total_payment = DocumentFee::where('document_id', $document->id)->where('is_canceled', false)->count() == 0;
                            if ($is_total_payment) {
                                $document_payment->glosa = "Pago total con letra de cambio {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                                $document->total_canceled = 1;
                            } else {
                                $document_payment->glosa = "Pago parcial con letra de cambio {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                                $document->total_canceled = 0;
                            }
                            $document_payment->save();
                        } else {
                            $document_payment = new DocumentPayment();
                            $document_payment->document_id = $document->id;
                            $document_payment->date_of_payment = date('Y-m-d');
                            $document_payment->payment_method_type_id = '09';
                            $document_payment->payment = $amount;

                            $total_payments = DocumentPayment::where('document_id', $document->id)->sum('payment');
                            $is_total_payment = ($total_payments + $amount) >= $document->total;

                            if ($is_total_payment) {
                                $document_payment->glosa = "Pago total con letra de cambio {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                                $document->total_canceled = 1;
                            } else {
                                $document_payment->glosa = "Pago parcial con letra de cambio {$bill_of_exchange->series}-{$bill_of_exchange->number}";
                                $document->total_canceled = 0;
                            }

                            $document_payment->save();
                        }

                        $document->save();
                    }
                }
            }

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Letra(s) de cambio registrada(s) con éxito',
                'ids' => $created_bills
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    private function restoreDocument($document_id, $original_amount, $amount)
    {
        $document = Document::find($document_id);
        $document_payment = DocumentPayment::where('document_id', $document_id)->where('payment', $original_amount)
            ->where('payment_method_type_id', '09')->first();
        if ($document_payment) {
            if($amount == 0){
                $document_payment->delete();
            }else{
                $document_payment->payment = $amount;
                $document_payment->save();
            }
        }
        $total_payments = DocumentPayment::where('document_id', $document->id)->sum('payment');
        $is_total_payment = ($total_payments + $amount) >= $document->total;
        if ($is_total_payment) {
            $document->total_canceled = 1;
        } else {
            $document->total_canceled = 0;
        }
        $document->save();
    }
    private function restoreFee($fee_id, $original_amount, $amount)
    {
        $fee = DocumentFee::find($fee_id);
        $document_id = $fee->document_id;
        $document_payment = DocumentPayment::where('document_id', $document_id)->where('payment', $original_amount)
            ->where('payment_method_type_id', '09')->first();
        if ($document_payment) {
            if($amount == 0){
                $document_payment->delete();
            }else{
                $document_payment->payment = $amount;
                $document_payment->save();
            }
        }
        $remain = $original_amount - $amount;
        $fee->amount = $remain;
        if ($fee->amount <= 0) {
            $fee->is_canceled = true;
        } else {
            $fee->is_canceled = false;
        }
        $fee->save();
    }
    private function deleteBillOfExchangeDocument($id){
        $bill_of_exchange_document = BillOfExchangeDocument::find($id);
        $bill_of_exchange_document->delete();
    }
    public function edit(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $id = $request->input('id');
            $total = $request->input('total');
            $date_of_due = $request->input('date_of_due');
            $code = $request->input('code');
            $documents = $request->input('documents');
            $multiple = $request->input('multiple');

            if ($multiple) {
                $total = 0;
                $same_currency = true;
                $currency_type_id_values = array_unique(array_column($documents, 'currency_type_id'));
                if (count($currency_type_id_values) > 1) {
                    $same_currency = false;
                }
                foreach ($documents as $document) {
                    $bill_of_exchange_document = BillOfExchangeDocument::find($document['id']);
                    $document_id = $document['document_id'];
                    $is_fee = $document['is_fee'];
                    $fee_id = $document['fee_id'];
                    $total_document = $document['total'];
                    if($total_document == 0){
                        $this->deleteBillOfExchangeDocument($document['id']);
                    }
                    $bill_of_exchange_document->total = $total_document;
                    $bill_of_exchange_document->save();
                    $original_document = Document::find($document_id);
                    $original_amount = $original_document->total;
                    if($is_fee){
                        $this->restoreFee($fee_id, $original_amount, $document['total']);
                    }else{
                        $this->restoreDocument($document_id, $original_amount, $document['total']);
                    }
                    if($total_document > 0){
                        $total += $total_document;
                    }else{
                        $bill_of_exchange_document->delete();
                    }
                }
            }

            $bill_of_exchange = BillOfExchange::find($id);
            $bill_of_exchange->total = $total;
            $bill_of_exchange->date_of_due = $date_of_due;
            $bill_of_exchange->code = $code;
            $bill_of_exchange->save();
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Letra de cambio editada con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function old_store(Request $request)
    {

        $documents_id = $request->input('documents_id');

        $documents = Document::whereIn('id', $documents_id);
        $document_payment = DocumentPayment::whereIn('document_id', $documents_id);
        $total_payments = $document_payment->sum('payment');
        // $total_documents = $documents->sum('total');
        $total_documents = 0;
        $code = $request->input('code');
        $data_documents = $documents->get();
        $unique_exchange_rates = collect($data_documents)->pluck('exchange_rate_sale')->unique();

        foreach ($data_documents as $document) {
            if ($unique_exchange_rates->count() > 1) {
                if ($document->currency_type_id == 'PEN') {
                    $total_documents += $document->total;
                } else {
                    $total_documents += $document->total * $document->exchange_rate_sale;
                }
            } else {
                $total_documents += $document->total;
            }
        }
        $total_pending = $total_documents - $total_payments;
        $date_of_due = $request->input('date_of_due');
        $bill_of_exchange = new BillOfExchange;
        $bill_of_exchange->series = "LC01";
        if ($unique_exchange_rates->count() > 1) {
            $bill_of_exchange->exchange_rate_sale = 1;
            $bill_of_exchange->currency_type_id = 'PEN';
        } else {
            $bill_of_exchange->exchange_rate_sale = $unique_exchange_rates->first();
            $bill_of_exchange->currency_type_id = $data_documents->first()->currency_type_id;
        }
        // $bill_of_exchange->currency_type_id = 
        $bill_of_exchange->number = (BillOfExchange::count() == 0) ? 1 : BillOfExchange::orderBy('number', 'desc')->first()->number + 1;
        $bill_of_exchange->date_of_due = $date_of_due;
        $bill_of_exchange->total = $total_pending;
        $bill_of_exchange->establishment_id = auth()->user()->establishment_id;
        $bill_of_exchange->customer_id = $request->input('customer_id');
        $bill_of_exchange->user_id = auth()->id();
        $bill_of_exchange->code = $code;
        $bill_of_exchange->save();

        foreach ($data_documents as $document) {
            $total = $document->total;
            $payment = $document->payments->sum('payment');
            $total_pending_payment = $total - $payment;
            $bill_of_exchange_document = new BillOfExchangeDocument;
            $bill_of_exchange_document->bill_of_exchange_id = $bill_of_exchange->id;
            $bill_of_exchange_document->document_id = $document->id;
            $bill_of_exchange_document->total = $total_pending_payment;
            $bill_of_exchange_document->save();
            $document->bill_of_exchange_id = $bill_of_exchange->id;
            $document->total_pending_payment = 0;
            $document->total_canceled = 1;
            $document->save();
        }


        return [
            'success' => true,
            'message' => 'Letra de cambio registrada con éxito',
            'id' => $bill_of_exchange->id
        ];
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new BillOfExchangeCollection($records->paginate(config('tenant.items_per_page')));
    }
    function getRecords(Request $request)
    {
        $column = $request->input('column');
        $value = $request->input('value');
        $start_dates = $request->input('start_dates');
        $end_dates = $request->input('end_dates');
        $records = BillOfExchange::query();

        if ($column && $value) {
            if ($column == 'customer_id') {
                $records = $records->where('customer_id', $value);
            } else if ($column == 'seller') {
                $records = $records->whereHas('items', function($query) use ($value){
                    $query->whereHas('document', function($query) use ($value){
                        $query->whereHas('seller', function($query) use ($value){
                            $query->where('name', 'like', "%{$value}%");
                        });
                    });
                });
            } else {
                $records = $records->where($column, 'like', "%{$value}%");
            }
        }

        if ($start_dates && $end_dates) {
            // Convertimos las fechas a Carbon y aseguramos el formato correcto
            $start = Carbon::parse($start_dates)->startOfDay();
            $end = Carbon::parse($end_dates)->endOfDay();


            $records = $records->whereBetween('created_at', [
                $start,
                $end
            ]);
        } else {
            if ($start_dates) {
                $start = Carbon::parse($start_dates)->startOfDay();
                $records = $records->where('created_at', '>=', $start);
            }
            if ($end_dates) {
                $end = Carbon::parse($end_dates)->endOfDay();
                $records = $records->where('created_at', '<=', $end);
            }
        }

        $records = $records->orderBy('id', 'desc');



        return $records;
    }
    public function record($id)
    {
        $record = BillOfExchange::findOrFail($id);
        return new BillOfExchangeResource($record);
    }
    public function columns()
    {
        return [
            'series' => 'Serie',
            'number' => 'Número',
            'date_of_due' => 'Fecha de vencimiento',
            'document_id' => 'Documento',
            'total' => 'Total',
            'customer_id' => 'Cliente',
            'seller' => 'Vendedor',
        ];
    }
    public function index()
    {
        return view('tenant.bill_of_exchange.index');
    }

    public function exportPdf(Request $request)
    {
        try {
            $company = Company::active();
            $date = Carbon::now()->format('Y-m-d');

            $records = $this->getRecordsForExport($request)
            ->where('total_canceled', 0)
            ->latest()
            ->get();

            $pdf = PDF::loadView('tenant.bill_of_exchange.export', [
                'company' => $company,
                'records' => $records,
                'date' => $date,
            ])
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isPhpEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'debugLayout' => false,
                    'dpi' => 150,
                    'defaultFont' => 'sans-serif',

                    'enable_php' => true
                ]);
            $filename = 'Reporte_Letras_' . $date;

            return $pdf->stream($filename . '.pdf');
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function exportExcel(Request $request)
    {
        try {
            $date = Carbon::now()->format('Y-m-d');
    
            $records = $this->getRecordsForExport($request)
            ->where('total_canceled', 0)
            ->latest()
            ->get();
    
            $company = Company::active();
            $filename = 'Reporte_Letras_' . $date;
            $date = Carbon::now()->format('Y-m-d');
            return Excel::download(new BillOfExchangeExport($records, $company, $date), $filename . '.xlsx');
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'message' => $e->getMessage()
            ], 500);
        }
    }
    private function getRecordsForExport($request)
    {
        // $records = BillOfExchange::query()
        //     ->with(['customer', 'user', 'items.document', 'currency_type']);

        // if ($request->date_start && $request->date_end) {
        //     $records->whereBetween('date_of_due', [$request->date_start, $request->date_end]);
        // }

        // if ($request->customer_id) {
        //     $records->where('customer_id', $request->customer_id);
        // }

        // if ($request->series) {
        //     $records->where('series', $request->series);
        // }

        // if ($request->number) {
        //     $records->where('number', $request->number);
        // }
        $records = $this->getRecords($request);
        return $records;
    }
    public function revert($bill_of_exchange_id)
    {
        try {
            DB::connection('tenant')->beginTransaction();

            // Obtener la letra de cambio
            $bill_of_exchange = BillOfExchange::find($bill_of_exchange_id);
            if (!$bill_of_exchange) {
                throw new Exception("La letra de cambio no existe");
            }

            // Obtener los documentos relacionados
            $bill_of_exchange_documents = BillOfExchangeDocument::where('bill_of_exchange_id', $bill_of_exchange_id)->get();

            foreach ($bill_of_exchange_documents as $bill_of_exchange_document) {
                $document = Document::find($bill_of_exchange_document->document_id);

                if ($document) {
                    // Buscar y eliminar el pago asociado a esta letra
                    $document_payment = DocumentPayment::where('document_id', $document->id)
                        ->where('payment_method_type_id', '09')
                        ->where('glosa', 'like', "%{$bill_of_exchange->series}-{$bill_of_exchange->number}%")
                        ->first();

                    if ($document_payment) {
                        $payment_amount = $document_payment->payment;
                        $document_payment->delete();

                        // Si es una cuota
                        if ($bill_of_exchange_document->is_fee && $bill_of_exchange_document->fee_id) {
                            $fee = DocumentFee::find($bill_of_exchange_document->fee_id);
                            if ($fee) {
                                $fee->amount = $fee->amount + $payment_amount;
                                $fee->is_canceled = false;
                                $fee->save();
                            }
                        }

                        // Actualizar el estado del documento
                        $total_payments = DocumentPayment::where('document_id', $document->id)->sum('payment');
                        $document->total_canceled = ($total_payments >= $document->total) ? 1 : 0;
                        $document->bill_of_exchange_id = null;
                        $document->save();
                    }
                }
            }

            // Eliminar todos los registros de pagos de la letra
            BillOfExchangePayment::where('bill_of_exchange_id', $bill_of_exchange_id)->delete();

            // Eliminar los documentos de la letra
            foreach ($bill_of_exchange_documents as $bill_of_exchange_document) {
                $bill_of_exchange_document->delete();
            }

            // Eliminar la letra
            $bill_of_exchange->delete();

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Letra de cambio revertida con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
