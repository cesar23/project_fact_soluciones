<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\GlobalPayment;
use App\Models\Tenant\Cash;
use App\Models\Tenant\BankAccount;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Tenant\Company;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Http\Resources\GlobalPaymentCollection;
use Modules\Finance\Exports\BalanceExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant\Establishment;
use Carbon\Carbon;
use App\Models\Tenant\Person;
use Modules\Dashboard\Helpers\DashboardView;
use App\Exports\AccountsReceivable;
use App\Models\Tenant\Configuration;
use Modules\Finance\Exports\UnpaidPaymentMethodDayExport;
use App\Models\Tenant\User;
use App\Models\Tenant\PaymentMethodType;
use Modules\Finance\Http\Resources\UnpaidCollection;
use Modules\Finance\Traits\UnpaidTrait;
use Modules\Item\Models\WebPlatform;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Document;
use ErrorException;
use Exception;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use App\CoreFacturalo\Template;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use App\Http\Controllers\Tenant\DocumentPaymentController;
use App\Http\Controllers\Tenant\SaleNotePaymentController;
use App\Http\Requests\Tenant\DocumentPaymentRequest;
use App\Http\Requests\Tenant\SaleNotePaymentRequest;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\Multipayment;
use App\Models\Tenant\MultipaymentItem;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\Series;
use App\Models\Tenant\StateType;
use App\Models\Tenant\Zone;
use Illuminate\Support\Facades\DB;

class UnpaidController extends Controller
{

    use FinanceTrait, UnpaidTrait;
    use StorageDocument;

    protected $sale_note;
    protected $company;
    protected $document;
    protected $configuration;

    public function index(Request $request)
    {
        $customer_id = $request->customer_id ?? null;
        $configuration = Configuration::getPublicConfig();

        return view('finance::unpaid.index', compact('configuration', 'customer_id'));
    }


