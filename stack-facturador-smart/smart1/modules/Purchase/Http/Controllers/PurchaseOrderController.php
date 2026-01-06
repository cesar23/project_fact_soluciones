<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\SearchItemController;
use App\Http\Controllers\Tenant\EmailController;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Configuration;
use App\Traits\OfflineTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Person;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Company;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Str;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Template;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Exception;
use Illuminate\Support\Facades\Mail;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Purchase\Http\Resources\PurchaseOrderCollection;
use Modules\Purchase\Http\Resources\PurchaseOrderResource;
use Modules\Purchase\Mail\PurchaseOrderEmail;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Series;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\PurchaseOrderRequest;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\Exports\PurchaseOrderExport;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\User;
use Modules\Sale\Models\SaleOpportunity;
use Modules\Finance\Helpers\UploadFileHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{

    use StorageDocument;
    use OfflineTrait;

    protected $purchase_order;
    protected $company;

    public function index()
    {
        return view('purchase::purchase-orders.index');
    }

    public function reportIndex()
    {
        return view('tenant.reports.purchase_orders.index');
    }
    public function reportColumns()
    {
    }

    


    public function getDatesOfPeriod($request)
    {

        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];

        $d_start = null;
        $d_end = null;
        /** @todo: Eliminar periodo, fechas y cambiar por

            $date_start = $request['date_start'];
            $date_end = $request['date_end'];
            \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        return [
            'd_start' => $d_start,
            'd_end' => $d_end
        ];
    }
    public function getReportRecords($request)
    {
        $data_of_period = $this->getDatesOfPeriod($request);
        $params = (object)[
            'date_start' => $data_of_period['d_start'],
            'date_end' => $data_of_period['d_end'],
        ];
        $records = PurchaseOrder::query();
        if ($params->date_start && $params->date_end) {
            $records = $records->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
        }
        if ($request->establishment_id) {
            $records = $records->where('establishment_id', $request->establishment_id);
        }
        if($request->client_internal_id){
            $records = $records->where('client_internal_id', 'like', "%{$request->client_internal_id}%");
        }
        if ($request->person_id) {
            $records = $records->whereHas('quotation', function ($query) use ($request) {
                $query->where('customer_id', $request->person_id);
            });
        }
        if ($request->number) {
            $records = $records->whereHas('quotation', function ($query) use ($request) {
                $query->where('number', 'like', "%{$request->number}%");
            });
        }

        $records = $records->latest();


        return $records;
    }
    public function reportRecords(Request $request)
    {
        $records = $this->getReportRecords($request);


        return new PurchaseOrderCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function reportExcel(Request $request)
    {
        $records = $this->getReportRecords($request)->get()->transform(
            function ($row) {
                $customer_name = "";
                $quotation_number = "";
                if ($row->quotation) {
                    $customer_name = $row->quotation->customer->name;
                    $quotation_number = $row->quotation->number_full;
                }
                return [
                    'customer_name' => $customer_name,
                    'quotation_number' => $quotation_number,
                    'id' => $row->id,
                    'prefix' => $row->prefix,
                    'created_by_id' => $row->created_by_id,
                    'approved_by_id' => $row->approved_by_id,
                    'client_internal_id' => $row->client_internal_id,
                    'quotation_id' => $row->quotation_id,
                    'type' => $row->type,
                    'observation' => $row->observation,
                    // 'purchases' => $row->purchases,
                    'has_purchases' => ($row->purchases->count()) ? true : false,
                    'soap_type_id' => $row->soap_type_id,
                    'external_id' => $row->external_id,
                    'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                    'date_of_due' => ($row->date_of_due) ? $row->date_of_due->format('Y-m-d') : '-',
                    // Para compatibilidad: mostrar series/number solo si existen, sino null
                    'series' => $row->series ?: null,
                    'number' => $row->number ?: null,
                    'number_full' => $row->number_full,
                    // Para registros antiguos, generar el formato compatible
                    'legacy_number' => $row->series ? null : ($row->prefix . '-' . str_pad($row->id, 8, '0', STR_PAD_LEFT)),
                    'supplier_name' => $row->supplier->name,
                    'supplier_number' => $row->supplier->number,
                    'currency_type_id' => $row->currency_type_id,
                    'total_exportation' => $row->total_exportation,
                    'total_free' => number_format($row->total_free, 2),
                    'total_unaffected' => number_format($row->total_unaffected, 2),
                    'total_exonerated' => number_format($row->total_exonerated, 2),
                    'total_taxed' => number_format($row->total_taxed, 2),
                    'total_igv' => number_format($row->total_igv, 2),
                    'total' => number_format($row->total, 2),
                    'state_type_id' => $row->state_type_id,
                    'state_type_description' => $row->state_type->description,
                    // 'payment_method_type_description' => isset($row->purchase_payments['payment_method_type']['description'])?$row->purchase_payments['payment_method_type']['description']:'-',
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                    'sale_opportunity_number_full' => ($row->sale_opportunity) ? $row->sale_opportunity->number_full : $row->sale_opportunity_number,
                    'show_actions_row' => $row->getShowActionsRow(),

                ];
            }
        );
        $company = Company::active();
        $establishment_id = $request->establishment_id ?? auth()->user()->establishment_id;
        $establishment = Establishment::where('id', $establishment_id)->first();
        return (new PurchaseOrderExport)
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->download('Reporte_orden_compra_' . Carbon::now() . '.xlsx');
    }
    public function reportPdf(Request $request)
    {
        $records = $this->getReportRecords($request)->get()->transform(
            function ($row) {
                $customer_name = "";
                $quotation_number = "";
                if ($row->quotation) {
                    $customer_name = $row->quotation->customer->name;
                    $quotation_number = $row->quotation->number_full;
                }
                return [
                    'customer_name' => $customer_name,
                    'quotation_number' => $quotation_number,
                    'id' => $row->id,
                    'prefix' => $row->prefix,
                    'created_by_id' => $row->created_by_id,
                    'approved_by_id' => $row->approved_by_id,
                    'client_internal_id' => $row->client_internal_id,
                    'quotation_id' => $row->quotation_id,
                    'type' => $row->type,
                    'observation' => $row->observation,
                    // 'purchases' => $row->purchases,
                    'has_purchases' => ($row->purchases->count()) ? true : false,
                    'soap_type_id' => $row->soap_type_id,
                    'external_id' => $row->external_id,
                    'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                    'date_of_due' => ($row->date_of_due) ? $row->date_of_due->format('Y-m-d') : '-',
                    // Para compatibilidad: mostrar series/number solo si existen, sino null
                    'series' => $row->series ?: null,
                    'number' => $row->number ?: null,
                    'number_full' => $row->number_full,
                    // Para registros antiguos, generar el formato compatible
                    'legacy_number' => $row->series ? null : ($row->prefix . '-' . str_pad($row->id, 8, '0', STR_PAD_LEFT)),
                    'supplier_name' => $row->supplier->name,
                    'supplier_number' => $row->supplier->number,
                    'currency_type_id' => $row->currency_type_id,
                    'total_exportation' => $row->total_exportation,
                    'total_free' => number_format($row->total_free, 2),
                    'total_unaffected' => number_format($row->total_unaffected, 2),
                    'total_exonerated' => number_format($row->total_exonerated, 2),
                    'total_taxed' => number_format($row->total_taxed, 2),
                    'total_igv' => number_format($row->total_igv, 2),
                    'total' => number_format($row->total, 2),
                    'state_type_id' => $row->state_type_id,
                    'state_type_description' => $row->state_type->description,
                    // 'payment_method_type_description' => isset($row->purchase_payments['payment_method_type']['description'])?$row->purchase_payments['payment_method_type']['description']:'-',
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                    'sale_opportunity_number_full' => ($row->sale_opportunity) ? $row->sale_opportunity->number_full : $row->sale_opportunity_number,
                    'show_actions_row' => $row->getShowActionsRow(),

                ];
            }
        );
        $company = Company::active();
        $establishment_id = $request->establishment_id ?? auth()->user()->establishment_id;
        $establishment = Establishment::where('id', $establishment_id)->first();
        $pdf = PDF::loadView('tenant.reports.purchase_orders.report_pdf', compact('records', 'company', 'establishment'))
            ->setPaper('a4', 'landscape');
        $filename = 'Reporte_orden_compra' . date('YmdHis') . '.pdf';
        return $pdf->stream($filename);
    }
    public function create($id = null)
    {
        $sale_opportunity = null;
        return view('purchase::purchase-orders.form', compact('id', 'sale_opportunity'));
    }

    public function generateQuotation($id)
    {
        $purchase_quotation = Quotation::with(['items'])->findOrFail($id);

        if (!$purchase_quotation->date_of_issue instanceof Carbon) {
            $purchase_quotation->date_of_issue = Carbon::parse($purchase_quotation->date_of_issue);
        }

        $purchase_quotation->date_of_issue = $purchase_quotation->date_of_issue->format('Y-m-d');
        $isQuotation = true;
        return view('purchase::purchase-orders.generate', compact('purchase_quotation', 'isQuotation'));
    }

    public function generate($id)
    {
        $purchase_quotation = PurchaseQuotation::with(['items'])->findOrFail($id);
        $isQuotation = false;
        return view('purchase::purchase-orders.generate', compact('purchase_quotation', 'isQuotation'));
    }

    public function generateFromSaleOpportunity($id)
    {
        $sale_opportunity = SaleOpportunity::with(['items'])->findOrFail($id);
        $id = null;

        return view('purchase::purchase-orders.form', compact('id', 'sale_opportunity'));
    }

    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión',
            'type' => 'Tipo',
        ];
    }

    public function records(Request $request)
    {
        $records = PurchaseOrder::where($request->column, 'like', "%{$request->value}%")
            ->whereTypeUser()
            ->latest();

        return new PurchaseOrderCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function tables()
    {

        $suppliers = $this->table('suppliers');
        $establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
            ];
        });
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $currency_types = CurrencyType::whereActive()->get();
        $company = Company::active();
        $payment_method_types = PaymentMethodType::all();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $users = User::where('type', '<>', 'superadmin')
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                ];
            });

        return compact(
            'affectation_igv_types',
            'suppliers',
            'users',
            'establishment',
            'establishments',
            'company',
            'currency_types',
            'payment_method_types'
        );
    }


    public function item_tables()
    {

        // $items = $this->table('items');
        $items =  SearchItemController::getItemToPurchaseOrder();

        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $warehouses = Warehouse::where('active', true)->get();

        $operation_types = OperationType::whereActive()->get();
        $is_client = $this->getIsClient();

        return compact(
            'items',
            'categories',
            'affectation_igv_types',
            'system_isc_types',
            'price_types',
            'discount_types',
            'charge_types',
            'attribute_types',
            'warehouses',
            'attribute_types',
            'operation_types',
            'is_client'
        );
    }


    public function record($id)
    {
        $record = new PurchaseOrderResource(PurchaseOrder::findOrFail($id));

        return $record;
    }


    public function getFullDescription($row)
    {

        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }


    public function store(PurchaseOrderRequest $request)
    {

        DB::connection('tenant')->transaction(function () use ($request) {

            $data = $this->mergeData($request);

            $id = $request->input('id');

            if (!$id && $data['series']) {
                $lastPurchaseOrder = PurchaseOrder::where('series', $data['series'])
                    ->orderByRaw('CAST(number AS UNSIGNED) DESC')
                    ->first();
                
                $data['number'] = $lastPurchaseOrder ? $lastPurchaseOrder->number + 1 : 1;
            }else{
            unset($data['number']);
            }

            $this->purchase_order =  PurchaseOrder::updateOrCreate(['id' => $id], $data);

            $this->purchase_order->items()->delete();

            foreach ($data['items'] as $row) {
                $this->purchase_order->items()->create($row);
            }

            $temp_path = $request->input('attached_temp_path');

            if ($temp_path) {

                $datenow = date('YmdHis');
                $file_name_old = $request->input('attached');
                $file_name_old_array = explode('.', $file_name_old);
                $file_name = Str::slug($this->purchase_order->id) . '-' . $datenow . '.' . $file_name_old_array[1];
                $file_content = file_get_contents($temp_path);

                // validaciones archivos
                $allowed_file_types_images = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg'];
                $is_image = UploadFileHelper::getIsImage($temp_path, $allowed_file_types_images);

                $allowed_file_types = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg', 'application/pdf'];
                UploadFileHelper::checkIfValidFile($file_name, $temp_path, $is_image, 'jpg,jpeg,png,gif,svg,pdf', $allowed_file_types);
                // validaciones archivos

                Storage::disk('tenant')->put('purchase_order_attached' . DIRECTORY_SEPARATOR . $file_name, $file_content);
                $this->purchase_order->upload_filename = $file_name;
                $this->purchase_order->save();
            }

            $this->setFilename();
            $this->createPdf($this->purchase_order, "a4", $this->purchase_order->filename);
            //$this->email($this->purchase_order);
        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->purchase_order->id,
                'number_full' => $this->purchase_order->number_full,
            ],
        ];
    }


    public function mergeData($inputs)
    {

        $this->company = Company::active();

        $values = [
            'user_id' => auth()->id(),
            'supplier' => PersonInput::set($inputs['supplier_id']),
            'external_id' => Str::uuid()->toString(),
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01'
        ];

        $inputs->merge($values);

        return $inputs->all();
    }



    private function setFilename()
    {

        $name = [$this->purchase_order->prefix, $this->purchase_order->id, date('Ymd')];
        $this->purchase_order->filename = join('-', $name);
        $this->purchase_order->save();
    }


    public function table($table)
    {
        switch ($table) {
            case 'suppliers':

                $suppliers = Person::whereType('suppliers')->orderBy('name')->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'email' => $row->email,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code
                    ];
                });
                return $suppliers;

                break;

            case 'items':

                $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

                $items = Item::orderBy('description')->whereNotIsSet()
                    ->get()->transform(function ($row) {
                        $full_description = $this->getFullDescription($row);
                        return [
                            'id' => $row->id,
                            'full_description' => $full_description,
                            'description' => $row->description,
                            'model' => $row->model,
                            'currency_type_id' => $row->currency_type_id,
                            'currency_type_symbol' => $row->currency_type->symbol,
                            'sale_unit_price' => $row->sale_unit_price,
                            'purchase_unit_price' => $row->purchase_unit_price,
                            'unit_type_id' => $row->unit_type_id,
                            'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                            'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                            'has_perception' => (bool) $row->has_perception,
                            'purchase_has_igv' => (bool) $row->purchase_has_igv,
                            'percentage_perception' => $row->percentage_perception,
                            'item_unit_types' => collect($row->item_unit_types)->transform(function ($row) {
                                return [
                                    'id' => $row->id,
                                    'description' => "{$row->description}",
                                    'item_id' => $row->item_id,
                                    'unit_type_id' => $row->unit_type_id,
                                    'quantity_unit' => $row->quantity_unit,
                                    'price1' => $row->price1,
                                    'price2' => $row->price2,
                                    'price3' => $row->price3,
                                    'price_default' => $row->price_default,
                                    'warehouse_id' => $row->warehouse_id,
                                ];
                            }),
                            'series_enabled' => (bool) $row->series_enabled,
                        ];
                    });
                return $items;

                break;
            default:
                return [];

                break;
        }
    }


    public function download($external_id, $format = "a4")
    {

        $purchase_order = PurchaseOrder::where('external_id', $external_id)->first();

        if (!$purchase_order) throw new Exception("El código {$external_id} es inválido, no se encontro la cotización de compra relacionada");

        $this->reloadPDF($purchase_order, $format, $purchase_order->filename);

        return $this->downloadStorage($purchase_order->filename, 'purchase_order');
    }

    public function downloadAttached($external_id)
    {

        $purchase_order = PurchaseOrder::where('external_id', $external_id)->first();

        if (!$purchase_order) throw new Exception("El código {$external_id} es inválido, no se encontro la orden de compra relacionada");

        return Storage::disk('tenant')->download('purchase_order_attached' . DIRECTORY_SEPARATOR . $purchase_order->upload_filename);
    }

    public function toPrint($external_id, $format)
    {

        $purchase_order = PurchaseOrder::where('external_id', $external_id)->first();

        if (!$purchase_order) throw new Exception("El código {$external_id} es inválido, no se encontro la cotización de compra relacionada");

        $this->reloadPDF($purchase_order, $format, $purchase_order->filename);
        $temp = tempnam(sys_get_temp_dir(), 'purchase_order');

        file_put_contents($temp, $this->getStorage($purchase_order->filename, 'purchase_order'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$purchase_order->filename.'"'
        ];
        */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($purchase_order->filename));
    }


    private function reloadPDF($purchase_order, $format, $filename)
    {
        $this->createPdf($purchase_order, $format, $filename);
    }


    public function createPdf($purchase_order = null, $format_pdf = null, $filename = null)
    {
        ini_set("pcre.backtrack_limit", "5000000");

        $template = new Template();
        
        $document = ($purchase_order != null) ? $purchase_order : $this->purchase_order;
        $company = ($this->company != null) ? $this->company : Company::active();
        $filename = ($filename != null) ? $filename : $this->purchase_order->filename;

        $base_template = Establishment::find($document->establishment_id)->template_pdf;
        
        // Configuración base del PDF según el formato
        $mpdf_config = [];
        
        if ($format_pdf == 'ticket') {
            // Configuración específica para ticket
            $fixed_height = 150;
            $items_height = 10;
            $total_height = $fixed_height + ($items_height * count($document->items));
            $mpdf_config = [
                'mode' => 'utf-8',
                'format' => [80, $total_height], // 80mm de ancho, altura automática
                'margin_top' => 2,
                'margin_right' => 2,
                'margin_bottom' => 2,
                'margin_left' => 2,
                'orientation' => 'P'
            ];
        } else if ($base_template == 'purchase_order') {
            // Configuración para purchase_order normal
            $mpdf_config = [
                'margin_top' => 5,
                'margin_right' => 5,
                'margin_bottom' => 5,
                'margin_left' => 5
            ];
        }
        
        $html = $template->pdf($base_template, "purchase_order", $company, $document, $format_pdf);
        $pdf_font_regular = config('tenant.pdf_name_regular');
        $pdf_font_bold = config('tenant.pdf_name_bold');

        if ($pdf_font_regular != false) {
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            // Agregar configuración de fuentes manteniendo la configuración base
            $mpdf_config['fontDir'] = array_merge($fontDirs, [
                app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                    DIRECTORY_SEPARATOR . 'pdf' .
                    DIRECTORY_SEPARATOR . $base_template .
                    DIRECTORY_SEPARATOR . 'font')
            ]);
            $mpdf_config['fontdata'] = $fontData + [
                'custom_bold' => [
                    'R' => $pdf_font_bold . '.ttf',
                ],
                'custom_regular' => [
                    'R' => $pdf_font_regular . '.ttf',
                ],
            ];
        }
        
        // Crear PDF con toda la configuración consolidada
        $pdf = new Mpdf($mpdf_config);

        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');
        $stylesheet = file_get_contents($path_css);
        
        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
        
        if ($format_pdf != 'ticket') {
            if (config('tenant.pdf_template_footer')) {
                $html_footer = $template->pdfFooter($base_template, $document);
                $pdf->SetHTMLFooter($html_footer);
            }
        }

        $this->uploadFile($filename, $pdf->output('', 'S'), 'purchase_order');
    }


    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }


    public function email(Request $request)
    {
        $record = PurchaseOrder::find($request->input('id'));
        $customer_email = $request->input('customer_email');

        $email = $customer_email;
        $mailable = new  PurchaseOrderEmail($record);
        $id = (int)$record->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 5);
        /*
        Configuration::setConfigSmtpMail();
        $array_email = explode(',', $customer_email);
        if (count($array_email) > 1) {
            foreach ($array_email as $email_to) {
                $email_to = trim($email_to);
                if(!empty($email_to)) {
                    Mail::to($email_to)->send(new  PurchaseOrderEmail($record));
                }
            }
        } else {
            Mail::to($customer_email)->send(new  PurchaseOrderEmail($record));
        }
        */
        return [
            'success' => true
        ];
    }

    public function uploadAttached(Request $request)
    {

        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg,pdf', false);

        if (!$validate_upload['success']) {
            return $validate_upload;
        }

        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];

            return $this->upload_attached($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    function upload_attached($request)
    {
        $file = $request['file'];
        $type = $request['type'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }

    public function anular($id)
    {
        $obj =  PurchaseOrder::find($id);
        $obj->state_type_id = 11;
        $obj->save();
        return [
            'success' => true,
            'message' => 'Orden de compra anulada con éxito'
        ];
    }

    public function searchItemByIds(Request $request)
    {
        $ids = $request->input('ids');
        $items = Item::whereIn('id', $ids)->get();
        $items = SearchItemController::TransformModalToPurchaseOrder($items);
        return $items;
    }
    /**
     * @param $id
     *
     * @return array
     */
    public function searchItemById($id)
    {
        $items = SearchItemController::getItemToPurchaseOrder(null, $id);

        return compact('items');
    }

    public function getSeriesByType(Request $request)
    {
        $type = $request->input('type', 'goods');
        $establishment_id = $request->input('establishment_id', auth()->user()->establishment_id);
        
        $document_type_id = $type === 'goods' ? 'OCB' : 'OCS';
        
        $series = Series::where('establishment_id', $establishment_id)
            ->where('document_type_id', $document_type_id)
            ->get()
            ->transform(function ($serie) {
                return [
                    'id' => $serie->id,
                    'number' => $serie->number,
                    'description' => $serie->number,
                ];
            });

        return response()->json($series);
    }

    public function getNextNumber(Request $request)
    {
        $series = $request->input('series');
        
        if (!$series) {
            return response()->json(['number' => 1]);
        }

        $lastPurchaseOrder = PurchaseOrder::where('series', $series)
            ->orderByRaw('CAST(number AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $lastPurchaseOrder ? $lastPurchaseOrder->number + 1 : 1;

        return response()->json(['number' => $nextNumber]);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function searchItems(Request $request)
    {
        $items = SearchItemController::getItemToPurchaseOrder($request);

        return compact('items');
    }


    /**
     * @deprecated
     * @param \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection $items
     */
    public function formatItem($items)
    {
        // $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();
        return $items->transform(function ($row) {
            $full_description = $this->getFullDescription($row);
            return [
                'id' => $row->id,
                'full_description' => $full_description,
                'description' => $row->description,
                'model' => $row->model,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => $row->sale_unit_price,
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_perception' => (bool) $row->has_perception,
                'purchase_has_igv' => (bool) $row->purchase_has_igv,
                'percentage_perception' => $row->percentage_perception,
                'item_unit_types' => collect($row->item_unit_types)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => "{$row->description}",
                        'item_id' => $row->item_id,
                        'unit_type_id' => $row->unit_type_id,
                        'quantity_unit' => $row->quantity_unit,
                        'price1' => $row->price1,
                        'price2' => $row->price2,
                        'price3' => $row->price3,
                        'price_default' => $row->price_default,
                        'warehouse_id' => $row->warehouse_id,
                    ];
                }),
                'series_enabled' => (bool) $row->series_enabled,
            ];
        });
    }
}
