<?php

namespace Modules\Pos\Http\Controllers;

use App\Http\Controllers\Tenant\EmailController;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BillOfExchange;
use App\Models\Tenant\BillOfExchangePayment;
use App\Models\Tenant\Cash;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\PackageHandlerPayment;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Pos\Exports\ReportCashExport;
use Modules\Pos\Mail\CashEmail;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpensePayment;
use Modules\Finance\Models\GlobalPayment;
use Modules\Finance\Models\IncomePayment;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Sale\Models\QuotationPayment;
use Modules\Sale\Models\TechnicalService;
use Modules\Sale\Models\TechnicalServicePayment;

class CashController extends Controller
{

    private const PAYMENT_METHOD_TYPE_CASH = '01';
    protected $method_type_cash_id = null;
    protected $method_types;
    protected $method_type_bank_ids = [];
    protected $method_type_cash_ids = [];
    /**
     *
     * Usado en:
     * CashController - App
     *
     * @param  Request $request
     * @return array
     *
     */
    public function email(Request $request)
    {
        $request->validate(
            ['email' => 'required']
        );

        $company = Company::active();
        $email = $request->input('email');

        $mailable = new CashEmail($company, $this->getPdf($request->cash_id));
        $model = Cash::find($request->cash_id);
        $id = (int) $model->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, $model);
        /*
        Configuration::setConfigSmtpMail();
        $array_email = explode(',', $email);
        if (count($array_email) > 1) {
            foreach ($array_email as $email_to) {
                $email_to = trim($email_to);
                if(!empty($email_to)) {
                    Mail::to($email_to)->send(new CashEmail($company, $this->getPdf($request->cash_id)));
                }
            }
        } else {
            Mail::to($email)->send(new CashEmail($company, $this->getPdf($request->cash_id)));
        }*/

