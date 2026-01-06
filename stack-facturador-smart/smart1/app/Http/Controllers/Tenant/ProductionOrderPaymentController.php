<?php
namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Template;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SaleNotePaymentRequest;
use App\Http\Resources\Tenant\ProductionOrderPaymentCollection;
use App\Models\Tenant\Company;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Cash;
use App\Models\Tenant\ProductionOrder;
use App\Models\Tenant\ProductionOrderPayment;
use App\Models\Tenant\User;

class ProductionOrderPaymentController extends Controller
{
    use StorageDocument, FinanceTrait, FilePaymentTrait;

    public function records($production_order_id)
    {
        $records = ProductionOrderPayment::where('production_order_id', $production_order_id)->get();

        return new ProductionOrderPaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }

    public function document($production_order_id)
    {
        /** @var ProductionOrder $production_order */
        $production_order = ProductionOrder::find($production_order_id);

        $total_paid = round(collect($production_order->payments)->sum('payment'), 2);
        $total = $production_order->total;
        $total_difference = round($total - $total_paid, 2);

        // if($total_difference < 1)
        // {
        //     $production_order->total_canceled = true;
        //     $production_order->save();
        // }

        return [
            'identifier' => $production_order->identifier,
            'full_number' => $production_order->getNumberFullAttribute(),
            'number_full' => $production_order->getNumberFullAttribute(),
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
            'paid' => $production_order->total_canceled,
            'external_id' => $production_order->external_id,
        ];
    }

    public function store(SaleNotePaymentRequest $request)
    {
        $id = $request->input('id');

         DB::connection('tenant')->transaction(function () use ($id, $request) {

            $record = SaleNotePayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->saveFiles($record, $request, 'production_orders');

        });

        if($request->paid == true)
        {
            $production_order = SaleNote::find($request->production_order_id);
            $production_order->total_canceled = true;
            $production_order->save();

            $credit = CashDocumentCredit::where([
                ['status', 'PENDING'],
                ['production_order_id',  $production_order->id]
            ])->first();

            if($credit) {

                $cash = Cash::where([
                    ['user_id', User::getUserCashId()],
                    ['state', true],
                ])->first();

                $credit->status = 'PROCESSED';
                $credit->cash_id_processed = $cash->id;
                $credit->save();

                $req = [
                    'document_id' => null,
                    'production_order_id' => $production_order->id
                ];

                $cash->cash_documents()->updateOrCreate($req);

            }

        }

        $this->createPdf($request->input('production_order_id'));

        return [
            'success' => true,
            'message' => ($id)?'Pago editado con éxito':'Pago registrado con éxito'
        ];
    }

    public function destroy($id)
    {
        $item = SaleNotePayment::findOrFail($id);
        $production_order_id = $item->production_order_id;
        $item->delete();

        $production_order = SaleNote::find($item->production_order_id);
        $production_order->total_canceled = false;
        $production_order->save();

        $this->createPdf($production_order_id);

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }

    public function createPdf($production_order_id, $format = null)
    {
        $production_order = SaleNote::find($production_order_id);
        $total_paid = round(collect($production_order->payments)->sum('payment'), 2);
        $total = $production_order->total;
        $total_difference = round($total - $total_paid, 2);

        if($total_difference == 0) {
            Log::info('true '.$total_difference);
            $production_order->total_canceled = true;
        } else {
            Log::info('false '.$total_difference);
            $production_order->total_canceled = false;
        }
        $production_order->save();


        $company = Company::first();

        $template = new Template();
        $pdf = null;
        if($format == 'a5')
        {
              $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    78,
                    220
                    ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);

        }
        else{
           $pdf = new Mpdf();
        }

        $document = SaleNote::find($production_order_id);

        $base_template = config('tenant.pdf_template');

        $html = $template->pdf($base_template, "production_order", $company, $document,"a4");

        $pdf_font_regular = config('tenant.pdf_name_regular');
        $pdf_font_bold = config('tenant.pdf_name_bold');

        if ($pdf_font_regular != false) {
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $pdf = new Mpdf([
                'fontDir' => array_merge($fontDirs, [
                    app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                        DIRECTORY_SEPARATOR.'pdf'.
                        DIRECTORY_SEPARATOR.$base_template.
                        DIRECTORY_SEPARATOR.'font')
                ]),
                'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold.'.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular.'.ttf',
                        ],
                    ]
            ]);
        }

        $path_css = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
            DIRECTORY_SEPARATOR.'pdf'.
            DIRECTORY_SEPARATOR.$base_template.
            DIRECTORY_SEPARATOR.'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        if(config('tenant.pdf_template_footer')) {
            $html_footer = $template->pdfFooter($base_template,$document);
            $pdf->SetHTMLFooter($html_footer);
        }

        $this->uploadStorage($document->filename, $pdf->output('', 'S'), 'production_order');
        return $document->filename;
//        $this->uploadFile($pdf->output('', 'S'), 'production_order');
    }

    public function toPrint($production_order_id, $format)
    {
        $filename = $this->createPdf($production_order_id, $format);
        $temp = tempnam(sys_get_temp_dir(), 'production_order');
        file_put_contents($temp, $this->getStorage($filename, 'production_order'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ];
        */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($filename));
    }


}