    public function filter()
    {

        $departments = Department::all();
        $provinces = Province::all();
        $customer_temp = Person::whereType('customers')->orderBy('name')
        ->limit(20)
        ->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });
        $customer = [];
        $customer[] = [
            'id' => null,
            'description' => 'Todos',
            'name' => 'Todos',
            'number' => '',
            'identity_document_type_id' => '',
        ];
        $customers = array_merge($customer, $customer_temp->toArray());
        $zones =  Zone::all();
        $establishments = DashboardView::getEstablishments();

        $users = [];

        if (auth()->user()->type == 'admin') {
            $users = User::whereIn('type', ['admin', 'seller'])->get();
        }

        $payment_method_types = PaymentMethodType::whereIn('id', ['05', '08', '09'])->get();
        $web_platforms = WebPlatform::all();

        return compact(
            'departments',
            'provinces',
            'customers',
            'establishments',
            'users',
            'payment_method_types',
            'web_platforms',
            'zones'
        );
    }

    public function multiplePayReceipt($multipayment_id)
    {
        $multipayment = Multipayment::find($multipayment_id);
        $establishment = Establishment::find($multipayment->user->establishment_id);
        $records = MultipaymentItem::where('multipayment_id', $multipayment_id)->get();
        $height = 250 + count($records) * 80;
        $company = Company::first();
        $pdf = PDF::loadView('finance::unpaid.reports.multiple_pay_receipt', compact("records", "company", "multipayment", "establishment"))
            ->setPaper(array(0, 0, 180, $height));
        $filename = 'Comprobante_Multipago_' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }
    public function multiplePay(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $payment = (object) $request->all();
            $selecteds = $payment->selecteds;
            $multipayment = new Multipayment;
            $multipayment->payment = $payment->payment;
            $multipayment->remaining = $payment->remaining;
            $multipayment->user_id = auth()->user()->id;
            $multipayment->date_of_issue = date('Y-m-d');
            $multipayment->save();
            foreach ($selecteds as $selected) {
                $type = $selected["type"];
                $total_payment = $selected["payment"];
                if ($type == 'document') {
                    $document_id = $selected["id"];
                    $document_payment = clone $payment;
                    $document_payment->document_id = $document_id;
                    $document_payment->payment = $total_payment;
                    $payments = DocumentPayment::where('document_id', $document_id)->sum('payment');
                    $document_total = Document::find($document_id)->total;
                    $total_remaining = $document_total - $payments;
                    $request_document_payment = new DocumentPaymentRequest();
                    $request_document_payment->merge((array)$document_payment);
                    $response = (new DocumentPaymentController())->store($request_document_payment);
                    $document_payment_id  = $response['id'];
                    $multipayment_item = new MultipaymentItem;
                    $multipayment_item->multipayment_id = $multipayment->id;
                    $multipayment_item->document_payment_id = $document_payment_id;
                    $multipayment_item->total = $total_payment;
                    $multipayment_item->remaining = $total_remaining;
                    $multipayment_item->save();
                }
                if ($type == 'sale_note') {
                    $sale_note_id = $selected["id"];
                    $sale_note_payment = clone $payment;
                    $sale_note_payment->sale_note_id = $sale_note_id;
                    $payments = SaleNotePayment::where('sale_note_id', $sale_note_id)->sum('payment');
                    $sale_note_total = SaleNote::find($sale_note_id)->total;
                    $total_remaining = $sale_note_total - $payments;
                    $sale_note_payment->payment = $total_payment;
                    $request_sale_note_payment = new SaleNotePaymentRequest();
                    $request_sale_note_payment->merge((array)$sale_note_payment);
                    $response =  (new SaleNotePaymentController())->store($request_sale_note_payment);
                    $sale_note_payment_id  = $response['id'];
                    $multipayment_item = new MultipaymentItem;
                    $multipayment_item->multipayment_id = $multipayment->id;
                    $multipayment_item->sale_note_payment_id = $sale_note_payment_id;
                    $multipayment_item->total = $total_payment;
                    $multipayment_item->remaining = $total_remaining;
                    $multipayment_item->save();
                }
            }
            DB::connection('tenant')->commit();
            $receipt_url = url("") . "/finances/unpaid/multiple-pay/receipt/" . $multipayment->id;
            return [
                'success' => true,
                "message" => "Pagos realizados con éxito",
                "receipt_url" => $receipt_url
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile()

            ];
        }
    }
    private function ajusteDocumentFees($customer_id){
        $tenat_connection = DB::connection('tenant');
        $documents = $tenat_connection->table('documents')
        ->leftJoin('document_payments', 'documents.id', '=', 'document_payments.document_id')
        ->where('date_of_issue', '>=', '2024-01-01')
        ->where('payment_condition_id', '02')
        ->where('customer_id', $customer_id)
        ->groupBy('documents.id', 'documents.date_of_issue', 'documents.total')
        ->havingRaw('IFNULL(SUM(document_payments.payment), 0) <= documents.total AND EXISTS (SELECT 1 FROM document_fee df WHERE df.document_id = documents.id AND ((df.original_amount IS NULL OR df.original_amount = 0) OR df.is_canceled = 0))')
        ->select('documents.id', 'documents.date_of_issue', 'documents.total')
        ->get();

        foreach ($documents as $document) {
            $db_document = Document::find($document->id);
            $db_document->ajustDocumentFee();
            // (new DocumentPaymentController())->updateDocumentFees($document->id);
        }
    }
    public function eeccCustomer(Request $request)
    {
        $date = $request->date_end ?? now()->format('Y-m-d');
        $this->ajusteDocumentFees($request->customer_id);
        $customer_id = $request->customer_id;
        $records = $this->transformRecordsByCustomer((new DashboardView())->getUnpaidByCustomer($customer_id)->get())
            ->toArray();

        $bank_accounts = BankAccount::all()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                'number' => $row->number,
                'cci' => $row->cci,
            ];
        });
        $customer = Person::whereType('customers')->where('id', $request->customer_id)->first();
    

        $company = Company::first();

        // Agregamos el script de página directamente en la vista
        $html = view('finance::unpaid.reports.eecc_customer', compact("records", "company", "date", "customer", "bank_accounts"))->render();

        // Agregamos el script para el número de página de manera más simple
        // $html .= '
        // <script type="text/php">
        //     if (isset($pdf)) {
        //         $pdf->page_text(30, $pdf->get_height() - 20, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8);
        //     }
        // </script>';
        // Creamos el PDF con márgenes ajustados
        $pdf = PDF::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isPhpEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'debugLayout' => false,
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'margin_bottom' => 15,
                'margin_top' => 15,
                'margin_right' => 15,
                'margin_left' => 15,
                'enable_php' => true
            ]);

        $filename = 'Reporte_Cuentas_Por_Cobrar_' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }
    public function table($table)
    {
        if ($table === 'customers') {
            $customers = Person::with('addresses')
                ->whereType('customers')
                ->whereIsEnabled()
                ->whereFilterCustomerBySeller('customers')
                ->orderBy('name')
                ->take(20)
                ->get()->transform(function ($row) {
                    /** @var Person $row */
                    return $row->getCollectionData();
                    /** Se ha movido la salida, al modelo */
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code,
                        'addresses' => $row->addresses,
                        'address' => $row->address,
                        'internal_code' => $row->internal_code,
                    ];
                });
            return $customers;
        }
        return [];
    }
    public function data_table()
    {

        $customers = $this->table('customers');
        $state_types = StateType::get();
        $document_types = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $series = Series::whereIn('document_type_id', ['01', '03', '80'])->get();
        $user = auth('api')->user() ?? auth()->user();
        $establishments = Establishment::query();
        if ($user->type != 'admin' && $user->type != 'superadmin') {
            $establishments->where('id', $user->establishment_id);
        }
        $establishments = $establishments->whereActive()->get();

        return compact(
            'customers',
            'document_types',
            'series',
            'establishments',
            'state_types'
        );
    }
    function document_payment($id, $payment)
    {

        $data =  DB::connection('tenant')->transaction(function () use ($id, $payment) {

            $record = DocumentPayment::firstOrNew(['id' => $id]);
            $record->fill($payment);
            $record->save();
            $this->createGlobalPayment($record, $payment);

            $this->saveFiles($record, $payment, 'documents');

            return $record;
        });

        $document_balance = (object)$this->document($payment->document_id);

        if ($document_balance->total_difference < 0.01) {

            $credit = CashDocumentCredit::where([
                ['status', 'PENDING'],
                ['document_id', $payment->document_id]
            ])->first();

            if ($credit) {

                $cash = Cash::where([
                    ['user_id', auth()->user()->id],
                    ['state', true],
                ])->first();

                $credit->status = 'PROCESSED';
                $credit->cash_id_processed = $cash->id;
                $credit->save();

                $req = [
                    'document_id' => $payment->document_id,
                    'sale_note_id' => null
                ];

                $cash->cash_documents()->updateOrCreate($req);
            }
        }
    }
    public function records(Request $request)
    {
        $config = Configuration::first();
        $from_unpaid_modal = $request->from_unpaid_modal;
        if($config->bill_of_exchange_special){
            $records = (new DashboardView())->getUnpaidFilterUserEspecial($request->all());
        }else{
            $records = (new DashboardView())->getUnpaidFilterUser($request->all());
        }
        $config = Configuration::first();
        if ($from_unpaid_modal) {
            return new UnpaidCollection($records
                ->orderBy('date_of_issue', 'asc')
                ->get());
        }
        return (new UnpaidCollection($records
            ->orderBy('date_of_issue', 'asc')
            ->paginate(config('tenant.items_per_page'))))->additional([
            'configuration' => $config->finances
        ]);
    }

    public function unpaidall()
    {
        return Excel::download(new AccountsReceivable, 'Allclients.xlsx');
    }


    public function reportPaymentMethodDays(Request $request)
    {

        $all_records = $this->transformRecords((new DashboardView())->getUnpaidFilterUser($request->all())->get());

        $records = collect($all_records)->where('total_to_pay', '>', 0)->where('type', 'document')->map(function ($row) {
            $row['difference_days'] = Carbon::parse($row['date_of_issue'])->diffInDays($row['date_of_due']);
            return $row;
        });

        $company = Company::first();

        $unpaidPaymentMethodDayExport = new UnpaidPaymentMethodDayExport();
        $unpaidPaymentMethodDayExport
            ->company($company)
            ->records($records);
        return $unpaidPaymentMethodDayExport->download('Reporte_C_Cobrar_F_Pago' . Carbon::now() . '.xlsx');
    }
    public function pdf_s_especial(Request $request)
    {
        $records = $this->transformRecords_especial((new DashboardView())->getUnpaidFilterUser($request->all())->get());


        $company = Company::first();

        $pdf = PDF::loadView('finance::unpaid.reports.report_pdf_s_especial', compact("records", "company"))
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


        $filename = 'Reporte_Cuentas_Por_Cobrar_' . date('YmdHis');

        // return $pdf->download($filename . '.pdf');
        return $pdf->stream($filename . '.pdf');
    }
    public function pdf_s(Request $request)
    {
        $records = $this->transformRecords((new DashboardView())->getUnpaidFilterUser($request->all())->get());

        $company = Company::first();

        $pdf = PDF::loadView('finance::unpaid.reports.report_pdf_s', compact("records", "company"))
            ->setPaper('a4', 'landscape');;

        $filename = 'Reporte_Cuentas_Por_Cobrar_' . date('YmdHis');

        // return $pdf->download($filename . '.pdf');
        return $pdf->stream($filename . '.pdf');
    }

    public function pdf_h(Request $request)
    {
        $records = $this->transformRecords((new DashboardView())->getUnpaidFilterUser($request->all())->get());

        $company = Company::first();

        $pdf = PDF::loadView('finance::unpaid.reports.report_pdf_h', compact("records", "company"))
            ->setPaper('a4', 'landscape');;

        $filename = 'Reporte_Cuentas_Por_Cobrar_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }
    public function pdf(Request $request)
    {

        $records = $this->transformRecords((new DashboardView())->getUnpaidFilterUser($request->all())->get()

        );

        $company = Company::first();

        $pdf = PDF::loadView('finance::unpaid.reports.report_pdf', compact("records", "company"));

        $filename = 'Reporte_Cuentas_Por_Cobrar_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function toPrint($external_id, $type, $format)
    {
        if ($type == 'sale') {
            $sale_note = SaleNote::where('external_id', $external_id)->first();
        } else {
            $sale_note = Document::where('external_id', $external_id)->first();
        }


        if (!$sale_note) throw new Exception("El código {$external_id} es inválido, no se encontro la nota de venta relacionada");
        $this->reloadPDF($sale_note, $format, $sale_note->filename);
        $temp = tempnam(sys_get_temp_dir(), 'unpaid');


        file_put_contents($temp, $this->getStorage($sale_note->filename, 'unpaid'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$sale_note->filename.'"'
        ];
        */

        return response()->file($temp, GeneralPdfHelper::pdfResponseFileHeaders($sale_note->filename));
    }

    private function reloadPDF($sale_note, $format, $filename)
    {
        $this->createPdf($sale_note, $format, $filename);
    }

    public function createPdf($sale_note = null, $format_pdf = null, $filename = null)
    {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $this->company = ($this->company != null) ? $this->company : Company::active();
        $this->document = ($sale_note != null) ? $sale_note : $this->sale_note;

        $this->configuration = Configuration::first();
        // $configuration = $this->configuration->formats;
        $base_template = Establishment::find($this->document->establishment_id)->template_pdf;

        $html = $template->pdf($base_template, "unpaid", $this->company, $this->document, $format_pdf);

        /* cuentas por cobrar formato a4 */
        if (($format_pdf === 'ticket') or ($format_pdf === 'ticket_58') or ($format_pdf == 'ticket_50')) {

            $width = ($format_pdf === 'ticket_58') ? 56 : 78;
            if (config('tenant.enabled_template_ticket_80')) $width = 76;
            if ($format_pdf === 'ticket_50') $width = 45;

            $company_logo      = ($this->company->logo) ? 40 : 0;
            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);
            $payments     = $this->document->payments()->count() * 2;
            $discount_global = 0;
            $extra_by_item_description = 0;
            foreach ($this->document->items as $it) {
                if (strlen($it->item->description) > 100) {
                    $extra_by_item_description += 24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $this->document->legends != '' ? '10' : '0';
            $bank_accounts = BankAccount::count() * 6;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    120 +
                        ($quantity_rows * 8) +
                        ($discount_global * 3) +
                        $company_logo +
                        $payments +
                        $company_name +
                        $company_address +
                        $company_number +
                        $customer_name +
                        $customer_address +
                        $p_order +
                        $legends +
                        $bank_accounts +
                        $total_exportation +
                        $total_free +
                        $total_unaffected +
                        $total_exonerated +
                        $extra_by_item_description +
                        $total_taxed
                ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else {
            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $pdf = new Mpdf([
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                            DIRECTORY_SEPARATOR . 'pdf' .
                            DIRECTORY_SEPARATOR . $base_template .
                            DIRECTORY_SEPARATOR . 'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold . '.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular . '.ttf',
                        ],
                    ]
                ]);
            }
        }
        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);


        $this->uploadFile($this->document->filename, $pdf->output('', 'S'), 'unpaid');
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }
}