        return [
            'success' => true
        ];
    }

    /**
     * Obtiene el string del metodo de pago
     *
     * @param $payment_id
     *
     * @return string
     */
    public static function getStringPaymentMethod($payment_id)
    {
        $payment_method = PaymentMethodType::find($payment_id);
        return (!empty($payment_method)) ? $payment_method->description : '';
    }

    /**
     * Genera un formato de numero para las operaciones del reporte.
     *
     * @param int    $number
     * @param int    $decimal
     * @param string $decimal_separador
     * @param string $miles_separador
     *
     * @return string
     */
    public static function FormatNumber($number = 0, $decimal = 2, $decimal_separador = '.', $miles_separador = '')
    {
        return number_format($number, $decimal, $decimal_separador, $miles_separador);
    }
    public function setDataToReportSpecial($cash_id = 0, $withBank = false)
    {

        Log::info("setDataToReport");
        set_time_limit(0);
        $data = [];
        /** @var Cash $cash */
        $cash = Cash::findOrFail($cash_id);
        $data["counter"] = $cash->counter;
        $document_credit = Document::where('payment_condition_id',  '02')
            ->whereIn('document_type_id', ['03', '01'])
            ->whereIn('state_type_id', ['01', '03', '05', '13'])
            ->where('cash_id', $cash_id)
            ->get()->transform(
                function ($row) {
                    return [
                        "number" => $row->getNumberFullAttribute(),
                        "type" => $row->document_type->description,
                        "date_of_issue" => $row->date_of_issue->format('Y-m-d'),
                        "customer_name" => $row->customer->name,
                        "customer_number" => $row->customer->number,
                        "currency_type_id" => $row->currency_type_id,
                        "total" => $row->total,
                        "credit_type" => optional($row->payment_method_type)->description ?? "-",
                    ];
                }

            );

        $sale_note_credit = SaleNote::whereHas('payment_method_type', function ($query) {
            $query->where('is_credit', true);
        })
            ->where('cash_id', $cash_id)
            ->get()->transform(
                function ($row) {
                    return [
                        "number" => $row->number_full,
                        "type" => "NOTA DE VENTA",
                        "date_of_issue" => $row->date_of_issue->format('Y-m-d'),
                        "customer_name" => $row->customer->name,
                        "customer_number" => $row->customer->number,
                        "currency_type_id" => $row->currency_type_id,
                        "total" => $row->total,
                        "credit_type" => optional($row->payment_method_type)->description ?? "-",
                    ];
                }
            );

        $document_credit = $document_credit->concat($sale_note_credit);
        $document_credit_total = $document_credit->sum('total');

        $sale_note_unpaid = SaleNote::where('cash_id', $cash_id)
            ->where('total_canceled', 0)
            ->where('paid', 0)
            ->withSum('payments', 'payment')
            ->get()
            ->sum(function ($saleNote) {
                return $saleNote->total - $saleNote->getRelation('payments')->sum('payment');
            });

        // $document_credit_total += $sale_note_unpaid;
        $establishment = $cash->user->establishment;
        $status_type_id = self::getStateTypeId();
        $final_balance = 0;
        $cash_income = 0;
        $credit = 0;
        $cash_egress = 0;
        $cash_final_balance = 0;
        $cash_documents = GlobalPayment::where('destination_id', $cash_id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->get();

        $cpe_n = [];
        $all_documents = [];
        $methods_payment_credit = PaymentMethodType::NonCredit()->get()->transform(function ($row) {
            return $row->id;
        })->toArray();
        $methods_payment = collect(PaymentMethodType::all())->transform(function ($row) {
            return (object)[
                'id'   => $row->id,
                'name' => $row->description,
                'description' => $row->description,
                'is_credit' => $row->is_credit,
                'is_cash' => $row->is_cash,
                'is_digital' => $row->is_digital,
                'is_bank' => $row->is_bank,
                'sum'  => 0,
            ];
        });

        $company = Company::first();
        $data["document_credit_total"] = $document_credit_total;
        $data['cash'] = $cash;
        $data['cash_user_name'] = $cash->user->name;
        $data['cash_date_opening'] = $cash->date_opening;
        $data['cash_state'] = $cash->state;
        $data['cash_date_closed'] = $cash->date_closed;
        $data['cash_time_closed'] = $cash->time_closed;
        $data['cash_time_opening'] = $cash->time_opening;
        $data['cash_documents'] = $cash_documents;
        $data['cash_documents_total'] = (int)$cash_documents->count();

        $data['company_name'] = $company->name;
        $data['company_number'] = $company->number;
        $data['company'] = $company;

        $data['status_type_id'] = $status_type_id;

        $data['establishment'] = $establishment;
        $data['establishment_address'] = $establishment->address;
        $data['establishment_department_description'] = $establishment->department->description;
        $data['establishment_district_description'] = $establishment->district->description;
        $data['nota_venta'] = 0;

        $data['total_tips'] = 0;
        $data['total_payment_cash_01_document'] = 0;
        $data['total_payment_cash_01_sale_note'] = 0;
        $data['total_cash_payment_method_type_01'] = 0;
        $data['separate_cash_transactions'] = Configuration::getSeparateCashTransactions();

        $data['total_cash_income_pmt_01'] = 0; // total de ingresos en efectivo y destino caja
        $data['total_cash_egress_pmt_01'] = 0; // total de egresos (compras + gastos) en efectivo y destino caja
        // $total_purchase_payment_method_cash = 0; // total de pagos en efectivo para compras sin considerar destino

        $cash_income_x = 0;

        $nota_credito = 0;
        $nota_debito = 0;


        $items = 0; // declaro items
        $all_items = []; // declaro items
        $collection_items = new Collection();
        $sale_notes_total = 0;
        /************************/
        foreach ($cash_documents as $cash_document) {
            $temp = [];
            $notes = [];
            $usado = '';
            $payment_method_description = null;

            /** Documentos de Tipo Nota de venta */
            if ($cash_document->payment_type == 'App\Models\Tenant\SaleNotePayment') {
                $sale_note_payment = SaleNotePayment::find($cash_document->payment_id);
                if ($sale_note_payment) {
                    $sale_note = $sale_note_payment->sale_note;
                    $reference = $sale_note_payment->reference;
                    $pays = [];
                    if (in_array($sale_note->state_type_id, $status_type_id)) {
                        $record_total = 0;
                        $total = self::CalculeTotalOfCurency(
                            $sale_note->total,
                            $sale_note->currency_type_id,
                            $sale_note->exchange_rate_sale
                        );
                        $cash_income += $sale_note_payment->payment;
                        $final_balance += $sale_note_payment->payment;


                        foreach ($methods_payment as $record) {
                            if ($sale_note_payment->payment_method_type_id == $record->id) {
                                $payment_amount = 0;
                                $sale_note =  $sale_note_payment->sale_note;
                                if ($sale_note->currency_type_id === 'PEN') {
                                    $payment_amount = $sale_note_payment->payment;
                                } else {
                                    $payment_amount = $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                }
                                $payment_method_description = $record->description;
                                // $record->sum = ($record->sum + $sale_note_payment->payment);
                                $record->sum = ($record->sum + $payment_amount);
                                if ($record->is_cash) {
                                    $data['total_payment_cash_01_sale_note'] += $payment_amount;
                                    // $sale_note =  $sale_note_payment->sale_note;


                                    // if ($sale_note->currency_type_id === 'PEN') {
                                    //     $data['total_payment_cash_01_sale_note'] += $sale_note_payment->payment;
                                    // } else {
                                    //     $data['total_payment_cash_01_sale_note'] += $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                    // }
                                }
                                if ($record->is_cash) {
                                    $sale_notes_total += $payment_amount;
                                    $cash_income_x += $payment_amount;
                                    // $sale_note =  $sale_note_payment->sale_note;
                                    // if ($sale_note->currency_type_id === 'PEN') {
                                    //     $cash_income_x += $sale_note_payment->payment;
                                    // } else {
                                    //     $cash_income_x += $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                    // }
                                }
                            }
                        }

                        $data['total_cash_income_pmt_01'] += $sale_note_payment->payment;
                        $data['total_tips'] += $sale_note->tip ? $sale_note->tip->total : 0;
                    }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'state_type_id' => $sale_note->state_type_id,
                        'seller_id'                => $sale_note->seller_id,
                        'seller_name'               => $sale_note->seller->name,
                        'payment_method_description' => $payment_method_description,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'NOTA DE VENTA',
                        'number'                    => $sale_note->number_full,
                        // 'date_of_issue'             => $date_payment,
                        'date_of_issue'             => $sale_note->date_of_issue->format('Y-m-d'),
                        'date_sort'                 => $sale_note->date_of_issue,
                        'customer_name'             => $sale_note->customer->name,
                        'customer_number'           => $sale_note->customer->number,
                        'total'                     => ((!in_array($sale_note->state_type_id, $status_type_id)) ? 0
                            : $sale_note->total),
                        'currency_type_id'          => $sale_note->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'sale_note',
                        // 'total_payments'            => (!in_array($sale_note->state_type_id, $status_type_id)) ? 0 : $sale_note->payments->sum('payment'),
                        'total_payments'            => $sale_note_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $sale_note->created_at->format('YmdHis'),
                        'reference'                => $reference,
                    ];
                    if ($temp['document_type_description'] === 'NOTA DE VENTA') {
                    }
                    // items
                    foreach ($sale_note->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }
                }
                // fin items

            } elseif (
                $cash_document->payment_type == 'App\Models\Tenant\PackageHandlerPayment'
            ) {
                $package_handler_payment = PackageHandlerPayment::find($cash_document->payment_id);
                if ($package_handler_payment) {
                    $package_handler = $package_handler_payment->package_handler;
                    $reference = $package_handler_payment->reference;
                    $pays = [];
                    // if (in_array($package_handler->state_type_id, $status_type_id)) {
                    $record_total = 0;
                    $total = self::CalculeTotalOfCurency(
                        $package_handler->total,
                        $package_handler->currency_type_id,
                        $package_handler->exchange_rate_sale
                    );
                    $cash_income += $package_handler_payment->payment;
                    $final_balance += $package_handler_payment->payment;


                    foreach ($methods_payment as $record) {
                        if ($package_handler_payment->payment_method_type_id == $record->id) {
                            $payment_method_description = $record->description;
                            $record->sum = ($record->sum + $package_handler_payment->payment);
                            if ($record->is_cash) $data['total_payment_cash_01_sale_note'] += $package_handler_payment->payment;
                            if ($record->is_cash) $cash_income_x += $package_handler_payment->payment;
                        }
                    }

                    $data['total_cash_income_pmt_01'] += $package_handler_payment->payment;
                    $data['total_tips'] += 0;
                    // }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'TICKET DE ENCOMIENDA',
                        'number'                    => $package_handler->series . "-" . $package_handler->number,
                        'date_of_issue'             => $date_payment,
                        'date_sort'                 => $package_handler->date_of_issue,
                        'customer_name'             => $package_handler->sender->name,
                        'customer_number'           => $package_handler->sender->number,
                        'total'                     => $package_handler->total,
                        'currency_type_id'          => $package_handler->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'sale_note',
                        'total_payments'            => (!in_array($package_handler->state_type_id, $status_type_id)) ? 0 : $package_handler->payments->sum('payment'),
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $package_handler->created_at->format('YmdHis'),
                    ];

                    // items
                    foreach ($package_handler->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }
                }
                // fin items

            }
            /** Documentos de Tipo Document */
            elseif ($cash_document->payment_type == 'App\Models\Tenant\DocumentPayment') {
                $record_total = 0;
                // $document = $cash_document->document;
                $document_payment = DocumentPayment::find($cash_document->payment_id);
                if ($document_payment) {
                    $reference = $document_payment->reference;
                    $document = $document_payment->document;

                    $payment_condition_id = $document->payment_condition_id;
                    $pays = $document->payments;
                    $pagado = 0;
                    if (in_array($document->state_type_id, $status_type_id)) {
                        if ($payment_condition_id == '01') {
                            $total = self::CalculeTotalOfCurency(
                                // $document->total,
                                $document_payment->payment,
                                $document->currency_type_id,
                                $document->exchange_rate_sale
                            );
                            $usado .= '<br>Tomado para income<br>';
                            $cash_income += $document_payment->payment;
                            $final_balance += $document_payment->payment;
                            if (count($pays) > 0) {
                                $usado .= '<br>Se usan los pagos<br>';
                                foreach ($methods_payment as $record) {
                                    if ($document_payment->payment_method_type_id == $record->id) {
                                        $document =  $document_payment->document;
                                        $payment_amount = $document_payment->payment;
                                        if ($document->currency_type_id === 'PEN') {
                                            $payment_amount = $document_payment->payment;
                                        } else {
                                            $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                        }
                                        $payment_method_description = $record->description;


                                        $record->sum = ($record->sum + $payment_amount);

                                        if (!empty($record_total)) {
                                            $usado .= self::getStringPaymentMethod($record->id) . '<br>Se usan los pagos Tipo ' . $record->id . '<br>';
                                        }

                                        if ($record->is_cash) {
                                            $data['total_payment_cash_01_document'] += $payment_amount;
                                        }
                                        if ($record->is_cash) {

                                            $cash_income_x += $payment_amount;
                                        }
                                    }
                                }
                            }
                        } else {
                            $usado .= '<br> state_type_id: ' . $document->state_type_id . '<br>';
                            foreach ($methods_payment as $record) {
                                if ($document_payment->payment_method_type_id == $record->id) {
                                    $payment_method_description = $record->description;
                                    $document =  $document_payment->document;
                                    $payment_amount = $document_payment->payment;
                                    if ($document->currency_type_id !== 'PEN') {
                                        $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                    }
                                    if ($record->is_cash) {
                                        $data['total_payment_cash_01_document'] += $payment_amount;

                                        $cash_income_x += $payment_amount;
                                    }



                                    $record->sum += $payment_amount;

                                    $record_total = $pays
                                        ->where('payment_method_type_id', $record->id)
                                        ->whereIn('document.state_type_id', $status_type_id)
                                        ->transform(function ($row) {
                                            if (!empty($row->change) && !empty($row->payment)) {
                                                return (object)[
                                                    'payment' => $row->change * $row->payment,
                                                ];
                                            }
                                            return (object)[
                                                'payment' => $row->payment,
                                            ];
                                        })
                                        ->sum('payment');
                                    $usado .= "Id de documento {$document->id} - " . self::getStringPaymentMethod($record->id) . " /* $record_total */<br>";
                                    $total_paid = $document->payments->sum('payment');
                                    if ($record->id == '09') {

                                        $usado .= '<br>Se usan los pagos Credito Tipo ' . $record->id . ' ****<br>';
                                        // $record->sum += $document->total;
                                        // 

                                        $credit += $document->total - $total_paid;
                                        // $credit += $document_payment->payment;
                                    } elseif ($record_total != 0) {
                                        if ((in_array($record->id, $methods_payment_credit))) {

                                            // $record->sum += $document_payment->payment;
                                            $pagado += $document_payment->payment;
                                            // $cash_income += $document_payment->payment;
                                            $credit -= $document->total == $total_paid ? 0 : $document_payment->payment;

                                            $final_balance += $document_payment->payment;
                                        } else {

                                            $record->sum += $document_payment->payment;
                                            // $credit += $record_total;
                                            $credit += $document->total == $total_paid ? 0 : $document_payment->payment;
                                        }
                                    }
                                }
                            }
                        }

                        $data['total_tips'] += $document->tip ? $document->tip->total : 0;
                        // $data['total_cash_income_pmt_01'] += $this->getIncomeEgressCashDestination($document->payments);
                        $data['total_cash_income_pmt_01'] += $document_payment->payment;
                    }
                    if ($record_total != $document->total) {
                        $usado .= '<br> Los montos son diferentes ' . $document->total . " vs " . $pagado . "<br>";
                    }
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $order_number = $document->document_type_id === '01' ? 1 : 2;
                    $temp = [
                        'state_type_id'            => $document->state_type_id,
                        'seller_id'                => $document->seller_id,
                        'seller_name'               => $document->seller->name,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => $document->document_type->description,
                        'number'                    => $document->number_full,
                        'date_of_issue'             => $date_payment,
                        'date_sort'                 => $document->date_of_issue,
                        'customer_name'             => $document->customer->name,
                        'customer_number'           => $document->customer->number,
                        'total'                     => (!in_array($document->state_type_id, $status_type_id)) ? 0
                            : $document->total,
                        'currency_type_id'          => $document->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,

                        'tipo' => 'document',
                        'total_payments'            => (!in_array($document->state_type_id, $status_type_id)) ? 0 : $document_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $document->created_at->format('YmdHis'),

                    ];
                    /* Notas de credito o debito*/
                    // $notes = $document->getNotes();

                    // items
                    foreach ($document->items as $item) {
                        $items++;
                        array_push($all_items, $item);
                        $collection_items->push($item);
                    }
                }
                // fin items
            }
            /** Documentos de Tipo Servicio tecnico */
            elseif ($cash_document->payment_type == 'App\Models\Tenant\TechnicalServicePayment') {
                $usado = '<br>Se usan para cash<br>';
                // $technical_service = $cash_document->technical_service;
                $technical_service_payment = TechnicalServicePayment::find($cash_document->payment_id);
                $reference = $technical_service_payment->reference;
                if ($technical_service_payment) {
                    $technical_service  = $technical_service_payment->technical_service;

                    if ($technical_service->applyToCash()) {
                        $cash_income += $technical_service_payment->payment;
                        $final_balance += $technical_service_payment->payment;

                        if (count($technical_service->payments) > 0) {
                            $usado = '<br>Se usan los pagos<br>';
                            $pays = $technical_service->payments;
                            foreach ($methods_payment as $record) {
                                if ($technical_service_payment->payment_method_type_id == $record->id) {
                                    if ($record->is_cash) $cash_income_x += $technical_service_payment->payment;
                                    $payment_method_description = $record->description;
                                    $record->sum = ($record->sum + $technical_service_payment->payment);
                                    if (!empty($record_total)) {
                                        $usado .= self::getStringPaymentMethod($record->id) . '<br>Se usan los pagos Tipo ' . $record->id . '<br>';
                                    }
                                }
                            }
                            $data['total_cash_income_pmt_01'] += $technical_service_payment->payment;
                        }

                        $order_number = 4;

                        $temp = [
                            'type_transaction'          => 'Venta',
                            'document_type_description' => 'Servicio técnico',
                            'number'                    => 'TS-' . $technical_service->id, //$value->document->number_full,
                            'date_of_issue'             => $technical_service->date_of_issue->format('Y-m-d'),
                            'date_sort'                 => $technical_service->date_of_issue,
                            'customer_name'             => $technical_service->customer->name,
                            'customer_number'           => $technical_service->customer->number,
                            'total'                     => $technical_service->total_record,
                            // 'total'                     => $technical_service->cost,
                            'currency_type_id'          => 'PEN',
                            'usado'                     => $usado . " " . __LINE__,
                            'tipo'                      => 'technical_service',
                            'total_payments'            => $technical_service->payments->sum('payment'),
                            'type_transaction_prefix'   => 'income',
                            'order_number_key'          => $order_number . '_' . $technical_service->created_at->format('YmdHis'),
                        ];
                    }
                }
            }
            /** Documentos de Tipo Gastos */
            elseif ($cash_document->payment_type == 'Modules\Expense\Models\ExpensePayment') {
                // $expense_payment = $cash_document->expense_payment;
                $expense_payment = ExpensePayment::find($cash_document->payment_id);
                $reference = $expense_payment->reference;
                $total_expense_payment = 0;

                if ($expense_payment->expense->state_type_id == '05') {
                    $total_expense_payment = self::CalculeTotalOfCurency(
                        $expense_payment->payment,
                        $expense_payment->expense->currency_type_id,
                        $expense_payment->expense->exchange_rate_sale
                    );

                    $cash_egress += $total_expense_payment;
                    $final_balance -= $total_expense_payment;
                    // $cash_egress += $total;
                    // $final_balance -= $total;
                    foreach ($methods_payment as $record) {
                        if ($expense_payment->expense_method_type_id == "1" && $record->is_cash) {
                            $payment_method_description = $record->description;
                            $record->sum = ($record->sum - $expense_payment->payment);
                        }
                    }
                    $data['total_cash_egress_pmt_01'] += $total_expense_payment;
                }

                $order_number = 9;

                $temp = [
                    'type_transaction'          => 'Gasto',
                    'document_type_description' => $expense_payment->expense->expense_type->description,
                    'number'                    => $expense_payment->expense->number,
                    'date_of_issue'             => $expense_payment->expense->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $expense_payment->expense->date_of_issue,
                    'customer_name'             => $expense_payment->expense->supplier->name,
                    'customer_number'           => $expense_payment->expense->supplier->number,
                    'total'                     => -$total_expense_payment,
                    // 'total'                     => -$expense_payment->payment,
                    'currency_type_id'          => $expense_payment->expense->currency_type_id,
                    'usado'                     => $usado . " " . __LINE__,

                    'tipo' => 'expense_payment',
                    'total_payments'            => $total_expense_payment,
                    // 'total_payments'            => -$expense_payment->payment,
                    'type_transaction_prefix'   => 'egress',
                    'order_number_key'          => $order_number . '_' . $expense_payment->expense->created_at->format('YmdHis'),

                ];
            }
            /** Documentos de Tipo ingresos */
            elseif ($cash_document->payment_type == 'Modules\Finance\Models\IncomePayment') {
                $income_payment = IncomePayment::find($cash_document->payment_id);
                $reference = $income_payment->reference;
                // $income_payment = $cash_document->income_payment;
                $total_income_payment = 0;

                if ($income_payment->income->state_type_id == '05') {
                    $total_income_payment = self::CalculeTotalOfCurency(
                        $income_payment->payment,
                        $income_payment->income->currency_type_id,
                        $income_payment->income->exchange_rate_sale
                    );
                    $cash_income += $total_income_payment;
                    $final_balance += $total_income_payment;
                    foreach ($methods_payment as $record) {

                        if ($income_payment->payment_method_type_id == $record->id) {
                            if ($record->is_cash) {

                                $cash_income_x += $income_payment->payment;
                            }
                            $payment_method_description = $record->description;
                            $record->sum = ($record->sum + $income_payment->payment);
                        }
                    }
                    // $cash_egress += $total;
                    // $final_balance -= $total;

                    $data['total_cash_income_pmt_01'] += $total_income_payment;
                }

                $order_number = 9;

                $temp = [
                    'type_transaction'          => 'Ingreso',
                    'document_type_description' => $income_payment->income->income_type->description,
                    'number'                    => $income_payment->income->id,
                    'date_of_issue'             => $income_payment->income->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $income_payment->income->date_of_issue,
                    'customer_name'             => $income_payment->income->customer,
                    'customer_number'           => '-',
                    'total'                     => $total_income_payment,
                    // 'total'                     => -$expense_payment->payment,
                    'currency_type_id'          => $income_payment->income->currency_type_id,
                    'usado'                     => $usado . " " . __LINE__,

                    'tipo' => 'expense_payment',
                    'total_payments'            => $total_income_payment,
                    // 'total_payments'            => -$expense_payment->payment,
                    'type_transaction_prefix'   => 'income',
                    'order_number_key'          => $order_number . '_' . $income_payment->income->created_at->format('YmdHis'),

                ];
            }
            /** Documentos de Tipo compras */
            else if ($cash_document->payment_type == 'App\Models\Tenant\PurchasePayment') {

                /**
                 * @var \App\Models\Tenant\CashDocument $cash_document
                 * @var \App\Models\Tenant\Purchase $purchase
                 * @var \Illuminate\Database\Eloquent\Collection $payments
                 */
                // $purchase = $cash_document->purchase;
                $purchase_payment = PurchasePayment::find($cash_document->payment_id);
                $purchase = $purchase_payment->purchase;
                $reference = $purchase_payment->reference;
                if (in_array($purchase->state_type_id, $status_type_id)) {

                    $payments = $purchase->purchase_payments;
                    $record_total = 0;

                    if (count($payments) > 0) {
                        $pays = $payments;
                        foreach ($methods_payment as $record) {
                            $record_total = $pays->where('payment_method_type_id', $record->id)->sum('payment');
                            $record->sum = ($record->sum - $record_total);
                            $cash_egress += $record_total;
                            $final_balance -= $record_total;
                        }

                        // $data['total_cash_egress_pmt_01'] += $this->getIncomeEgressCashDestination($payments);
                        if ($purchase_payment->payment_method_type_id == '01') {
                            $data['total_cash_egress_pmt_01'] += $purchase_payment->payment;
                        }
                        // $total_purchase_payment_method_cash += $this->getPaymentsByCashFilter($payments)->sum('payment');
                    }
                }

                $order_number = $purchase->document_type_id == '01' ? 7 : 8;

                $temp = [
                    'type_transaction'          => 'Compra',
                    'document_type_description' => $purchase->document_type->description,
                    'number'                    => $purchase->number_full,
                    'date_of_issue'             => $purchase->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $purchase->date_of_issue,
                    'customer_name'             => $purchase->supplier->name,
                    'customer_number'           => $purchase->supplier->number,
                    'total'                     => ((!in_array($purchase->state_type_id, $status_type_id)) ? 0 : $purchase->total),
                    'currency_type_id'          => $purchase->currency_type_id,
                    'usado'                     => $usado . " " . __LINE__,
                    'tipo'                      => 'purchase',
                    'total_payments'            => (!in_array($purchase->state_type_id, $status_type_id)) ? 0 : $purchase->payments->sum('payment'),
                    'type_transaction_prefix'   => 'egress',
                    'order_number_key'          => $order_number . '_' . $purchase->created_at->format('YmdHis'),
                ];
            }
            /** Cotizaciones */
            else if ($cash_document->payment_type == 'Modules\Sale\Models\QuotationPayment') {
                $quotation_payment = QuotationPayment::find($cash_document->payment_id);
                $reference = $quotation_payment->reference;
                $quotation = $quotation_payment->quotation;
                $payment_amount = $quotation_payment->payment;
                if ($quotation->currency_type_id === 'PEN') {
                    $payment_amount = $quotation_payment->payment;
                } else {
                    $payment_amount = $quotation_payment->payment * $quotation->exchange_rate_sale;
                }
                // validar si cumple condiciones para usar registro en reporte
                if ($quotation->applyQuotationToCash()) {
                    if (in_array($quotation->state_type_id, $status_type_id)) {
                        $record_total = 0;

                        // $total = self::CalculeTotalOfCurency(
                        //     $quotation->total,
                        //     $quotation->currency_type_id,
                        //     $quotation->exchange_rate_sale
                        // );

                        $cash_income += $payment_amount;
                        $final_balance += $payment_amount;



                        foreach ($methods_payment as $record) {
                            if ($quotation_payment->payment_method_type_id == $record->id) {
                                if ($record->is_cash) {

                                    $cash_income_x += $payment_amount;
                                }
                                $payment_method_description = $record->description;
                                $record->sum = ($record->sum + $payment_amount);
                            }
                        }
                        $data['total_cash_income_pmt_01'] += $payment_amount;
                    }

                    $order_number = 5;

                    $temp = [
                        'seller_id'                => $quotation->seller_id,
                        'seller_name'               => $quotation->seller->name,
                        'type_transaction'          => 'Venta (Pago a cuenta)',
                        'document_type_description' => 'COTIZACION  ',
                        'number'                    => $quotation->number_full,
                        'date_of_issue'             => $quotation->date_of_issue->format('Y-m-d'),
                        'date_sort'                 => $quotation->date_of_issue,
                        'customer_name'             => $quotation->customer->name,
                        'customer_number'           => $quotation->customer->number,
                        'total'                     => ((!in_array($quotation->state_type_id, $status_type_id)) ? 0 : $quotation->total),
                        'currency_type_id'          => $quotation->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'quotation',
                        'total_payments'            => (!in_array($quotation->state_type_id, $status_type_id)) ? 0 : $quotation->payments->sum('payment'),
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $quotation->created_at->format('YmdHis'),
                    ];
                }
                /** Cotizaciones */
            }



            if (!empty($temp)) {
                $temp['reference'] = $reference;
                $temp['payment_method_description'] = $payment_method_description;
                $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                $temp['total_string'] = self::FormatNumber($temp['total']);

                $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                if (!in_array($state_type_id, ['11', '13'])) {
                    $all_documents[] = $temp;
                }
            }

            /** Notas de credito o debito */
            if ($notes !== null) {
                foreach ($notes as $note) {
                    $usado = 'Tomado para ';
                    /** @var \App\Models\Tenant\Note $note */
                    $sum = $note->isDebit();
                    $type = ($note->isDebit()) ? 'Nota de debito' : 'Nota de crédito';
                    $document = $note->getDocument();
                    if (in_array($document->state_type_id, $status_type_id)) {
                        $record_total = $document->getTotal();
                        /** Si es credito resta */
                        if ($sum) {
                            $usado .= 'Nota de debito';
                            $nota_debito += $record_total;
                            $final_balance += $record_total;
                            $usado .= "Id de documento {$document->id} - Nota de Debito /* $record_total * /<br>";
                        } else {
                            $usado .= 'Nota de credito';
                            $nota_credito += $record_total;
                            $final_balance -= $record_total;
                            $usado .= "Id de documento {$document->id} - Nota de Credito /* $record_total * /<br>";
                        }

                        $order_number = $note->isDebit() ? 6 : 10;

                        $temp = [
                            'type_transaction'          => $type,
                            'document_type_description' => $document->document_type->description,
                            'number'                    => $document->number_full,
                            'date_of_issue'             => $document->date_of_issue->format('Y-m-d'),
                            'date_sort'                 => $document->date_of_issue,
                            'customer_name'             => $document->customer->name,
                            'customer_number'           => $document->customer->number,
                            'total'                     => (!in_array($document->state_type_id, $status_type_id)) ? 0
                                : $document->total,
                            'currency_type_id'          => $document->currency_type_id,
                            'usado'                     => $usado . ' ' . __LINE__,
                            'tipo'                      => 'document',
                            'type_transaction_prefix'   => $note->isDebit() ? 'income' : 'egress',
                            'order_number_key'          => $order_number . '_' . $document->created_at->format('YmdHis'),
                        ];

                        $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                        $temp['total_string'] = self::FormatNumber($temp['total']);
                        $all_documents[] = $temp;
                    }
                }
            }
        }

        if ($withBank) {
            $document_bank = DocumentPayment::where(function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id)
                    ->orWhereHas('document', function ($q) use ($cash_id) {
                        $q->where('cash_id', $cash_id);
                    });
            })
                ->whereHas('global_payment', function ($query) {
                    $query->where('destination_type', 'App\Models\Tenant\BankAccount');
                })
                ->get();



            foreach ($document_bank as $document_payment) {
                $record_total = 0;

                if ($document_payment) {
                    $reference = $document_payment->reference;
                    $document = $document_payment->document;
                    $payment_condition_id = $document->payment_condition_id;
                    $pays = $document->payments;
                    $pagado = 0;
                    if (in_array($document->state_type_id, $status_type_id)) {
                        if ($payment_condition_id == '01') {
                            $total = self::CalculeTotalOfCurency(
                                // $document->total,
                                $document_payment->payment,
                                $document->currency_type_id,
                                $document->exchange_rate_sale
                            );
                            $usado .= '<br>Tomado para income<br>';
                            $cash_income += $document_payment->payment;
                            $final_balance += $document_payment->payment;
                            if (count($pays) > 0) {
                                $usado .= '<br>Se usan los pagos<br>';
                                foreach ($methods_payment as $record) {
                                    if ($document_payment->payment_method_type_id == $record->id) {
                                        $document =  $document_payment->document;
                                        $payment_amount = $document_payment->payment;
                                        if ($document->currency_type_id === 'PEN') {
                                            $payment_amount = $document_payment->payment;
                                        } else {
                                            $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                        }
                                        $payment_method_description = $record->description;
                                        $record->sum = ($record->sum + $payment_amount);

                                        if (!empty($record_total)) {
                                            $usado .= self::getStringPaymentMethod($record->id) . '<br>Se usan los pagos Tipo ' . $record->id . '<br>';
                                        }

                                        if ($record->is_cash) {
                                            $data['total_payment_cash_01_document'] += $payment_amount;
                                        }
                                        if ($record->is_cash) {

                                            $cash_income_x += $payment_amount;
                                        }
                                    }
                                }
                            }
                        } else {


                            $usado .= '<br> state_type_id: ' . $document->state_type_id . '<br>';
                            foreach ($methods_payment as $record) {
                                if ($document_payment->payment_method_type_id == $record->id) {



                                    $payment_method_description = $record->description;
                                    $document =  $document_payment->document;
                                    $payment_amount = $document_payment->payment;
                                    if ($document->currency_type_id !== 'PEN') {
                                        $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                    }
                                    if ($record->is_cash) {
                                        $data['total_payment_cash_01_document'] += $payment_amount;

                                        $cash_income_x += $payment_amount;
                                    }


                                    $record->sum += $payment_amount;

                                    $record_total = $pays
                                        ->where('payment_method_type_id', $record->id)
                                        ->whereIn('document.state_type_id', $status_type_id)
                                        ->transform(function ($row) {
                                            if (!empty($row->change) && !empty($row->payment)) {
                                                return (object)[
                                                    'payment' => $row->change * $row->payment,
                                                ];
                                            }
                                            return (object)[
                                                'payment' => $row->payment,
                                            ];
                                        })
                                        ->sum('payment');
                                    $usado .= "Id de documento {$document->id} - " . self::getStringPaymentMethod($record->id) . " /* $record_total */<br>";
                                    $total_paid = $document->payments->sum('payment');
                                    if ($record->id == '09') {

                                        $usado .= '<br>Se usan los pagos Credito Tipo ' . $record->id . ' ****<br>';
                                        // $record->sum += $document->total;
                                        // 

                                        $credit += $document->total - $total_paid;
                                        // $credit += $document_payment->payment;
                                    } elseif ($record_total != 0) {
                                        if ((in_array($record->id, $methods_payment_credit))) {

                                            // $record->sum += $document_payment->payment;
                                            $pagado += $document_payment->payment;
                                            // $cash_income += $document_payment->payment;
                                            $credit -= $document->total == $total_paid ? 0 : $document_payment->payment;

                                            $final_balance += $document_payment->payment;
                                        } else {

                                            $record->sum += $document_payment->payment;
                                            // $credit += $record_total;
                                            $credit += $document->total == $total_paid ? 0 : $document_payment->payment;
                                        }
                                    }
                                }
                            }
                        }

                        $data['total_tips'] += $document->tip ? $document->tip->total : 0;
                        // $data['total_cash_income_pmt_01'] += $this->getIncomeEgressCashDestination($document->payments);
                        $data['total_cash_income_pmt_01'] += $document_payment->payment;
                    }
                    if ($record_total != $document->total) {
                        $usado .= '<br> Los montos son diferentes ' . $document->total . " vs " . $pagado . "<br>";
                    }
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $order_number = $document->document_type_id === '01' ? 1 : 2;
                    $temp = [
                        'state_type_id'            => $document->state_type_id,
                        'seller_id'                => $document->seller_id,
                        'seller_name'               => $document->seller->name,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => $document->document_type->description,
                        'number'                    => $document->number_full,
                        'date_of_issue'             => $date_payment,
                        'date_sort'                 => $document->date_of_issue,
                        'customer_name'             => $document->customer->name,
                        'customer_number'           => $document->customer->number,
                        'total'                     => (!in_array($document->state_type_id, $status_type_id)) ? 0
                            : $document->total,
                        'currency_type_id'          => $document->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,

                        'tipo' => 'document',
                        'total_payments'            => (!in_array($document->state_type_id, $status_type_id)) ? 0 : $document_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $document->created_at->format('YmdHis'),

                    ];
                    /* Notas de credito o debito*/
                    $notes = $document->getNotes();

                    // items
                    foreach ($document->items as $item) {
                        $items++;
                        array_push($all_items, $item);
                        $collection_items->push($item);
                    }
                    $temp['reference'] = $reference;
                    $temp['payment_method_description'] = $payment_method_description;
                    $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                    $temp['total_string'] = self::FormatNumber($temp['total']);

                    $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                    $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                    if (!in_array($state_type_id, ['11', '13'])) {
                        $all_documents[] = $temp;
                    }
                }
            }

            $sale_note_bank = SaleNotePayment::where(function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id)
                    ->orWhereHas('sale_note', function ($q) use ($cash_id) {
                        $q->where('cash_id', $cash_id);
                    });
            })->whereHas('global_payment', function ($query) {
                $query->where('destination_type', 'App\Models\Tenant\BankAccount');
            })->get();


            foreach ($sale_note_bank as $sale_note_payment) {
                if ($sale_note_payment) {
                    $sale_note = $sale_note_payment->sale_note;
                    $reference = $sale_note_payment->reference;
                    $pays = [];
                    if (in_array($sale_note->state_type_id, $status_type_id)) {
                        $record_total = 0;
                        $total = self::CalculeTotalOfCurency(
                            $sale_note->total,
                            $sale_note->currency_type_id,
                            $sale_note->exchange_rate_sale
                        );
                        $cash_income += $sale_note_payment->payment;
                        $final_balance += $sale_note_payment->payment;


                        foreach ($methods_payment as $record) {
                            if ($sale_note_payment->payment_method_type_id == $record->id) {
                                $payment_amount = 0;
                                $sale_note =  $sale_note_payment->sale_note;
                                if ($sale_note->currency_type_id === 'PEN') {
                                    $payment_amount = $sale_note_payment->payment;
                                } else {
                                    $payment_amount = $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                }
                                $payment_method_description = $record->description;
                                $record->sum = ($record->sum + $payment_amount);
                                if ($record->is_cash) {
                                    $data['total_payment_cash_01_sale_note'] += $payment_amount;
                                }
                                if ($record->is_cash) {

                                    $cash_income_x += $payment_amount;
                                }
                            }
                        }

                        $data['total_cash_income_pmt_01'] += $sale_note_payment->payment;
                        $data['total_tips'] += $sale_note->tip ? $sale_note->tip->total : 0;
                    }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'state_type_id' => $sale_note->state_type_id,
                        'seller_id'                => $sale_note->seller_id,
                        'seller_name'               => $sale_note->seller->name,
                        'payment_method_description' => $payment_method_description,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'NOTA DE VENTA',
                        'number'                    => $sale_note->number_full,
                        // 'date_of_issue'             => $date_payment,
                        'date_of_issue'             => $sale_note->date_of_issue->format('Y-m-d'),
                        'date_sort'                 => $sale_note->date_of_issue,
                        'customer_name'             => $sale_note->customer->name,
                        'customer_number'           => $sale_note->customer->number,
                        'total'                     => ((!in_array($sale_note->state_type_id, $status_type_id)) ? 0
                            : $sale_note->total),
                        'currency_type_id'          => $sale_note->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'sale_note',
                        // 'total_payments'            => (!in_array($sale_note->state_type_id, $status_type_id)) ? 0 : $sale_note->payments->sum('payment'),
                        'total_payments'            => $sale_note_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $sale_note->created_at->format('YmdHis'),
                        'reference'                => $reference,
                    ];
                    if ($temp['document_type_description'] === 'NOTA DE VENTA') {
                    }
                    // items
                    foreach ($sale_note->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }

                    $temp['reference'] = $reference;
                    $temp['payment_method_description'] = $payment_method_description;
                    $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                    $temp['total_string'] = self::FormatNumber($temp['total']);

                    $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                    $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                    if (!in_array($state_type_id, ['11', '13'])) {
                        $all_documents[] = $temp;
                    }
                }
            }
            $quotation_payments = QuotationPayment::whereHas('quotation', function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id);
            })->whereHas('global_payment', function ($query) {
                $query->where('destination_type', 'App\Models\Tenant\BankAccount');
            })->get();
            foreach ($quotation_payments as $quotation_payment) {
                if ($quotation_payment) {
                    $quotation = $quotation_payment->quotation;
                    $reference = $quotation_payment->reference;
                    $pays = [];
                    if (in_array($quotation->state_type_id, $status_type_id)) {
                        $record_total = 0;
                        $total = self::CalculeTotalOfCurency(
                            $quotation->total,
                            $quotation->currency_type_id,
                            $quotation->exchange_rate_sale
                        );
                        $cash_income += $quotation_payment->payment;
                        $final_balance += $quotation_payment->payment;


                        foreach ($methods_payment as $record) {
                            if ($sale_note_payment->payment_method_type_id == $record->id) {
                                $payment_amount = 0;
                                $quotation =  $quotation_payment->quotation;
                                if ($quotation->currency_type_id === 'PEN') {
                                    $payment_amount = $quotation_payment->payment;
                                } else {
                                    $payment_amount = $quotation_payment->payment * $quotation->exchange_rate_sale;
                                }
                                $payment_method_description = $record->description;
                                $record->sum = ($record->sum + $payment_amount);
                                if ($record->is_cash) {
                                    // $data['total_payment_cash_01_quotation'] += $payment_amount;

                                }
                                if ($record->is_cash) {

                                    $cash_income_x += $payment_amount;
                                }
                            }
                        }

                        $data['total_cash_income_pmt_01'] += $quotation_payment->payment;
                        $data['total_tips'] += $quotation->tip ? $quotation->tip->total : 0;
                    }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'state_type_id' => $quotation->state_type_id,
                        'seller_id'                => $quotation->seller_id,
                        'seller_name'               => $quotation->seller->name,
                        'payment_method_description' => $payment_method_description,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'COTIZACION',
                        'number'                    => $quotation->number_full,
                        // 'date_of_issue'             => $date_payment,
                        'date_of_issue'             => $quotation->date_of_issue->format('Y-m-d'),
                        'date_sort'                 => $quotation->date_of_issue,
                        'customer_name'             => $quotation->customer->name,
                        'customer_number'           => $quotation->customer->number,
                        'total'                     => ((!in_array($quotation->state_type_id, $status_type_id)) ? 0
                            : $quotation->total),
                        'currency_type_id'          => $quotation->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'quotation',
                        // 'total_payments'            => (!in_array($quotation->state_type_id, $status_type_id)) ? 0 : $quotation->payments->sum('payment'),
                        'total_payments'            => $quotation_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $quotation->created_at->format('YmdHis'),
                        'reference'                => $reference,
                    ];
                    if ($temp['document_type_description'] === 'COTIZACION') {
                    }
                    // items
                    foreach ($quotation->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }

                    $temp['reference'] = $reference;
                    $temp['payment_method_description'] = $payment_method_description;
                    $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                    $temp['total_string'] = self::FormatNumber($temp['total']);

                    $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                    $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                    if (!in_array($state_type_id, ['11', '13'])) {
                        $all_documents[] = $temp;
                    }
                }
            }
        }

        $data['all_documents'] = $all_documents;
        $temp = [];

        foreach ($methods_payment as $index => $item) {
            $temp[] = [
                'iteracion' => $index + 1,
                'name'      => $item->name,
                'sum'       => self::FormatNumber($item->sum),
                'is_bank'  => $item->is_bank,
                'is_credit' => $item->is_credit,
                'is_cash'  => $item->is_cash,
                'is_digital' => $item->is_digital,
                'payment_method_type_id'       => $item->id ?? null,
            ];
        }

        $data['nota_credito'] = $nota_credito;
        $data['nota_debito'] = $nota_debito;
        $data['methods_payment'] = $temp;
        $data['total_virtual'] = 0;
        foreach ($data['methods_payment'] as $element) {
            $name = strtolower($element["name"]); // Convertir a minúsculas para la comparación

            if ($name === "yape") {
                $data['total_virtual'] += $element["sum"];
            } elseif ($name === "plin") {
                $data['total_virtual'] += $element["sum"];
            }
        }
        $data['credit'] = self::FormatNumber($credit);
        $data['cash_beginning_balance'] = self::FormatNumber($cash->beginning_balance);
        $cash_final_balance = $final_balance + $cash->beginning_balance;
        $data['cash_egress'] = self::FormatNumber($cash_egress);
        $data['cash_final_balance'] = self::FormatNumber($cash_final_balance)  + $data['cash_egress'];

        $data['cash_income'] = self::FormatNumber($cash_income);

        $data['total_cash_payment_method_type_01'] = self::FormatNumber($this->getTotalCashPaymentMethodType01($data));
        $data['total_efectivo'] = $data['total_cash_payment_method_type_01'] - $data['total_virtual'];
        $data['total_cash_egress_pmt_01'] = self::FormatNumber($data['total_cash_egress_pmt_01']);
        // $cash_income_x = $this->sumMethodsPayment($data, "is_cash");
        $cash_digital_x = $this->sumMethodsPayment($data, "is_digital");
        $cash_bank_x = $this->sumMethodsPayment($data, "is_bank");
        $receivable_x = $this->sumMethodsPayment($data, "is_credit");
        $items_to_report = $this->getFormatItemToReport($collection_items);

        $data['items'] = $items;
        $data['all_items'] = $all_items;
        $data['items_to_report'] = $items_to_report;
        $data['cash_income_x'] = $cash_income_x;
        $data['cash_digital_x'] = $cash_digital_x;
        $data['cash_bank_x'] = $cash_bank_x;
        $data['receivable_x'] = $receivable_x;
        $data['document_credit'] = $document_credit;




        //$data["all_documents"] es un array de arrays, cada elemento tiene una key "number" quiero eliminar los repetidos
        $data["all_documents"] = array_map("unserialize", array_unique(array_map("serialize", $data["all_documents"])));
        //$cash_income = ($final_balance > 0) ? ($cash_final_balance - $cash->beginning_balance) : 0;
        return $data;
    }

    public function getClosedCashInfo($id = 0)
    {
        if ($id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se proporciono un id'
            ]);
        }
        $methods_payment = PaymentMethodType::where('is_cash', true)->pluck('id')->toArray();
        $document_payments_ids = GlobalPayment::where('destination_id', $id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->where('payment_type', 'App\Models\Tenant\DocumentPayment')
            ->pluck('payment_id')->toArray();
        $sale_note_payments_ids = GlobalPayment::where('destination_id', $id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->where('payment_type', 'App\Models\Tenant\SaleNotePayment')
            ->pluck('payment_id')->toArray();
        $purchase_payments_ids = GlobalPayment::where('destination_id', $id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->where('payment_type', 'App\Models\Tenant\PurchasePayment')
            ->pluck('payment_id')->toArray();
        $income_payments_ids = GlobalPayment::where('destination_id', $id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->where('payment_type', 'App\Models\Tenant\IncomePayment')
            ->pluck('payment_id')->toArray();
        $expense_payments_ids = GlobalPayment::where('destination_id', $id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->where('payment_type', 'App\Models\Tenant\ExpensePayment')
            ->pluck('payment_id')->toArray();

        $expense_method_type_id = 1;

        $document_payments = DocumentPayment::whereIn('id', $document_payments_ids)->whereIn('payment_method_type_id', $methods_payment)->sum('payment');
        $sale_note_payments = SaleNotePayment::whereIn('id', $sale_note_payments_ids)->whereIn('payment_method_type_id', $methods_payment)->sum('payment');
        $purchase_payments = PurchasePayment::whereIn('id', $purchase_payments_ids)->whereIn('payment_method_type_id', $methods_payment)->sum('payment');
        $income_payments = IncomePayment::whereIn('id', $income_payments_ids)->whereIn('payment_method_type_id', $methods_payment)->sum('payment');
        $expense_payments = ExpensePayment::whereIn('id', $expense_payments_ids)->where('expense_method_type_id', $expense_method_type_id)->sum('payment');

        $income = $document_payments + $sale_note_payments  + $income_payments;
        $egress = $purchase_payments + $expense_payments;

        return $income - $egress;
    }
    public function getDataToClosedCash($id = 0, $withBank = false)
    {

        $cash = Cash::findOrFail($id);
        $final_balance = 0;
        $income = 0;
        $cash_documents = GlobalPayment::where('destination_id', $cash->id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->get();
        $final_balance_with_banks = 0;
        $sale_note_payments_total = 0;
        $document_payments_total = 0;
        $expense_payments_total = 0;
        $purchase_payments_total = 0;
        $income_payments_total = 0;
        $package_handler_payments_total = 0;
        $technical_service_payments_total = 0;
        foreach ($cash_documents as $cash_document) {


            if ($cash_document->payment_type == 'App\Models\Tenant\SaleNotePayment') {
                $sale_note_payment = SaleNotePayment::find($cash_document->payment_id);
                if (!$sale_note_payment) {
                    continue;
                }
                $sale_note = $sale_note_payment->sale_note;
                if ($sale_note->quotation_id && $sale_note->quotation->payments->count() > 0) {
                    continue;
                }
                if ($sale_note_payment && $sale_note_payment->sale_note && in_array($sale_note_payment->sale_note->state_type_id, ['01', '03', '05', '07', '13'])) {
                    $sale_note_payments_total += ($sale_note_payment->sale_note->currency_type_id == 'PEN') ? $sale_note_payment->payment : ($sale_note_payment->payment * $sale_note_payment->sale_note->exchange_rate_sale);
                    $final_balance += ($sale_note_payment->sale_note->currency_type_id == 'PEN') ? $sale_note_payment->payment : ($sale_note_payment->payment * $sale_note_payment->sale_note->exchange_rate_sale);
                }

                // $final_balance += $cash_document->sale_note->total;

            } else if ($cash_document->payment_type == 'App\Models\Tenant\DocumentPayment') {
                $document_payment = DocumentPayment::find($cash_document->payment_id);
                if ($document_payment && $document_payment->document && in_array($document_payment->document->state_type_id, ['01', '03', '05', '07', '13'])) {
                    $document_payments_total += ($document_payment->document->currency_type_id == 'PEN') ? $document_payment->payment : ($document_payment->payment * $document_payment->document->exchange_rate_sale);
                    $final_balance += ($document_payment->document->currency_type_id == 'PEN') ? $document_payment->payment : ($document_payment->payment * $document_payment->document->exchange_rate_sale);
                }

                // $final_balance += $cash_document->document->total;

            } else if ($cash_document->payment_type == 'Modules\Expense\Models\ExpensePayment') {
                $expense_payment = ExpensePayment::find($cash_document->payment_id);
                if ($expense_payment && $expense_payment->expense && $expense_payment->expense->state_type_id == '05') {
                    $expense_payments_total  += ($expense_payment->expense->currency_type_id == 'PEN') ? $expense_payment->payment : ($expense_payment->payment  * $expense_payment->expense->exchange_rate_sale);
                    $final_balance -= ($expense_payment->expense->currency_type_id == 'PEN') ? $expense_payment->payment : ($expense_payment->payment  * $expense_payment->expense->exchange_rate_sale);
                }

                // $final_balance -= $cash_document->expense_payment->payment;

            } else if ($cash_document->payment_type == 'App\Models\Tenant\PurchasePayment') {
                $purchase_payment = PurchasePayment::find($cash_document->payment_id);
                if ($purchase_payment && $purchase_payment->purchase && in_array($purchase_payment->purchase->state_type_id, ['01', '03', '05', '07', '13'])) {
                    if ($purchase_payment->purchase->total_canceled == 1) {
                        $purchase_payments_total += ($purchase_payment->purchase->currency_type_id == 'PEN') ? $purchase_payment->payment : ($purchase_payment->payment * $purchase_payment->purchase->exchange_rate_sale);
                        $final_balance -= ($purchase_payment->purchase->currency_type_id == 'PEN') ? $purchase_payment->payment : ($purchase_payment->payment * $purchase_payment->purchase->exchange_rate_sale);
                    }
                }
            } else if ($cash_document->payment_type == 'Modules\Finance\Models\IncomePayment') {
                $income_payment = IncomePayment::find($cash_document->payment_id);
                if ($income_payment && $income_payment->income && $income_payment->income->state_type_id == '05') {
                    $income_payments_total += ($income_payment->income->currency_type_id == 'PEN') ? $income_payment->payment : ($income_payment->payment  * $income_payment->income->exchange_rate_sale);
                    $final_balance += ($income_payment->income->currency_type_id == 'PEN') ? $income_payment->payment : ($income_payment->payment  * $income_payment->income->exchange_rate_sale);
                }
            } else if (
                $cash_document->payment_type == 'App\Models\Tenant\PackageHandlerPayment'
            ) {

                $package_handler_payment = PackageHandlerPayment::find($cash_document->payment_id);
                if ($package_handler_payment) {
                    $package_handler_payments_total += $package_handler_payment->payment;
                    $final_balance += $package_handler_payment->payment;
                }
            } elseif ($cash_document->payment_type == 'App\Models\Tenant\TechnicalServicePayment') {
                $technical_service_payment = TechnicalServicePayment::find($cash_document->payment_id);
                if ($technical_service_payment) {
                    $technical_service_payments_total += $technical_service_payment->payment;
                    $final_balance += $technical_service_payment->payment;
                }
            }
        }

        $cash_id = $cash->id;
        $valid_state_types = ['01', '03', '05', '07', '13'];

        // Consulta optimizada para pagos de documentos bancarios
        $document_payments = collect([]);

        // Suma de pagos de documentos
        foreach ($document_payments as $document_payment) {
            if ($document_payment->document) {
                $final_balance_with_banks += ($document_payment->document->currency_type_id == 'PEN')
                    ? $document_payment->payment
                    : ($document_payment->payment * $document_payment->document->exchange_rate_sale);
            }
        }

        // Consulta optimizada para pagos de notas de venta
        $sale_note_payments = collect([]);

        // Suma de pagos de notas de venta
        foreach ($sale_note_payments as $sale_note_payment) {
            if ($sale_note_payment->sale_note) {
                $final_balance_with_banks += ($sale_note_payment->sale_note->currency_type_id == 'PEN')
                    ? $sale_note_payment->payment
                    : ($sale_note_payment->payment * $sale_note_payment->sale_note->exchange_rate_sale);
            }
        }


        return [
            'success' => true,
            'final_balance' => number_format($final_balance_with_banks + $cash->beginning_balance + $final_balance, 2, '.', ''),
        ];
    }








    /**
     * @param int $cash_id
     *
     * @return array
     */
    public function setDataToReport($cash_id = 0, $withBank = false)
    {
        Log::info("setDataToReport");
        set_time_limit(0);
        $data = [];
        /** @var Cash $cash */
        $cash = Cash::findOrFail($cash_id);
        $currency_type_id = $cash->currency_type_id;
        $data["counter"] = $cash->counter;
        $credit_notes = Document::where('document_type_id', '07')
            ->whereIn('state_type_id', ['01', '03', '05', '13'])
            ->where('cash_id', $cash_id)
            ->get();
        $document_credit = Document::where('payment_condition_id',  '02')
            ->whereIn('document_type_id', ['03', '01'])
            ->whereIn('state_type_id', ['01', '03', '05', '13'])
            ->where('cash_id', $cash_id)
            ->get()->transform(
                function ($row) {
                    return [
                        "number" => $row->getNumberFullAttribute(),
                        "type" => $row->document_type->description,
                        "date_of_issue" => $row->date_of_issue->format('Y-m-d'),
                        "customer_name" => $row->customer->name,
                        "customer_number" => $row->customer->number,
                        "currency_type_id" => $row->currency_type_id,
                        "total" => $row->total,
                        "credit_type" => optional($row->payment_method_type)->description ?? "-",
                    ];
                }

            );

        $sale_note_credit = SaleNote::whereHas('payment_method_type', function ($query) {
            $query->where('is_credit', true);
        })
            ->where('cash_id', $cash_id)
            ->get()->transform(
                function ($row) {
                    return [
                        "number" => $row->number_full,
                        "type" => "NOTA DE VENTA",
                        "date_of_issue" => $row->date_of_issue->format('Y-m-d'),
                        "customer_name" => $row->customer->name,
                        "customer_number" => $row->customer->number,
                        "currency_type_id" => $row->currency_type_id,
                        "total" => $row->total,
                        "credit_type" => optional($row->payment_method_type)->description ?? "-",
                    ];
                }
            );

        $document_credit = $document_credit->concat($sale_note_credit);
        $document_credit_total = $document_credit->sum('total');



        // $document_credit_total += $sale_note_unpaid;
        $establishment = $cash->user->establishment;
        $status_type_id = self::getStateTypeId();
        $final_balance = 0;
        $cash_income = 0;
        $credit = 0;
        $cash_egress = 0;
        $cash_final_balance = 0;
        $cash_documents = GlobalPayment::where('destination_id', $cash_id)
            ->where('destination_type', 'App\Models\Tenant\Cash')
            ->get();

        $cpe_n = [];
        $all_documents = [];
        $methods_payment_credit = PaymentMethodType::NonCredit()->get()->transform(function ($row) {
            return $row->id;
        })->toArray();

        $methods_payment = collect(PaymentMethodType::all())->transform(function ($row) {
            return (object)[
                'id'   => $row->id,
                'name' => $row->description,
                'description' => $row->description,
                'is_credit' => $row->is_credit,
                'is_cash' => $row->is_cash,
                'is_digital' => $row->is_digital,
                'is_bank' => $row->is_bank,
                'sum'  => 0,
            ];
        });

        // $methods_payment_egress = clone $methods_payment;
        $methods_payment_egress = collect(PaymentMethodType::all())->transform(function ($row) {
            return (object)[
                'id'   => $row->id,
                'name' => $row->description,
                'description' => $row->description,
                'is_credit' => $row->is_credit,
                'is_cash' => $row->is_cash,
                'is_digital' => $row->is_digital,
                'is_bank' => $row->is_bank,
                'sum'  => 0,
            ];
        });

        $company = Company::first();
        $data["document_credit_total"] = $document_credit_total;
        $data['cash'] = $cash;
        $data['cash_user_name'] = $cash->user->name;
        $data['cash_date_opening'] = $cash->date_opening;
        $data['cash_state'] = $cash->state;
        $data['cash_date_closed'] = $cash->date_closed;
        $data['cash_time_closed'] = $cash->time_closed;
        $data['cash_time_opening'] = $cash->time_opening;
        $data['cash_documents'] = $cash_documents;
        $data['cash_documents_total'] = (int)$cash_documents->count();

        $data['company_name'] = $company->name;
        $data['company_number'] = $company->number;
        $data['company'] = $company;

        $data['status_type_id'] = $status_type_id;

        $data['establishment'] = $establishment;
        $data['establishment_address'] = $establishment->address;
        $data['establishment_department_description'] = $establishment->department->description;
        $data['establishment_district_description'] = $establishment->district->description;
        $data['nota_venta'] = 0;

        $data['total_tips'] = 0;
        $data['total_payment_cash_01_document'] = 0;
        $data['total_payment_cash_01_sale_note'] = 0;
        $data['total_cash_payment_method_type_01'] = 0;
        $data['separate_cash_transactions'] = Configuration::getSeparateCashTransactions();

        $data['total_cash_income_pmt_01'] = 0; // total de ingresos en efectivo y destino caja
        $data['total_cash_egress_pmt_01'] = 0; // total de egresos (compras + gastos) en efectivo y destino caja
        // $total_purchase_payment_method_cash = 0; // total de pagos en efectivo para compras sin considerar destino

        $cash_income_x = 0;

        $nota_credito = 0;
        $nota_debito = 0;


        $items = 0; // declaro items
        $all_items = []; // declaro items
        $collection_items = new Collection();
        $sale_notes_total = 0;
        /************************/
        foreach ($cash_documents as $cash_document) {
            $temp = [];
            $notes = [];
            $usado = '';
            $payment_method_description = null;

            /** Documentos de Tipo Nota de venta */
            if ($cash_document->payment_type == 'App\Models\Tenant\SaleNotePayment') {
                $sale_note_payment = SaleNotePayment::find($cash_document->payment_id);
                if ($sale_note_payment) {

                    $sale_note = $sale_note_payment->sale_note;

                    if ($sale_note->quotation_id && $sale_note->quotation->payments->count() > 0) {
                        continue;
                    }

                    $reference = $sale_note_payment->reference;
                    $pays = [];
                    if (in_array($sale_note->state_type_id, $status_type_id)) {
                        $record_total = 0;
                        $total = self::CalculeTotalOfCurency(
                            $sale_note->total,
                            $sale_note->currency_type_id,
                            $sale_note->exchange_rate_sale
                        );
                        $cash_income += $sale_note_payment->payment;
                        $final_balance += $sale_note_payment->payment;


                        foreach ($methods_payment as $record) {
                            if ($sale_note_payment->payment_method_type_id == $record->id) {
                                $payment_amount = 0;
                                $sale_note =  $sale_note_payment->sale_note;
                                if ($sale_note->currency_type_id === 'PEN') {
                                    $payment_amount = $sale_note_payment->payment;
                                } else {
                                    $payment_amount = $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                }
                                $payment_method_description = $record->description;
                                // $record->sum = ($record->sum + $sale_note_payment->payment);
                                $record->sum = ($record->sum + $payment_amount);
                                if ($record->is_cash) {
                                    $data['total_payment_cash_01_sale_note'] += $payment_amount;
                                    // $sale_note =  $sale_note_payment->sale_note;


                                    // if ($sale_note->currency_type_id === 'PEN') {
                                    //     $data['total_payment_cash_01_sale_note'] += $sale_note_payment->payment;
                                    // } else {
                                    //     $data['total_payment_cash_01_sale_note'] += $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                    // }
                                }
                                if ($record->is_cash) {
                                    $sale_notes_total += $payment_amount;
                                    $cash_income_x += $payment_amount;
                                    // $sale_note =  $sale_note_payment->sale_note;
                                    // if ($sale_note->currency_type_id === 'PEN') {
                                    //     $cash_income_x += $sale_note_payment->payment;
                                    // } else {
                                    //     $cash_income_x += $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                    // }
                                }
                            }
                        }

                        $data['total_cash_income_pmt_01'] += $sale_note_payment->payment;
                        $data['total_tips'] += $sale_note->tip ? $sale_note->tip->total : 0;
                    }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'state_type_id' => $sale_note->state_type_id,
                        'seller_id'                => $sale_note->seller_id,
                        'seller_name'               => $sale_note->seller->name,
                        'payment_method_description' => $payment_method_description,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'NOTA DE VENTA',
                        'number'                    => $sale_note->number_full,
                        // 'date_of_issue'             => $date_payment,
                        'date_of_issue'             => $sale_note->date_of_issue->format('Y-m-d'),
                        'date_sort'                 => $sale_note->date_of_issue,
                        'customer_name'             => $sale_note->customer->name,
                        'customer_number'           => $sale_note->customer->number,
                        'total'                     => ((!in_array($sale_note->state_type_id, $status_type_id)) ? 0
                            : $sale_note->total),
                        'currency_type_id'          => $sale_note->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'sale_note',
                        // 'total_payments'            => (!in_array($sale_note->state_type_id, $status_type_id)) ? 0 : $sale_note->payments->sum('payment'),
                        'total_payments'            => $sale_note_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $sale_note->created_at->format('YmdHis'),
                        'reference'                => $reference,
                    ];
                    if ($temp['document_type_description'] === 'NOTA DE VENTA') {
                    }
                    // items
                    foreach ($sale_note->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }
                }
                // fin items

            } elseif (
                $cash_document->payment_type == 'App\Models\Tenant\PackageHandlerPayment'
            ) {
                $package_handler_payment = PackageHandlerPayment::find($cash_document->payment_id);
                if ($package_handler_payment) {
                    $package_handler = $package_handler_payment->package_handler;
                    $reference = $package_handler_payment->reference;
                    $pays = [];
                    // if (in_array($package_handler->state_type_id, $status_type_id)) {
                    $record_total = 0;
                    $total = self::CalculeTotalOfCurency(
                        $package_handler->total,
                        $package_handler->currency_type_id,
                        $package_handler->exchange_rate_sale
                    );
                    $cash_income += $package_handler_payment->payment;
                    $final_balance += $package_handler_payment->payment;


                    foreach ($methods_payment as $record) {
                        if ($package_handler_payment->payment_method_type_id == $record->id) {
                            $payment_method_description = $record->description;
                            $record->sum = ($record->sum + $package_handler_payment->payment);
                            if ($record->is_cash) $data['total_payment_cash_01_sale_note'] += $package_handler_payment->payment;
                            if ($record->is_cash) $cash_income_x += $package_handler_payment->payment;
                        }
                    }

                    $data['total_cash_income_pmt_01'] += $package_handler_payment->payment;
                    $data['total_tips'] += 0;
                    // }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'TICKET DE ENCOMIENDA',
                        'number'                    => $package_handler->series . "-" . $package_handler->number,
                        'date_of_issue'             => $date_payment,
                        'date_sort'                 => $package_handler->date_of_issue,
                        'customer_name'             => $package_handler->sender->name,
                        'customer_number'           => $package_handler->sender->number,
                        'total'                     => $package_handler->total,
                        'currency_type_id'          => $package_handler->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'sale_note',
                        'total_payments'            => (!in_array($package_handler->state_type_id, $status_type_id)) ? 0 : $package_handler->payments->sum('payment'),
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $package_handler->created_at->format('YmdHis'),
                    ];

                    // items
                    foreach ($package_handler->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }
                }
                // fin items

            }
            /** Documentos de Tipo Document */
            elseif ($cash_document->payment_type == 'App\Models\Tenant\DocumentPayment') {
                $record_total = 0;
                // $document = $cash_document->document;
                $document_payment = DocumentPayment::find($cash_document->payment_id);
                if ($document_payment) {
                    $reference = $document_payment->reference;
                    $document = $document_payment->document;

                    $payment_condition_id = $document->payment_condition_id;
                    $pays = $document->payments;
                    $pagado = 0;
                    if (in_array($document->state_type_id, $status_type_id)) {
                        if ($payment_condition_id == '01') {
                            $total = self::CalculeTotalOfCurency(
                                // $document->total,
                                $document_payment->payment,
                                $document->currency_type_id,
                                $document->exchange_rate_sale
                            );
                            $usado .= '<br>Tomado para income<br>';
                            $cash_income += $document_payment->payment;
                            $final_balance += $document_payment->payment;
                            if (count($pays) > 0) {
                                $usado .= '<br>Se usan los pagos<br>';
                                foreach ($methods_payment as $record) {
                                    if ($document_payment->payment_method_type_id == $record->id) {
                                        $document =  $document_payment->document;
                                        $payment_amount = $document_payment->payment;
                                        if ($document->currency_type_id === 'PEN') {
                                            $payment_amount = $document_payment->payment;
                                        } else {
                                            $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                        }
                                        $payment_method_description = $record->description;


                                        $record->sum = ($record->sum + $payment_amount);

                                        if (!empty($record_total)) {
                                            $usado .= self::getStringPaymentMethod($record->id) . '<br>Se usan los pagos Tipo ' . $record->id . '<br>';
                                        }

                                        if ($record->is_cash) {
                                            $data['total_payment_cash_01_document'] += $payment_amount;
                                        }
                                        if ($record->is_cash) {

                                            $cash_income_x += $payment_amount;
                                        }
                                    }
                                }
                            }
                        } else {
                            $usado .= '<br> state_type_id: ' . $document->state_type_id . '<br>';
                            foreach ($methods_payment as $record) {
                                if ($document_payment->payment_method_type_id == $record->id) {
                                    $payment_method_description = $record->description;
                                    $document =  $document_payment->document;
                                    $payment_amount = $document_payment->payment;
                                    if ($document->currency_type_id !== 'PEN') {
                                        $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                    }
                                    if ($record->is_cash) {
                                        $data['total_payment_cash_01_document'] += $payment_amount;

                                        $cash_income_x += $payment_amount;
                                    }



                                    $record->sum += $payment_amount;

                                    $record_total = $pays
                                        ->where('payment_method_type_id', $record->id)
                                        ->whereIn('document.state_type_id', $status_type_id)
                                        ->transform(function ($row) {
                                            if (!empty($row->change) && !empty($row->payment)) {
                                                return (object)[
                                                    'payment' => $row->change * $row->payment,
                                                ];
                                            }
                                            return (object)[
                                                'payment' => $row->payment,
                                            ];
                                        })
                                        ->sum('payment');
                                    $usado .= "Id de documento {$document->id} - " . self::getStringPaymentMethod($record->id) . " /* $record_total */<br>";
                                    $total_paid = $document->payments->sum('payment');
                                    if ($record->id == '09') {

                                        $usado .= '<br>Se usan los pagos Credito Tipo ' . $record->id . ' ****<br>';
                                        // $record->sum += $document->total;
                                        // 

                                        $credit += $document->total - $total_paid;
                                        // $credit += $document_payment->payment;
                                    } elseif ($record_total != 0) {
                                        if ((in_array($record->id, $methods_payment_credit))) {

                                            // $record->sum += $document_payment->payment;
                                            $pagado += $document_payment->payment;
                                            // $cash_income += $document_payment->payment;
                                            $credit -= $document->total == $total_paid ? 0 : $document_payment->payment;

                                            $final_balance += $document_payment->payment;
                                        } else {

                                            $record->sum += $document_payment->payment;
                                            // $credit += $record_total;
                                            $credit += $document->total == $total_paid ? 0 : $document_payment->payment;
                                        }
                                    }
                                }
                            }
                        }

                        $data['total_tips'] += $document->tip ? $document->tip->total : 0;
                        // $data['total_cash_income_pmt_01'] += $this->getIncomeEgressCashDestination($document->payments);
                        $data['total_cash_income_pmt_01'] += $document_payment->payment;
                    }
                    if ($record_total != $document->total) {
                        $usado .= '<br> Los montos son diferentes ' . $document->total . " vs " . $pagado . "<br>";
                    }
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $order_number = $document->document_type_id === '01' ? 1 : 2;
                    $temp = [
                        'state_type_id'            => $document->state_type_id,
                        'seller_id'                => $document->seller_id,
                        'seller_name'               => $document->seller->name,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => $document->document_type->description,
                        'number'                    => $document->number_full,
                        'date_of_issue'             => $date_payment,
                        'date_sort'                 => $document->date_of_issue,
                        'customer_name'             => $document->customer->name,
                        'customer_number'           => $document->customer->number,
                        'total'                     => (!in_array($document->state_type_id, $status_type_id)) ? 0
                            : $document->total,
                        'currency_type_id'          => $document->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,

                        'tipo' => 'document',
                        'total_payments'            => (!in_array($document->state_type_id, $status_type_id)) ? 0 : $document_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $document->created_at->format('YmdHis'),

                    ];
                    /* Notas de credito o debito*/
                    // $notes = $document->getNotes();

                    // items
                    foreach ($document->items as $item) {
                        $items++;
                        array_push($all_items, $item);
                        $collection_items->push($item);
                    }
                }
                // fin items
            }
            /** Documentos de Tipo Servicio tecnico */
            elseif ($cash_document->payment_type == 'App\Models\Tenant\TechnicalServicePayment') {
                $usado = '<br>Se usan para cash<br>';
                // $technical_service = $cash_document->technical_service;
                $technical_service_payment = TechnicalServicePayment::find($cash_document->payment_id);
                $reference = $technical_service_payment->reference;
                if ($technical_service_payment) {
                    $technical_service  = $technical_service_payment->technical_service;

                    if ($technical_service->applyToCash()) {
                        $cash_income += $technical_service_payment->payment;
                        $final_balance += $technical_service_payment->payment;

                        if (count($technical_service->payments) > 0) {
                            $usado = '<br>Se usan los pagos<br>';
                            $pays = $technical_service->payments;
                            foreach ($methods_payment as $record) {
                                if ($technical_service_payment->payment_method_type_id == $record->id) {
                                    if ($record->is_cash) $cash_income_x += $technical_service_payment->payment;
                                    $payment_method_description = $record->description;
                                    $record->sum = ($record->sum + $technical_service_payment->payment);
                                    if (!empty($record_total)) {
                                        $usado .= self::getStringPaymentMethod($record->id) . '<br>Se usan los pagos Tipo ' . $record->id . '<br>';
                                    }
                                }
                            }
                            $data['total_cash_income_pmt_01'] += $technical_service_payment->payment;
                        }

                        $order_number = 4;

                        $temp = [
                            'type_transaction'          => 'Venta',
                            'document_type_description' => 'Servicio técnico',
                            'number'                    => 'TS-' . $technical_service->id, //$value->document->number_full,
                            'date_of_issue'             => $technical_service->date_of_issue->format('Y-m-d'),
                            'date_sort'                 => $technical_service->date_of_issue,
                            'customer_name'             => $technical_service->customer->name,
                            'customer_number'           => $technical_service->customer->number,
                            'total'                     => $technical_service->total_record,
                            // 'total'                     => $technical_service->cost,
                            'currency_type_id'          => 'PEN',
                            'usado'                     => $usado . " " . __LINE__,
                            'tipo'                      => 'technical_service',
                            'total_payments'            => $technical_service->payments->sum('payment'),
                            'type_transaction_prefix'   => 'income',
                            'order_number_key'          => $order_number . '_' . $technical_service->created_at->format('YmdHis'),
                        ];
                    }
                }
            }
            /** Documentos de Tipo Gastos */
            elseif ($cash_document->payment_type == 'Modules\Expense\Models\ExpensePayment') {
                // $expense_payment = $cash_document->expense_payment;
                $expense_payment = ExpensePayment::find($cash_document->payment_id);
                $reference = $expense_payment->reference;
                $total_expense_payment = 0;

                if ($expense_payment->expense->state_type_id == '05') {
                    $total_expense_payment = self::CalculeTotalOfCurency(
                        $expense_payment->payment,
                        $expense_payment->expense->currency_type_id,
                        $expense_payment->expense->exchange_rate_sale
                    );

                    $cash_egress += $total_expense_payment;
                    $final_balance -= $total_expense_payment;
                    // $cash_egress += $total;
                    // $final_balance -= $total;
                    foreach ($methods_payment_egress as $record) {

                        if ($expense_payment->expense_method_type_id == "1" && $record->is_cash && $record->description   == "Efectivo") {
                            $payment_method_description = $record->description;
                            $record->sum = ($record->sum + $expense_payment->payment);
                        }
                    }
                    $data['total_cash_egress_pmt_01'] += $total_expense_payment;
                }

                $order_number = 9;

                $temp = [
                    'type_transaction'          => 'Gasto',
                    'document_type_description' => $expense_payment->expense->expense_type->description,
                    'number'                    => $expense_payment->expense->number,
                    'date_of_issue'             => $expense_payment->expense->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $expense_payment->expense->date_of_issue,
                    'customer_name'             => $expense_payment->expense->supplier->name,
                    'customer_number'           => $expense_payment->expense->supplier->number,
                    'total'                     => -$total_expense_payment,
                    // 'total'                     => -$expense_payment->payment,
                    'currency_type_id'          => $expense_payment->expense->currency_type_id,
                    'usado'                     => $usado . " " . __LINE__,

                    'tipo' => 'expense_payment',
                    'total_payments'            => $total_expense_payment,
                    // 'total_payments'            => -$expense_payment->payment,
                    'type_transaction_prefix'   => 'egress',
                    'order_number_key'          => $order_number . '_' . $expense_payment->expense->created_at->format('YmdHis'),

                ];
            }
            /** Documentos de Tipo ingresos */
            elseif ($cash_document->payment_type == 'Modules\Finance\Models\IncomePayment') {
                $income_payment = IncomePayment::find($cash_document->payment_id);
                $reference = $income_payment->reference;
                // $income_payment = $cash_document->income_payment;
                $total_income_payment = 0;

                if ($income_payment->income->state_type_id == '05') {
                    $total_income_payment = self::CalculeTotalOfCurency(
                        $income_payment->payment,
                        $income_payment->income->currency_type_id,
                        $income_payment->income->exchange_rate_sale
                    );
                    $cash_income += $total_income_payment;
                    $final_balance += $total_income_payment;
                    foreach ($methods_payment as $record) {

                        if ($income_payment->payment_method_type_id == $record->id) {
                            if ($record->is_cash) {

                                $cash_income_x += $income_payment->payment;
                            }
                            $payment_method_description = $record->description;
                            $record->sum = ($record->sum + $income_payment->payment);
                        }
                    }
                    // $cash_egress += $total;
                    // $final_balance -= $total;

                    $data['total_cash_income_pmt_01'] += $total_income_payment;
                }

                $order_number = 9;

                $temp = [
                    'type_transaction'          => 'Ingreso',
                    'document_type_description' => $income_payment->income->income_type->description,
                    'number'                    => $income_payment->income->id,
                    'date_of_issue'             => $income_payment->income->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $income_payment->income->date_of_issue,
                    'customer_name'             => $income_payment->income->customer,
                    'customer_number'           => '-',
                    'total'                     => $total_income_payment,
                    // 'total'                     => -$expense_payment->payment,
                    'currency_type_id'          => $income_payment->income->currency_type_id,
                    'usado'                     => $usado . " " . __LINE__,

                    'tipo' => 'expense_payment',
                    'total_payments'            => $total_income_payment,
                    // 'total_payments'            => -$expense_payment->payment,
                    'type_transaction_prefix'   => 'income',
                    'order_number_key'          => $order_number . '_' . $income_payment->income->created_at->format('YmdHis'),

                ];
            }
            /** Documentos de Tipo compras */
            else if ($cash_document->payment_type == 'App\Models\Tenant\PurchasePayment') {

                /**
                 * @var \App\Models\Tenant\CashDocument $cash_document
                 * @var \App\Models\Tenant\Purchase $purchase
                 * @var \Illuminate\Database\Eloquent\Collection $payments
                 */
                // $purchase = $cash_document->purchase;
                $purchase_payment = PurchasePayment::find($cash_document->payment_id);
                $purchase = $purchase_payment->purchase;
                $reference = $purchase_payment->reference;

                if (in_array($purchase->state_type_id, $status_type_id)) {

                    $payments = $purchase->purchase_payments;
                    $record_total = 0;
                    foreach ($methods_payment_egress as $record) {
                        if ($purchase_payment->payment_method_type_id == $record->id) {
                            $record->sum = ($record->sum + $purchase_payment->payment);
                            $cash_egress += $purchase_payment->payment;
                            $final_balance -= $purchase_payment->payment;
                        }
                    }
                    if ($purchase_payment->payment_method_type_id == '01') {
                        $data['total_cash_egress_pmt_01'] += $purchase_payment->payment;
                    }


                    // if (count($payments) > 0) {
                    //     $pays = $payments;
                    //     foreach ($methods_payment_egress as $record) {
                    //         $record_total = $pays->where('payment_method_type_id', $record->id)->sum('payment');
                    //         $record->sum = ($record->sum + $record_total);
                    //         $cash_egress += $record_total;
                    //         $final_balance -= $record_total;
                    //     }

                    //     // $data['total_cash_egress_pmt_01'] += $this->getIncomeEgressCashDestination($payments);
                    //     if ($purchase_payment->payment_method_type_id == '01') {
                    //         $data['total_cash_egress_pmt_01'] += $purchase_payment->payment;
                    //     }
                    //     // $total_purchase_payment_method_cash += $this->getPaymentsByCashFilter($payments)->sum('payment');
                    // }
                }

                $order_number = $purchase->document_type_id == '01' ? 7 : 8;

                $temp = [
                    'type_transaction'          => 'Compra',
                    'document_type_description' => $purchase->document_type->description,
                    'number'                    => $purchase->number_full,
                    'date_of_issue'             => $purchase->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $purchase->date_of_issue,
                    'customer_name'             => $purchase->supplier->name,
                    'customer_number'           => $purchase->supplier->number,
                    'total'                     => ((!in_array($purchase->state_type_id, $status_type_id)) ? 0 : $purchase->total),
                    'currency_type_id'          => $purchase->currency_type_id,
                    'usado'                     => $usado . " " . __LINE__,
                    'tipo'                      => 'purchase',
                    'total_payments'            => (!in_array($purchase->state_type_id, $status_type_id)) ? 0 : $purchase_payment->payment,
                    'type_transaction_prefix'   => 'egress',
                    'order_number_key'          => $order_number . '_' . $purchase->created_at->format('YmdHis'),
                ];
            }
            /** Cotizaciones */
            else if ($cash_document->payment_type == 'Modules\Sale\Models\QuotationPayment') {
                $quotation_payment = QuotationPayment::find($cash_document->payment_id);
                if ($quotation_payment) {
                    $reference = $quotation_payment->reference;
                    $quotation = $quotation_payment->quotation;
                    $payment_amount = $quotation_payment->payment;
                    if ($quotation->currency_type_id === 'PEN') {
                        $payment_amount = $quotation_payment->payment;
                    } else {
                        $payment_amount = $quotation_payment->payment * $quotation->exchange_rate_sale;
                    }
                    // validar si cumple condiciones para usar registro en reporte
                    if ($quotation->applyQuotationToCash()) {
                        if (in_array($quotation->state_type_id, $status_type_id)) {
                            $record_total = 0;

                            // $total = self::CalculeTotalOfCurency(
                            //     $quotation->total,
                            //     $quotation->currency_type_id,
                            //     $quotation->exchange_rate_sale
                            // );

                            $cash_income += $payment_amount;
                            $final_balance += $payment_amount;



                            foreach ($methods_payment as $record) {
                                if ($quotation_payment->payment_method_type_id == $record->id) {
                                    if ($record->is_cash) {

                                        $cash_income_x += $payment_amount;
                                    }
                                    $payment_method_description = $record->description;
                                    $record->sum = ($record->sum + $payment_amount);
                                }
                            }
                            $data['total_cash_income_pmt_01'] += $payment_amount;
                        }

                        $order_number = 5;

                        $temp = [
                            'seller_id'                => $quotation->seller_id,
                            'seller_name'               => $quotation->seller->name,
                            'type_transaction'          => 'Venta (Pago a cuenta)',
                            'document_type_description' => 'COTIZACION  ',
                            'number'                    => $quotation->number_full,
                            'date_of_issue'             => $quotation->date_of_issue->format('Y-m-d'),
                            'date_sort'                 => $quotation->date_of_issue,
                            'customer_name'             => $quotation->customer->name,
                            'customer_number'           => $quotation->customer->number,
                            'total'                     => ((!in_array($quotation->state_type_id, $status_type_id)) ? 0 : $quotation->total),
                            'currency_type_id'          => $quotation->currency_type_id,
                            'usado'                     => $usado . " " . __LINE__,
                            'tipo'                      => 'quotation',
                            'total_payments'            => (!in_array($quotation->state_type_id, $status_type_id)) ? 0 : $quotation->payments->sum('payment'),
                            'type_transaction_prefix'   => 'income',
                            'order_number_key'          => $order_number . '_' . $quotation->created_at->format('YmdHis'),
                        ];
                    }
                }
                /** Cotizaciones */
            }



            if (!empty($temp)) {
                $temp['reference'] = $reference;
                $temp['payment_method_description'] = $payment_method_description;
                $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                $temp['total_string'] = self::FormatNumber($temp['total']);

                $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                if (!in_array($state_type_id, ['11', '13'])) {
                    $all_documents[] = $temp;
                }
            }

            /** Notas de credito o debito */
            $notes = null;
            if ($notes !== null) {
                foreach ($notes as $note) {
                    $usado = 'Tomado para ';
                    /** @var \App\Models\Tenant\Note $note */
                    $sum = $note->isDebit();
                    $type = ($note->isDebit()) ? 'Nota de debito' : 'Nota de crédito';
                    $document = $note->getDocument();
                    if (in_array($document->state_type_id, $status_type_id)) {
                        $record_total = $document->getTotal();
                        /** Si es credito resta */
                        if ($sum) {
                            $usado .= 'Nota de debito';
                            $nota_debito += $record_total;
                            $final_balance += $record_total;
                            $usado .= "Id de documento {$document->id} - Nota de Debito /* $record_total * /<br>";
                        } else {
                            $usado .= 'Nota de credito';
                            $nota_credito += $record_total;
                            $final_balance -= $record_total;
                            $usado .= "Id de documento {$document->id} - Nota de Credito /* $record_total * /<br>";
                        }

                        $order_number = $note->isDebit() ? 6 : 10;

                        $temp = [
                            'type_transaction'          => $type,
                            'document_type_description' => $document->document_type->description,
                            'number'                    => $document->number_full,
                            'date_of_issue'             => $document->date_of_issue->format('Y-m-d'),
                            'date_sort'                 => $document->date_of_issue,
                            'customer_name'             => $document->customer->name,
                            'customer_number'           => $document->customer->number,
                            'total'                     => (!in_array($document->state_type_id, $status_type_id)) ? 0
                                : $document->total,
                            'currency_type_id'          => $document->currency_type_id,
                            'usado'                     => $usado . ' ' . __LINE__,
                            'tipo'                      => 'document',
                            'type_transaction_prefix'   => $note->isDebit() ? 'income' : 'egress',
                            'order_number_key'          => $order_number . '_' . $document->created_at->format('YmdHis'),
                        ];

                        $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                        $temp['total_string'] = self::FormatNumber($temp['total']);
                        $all_documents[] = $temp;
                    }
                }
            }
        }
        foreach ($credit_notes as $credit_note) {
            $note = $credit_note->note;
            $usado = 'Tomado para ';
            /** @var \App\Models\Tenant\Note $note */
            $sum = $note->isDebit();
            $affected_document = $note->affected_document;
            $total_affected_document = $affected_document->total;
            $pays = $affected_document->payments;

            $type = ($note->isDebit()) ? 'Nota de debito' : 'Nota de crédito';
            $document = $note->getDocument();
            $total_credit_note = $document->total;


            if (in_array($document->state_type_id, $status_type_id)) {
                $record_total = $document->getTotal();
                /** Si es credito resta */
                if ($sum) {
                    $usado .= 'Nota de debito';
                    $nota_debito += $record_total;
                    $final_balance += $record_total;
                    $usado .= "Id de documento {$document->id} - Nota de Debito /* $record_total * /<br>";
                } else {
                    $usado .= 'Nota de credito';
                    $nota_credito += $record_total;
                    $final_balance -= $record_total;
                    $usado .= "Id de documento {$document->id} - Nota de Credito /* $record_total * /<br>";
                }

                $order_number = $note->isDebit() ? 6 : 10;

                $temp = [
                    'type_transaction'          => $type,
                    'document_type_description' => $document->document_type->description,
                    'number'                    => $document->number_full,
                    'number_2'                  => $affected_document->number_full,
                    'date_of_issue'             => $document->date_of_issue->format('Y-m-d'),
                    'date_of_issue_2'            => $affected_document->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $document->date_of_issue,
                    'customer_name'             => $document->customer->name,
                    'customer_number'           => $document->customer->number,
                    'total'                     => (!in_array($document->state_type_id, $status_type_id)) ? 0
                        : $document->total,
                    'total_2'                   => (!in_array($affected_document->state_type_id, $status_type_id)) ? 0
                        : $affected_document->total,
                    'currency_type_id'          => $document->currency_type_id,
                    'usado'                     => $usado . ' ' . __LINE__,
                    'tipo'                      => 'document',
                    'type_transaction_prefix'   => $note->isDebit() ? 'income' : 'egress',
                    'order_number_key'          => $order_number . '_' . $document->created_at->format('YmdHis'),
                    'total_final'               => $affected_document->total - $document->total,
                ];

                $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                $temp['total_string'] = self::FormatNumber($temp['total']);
                $all_documents[] = $temp;
            }
            if ($sum) {
                foreach ($methods_payment as $record) {
                    $record_total = $pays->where('payment_method_type_id', $record->id)->sum('payment');
                    if ($record_total != 0) {
                        $record->sum = ($record->sum + $record_total);
                        $final_balance += $record_total;
                    }
                }
            } else {
                $rquest = request()->url();
                $report_a4_detail_2 = strpos($rquest, 'report-a4-detail-2');

                $cash_egress += $total_credit_note;
                $factor = 1;
                if ($total_credit_note < $total_affected_document && $total_affected_document != 0) {
                    $factor = $total_credit_note / $total_affected_document;
                }

                $final_balance -= $total_credit_note;
                if ($report_a4_detail_2) {
                    foreach ($methods_payment as $record) {
                        $record_total = $pays->where('payment_method_type_id', $record->id)->sum('payment');
                        if ($record_total != 0) {
                            $record->sum = ($record->sum - $record_total * $factor);
                            if ($record->is_cash) {
                                $data['total_cash_egress_pmt_01'] += $record_total * $factor;
                            }
                        }
                    }
                } else {
                    $data['total_cash_egress_pmt_01'] += $total_credit_note;
                }
                // $data['total_cash_egress_pmt_01'] += $total_credit_note;
                // foreach ($methods_payment_egress as $record) {
                //     $record_total = $pays->where('payment_method_type_id', $record->id)->sum('payment');

                //     if ($record_total != 0) {
                //         $record->sum = ($record->sum + $record_total);
                //         if ($record->id == '01') {
                //             $data['total_cash_egress_pmt_01'] += $record_total;
                //         }

                //     }
                // }
            }
        }


        $sale_notes_payments = SaleNotePayment::where('cash_id', $cash_id)->whereHas('sale_note', function ($query) use ($cash_id) {
            $query->where('cash_id', '<>', $cash_id);
        })
            ->whereDoesntHave('global_payment')
            ->get();
        foreach ($sale_notes_payments as $sale_note_payment) {
            $temp = [];
            $notes = [];
            $usado = '';
            $payment_method_description = null;
            if ($sale_note_payment) {

                $sale_note = $sale_note_payment->sale_note;


                $reference = $sale_note_payment->reference;
                $pays = [];
                if (in_array($sale_note->state_type_id, $status_type_id)) {
                    $record_total = 0;

                    $cash_income += $sale_note_payment->payment;
                    $final_balance += $sale_note_payment->payment;


                    foreach ($methods_payment as $record) {
                        if ($sale_note_payment->payment_method_type_id == $record->id) {
                            $payment_amount = 0;
                            $sale_note =  $sale_note_payment->sale_note;
                            if ($sale_note->currency_type_id === 'PEN') {
                                $payment_amount = $sale_note_payment->payment;
                            } else {
                                $payment_amount = $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                            }
                            $payment_method_description = $record->description;
                            // $record->sum = ($record->sum + $sale_note_payment->payment);
                            $record->sum = ($record->sum + $payment_amount);
                            if ($record->is_cash) {
                                $data['total_payment_cash_01_sale_note'] += $payment_amount;
                            }
                            if ($record->is_cash) {
                                $sale_notes_total += $payment_amount;
                                $cash_income_x += $payment_amount;
                            }
                        }
                    }

                    $data['total_cash_income_pmt_01'] += $sale_note_payment->payment;
                    $data['total_tips'] += $sale_note->tip ? $sale_note->tip->total : 0;
                }

                $order_number = 3;
                $date_payment = Carbon::now()->format('Y-m-d');
                if (count($pays) > 0) {
                    foreach ($pays as $value) {
                        $date_payment = $value->date_of_payment->format('Y-m-d');
                    }
                }
                $temp = [
                    'state_type_id' => $sale_note->state_type_id,
                    'seller_id'                => $sale_note->seller_id,
                    'seller_name'               => $sale_note->seller->name,
                    'payment_method_description' => $payment_method_description,
                    'type_transaction'          => 'Venta',
                    'document_type_description' => 'NOTA DE VENTA',
                    'number'                    => $sale_note->number_full,
                    'date_of_issue'             => $sale_note->date_of_issue->format('Y-m-d'),
                    'date_sort'                 => $sale_note->date_of_issue,
                    'customer_name'             => $sale_note->customer->name,
                    'customer_number'           => $sale_note->customer->number,
                    'total'                     => ((!in_array($sale_note->state_type_id, $status_type_id)) ? 0
                        : $sale_note->total),
                    'currency_type_id'          => $sale_note->currency_type_id,
                    'usado'                     => $usado . " " . __LINE__,
                    'tipo'                      => 'sale_note',
                    // 'total_payments'            => (!in_array($sale_note->state_type_id, $status_type_id)) ? 0 : $sale_note->payments->sum('payment'),
                    'total_payments'            => $sale_note_payment->payment,
                    'type_transaction_prefix'   => 'income',
                    'order_number_key'          => $order_number . '_' . $sale_note->created_at->format('YmdHis'),
                    'reference'                => $reference,
                ];
                if ($temp['document_type_description'] === 'NOTA DE VENTA') {
                }

                foreach ($sale_note->items as $item) {
                    $items++;
                    array_push($all_items, $item);

                    $collection_items->push($item);
                }
            }

            if (!empty($temp)) {

                $temp['reference'] = $reference;
                $temp['payment_method_description'] = $payment_method_description;
                $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                $temp['total_string'] = self::FormatNumber($temp['total']);

                $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                if (!in_array($state_type_id, ['11', '13'])) {
                    $all_documents[] = $temp;
                }
            }
        }



        if ($withBank) {
            $document_bank = DocumentPayment::where(function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id)
                    ->orWhereHas('document', function ($q) use ($cash_id) {
                        $q->where('cash_id', $cash_id);
                    });
            })
                ->whereHas('global_payment', function ($query) {
                    $query->where('destination_type', 'App\Models\Tenant\BankAccount');
                })
                ->get();



            foreach ($document_bank as $document_payment) {
                $record_total = 0;

                if ($document_payment) {
                    $reference = $document_payment->reference;
                    $document = $document_payment->document;
                    $payment_condition_id = $document->payment_condition_id;
                    $pays = $document->payments;
                    $pagado = 0;
                    if (in_array($document->state_type_id, $status_type_id)) {
                        if ($payment_condition_id == '01') {
                            $total = self::CalculeTotalOfCurency(
                                // $document->total,
                                $document_payment->payment,
                                $document->currency_type_id,
                                $document->exchange_rate_sale
                            );
                            $usado .= '<br>Tomado para income<br>';
                            $cash_income += $document_payment->payment;
                            $final_balance += $document_payment->payment;
                            if (count($pays) > 0) {
                                $usado .= '<br>Se usan los pagos<br>';
                                foreach ($methods_payment as $record) {
                                    if ($document_payment->payment_method_type_id == $record->id) {
                                        $document =  $document_payment->document;
                                        $payment_amount = $document_payment->payment;
                                        if ($document->currency_type_id === 'PEN') {
                                            $payment_amount = $document_payment->payment;
                                        } else {
                                            $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                        }
                                        $payment_method_description = $record->description;
                                        $record->sum = ($record->sum + $payment_amount);

                                        if (!empty($record_total)) {
                                            $usado .= self::getStringPaymentMethod($record->id) . '<br>Se usan los pagos Tipo ' . $record->id . '<br>';
                                        }

                                        if ($record->is_cash) {
                                            $data['total_payment_cash_01_document'] += $payment_amount;
                                        }
                                        if ($record->is_cash) {

                                            $cash_income_x += $payment_amount;
                                        }
                                    }
                                }
                            }
                        } else {


                            $usado .= '<br> state_type_id: ' . $document->state_type_id . '<br>';
                            foreach ($methods_payment as $record) {
                                if ($document_payment->payment_method_type_id == $record->id) {



                                    $payment_method_description = $record->description;
                                    $document =  $document_payment->document;
                                    $payment_amount = $document_payment->payment;
                                    if ($document->currency_type_id !== 'PEN') {
                                        $payment_amount = $document_payment->payment * $document->exchange_rate_sale;
                                    }
                                    if ($record->is_cash) {
                                        $data['total_payment_cash_01_document'] += $payment_amount;

                                        $cash_income_x += $payment_amount;
                                    }


                                    $record->sum += $payment_amount;

                                    $record_total = $pays
                                        ->where('payment_method_type_id', $record->id)
                                        ->whereIn('document.state_type_id', $status_type_id)
                                        ->transform(function ($row) {
                                            if (!empty($row->change) && !empty($row->payment)) {
                                                return (object)[
                                                    'payment' => $row->change * $row->payment,
                                                ];
                                            }
                                            return (object)[
                                                'payment' => $row->payment,
                                            ];
                                        })
                                        ->sum('payment');
                                    $usado .= "Id de documento {$document->id} - " . self::getStringPaymentMethod($record->id) . " /* $record_total */<br>";
                                    $total_paid = $document->payments->sum('payment');
                                    if ($record->id == '09') {

                                        $usado .= '<br>Se usan los pagos Credito Tipo ' . $record->id . ' ****<br>';
                                        // $record->sum += $document->total;
                                        // 

                                        $credit += $document->total - $total_paid;
                                        // $credit += $document_payment->payment;
                                    } elseif ($record_total != 0) {
                                        if ((in_array($record->id, $methods_payment_credit))) {

                                            // $record->sum += $document_payment->payment;
                                            $pagado += $document_payment->payment;
                                            // $cash_income += $document_payment->payment;
                                            $credit -= $document->total == $total_paid ? 0 : $document_payment->payment;

                                            $final_balance += $document_payment->payment;
                                        } else {

                                            $record->sum += $document_payment->payment;
                                            // $credit += $record_total;
                                            $credit += $document->total == $total_paid ? 0 : $document_payment->payment;
                                        }
                                    }
                                }
                            }
                        }

                        $data['total_tips'] += $document->tip ? $document->tip->total : 0;
                        // $data['total_cash_income_pmt_01'] += $this->getIncomeEgressCashDestination($document->payments);
                        $data['total_cash_income_pmt_01'] += $document_payment->payment;
                    }
                    if ($record_total != $document->total) {
                        $usado .= '<br> Los montos son diferentes ' . $document->total . " vs " . $pagado . "<br>";
                    }
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $order_number = $document->document_type_id === '01' ? 1 : 2;
                    $temp = [
                        'state_type_id'            => $document->state_type_id,
                        'seller_id'                => $document->seller_id,
                        'seller_name'               => $document->seller->name,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => $document->document_type->description,
                        'number'                    => $document->number_full,
                        'date_of_issue'             => $date_payment,
                        'date_sort'                 => $document->date_of_issue,
                        'customer_name'             => $document->customer->name,
                        'customer_number'           => $document->customer->number,
                        'total'                     => (!in_array($document->state_type_id, $status_type_id)) ? 0
                            : $document->total,
                        'currency_type_id'          => $document->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,

                        'tipo' => 'document',
                        'total_payments'            => (!in_array($document->state_type_id, $status_type_id)) ? 0 : $document_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $document->created_at->format('YmdHis'),

                    ];
                    /* Notas de credito o debito*/
                    $notes = $document->getNotes();

                    // items
                    foreach ($document->items as $item) {
                        $items++;
                        array_push($all_items, $item);
                        $collection_items->push($item);
                    }
                    $temp['reference'] = $reference;
                    $temp['payment_method_description'] = $payment_method_description;
                    $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                    $temp['total_string'] = self::FormatNumber($temp['total']);

                    $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                    $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                    if (!in_array($state_type_id, ['11', '13'])) {
                        $all_documents[] = $temp;
                    }
                }
            }

            $sale_note_bank = SaleNotePayment::whereHas('sale_note', function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id);
            })->whereHas('global_payment', function ($query) {
                $query->where('destination_type', 'App\Models\Tenant\BankAccount');
            })->get();


            foreach ($sale_note_bank as $sale_note_payment) {
                if ($sale_note_payment) {
                    $sale_note = $sale_note_payment->sale_note;

                    if ($sale_note->quotation_id && $sale_note->quotation->payments->count() > 0) {
                        continue;
                    }

                    $reference = $sale_note_payment->reference;
                    $pays = [];
                    if (in_array($sale_note->state_type_id, $status_type_id)) {
                        $record_total = 0;
                        $total = self::CalculeTotalOfCurency(
                            $sale_note->total,
                            $sale_note->currency_type_id,
                            $sale_note->exchange_rate_sale
                        );
                        $cash_income += $sale_note_payment->payment;
                        $final_balance += $sale_note_payment->payment;


                        foreach ($methods_payment as $record) {
                            if ($sale_note_payment->payment_method_type_id == $record->id) {
                                $payment_amount = 0;
                                $sale_note =  $sale_note_payment->sale_note;
                                if ($sale_note->currency_type_id === 'PEN') {
                                    $payment_amount = $sale_note_payment->payment;
                                } else {
                                    $payment_amount = $sale_note_payment->payment * $sale_note->exchange_rate_sale;
                                }
                                $payment_method_description = $record->description;
                                $record->sum = ($record->sum + $payment_amount);
                                if ($record->is_cash) {
                                    $data['total_payment_cash_01_sale_note'] += $payment_amount;
                                }
                                if ($record->is_cash) {

                                    $cash_income_x += $payment_amount;
                                }
                            }
                        }

                        $data['total_cash_income_pmt_01'] += $sale_note_payment->payment;
                        $data['total_tips'] += $sale_note->tip ? $sale_note->tip->total : 0;
                    }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'state_type_id' => $sale_note->state_type_id,
                        'seller_id'                => $sale_note->seller_id,
                        'seller_name'               => $sale_note->seller->name,
                        'payment_method_description' => $payment_method_description,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'NOTA DE VENTA',
                        'number'                    => $sale_note->number_full,
                        // 'date_of_issue'             => $date_payment,
                        'date_of_issue'             => $sale_note->date_of_issue->format('Y-m-d'),
                        'date_sort'                 => $sale_note->date_of_issue,
                        'customer_name'             => $sale_note->customer->name,
                        'customer_number'           => $sale_note->customer->number,
                        'total'                     => ((!in_array($sale_note->state_type_id, $status_type_id)) ? 0
                            : $sale_note->total),
                        'currency_type_id'          => $sale_note->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'sale_note',
                        // 'total_payments'            => (!in_array($sale_note->state_type_id, $status_type_id)) ? 0 : $sale_note->payments->sum('payment'),
                        'total_payments'            => $sale_note_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $sale_note->created_at->format('YmdHis'),
                        'reference'                => $reference,
                    ];
                    if ($temp['document_type_description'] === 'NOTA DE VENTA') {
                    }
                    // items
                    foreach ($sale_note->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }

                    $temp['reference'] = $reference;
                    $temp['payment_method_description'] = $payment_method_description;
                    $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                    $temp['total_string'] = self::FormatNumber($temp['total']);

                    $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                    $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                    if (!in_array($state_type_id, ['11', '13'])) {
                        $all_documents[] = $temp;
                    }
                }
            }
            $quotation_payments = QuotationPayment::whereHas('quotation', function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id);
            })->whereHas('global_payment', function ($query) {
                $query->where('destination_type', 'App\Models\Tenant\BankAccount');
            })->get();

            foreach ($quotation_payments as $quotation_payment) {
                if ($quotation_payment) {
                    $quotation = $quotation_payment->quotation;
                    $reference = $quotation_payment->reference;
                    $pays = [];
                    if (in_array($quotation->state_type_id, $status_type_id)) {
                        $record_total = 0;
                        $total = self::CalculeTotalOfCurency(
                            $quotation->total,
                            $quotation->currency_type_id,
                            $quotation->exchange_rate_sale
                        );
                        $cash_income += $quotation_payment->payment;
                        $final_balance += $quotation_payment->payment;


                        foreach ($methods_payment as $record) {
                            if ($sale_note_payment->payment_method_type_id == $record->id) {
                                $payment_amount = 0;
                                $quotation =  $quotation_payment->quotation;
                                if ($quotation->currency_type_id === 'PEN') {
                                    $payment_amount = $quotation_payment->payment;
                                } else {
                                    $payment_amount = $quotation_payment->payment * $quotation->exchange_rate_sale;
                                }
                                $payment_method_description = $record->description;
                                $record->sum = ($record->sum + $payment_amount);
                                if ($record->is_cash) {
                                    // $data['total_payment_cash_01_quotation'] += $payment_amount;

                                }
                                if ($record->is_cash) {

                                    $cash_income_x += $payment_amount;
                                }
                            }
                        }

                        $data['total_cash_income_pmt_01'] += $quotation_payment->payment;
                        $data['total_tips'] += $quotation->tip ? $quotation->tip->total : 0;
                    }

                    $order_number = 3;
                    $date_payment = Carbon::now()->format('Y-m-d');
                    if (count($pays) > 0) {
                        foreach ($pays as $value) {
                            $date_payment = $value->date_of_payment->format('Y-m-d');
                        }
                    }
                    $temp = [
                        'state_type_id' => $quotation->state_type_id,
                        'seller_id'                => $quotation->seller_id,
                        'seller_name'               => $quotation->seller->name,
                        'payment_method_description' => $payment_method_description,
                        'type_transaction'          => 'Venta',
                        'document_type_description' => 'COTIZACION',
                        'number'                    => $quotation->number_full,
                        // 'date_of_issue'             => $date_payment,
                        'date_of_issue'             => $quotation->date_of_issue->format('Y-m-d'),
                        'date_sort'                 => $quotation->date_of_issue,
                        'customer_name'             => $quotation->customer->name,
                        'customer_number'           => $quotation->customer->number,
                        'total'                     => ((!in_array($quotation->state_type_id, $status_type_id)) ? 0
                            : $quotation->total),
                        'currency_type_id'          => $quotation->currency_type_id,
                        'usado'                     => $usado . " " . __LINE__,
                        'tipo'                      => 'quotation',
                        // 'total_payments'            => (!in_array($quotation->state_type_id, $status_type_id)) ? 0 : $quotation->payments->sum('payment'),
                        'total_payments'            => $quotation_payment->payment,
                        'type_transaction_prefix'   => 'income',
                        'order_number_key'          => $order_number . '_' . $quotation->created_at->format('YmdHis'),
                        'reference'                => $reference,
                    ];
                    if ($temp['document_type_description'] === 'COTIZACION') {
                    }
                    // items
                    foreach ($quotation->items as $item) {
                        $items++;
                        array_push($all_items, $item);

                        $collection_items->push($item);
                    }

                    $temp['reference'] = $reference;
                    $temp['payment_method_description'] = $payment_method_description;
                    $temp['usado'] = isset($temp['usado']) ? $temp['usado'] : '--';
                    $temp['total_string'] = self::FormatNumber($temp['total']);

                    $temp['total_payments'] = self::FormatNumber($temp['total_payments']);
                    $state_type_id = isset($temp['state_type_id']) ? $temp['state_type_id'] : null;
                    if (!in_array($state_type_id, ['11', '13'])) {
                        $all_documents[] = $temp;
                    }
                }
            }
        }

        $data['all_documents'] = $all_documents;
        $temp = [];

        foreach ($methods_payment as $index => $item) {
            $temp[] = [
                'iteracion' => $index + 1,
                'name'      => $item->name,
                'sum'       => self::FormatNumber($item->sum),
                'is_bank'  => $item->is_bank,
                'is_credit' => $item->is_credit,
                'is_cash'  => $item->is_cash,
                'is_digital' => $item->is_digital,
                'payment_method_type_id'       => $item->id ?? null,
            ];
        }
        foreach ($methods_payment_egress as $index => $item) {
            $temp_egress[] = [
                'iteracion' => $index + 1,
                'name'      => $item->name,
                'sum'       => self::FormatNumber($item->sum),
                'is_bank'  => $item->is_bank,
                'is_credit' => $item->is_credit,
                'is_cash'  => $item->is_cash,
                'is_digital' => $item->is_digital,
                'payment_method_type_id'       => $item->id ?? null,
            ];
        }

        $data['nota_credito'] = $nota_credito;
        $data['nota_debito'] = $nota_debito;
        $data['methods_payment'] = $temp;
        $data['methods_payment_egress'] = $temp_egress;
        $data['total_virtual'] = 0;
        foreach ($data['methods_payment'] as $element) {
            $name = strtolower($element["name"]); // Convertir a minúsculas para la comparación

            if ($name === "yape") {
                $data['total_virtual'] += $element["sum"];
            } elseif ($name === "plin") {
                $data['total_virtual'] += $element["sum"];
            }
        }
        $data['credit'] = self::FormatNumber($credit);
        $data['cash_beginning_balance'] = self::FormatNumber($cash->beginning_balance);
        $cash_final_balance = $final_balance + $cash->beginning_balance;
        $data['cash_egress'] = self::FormatNumber($cash_egress);
        $data['cash_final_balance'] = self::FormatNumber($cash_final_balance)  + $data['cash_egress'];

        $data['cash_income'] = self::FormatNumber($cash_income);

        $data['total_cash_payment_method_type_01'] = self::FormatNumber($this->getTotalCashPaymentMethodType01($data));
        $data['total_efectivo'] = $data['total_cash_payment_method_type_01'] - $data['total_virtual'];
        $data['total_cash_egress_pmt_01'] = self::FormatNumber($data['total_cash_egress_pmt_01']);
        // $cash_income_x = $this->sumMethodsPayment($data, "is_cash");
        $cash_digital_x = $this->sumMethodsPayment($data, "is_digital");
        $cash_bank_x = $this->sumMethodsPayment($data, "is_bank");
        $cash_digital_egress_x = $this->sumMethodsPayment($data, "is_digital", true);
        $cash_bank_egress_x = $this->sumMethodsPayment($data, "is_bank", true);
        $cash_egress_x = $this->sumMethodsPayment($data, "is_cash", true);
        $receivable_x = $this->sumMethodsPayment($data, "is_credit");
        $items_to_report = $this->getFormatItemToReport($collection_items);

        $data['items'] = $items;
        $data['all_items'] = $all_items;
        $data['items_to_report'] = $items_to_report;
        $data['cash_income_x'] = $cash_income_x;
        $data['cash_digital_x'] = $cash_digital_x;
        $data['cash_bank_x'] = $cash_bank_x;
        $data['receivable_x'] = $receivable_x;
        $data['document_credit'] = $document_credit;
        $data['cash_digital_egress_x'] = number_format(abs($cash_digital_egress_x), 2);
        $data['cash_bank_egress_x'] = number_format(abs($cash_bank_egress_x), 2);
        $data['cash_egress_x'] = number_format(abs($cash_egress_x), 2);


        //$data["all_documents"] es un array de arrays, cada elemento tiene una key "number" quiero eliminar los repetidos
        $data["all_documents"] = array_map("unserialize", array_unique(array_map("serialize", $data["all_documents"])));
        //$cash_income = ($final_balance > 0) ? ($cash_final_balance - $cash->beginning_balance) : 0;
        return $data;
    }

    /**
     * organizar items totales para mostrar cantidades y montos por item
     * obtener categorias y cantidad de productos por cada una
     *
     * @param  $items
     * @return array
     */
    public function getFormatItemToReport($items)
    {
        $items_all = [];
        $categories_all = [];
        $grouped = $items->groupBy('item_id');
        $group_cat = [];
        foreach ($grouped as $group) {
            $id = $group[0]->item_id;

            $name = $group[0]->item->description;
            $unit_price = $group[0]->unit_price;
            $quantity = 0;
            $total = 0;
            foreach ($group as $item) {
                $quantity = $quantity + $item->quantity;
                $total = $total + $item->total;
                $cat = [
                    'name' => $item->relation_item->category_id != null ? $item->relation_item->category->name : 'N/A',
                    'quantity' => $item->quantity,
                    'total' => $item->total
                ];
                array_push($group_cat, $cat);
            }

            $item = [
                'id' => $id,
                'name' => $name,
                'unit_price' => $unit_price,
                'quantity' => $quantity,
                'total' => $total
            ];


            array_push($items_all, $item);
        }

        $collect_cat = collect($group_cat)->groupBy('name');
        foreach ($collect_cat as $groups) {
            $cat_quantity = 0;
            $cat_total = 0;
            foreach ($groups as $cat) {
                $cat_quantity = $cat_quantity + $cat['quantity'];
                $cat_total = $cat_total + $cat['total'];
            }
            $cat_res = [
                'name' => $groups[0]['name'],
                'quantity' => $cat_quantity,
                'total' => $cat_total
            ];
            array_push($categories_all, $cat_res);
        }

        return [
            'items' => $items_all,
            'categories' => $categories_all
        ];
    }


    /**
     *
     * Obtener total de pagos en efectivo con destino caja
     *
     * @param  $payments
     * @return float
     */
    public function getIncomeEgressCashDestination($payments)
    {
        return $this->getPaymentsByCashFilter($payments)
            ->sum(function ($row) {

                $payment = 0;

                if ($row->global_payment ?? false) {
                    if ($row->global_payment->isCashDestination()) $payment = $row->payment;
                }

                return $payment;
            });
    }


    /**
     *
     * Filtrar pagos en efectivo
     *
     * @param  array $payments
     * @return array
     */
    public function getPaymentsByCashFilter($payments)
    {
        return $payments->where('payment_method_type_id', self::PAYMENT_METHOD_TYPE_CASH);
    }


    // public function sumMethodsPayment($data, $methods = [], $by_name = false)
    // {
    //     $total = 0;
    //     $methods_payment = $data['methods_payment'];
    //     foreach ($methods_payment as $method) {
    //         if ($by_name) {
    //             $name = strtolower($method["name"]);
    //         } else {
    //             $payment_method_type_id = $method['payment_method_type_id'];
    //         }
    //         if ($by_name) {
    //             if (in_array($name, $methods)) {
    //                 $total += $method["sum"];
    //             }
    //         } else {
    //             if (in_array($payment_method_type_id, $methods)) {
    //                 $total += $method["sum"];
    //             }
    //         }
    //     }
    //     return self::FormatNumber($total);
    // }
    public function sumMethodsPayment($data, $type, $is_egress = false)
    {
        $total = 0;
        $methods_payment = $data['methods_payment'];
        if ($is_egress) {
            $methods_payment = $data['methods_payment_egress'];
        }
        foreach ($methods_payment as $method) {
            if ($method[$type] ==  true) {
                // 
                $total += $method["sum"];
            }
        }
        return self::FormatNumber($total);
    }

    /**
     *
     * Obtener total caja
     * total caja inicial + total ingresos en efectivo con destino caja - total egresos en efectivo con destino caja
     *
     * @param  array $data
     * @return float
     */
    private function getTotalCashPaymentMethodType01($data)
    {

        //total caja inicial + total ingresos en efectivo con destino caja - total egresos en efectivo con destino caja
        return $data['cash_beginning_balance'] + $data['total_cash_income_pmt_01'] - $data['total_cash_egress_pmt_01'];

        // $total_cash_payment_method_type_01 = 0;

        // //total de todos los pagos en efectivo de diferentes documentos
        // $payment_method_01 = collect($data['methods_payment'])->where('payment_method_type_id', '01')->first();

        // if($payment_method_01)
        // {
        //     // al total de pagos en efectivo se le incrementa los pagos de la compra (porque estos no se filtran por destino, con total_cash_egress_pmt_01 se restaran todos los egresos)
        //     $total_income = $payment_method_01['sum'] + $total_purchase_payment_method_cash;

        //     // total ingresos + total caja inicial - total egresos en efectivo con destino caja
        //     $total_cash_payment_method_type_01 = $total_income + $data['cash_beginning_balance'] - $data['total_cash_egress_pmt_01'];
        // }
    }


    /**
     * @param int    $total
     * @param string $currency_type_id
     * @param int    $exchange_rate_sale
     *
     * @return float|int|mixed
     */
    public static function CalculeTotalOfCurency(
        $total = 0,
        $currency_type_id = 'PEN',
        $exchange_rate_sale = 1
    ) {
        if ($currency_type_id !== 'PEN') {
            $total = $total * $exchange_rate_sale;
        }
        return $total;
    }

    /**
     * Obtiene un array de status para sumarlos en los reportes
     *
     * @return string[]
     */
    public static function getStateTypeId()
    {
        return [
            '01', //Registrado
            '03', // Enviado
            '05', // Aceptado
            '07', // Observado
            // '09', // Rechazado
            // '11', // Anulado
            '13' // Por anular
        ];
    }

    /**
     * Genera un pdf basado en el formato deseado
     *
     * @param        $cash
     * @param string $format
     * @param integer $mm
     *
     * @return string
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    private function getPdf($cash, $format = 'ticket', $mm = null, $withBank = false)
    {
        $configuration = Configuration::first();
        if (!$configuration->report_box_egress) {
            if ($format == 'a4') {
                $format = 'a4_special';
            }
            $data = $this->setDataToReportSpecial($cash, $withBank);
        } else {

            $data = $this->setDataToReport($cash, $withBank);
        }
        $data['methods_payment'] = array_filter($data['methods_payment'], function ($method) {
            return isset($method['sum']) && $method['sum'] > 0;
        });
        // 

        $data['methods_payment'] = array_values($data['methods_payment']);
        foreach ($data['methods_payment'] as $index => &$method) {
            $method['iteracion'] = $index + 1;
        }
        unset($method);
        $quantity_rows = 30; //$cash->cash_documents()->count();

        $width = 78;
        if ($mm != null) {
            $width = $mm - 2;
        }

        $view = view('pos::cash.report_pdf_' . $format, compact('data'));
        if ($format === 'simple_a4') {
            $view = view('pos::cash.report_pdf_' . $format, compact('data'));
        }
        if ($format === 'simple_a4_seller') {
            $view = view('pos::cash.report_pdf_' . $format, compact('data'));
        }
        $html = $view->render();

        $pdf = new Mpdf([
            'mode' => 'utf-8',
        ]);
        $margin_top = 3;
        $margin_right = 3;
        $margin_bottom = 3;
        $margin_left = 3;
        if ($format === 'ticket') {
            $pdf = new Mpdf([
                'mode'          => 'utf-8',
                'format'        => [
                    $width,
                    190 +
                        ($quantity_rows * 8),
                ],
                'margin_top'    => $margin_top,
                'margin_right'  => $margin_right,
                'margin_bottom' => $margin_bottom,
                'margin_left'   => $margin_left,
            ]);
        }

        $chunkSize = 500000; // 500KB por chunk (ajustable según necesidad)
        $htmlLength = strlen($html);

        if ($htmlLength > $chunkSize) {
            // Si el HTML es muy grande, dividirlo en chunks
            $chunks = str_split($html, $chunkSize);
            foreach ($chunks as $chunk) {
                $pdf->WriteHTML($chunk);
            }
        } else {
            // Si es pequeño, usar el método normal
            $pdf->WriteHTML($html);
        }

        return $pdf->output('', 'S');
    }

    /**
     * Reporte en Ticket formato cash_pdf_ticket
     *
     * Usado en:
     * CashController - App
     *
     * @param $cash
     * @param integer $mm
     *
     * @return mixed
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function reportTicket(Request $request, $cash, $mm)
    {
        $withBank = $request->withBank;
        $temp = tempnam(sys_get_temp_dir(), 'cash_pdf_ticket_' . $mm);

        file_put_contents($temp, $this->getPdf($cash, 'ticket', $mm, $withBank));

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reporte"'
        ];

        return response()->file($temp, $headers);
    }

    /**
     * Reporte en A4 formato cash_pdf_a4
     *
     * Usado en:
     * CashController - App
     *
     * @param $cash
     *
     * @return mixed
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function reportA4Detail($cash)
    {
        $temp = tempnam(sys_get_temp_dir(), 'cash_pdf_a4_detail');
        file_put_contents($temp, $this->getPdf($cash, 'a4_detail'));

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reporte"'
        ];

        return response()->file($temp, $headers);
    }
    public function reportA4Detail2($cash)
    {
        $temp = tempnam(sys_get_temp_dir(), 'cash_pdf_a4_detail_2');
        file_put_contents($temp, $this->getPdf($cash, 'a4_detail_2'));

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reporte"'
        ];

        return response()->file($temp, $headers);
    }
    public function reportA4(Request $request, $cash)
    {
        $withBank = $request->withBank;

        $temp = tempnam(sys_get_temp_dir(), 'cash_pdf_a4');
        file_put_contents($temp, $this->getPdf($cash, 'a4', null, $withBank));

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reporte"'
        ];

        return response()->file($temp, $headers);
    }

    /**
     * Reporte en A4 formato cash_pdf_a4
     *
     * Usado en:
     * CashController - App
     *
     * @param $cash
     *
     * @return mixed
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function reportSimpleA4($cash)
    {
        $temp = tempnam(sys_get_temp_dir(), 'cash_pdf_a4');
        file_put_contents($temp, $this->getPdf($cash, 'simple_a4'));

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reporte"'
        ];

        return response()->file($temp, $headers);
    }
    public function reportSimpleSellerA4($cash)
    {
        $temp = tempnam(sys_get_temp_dir(), 'cash_pdf_a4');
        file_put_contents($temp, $this->getPdf($cash, 'simple_a4_seller'));

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reporte"'
        ];

        return response()->file($temp, $headers);
    }
    /**
     * Reporte Excel de reporte de caja
     *
     * @param $cash
     *
     * @return Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function reportExcel($cash)
    {
        $data = $this->setDataToReport($cash, true);


        $filename = "Reporte_POS - {$data['cash_user_name']} - {$data['cash_date_opening']} {$data['cash_time_opening']}";
        $report_cash_export = new ReportCashExport();
        $report_cash_export->setData($data);

        return $report_cash_export->download($filename . '.xlsx');
    }


    /**
     *
     * Obtener datos para header de reporte
     *
     * @param  Cash $cash
     * @return array
     */
    public function getHeaderCommonDataToReport($cash)
    {

        $company = Company::select('name', 'number')->first();

        $data['cash_user_name'] = $cash->user->name;
        $data['cash_date_opening'] = $cash->date_opening;
        $data['cash_state'] = $cash->state;
        $data['cash_date_closed'] = $cash->date_closed;
        $data['cash_time_closed'] = $cash->time_closed;
        $data['cash_time_opening'] = $cash->time_opening;
        $data['cash_beginning_balance'] = $cash->beginning_balance;
        $data['company_name'] = $company->name;
        $data['company_number'] = $company->number;

        $establishment = $cash->user->establishment;
        $data['establishment_address'] = $establishment->address;
        $data['establishment_department_description'] = $establishment->department->description;
        $data['establishment_district_description'] = $establishment->district->description;

        $data['total_income'] = 0;
        $data['total_egress'] = 0;

        return $data;
    }


    /**
     *
     * Generar reporte de ingresos y egresos por metodo de pago efectivo con destino caja
     *
     * Usado en:
     * CashController - App
     *
     * @param  int $cash
     */
    public function reportCashIncomeEgress($cash)
    {

        $cash = Cash::findOrFail($cash);
        $data_payments = collect();
        $data = $this->getHeaderCommonDataToReport($cash);
        $state_types_accepted = ['01', '03', '05', '07', '13'];
        // foreach ($cash->cash_documents as $cash_document) {
        //     $model_associated = $cash_document->getDataModelAssociated();
        //     $payments = $model_associated->getCashPayments();

        //     $payments->each(function ($payment) use ($data_payments) {
        //         $data_payments->push($payment);
        //     });
        // }

        $documents_payments = DocumentPayment::whereHas('document', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })->get();


        foreach ($documents_payments as $document_payment) {


            $payment = $document_payment->getRowResourceCashPayment();
            $data_payments->push($payment);
        }


        $sale_note_payments = SaleNotePayment::whereHas('sale_note', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted)
                ->where(function ($query) {
                    $query->whereNull('quotation_id') // No tiene cotización
                        ->orWhereHas('quotation', function ($q2) {
                            $q2->doesntHave('payments'); // Tiene cotización, pero sin pagos
                        });
                });
        })->get();


        foreach ($sale_note_payments as $payment) {

            $payment = $payment->getRowResourceCashPayment();
            $data_payments->push($payment);
        }


        $quotation_payments = QuotationPayment::whereHas('quotation', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })
            ->whereDoesntHave('global_payment')
            ->get();

        $quotation_payments_global_payment = QuotationPayment::whereHas('global_payment', function ($query) use ($cash, $state_types_accepted) {
            $query->where('destination_id', $cash->id)
                ->where('destination_type', Cash::class);
        })->get();

        $quotation_payments = $quotation_payments->merge($quotation_payments_global_payment);

        foreach ($quotation_payments as $payment) {
            $payment = $payment->getRowResourceCashPayment();
            $data_payments->push($payment);
        }

        // $income_payments = IncomePayment::whereHas('global_payment', function ($query) use ($cash) {
        //     $query->where('destination_id', $cash->id)
        //         ->where('destination_type', Cash::class);
        // })->get();

        // $expense_payments = ExpensePayment::whereHas('global_payment', function ($query) use ($cash) {
        //     $query->where('destination_id', $cash->id)
        //         ->where('destination_type', Cash::class);
        // })->get();

        $purchase_payments = PurchasePayment::whereHas('global_payment', function ($query) use ($cash) {
            $query->where('destination_id', $cash->id)
                ->where('destination_type', Cash::class);
        })
            ->whereHas('purchase', function ($query) use ($cash, $state_types_accepted) {
                $query->whereIn('state_type_id', $state_types_accepted);
            })
            ->get();
        foreach ($purchase_payments as $payment) {
            $payment = $payment->getRowResourceCashPayment();
            $data_payments->push($payment);
        }


        $data['total_income'] = $data_payments->where('type_transaction', 'income')->sum('payment');
        $data['total_egress'] = $data_payments->where('type_transaction', 'egress')->sum('payment');
        $data['total_balance'] = $data['total_income'] - $data['total_egress'];

        return $this->toPrintCashIncomeEgress(compact('data', 'data_payments'));
    }


    /**
     * Imprimir reporte de ingresos y egresos
     *
     * @param  array $data
     */
    public function toPrintCashIncomeEgress($data)
    {

        $view = view('pos::cash.reports.report_income_egress_pdf', $data);
        $html = $view->render();

        $pdf = new Mpdf(['mode' => 'utf-8']);
        $pdf->WriteHTML($html);

        $temp = tempnam(sys_get_temp_dir(), 'cash_report_income_egress_pdf');
        file_put_contents($temp, $pdf->output('', 'S'));

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="file.pdf"'
        ];

        return response()->file($temp, $headers);
    }

    private function getProducts($register, $model = null, $payment = null, $payment_type = null)
    {
        $is_document = false;
        $total_cash = 0;
        $total_bank = 0;
        $total_payment = 0;
        $reference = '';
        $payment_method_type_id = null;

        if ($model == InventoryTransfer::class) {
            $date_of_issue = $register->created_at->format('Y-m-d');
            $date_of_payment = '-';
            $number_full = $register->number_full;
            $customer_name = "TRANSFERENCIA DE STOCK";
            $date_of_due = null;
            $total = 0;
            $seller_name = $register->user->name;
            $items = $register->inventory->map(function ($item) {
                return [
                    'description' => $item->item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format(0, 2, '.', ''),
                ];
            });
        } else if ($model == Expense::class) {
            $date_of_issue = $register->date_of_issue->format('Y-m-d');
            $date_of_payment = '-';
            $number_full = $register->number_full;
            $customer_name = $register->supplier->name;
            $date_of_due = null;
            $total = $register->total;
            $seller_name = $register->user->name;
            $items = $register->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => 1,
                    'unit_price' => number_format($item->total, 2, '.', ''),
                ];
            });
        } else if ($model == Purchase::class) {
            $date_of_issue = $register->date_of_issue->format('Y-m-d');
            $date_of_payment = '-';
            $number_full = $register->number_full;
            $customer_name = $register->supplier->name;
            $date_of_due = null;
            $total = $register->total;
            $seller_name = $register->user->name;
            $items = $register->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => 1,
                    'unit_price' => number_format($item->total, 2, '.', ''),
                ];
            });
        } else {
            if ($register instanceof BillOfExchange) {
                $date_of_issue = $register->created_at->format('Y-m-d');
                $date_of_payment = $register->date_of_due->format('Y-m-d');
                $date_of_due = $register->date_of_due->format('Y-m-d');
            } else {
                $date_of_issue = $register->date_of_issue->format('Y-m-d');
                $date_of_payment = $register->date_of_issue->format('Y-m-d');
                $date_of_due = isset($register->invoice) ? $register->invoice->date_of_due->format('Y-m-d') : $register->date_of_issue->format('Y-m-d');
            }
            $currency_type_id = $register->currency_type_id;
            $exchange_rate_sale = $register->exchange_rate_sale;
            $number_full = $register->number_full;
            $customer_name = $register->customer->name;
            $total_payment = $register->total;

            if ($payment) {
                $payment_method_type_id = isset($payment->payment_method_type_id) ? $payment->payment_method_type_id : null;
                // Si es una colección de pagos, calcular total y concatenar referencias
                if (is_object($payment) && method_exists($payment, 'sum')) {
                    $total_payment = $payment->sum('payment');
                    $reference = $payment->pluck('reference')->filter()->implode(' | ');

                    // Calcular total_cash si es necesario
                    if ($payment_type == 'cash') {
                        $total_cash = $payment->sum('payment');
                    }
                    if ($payment_type == 'bank') {
                        $total_bank = $payment->sum('payment');
                    }
                } else {
                    // Si es un solo pago (compatibilidad hacia atrás)
                    $total_payment = $payment->payment;
                    $reference = $payment->reference;
                    if ($payment_type == 'cash') {
                        $total_cash = $total_payment;
                    }
                    if ($payment_type == 'bank') {
                        $total_bank = $total_payment;
                    }
                }
            }

            if ($currency_type_id == 'PEN') {
                $total = $register->total;
            } else {
                $total = $register->total * $exchange_rate_sale;
                $total_payment = $total_payment * $exchange_rate_sale;
            }

            $seller_name = $register->seller ? $register->seller->name : $register->user->name;
            $items = $register->items->map(function ($item) use ($currency_type_id, $exchange_rate_sale) {
                if ($currency_type_id == 'PEN') {
                    $unit_price = $item->unit_price;
                } else {
                    $unit_price = $item->unit_price * $exchange_rate_sale;
                }
                return [
                    'description' => isset($item->item) ? $item->item->description : '',
                    'quantity' => isset($item->quantity) ? $item->quantity : 1,
                    'unit_price' => isset($unit_price) ? number_format($unit_price, 2, '.', '') : 0,
                ];
            });
        }
        return [
            'is_document' => $is_document,
            'date_of_payment' => $date_of_payment,
            'date_of_issue' => $date_of_issue,
            'number_full' => $number_full,
            'customer_name' => $customer_name,
            'date_of_due' => $date_of_due,
            'total' => $total,
            'total_payment' => $total_payment,
            'seller_name' => $seller_name,
            'items' => $items,
            'total_cash' => $total_cash,
            'total_bank' => $total_bank,
            'reference' => $reference,
            'payment_method_type_id' => $payment_method_type_id
        ];
    }
    private function getMethodTypeCash()
    {
        $method_type_cash = DB::connection('tenant')->table('payment_method_types')
            ->get();
        $this->method_types = $method_type_cash;
        $this->method_type_cash_ids = DB::connection('tenant')->table('payment_method_types')
            ->where('is_bank', false)
            ->pluck('id')
            ->toArray();
        $this->method_type_bank_ids = DB::connection('tenant')->table('payment_method_types')
            ->where('is_bank', true)
            ->pluck('id')
            ->toArray();
    }

    public function getDataSpecialInfoCash($cash_id = 0)
    {
        try {
            $this->getMethodTypeCash();
            $cash = DB::connection('tenant')->table('cash')->where('id', $cash_id)->first();
            $establishment_id = $cash->establishment_id;
            $user_id = $cash->user_id;
            if ($establishment_id == null) {
                $user = DB::connection('tenant')->table('users')->where('id', $user_id)->first();
                $establishment_id = $user->establishment_id;
            }
            $warehouse_id = DB::connection('tenant')->table('warehouses')->where('establishment_id', $establishment_id)->first()->id;
            $not_in_state_types = ['11', '09'];

            // Validación temprana
            if (!$cash) {
                return [
                    'success' => false,
                    'message' => 'Caja no encontrada',
                ];
            }


            $date_start = $cash->date_opening;
            $date_end = $cash->date_closed;

            // Inicializar arrays
            $credits = [];
            $transfers = [];
            $document_credit_payment = [];
            $expenses = [];

            // Optimización 1: Eager loading para evitar N+1 queries
            $documents_credit = Document::with(['user', 'items'])
                ->where('cash_id', $cash_id)
                ->where('payment_condition_id', '02')
                ->whereNotIn('state_type_id', $not_in_state_types)
                ->get();

            // Optimización 2: Procesar directamente sin chunking innecesario
            foreach ($documents_credit as $document) {
                $credits[] = $this->getProducts($document, Document::class);
                $credits = array_map(function ($credit) {
                    $credit['date_of_payment'] = '-';
                    $credit['total_payment'] = 0;
                    return $credit;
                }, $credits);
            }

            $sale_notes_credit = SaleNote::with(['user', 'items'])
                ->where('cash_id', $cash_id)
                ->where('payment_condition_id', '02')
                ->whereNotIn('state_type_id', $not_in_state_types)
                ->get();
            foreach ($sale_notes_credit as $sale_note) {
                $credits[] = $this->getProducts($sale_note, SaleNote::class);
                $credits = array_map(function ($credit) {
                    $credit['date_of_payment'] = '-';
                    $credit['total_payment'] = 0;
                    return $credit;
                }, $credits);
            }

            // Optimización 3: Eager loading para transfers
            $transfers_data = InventoryTransfer::with(['user', 'inventory.item'])
                ->where('state', 2)
                ->where('warehouse_id', $warehouse_id)
                ->whereBetween('created_at', [$date_start, $date_end])
                ->get();

            foreach ($transfers_data as $transfer) {
                $transfers[] = $this->getProducts($transfer, InventoryTransfer::class);
            }

            // Optimización 4: Consulta optimizada para sale notes
            $sale_notes = SaleNote::with(['user', 'items'])
                ->where('cash_id', $cash_id)
                ->whereNotIn('state_type_id', $not_in_state_types)
                ->where('payment_condition_id', '01')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('documents')
                        ->whereColumn('sale_notes.id', 'documents.sale_note_id');
                })
                ->get();

            foreach ($sale_notes as $sale_note) {
                // $products_with_documents[] = $this->getProducts($sale_note, SaleNote::class,null,"cash");
                $document_cash[] = $this->getProducts($sale_note, SaleNote::class, null, "cash");
            }

            // Optimización 5: Consolidar consultas de pagos
            $global_payments = GlobalPayment::where('destination_type', Cash::class)
                ->where('destination_id', $cash_id);


            $purchase_payments = $global_payments->clone()
                ->where('payment_type', PurchasePayment::class)
                ->pluck('payment_id')
                ->toArray();

            $expense_payments = $global_payments->clone()
                ->where('payment_type', ExpensePayment::class)
                ->pluck('payment_id')
                ->toArray();

            $document_payments = $global_payments->clone()
                ->where('payment_type', DocumentPayment::class)
                ->pluck('payment_id')
                ->toArray();

            $sale_note_payments = $global_payments->clone()
                ->where('payment_type', SaleNotePayment::class)
                ->pluck('payment_id')
                ->toArray();

            $bill_of_exchange_payments = $global_payments->clone()
                ->where('payment_type', BillOfExchangePayment::class)
                ->pluck('payment_id')
                ->toArray();

            // Optimización 6: Consultas optimizadas para pagos de cuenta bancaria
            $document_payments_bank_account = DocumentPayment::with(['document', 'global_payment'])
                ->whereHas('global_payment', function ($query) {
                    $query->where('destination_type', BankAccount::class);
                })
                ->where(function ($q) use ($cash_id) {
                    $q->where('cash_id', $cash_id)
                        ->orWhereHas('document', function ($q) use ($cash_id) {
                            $q->where('cash_id', $cash_id);
                        });
                })
                ->pluck('id')
                ->toArray();

            $purchase_payments_bank_account = PurchasePayment::with(['purchase', 'global_payment'])
                ->whereHas('global_payment', function ($query) {
                    $query->where('destination_type', BankAccount::class);
                })
                ->where(function ($q) use ($cash_id) {
                    $q->where('cash_id', $cash_id)
                        ->orWhereHas('purchase', function ($q) use ($cash_id) {
                            $q->where('cash_id', $cash_id);
                        });
                })
                ->pluck('id')
                ->toArray();



            $sale_note_payments_bank_account = SaleNotePayment::with(['sale_note', 'global_payment'])
                ->whereHas('global_payment', function ($query) {
                    $query->where('destination_type', BankAccount::class);
                })
                ->where(function ($q) use ($cash_id) {
                    $q->where('cash_id', $cash_id)
                        ->orWhereHas('sale_note', function ($q) use ($cash_id) {
                            $q->where('cash_id', $cash_id);
                        });
                })
                ->pluck('id')
                ->toArray();

            $bill_of_exchange_payments_bank_account = BillOfExchangePayment::with(['bill_of_exchange', 'global_payment'])
                ->whereHas('global_payment', function ($query) {
                    $query->where('destination_type', BankAccount::class);
                })
                ->where(function ($q) use ($cash_id) {
                    $q->where('cash_id', $cash_id)
                        ->orWhereHas('bill_of_exchange', function ($q) use ($cash_id) {
                            $q->where('cash_id', $cash_id);
                        });
                })
                ->pluck('id')
                ->toArray();

            // Optimización 7: Consolidar arrays de pagos
            $all_document_payments = array_merge($document_payments, $document_payments_bank_account);
            $all_bill_of_exchange_payments = array_merge($bill_of_exchange_payments, $bill_of_exchange_payments_bank_account);
            $all_sale_note_payments = array_merge($sale_note_payments, $sale_note_payments_bank_account);
            $all_purchase_payments = array_merge($purchase_payments, $purchase_payments_bank_account);
            $total_cash = 0;
            $total_bank = 0;
            $document_cash = [];
            $document_bank_credit = [];
            $document_bank = [];

            // Optimización 8: Procesar pagos con eager loading y agrupar por documento
            if (!empty($all_document_payments)) {
                $document_payment_data = DocumentPayment::with(['document'])
                    ->whereIn('id', $all_document_payments)
                    ->get();

                // Agrupar por document_id para evitar duplicados
                $grouped_document_payments = $document_payment_data->groupBy('document_id');

                foreach ($grouped_document_payments as $document_id => $payments) {
                    $document = $payments->first()->document;

                    // Separar pagos por destino (Cash vs Bank)
                    $cash_payments = $payments->filter(function ($payment) {
                        return $payment->global_payment->destination_type == Cash::class;
                    });

                    $bank_payments = $payments->filter(function ($payment) {
                        return $payment->global_payment->destination_type != Cash::class;
                    });

                    // Procesar pagos a Cash si existen
                    if ($cash_payments->isNotEmpty()) {
                        if (
                            !in_array($document->state_type_id, $not_in_state_types) &&
                            ($document->cash_id != $cash_id || $document->payment_condition_id == "02")
                        ) {
                            $document_credit_payment[] = $this->getProducts($document, null, $cash_payments, "cash");
                        } else {
                            $document_cash[] = $this->getProducts($document, null, $cash_payments, 'cash');
                        }
                    }

                    // Procesar pagos a Bank si existen
                    if ($bank_payments->isNotEmpty()) {
                        if (
                            !in_array($document->state_type_id, $not_in_state_types) &&
                            ($document->cash_id != $cash_id || $document->payment_condition_id == "02")
                        ) {

                            $document_bank_credit[] = $this->getProducts($document, null, $bank_payments, "bank");
                        } else {
                            $document_bank[] = $this->getProducts($document, null, $bank_payments, 'bank');
                        }
                    }
                }
            }

            if (!empty($all_sale_note_payments)) {
                $sale_note_payment_data = SaleNotePayment::with(['sale_note'])
                    ->whereIn('id', $all_sale_note_payments)
                    ->get();

                // Agrupar por sale_note_id para evitar duplicados
                $grouped_sale_note_payments = $sale_note_payment_data->groupBy('sale_note_id');

                foreach ($grouped_sale_note_payments as $sale_note_id => $payments) {
                    $sale_note = $payments->first()->sale_note;


                    // Separar pagos por destino (Cash vs Bank)
                    $cash_payments = $payments->filter(function ($payment) {
                        return $payment->global_payment->destination_type == Cash::class;
                    });

                    $bank_payments = $payments->filter(function ($payment) {
                        return $payment->global_payment->destination_type != Cash::class;
                    });

                    // Procesar pagos a Cash si existen
                    if ($cash_payments->isNotEmpty()) {
                        if (!in_array($sale_note->state_type_id, $not_in_state_types) && ($sale_note->cash_id != $cash_id || $sale_note->payment_condition_id == "02")) {
                            $document_credit_payment[] = $this->getProducts($sale_note, null, $cash_payments, "cash");
                        } else {
                            $document_cash[] = $this->getProducts($sale_note, null, $cash_payments, "cash");
                        }
                    }

                    // Procesar pagos a Bank si existen
                    if ($bank_payments->isNotEmpty()) {
                        if (!in_array($sale_note->state_type_id, $not_in_state_types) && ($sale_note->cash_id != $cash_id || $sale_note->payment_condition_id == "02")) {
                            $document_bank_credit[] = $this->getProducts($sale_note, null, $bank_payments, "bank");
                        } else {

                            $document_bank[] = $this->getProducts($sale_note, null, $bank_payments, 'bank');
                        }
                    }
                }
            }

            if (!empty($all_bill_of_exchange_payments)) {
                $bill_of_exchange_payment_data = BillOfExchangePayment::with(['bill_of_exchange'])
                    ->whereIn('id', $all_bill_of_exchange_payments)
                    ->get();

                // Agrupar por bill_of_exchange_id para evitar duplicados
                $grouped_bill_of_exchange_payments = $bill_of_exchange_payment_data->groupBy('bill_of_exchange_id');

                foreach ($grouped_bill_of_exchange_payments as $bill_of_exchange_id => $payments) {
                    $bill_of_exchange = $payments->first()->bill_of_exchange;

                    if (!in_array($bill_of_exchange->state_type_id, $not_in_state_types)) {
                        // Separar pagos por destino (Cash vs Bank)
                        $cash_payments = $payments->filter(function ($payment) {
                            return $payment->global_payment->destination_type == Cash::class;
                        });

                        $bank_payments = $payments->filter(function ($payment) {
                            return $payment->global_payment->destination_type != Cash::class;
                        });

                        // Procesar pagos a Cash si existen
                        if ($cash_payments->isNotEmpty()) {
                            $document_credit_payment[] = $this->getProducts($bill_of_exchange, null, $cash_payments);
                        }

                        // Procesar pagos a Bank si existen (también van a document_credit_payment)
                        if ($bank_payments->isNotEmpty()) {
                            $document_credit_payment[] = $this->getProducts($bill_of_exchange, null, $bank_payments);
                        }
                    }
                }
            }

            if (!empty($all_purchase_payments)) {
                $purchase_payment_data = PurchasePayment::with(['purchase'])
                    ->whereIn('id', $all_purchase_payments)
                    ->get();

                $grouped_purchase_payments = $purchase_payment_data->groupBy('purchase_id');

                foreach ($grouped_purchase_payments as $purchase_id => $payments) {
                    $purchase = $payments->first()->purchase;


                    $cash_payments = $payments->filter(function ($payment) {
                        return $payment->global_payment->destination_type == Cash::class;
                    });

                    $bank_payments = $payments->filter(function ($payment) {
                        return $payment->global_payment->destination_type != Cash::class;
                    });

                    if ($cash_payments->isNotEmpty()) {
                        $expenses[] = $this->getProducts($purchase, Purchase::class, $cash_payments);
                    }

                    if ($bank_payments->isNotEmpty()) {
                        $expenses[] = $this->getProducts($purchase, Purchase::class, $bank_payments);
                    }
                }
            }


            if (!empty($expense_payments)) {
                $expense_payment_data = ExpensePayment::with(['expense'])
                    ->whereIn('id', $expense_payments)
                    ->get();

                // Agrupar por expense_id para evitar duplicados
                $grouped_expense_payments = $expense_payment_data->groupBy('expense_id');

                foreach ($grouped_expense_payments as $expense_id => $payments) {
                    $expense = $payments->first()->expense;

                    if (!in_array($expense->state_type_id, $not_in_state_types)) {
                        // Separar pagos por destino (Cash vs Bank)
                        $cash_payments = $payments->filter(function ($payment) {
                            return $payment->global_payment->destination_type == Cash::class;
                        });

                        $bank_payments = $payments->filter(function ($payment) {
                            return $payment->global_payment->destination_type != Cash::class;
                        });

                        // Procesar pagos a Cash si existen
                        if ($cash_payments->isNotEmpty()) {
                            $expenses[] = $this->getProducts($expense, Expense::class, $cash_payments);
                        }

                        // Procesar pagos a Bank si existen (también van a expenses)
                        if ($bank_payments->isNotEmpty()) {
                            $expenses[] = $this->getProducts($expense, Expense::class, $bank_payments);
                        }
                    }
                }
            }

            $company = Company::select('name', 'number', 'logo')->first();
            $user_name = null;
            $establishment_description = null;

            if ($establishment_id) {
                $establishment_description = Establishment::find($establishment_id)->description;
            }
            if ($user_id) {
                $user_name = User::find($user_id)->name;
            }

            $beginning_balance = $cash->beginning_balance;
            $data = [
                'beginning_balance' => $beginning_balance,
                'total_cash' => $total_cash,
                'total_bank' => $total_bank,
                'products_with_documents' => $document_cash,
                'products_with_documents_bank' => $document_bank,
                'transfers' => $transfers,
                'credits' => $credits,
                'document_credit_payment' => $document_credit_payment,
                'products_with_documents_bank_credit' => $document_bank_credit,
                'expenses' => $expenses,
                'company' => $company,
                'user_name' => $user_name,
                'establishment_description' => $establishment_description,
                'date_opening' => $cash->date_opening,
            ];

            $pdf = Pdf::loadView('pos::cash.report_pdf_a4_special_2', $data)
                ->setPaper('a4', 'landscape');
            $filename = "Reporte_de_caja_diario";

            return $pdf->stream($filename . '.pdf');
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'stack' => $e->getTrace(),
            ];
        }
    }
}
