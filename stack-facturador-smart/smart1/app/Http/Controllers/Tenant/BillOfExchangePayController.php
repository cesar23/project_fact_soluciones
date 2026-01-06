<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\BillOfExchangeCollection;
use App\Http\Resources\Tenant\BBillOfExchangePaymentPayCollection;
use App\Http\Resources\Tenant\BillOfExchangePayCollection;
use App\Http\Resources\Tenant\BillOfExchangePaymentPayCollection;
use App\Http\Resources\Tenant\BillOfExchangePayResource;
use App\Http\Resources\Tenant\BillOfExchangeResource;
use App\Models\Tenant\BillOfExchange;
use App\Models\Tenant\BillOfExchangeDocument;
use App\Models\Tenant\BBillOfExchangePaymentPay;
use App\Models\Tenant\BillOfExchangeDocumentPay;
use App\Models\Tenant\BillOfExchangePay;
use App\Models\Tenant\BillOfExchangePaymentPay;
use App\Models\Tenant\Cash;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FilePaymentTrait;
use Modules\Finance\Traits\FinanceTrait;

class BillOfExchangePayController  extends Controller
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
        $document = BillOfExchangePay::find($id);
        $company = Company::active();
        $establishment = Establishment::first();
        $pdf = Pdf::loadView('tenant.bill_of_exchange_pay.bill_of_exchange_pay', compact(
            "company",
            "document",
            "establishment"
        ))
        ->setPaper([0, 0, 370, 700], 'landscape');
        $filename = "Letra_de_cambio_{$document->series}-{$document->number}";

        return $pdf->stream($filename . '.pdf');
    }
    public function delete_payment($id)
    {
        $bill_of_exchange_payment = BillOfExchangePaymentPay::find($id);

        $bill_of_exchange_payment->delete();
        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }
    public function delete($id)
    {
        $bill_of_exchange = BillOfExchangePay::find($id);
        $documents = BillOfExchangeDocumentPay::where('bill_of_exchange_id', $id)->get();
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

            $record = BillOfExchangePaymentPay::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->saveFiles($record, $request, 'bill_of_exchange_pay');
        });

        if ($request->paid == true) {
            $bill_of_exchange_payment = BillOfExchangePay::find($request->bill_of_exchange_id);
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
        $bill_of_exchange = BillOfExchangePay::find($id);

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
            'paid' => $bill_of_exchange->total_canceled,
            'external_id' => $bill_of_exchange->id,
        ];
    }
    public function payments($bill_of_exchange_id)
    {
        $records = BillOfExchangePaymentPay::where('bill_of_exchange_id', $bill_of_exchange_id)->get();

        return new BillOfExchangePaymentPayCollection($records);
    }
    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }
    public function documentsCreditByClient(Request $request)
    {
        $request->validate([
            'client_id' => 'required|numeric|min:1',
        ]);
        $clientId = $request->client_id;
        $records = Purchase::without(['user', 'soap_type', 'state_type', 'currency_type'])

            ->select('series', 'number', 'id', 'date_of_issue', 'total', 'currency_type_id', 'exchange_rate_sale', 'total_canceled')
            ->selectRaw('(SELECT SUM(payment) FROM purchase_payments WHERE purchase_id = purchases.id) AS total_payment')
            ->selectRaw('purchases.total - IFNULL((SELECT SUM(payment) FROM purchase_payments WHERE purchase_id = purchases.id), 0) AS total')

            ->where('supplier_id', $clientId)
            // ->where('payment_condition_id', '02')
            ->whereIn('state_type_id', ['01','03','05'])
            // ->where('total_pending_payment', '>', 0)
            ->where('total_canceled', 0)
            ->orderBy('number', 'desc');

        $dateOfIssue = $request->date_of_issue;
        $dateOfDue = $request->date_of_due;
        if ($dateOfIssue && !$dateOfDue) {
            $records = $records->where('date_of_issue', $dateOfIssue);
        }

        if ($dateOfIssue && $dateOfDue) {
            $records = $records->whereBetween('date_of_issue', [$dateOfIssue, $dateOfDue]);
        }
        $sum_total = 0;
        $records = $records->take(20)
            ->get();
        $total = $records->sum('total');
        $payment_total = $records->sum('total_payment');
        $sum_total = $total - $payment_total;
        return response()->json([
            'success' => true,
            'data' => $records,
            'sum_total' => $sum_total,
        ], 200);
    }

    public function purchasesByClient(Request $request)
    {
        $request->validate([
            'client_id' => 'required|numeric|min:1',
        ]);

        $clientId = $request->client_id;
        $records = Purchase::without(['user', 'soap_type', 'state_type', 'currency_type'])
            ->with('supplier:id,name,number')
            ->select('series', 'number', 'id', 'date_of_issue', 'total', 'currency_type_id', 'exchange_rate_sale', 'total_canceled', 'supplier_id')
            ->selectRaw('(SELECT SUM(payment) FROM purchase_payments WHERE purchase_id = purchases.id) AS total_payment')
            ->selectRaw('purchases.total - IFNULL((SELECT SUM(payment) FROM purchase_payments WHERE purchase_id = purchases.id), 0) AS pending_amount')
            ->where('supplier_id', $clientId)
            ->whereIn('state_type_id', ['01','03','05'])
            ->where('total_canceled', 0)
            ->whereNull('bill_of_exchange_pay_id')
            ->havingRaw('pending_amount > 0')
            ->orderBy('date_of_issue', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $records,
        ], 200);
    }

    public function purchaseDetail($id)
    {
        $purchase = Purchase::without(['user', 'soap_type', 'state_type', 'currency_type'])
            ->with('supplier:id,name,number')
            ->select('series', 'number', 'id', 'date_of_issue', 'total', 'currency_type_id', 'exchange_rate_sale', 'total_canceled', 'supplier_id')
            ->selectRaw('(SELECT SUM(payment) FROM purchase_payments WHERE purchase_id = purchases.id) AS total_payment')
            ->selectRaw('purchases.total - IFNULL((SELECT SUM(payment) FROM purchase_payments WHERE purchase_id = purchases.id), 0) AS pending_amount')
            ->whereIn('state_type_id', ['01','03','05'])
            ->where('total_canceled', 0)
            ->whereNull('bill_of_exchange_pay_id')
            ->findOrFail($id);

        $purchase->supplier_name = $purchase->supplier->name ?? '';

        return response()->json([
            'success' => true,
            'data' => $purchase,
        ], 200);
    }

    public function generateMultiple(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:tenant.purchases,id',
            'customer_id' => 'required|exists:tenant.persons,id',
            'bills' => 'required|array|min:1|max:10',
            'bills.*.amount' => 'required|numeric|min:0.01',
            'bills.*.due_date' => 'required|date|after:today',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            $purchase = Purchase::findOrFail($request->purchase_id);
            $totalPayments = PurchasePayment::where('purchase_id', $purchase->id)->sum('payment');
            $pendingAmount = $purchase->total - $totalPayments;

            // Validate that bills sum equals pending amount
            $billsTotal = collect($request->bills)->sum('amount');
            if (abs($billsTotal - $pendingAmount) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'La suma de las letras debe ser igual al monto pendiente de pago'
                ], 422);
            }

            $billsCreated = [];

            foreach ($request->bills as $index => $billData) {
                $bill = new BillOfExchangePay;
                $bill->series = "LP01";
                $bill->number = (BillOfExchangePay::count() == 0) ? 1 : BillOfExchangePay::orderBy('number', 'desc')->first()->number + 1;
                $bill->date_of_due = $billData['due_date'];
                $bill->total = $billData['amount'];
                $bill->establishment_id = auth()->user()->establishment_id;
                $bill->supplier_id = $request->customer_id;
                $bill->user_id = auth()->id();
                $bill->currency_type_id = $purchase->currency_type_id;
                $bill->exchange_rate_sale = $purchase->exchange_rate_sale;
                $bill->save();

                // Create bill document relationship
                $billDocument = new BillOfExchangeDocumentPay;
                $billDocument->bill_of_exchange_id = $bill->id;
                $billDocument->purchase_id = $purchase->id;
                $billDocument->total = $billData['amount'];
                $billDocument->save();

                $billsCreated[] = $bill;
            }

            // Update purchase status only after all bills are created
            $purchase->bill_of_exchange_pay_id = $billsCreated[0]->id; // Reference to first bill
            $purchase->total_canceled = 1;
            $purchase->save();

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Se generaron ' . count($billsCreated) . ' letras de cambio con éxito',
                'bills_created' => count($billsCreated)
            ], 200);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar las letras: ' . $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {

        try {
            DB::connection('tenant')->beginTransaction();
            $purchases_id = $request->input('purchases_id');

            $documents = Purchase::whereIn('id', $purchases_id);
            $document_payment = PurchasePayment::whereIn('purchase_id', $purchases_id);
            $total_payments = $document_payment->sum('payment');
            
            // $total_documents = $documents->sum('total');
            $total_documents = 0;
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
            $bill_of_exchange = new BillOfExchangePay;
            $bill_of_exchange->series = "LP01";
            if ($unique_exchange_rates->count() > 1) {
                $bill_of_exchange->exchange_rate_sale = 1;
                $bill_of_exchange->currency_type_id = 'PEN';
            } else {
                $bill_of_exchange->exchange_rate_sale = $unique_exchange_rates->first();
                $bill_of_exchange->currency_type_id = $data_documents->first()->currency_type_id;
            }
            $bill_of_exchange->number = (BillOfExchangePay::count() == 0) ? 1 : BillOfExchangePay::orderBy('number', 'desc')->first()->number + 1;
            $bill_of_exchange->date_of_due = $date_of_due;
            $bill_of_exchange->total = $total_pending;
            $bill_of_exchange->establishment_id = auth()->user()->establishment_id;
            $bill_of_exchange->supplier_id = $request->input('customer_id');
            $bill_of_exchange->user_id = auth()->id();
            $bill_of_exchange->save();


            foreach ($data_documents as $document) {
                $total = $document->total;
                $payment = $document->payments->sum('payment');
                $total_pending_payment = $total - $payment;
                $bill_of_exchange_document = new BillOfExchangeDocumentPay;
                $bill_of_exchange_document->bill_of_exchange_id = $bill_of_exchange->id;
                $bill_of_exchange_document->purchase_id = $document->id;
                $bill_of_exchange_document->total = $total_pending_payment;
                $bill_of_exchange_document->save();
                $document->bill_of_exchange_pay_id = $bill_of_exchange->id;
                // $document->total_pending_payment = 0;
                $document->total_canceled = 1;
                $document->save();
            }
            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Letra por pagar registrada con éxito',
                'id' => $bill_of_exchange->id
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function records(Request $request)
    {
        $column = $request->input('column');
        $value = $request->input('value');
        $records = BillOfExchangePay::query();

        if ($column && $value) {
            $records = $records->where($column, 'like', "%{$value}%");
        }
        $records = $records->orderBy('id', 'desc');
        return new BillOfExchangePayCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function record($id)
    {
        $record = BillOfExchangePay::findOrFail($id);
        return new BillOfExchangePayResource($record);
    }
    public function edit(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $id = $request->input('id');
            $total = $request->input('total');
            $date_of_due = $request->input('date_of_due');

            $bill_of_exchange = BillOfExchangePay::find($id);
            $bill_of_exchange->total = $total;
            $bill_of_exchange->date_of_due = $date_of_due;
            $bill_of_exchange->save();
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Letra por pagar editada con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function columns()
    {
        return [
            'series' => 'Serie',
            'number' => 'Número',
            'date_of_due' => 'Fecha de vencimiento',
            'document_id' => 'Documento',
            'total' => 'Total',
        ];
    }
    public function index()
    {
        return view('tenant.bill_of_exchange_pay.index');
    }
}
