<?php

namespace App\Http\Controllers\Tenant;

use Exception;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Mpdf\HTMLParserMode;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Item;
use App\Models\Tenant\User;
use Illuminate\Support\Str;
use App\Traits\OfflineTrait;
use App\Traits\PrinterTrait;
use Illuminate\Http\Request;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Company;
use Mpdf\Config\FontVariables;
use App\CoreFacturalo\Template;

use Modules\Item\Models\ItemLot;
use Mpdf\Config\ConfigVariables;
use App\Models\Tenant\ItemSeller;
use App\Mail\Tenant\SaleNoteEmail;
use App\Models\Tenant\BankAccount;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\CashDocument;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\CoreFacturalo\HelperFacturalo;
use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Inventory\Models\Warehouse;
use App\Models\Tenant\PaymentMethodType;
use Modules\Document\Traits\SearchTrait;
use Modules\Finance\Traits\FinanceTrait;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use Modules\Finance\Traits\FilePaymentTrait;
use Modules\Inventory\Traits\InventoryTrait;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\SystemIscType;

use App\Http\Controllers\SearchItemController;
use App\CoreFacturalo\Requests\Inputs\Functions;
use Modules\Document\Models\SeriesConfiguration;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use Modules\Suscription\Models\Tenant\SuscriptionPayment;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\Http\Requests\Tenant\DispatchOrderRequest;
use App\Http\Resources\Tenant\DispatchOrderCollection;
use App\Http\Resources\Tenant\DispatchOrderResource;
use App\Http\Resources\Tenant\DispatchOrderResource2;
use App\Http\Resources\Tenant\DispatchOrderRouteCollection;
use App\Mail\Tenant\IntegrateSystemEmail;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchOrder;
use App\Models\Tenant\DispatchOrderItem;
use App\Models\Tenant\DispatchOrderPayment;
use App\Models\Tenant\DispatchOrderRoute;
use App\Models\Tenant\DispatchOrderRouteItem;
use App\Models\Tenant\MessageIntegrateSystem;
use App\Models\Tenant\ProductionOrder;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\StateDispatchOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Dispatch\Models\DispatchAddress;
use Modules\Inventory\Models\InventoryConfiguration;

class DispatchOrderController extends Controller
{

    use FinanceTrait;
    use InventoryTrait;
    use SearchTrait;
    use StorageDocument;
    use OfflineTrait;
    use FilePaymentTrait;
    use PrinterTrait;
    protected $configuration;
    protected $warehouse_id;
    protected $dispatch_order;
    protected $company;
    protected $apply_change;
    protected $document;


    public function pdfRoute($id, $format = 'a4')
    {
        $route = DispatchOrderRoute::find($id);
        $items = $route->items->transform(function ($row) {
            $dispatch_order = DispatchOrder::find($row->dispatch_order_id);
            return $dispatch_order->getCollectionDataRoute();
        });
        $company = Company::active();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $height = $items->count() * 10 + 580;
        if ($format == 'ticket') {
            $pdf = PDF::loadView(
                'tenant.dispatch_orders.pdf.ticket_route',
                compact("route", "items", "company", "establishment")
            )->setPaper(
                array(0, 0, 249.45, $height),
                'portrait'
            );
        } else {
            $pdf = PDF::loadView('tenant.dispatch_orders.pdf.a4_route', compact("route", "items", "company", "establishment"));
            $pdf->setPaper('a4', 'landscape');
        }

        return $pdf->stream('Ruta de despacho.pdf');
    }
    public function getRoute($id){
        $route = DispatchOrderRoute::find($id);
        $description = $route->description;
        $items = $route->items->transform(function ($row) {
            $dispatch_order = $row->dispatchOrder;
            $customer = $dispatch_order->customer;
            $number = $dispatch_order->number;
            $id = $dispatch_order->id;
            $location = "";
            if ($customer) {
                $district_id = $customer->district_id;
                if ($district_id) {
                    $location = func_get_location($district_id);
                }
            }
            return [
                'id' => $id,
                'customer' => $customer,
                'number' => $number,
                'location' => $location
            ];
        });
        return [
            'description' => $description,
            'items' => $items
        ];
    }
    public function toPrintDispatchOrder($type, $external_id){

        $document = DispatchOrder::where('external_id', $external_id)->first();
        $sale_note = SaleNote::find($document->sale_note_id);
        $customer_name = $sale_note->customer->name;
        $customer_document = $sale_note->customer->number;
        $payment_sum = $sale_note->payments->sum('payment');
        $pending = $sale_note->total - $payment_sum;
        $payments = $sale_note->payments;
        $payments_methods_descriptions = $payments->map(function ($row) {
            return isset($row["payment_method_type"]["description"]) ? $row["payment_method_type"]["description"] : null;
        })->filter()->values();
        
        $description_payments = $payments_methods_descriptions->isNotEmpty() 
            ? implode(', ', $payments_methods_descriptions->toArray()) 
            : 'No especificado';
        $dispatch_addresses = DispatchAddress::where('person_id', $document->customer_id)->first();
        $person = null;
        $person_document = null;
        $person_telephone = null;
        $reason = null;
        $location_id = null;
        $address = null;
        
        $district_name = null;
        $department_name = null;
        $province_name = null;
        $agency = null;
        
        if ($dispatch_addresses) {
            $agency = $dispatch_addresses->agency;
            $person = $dispatch_addresses->person;
            $identity_document_type_id = $dispatch_addresses->identity_document_type_id;
            $identity_document_type_description = null;
            if($identity_document_type_id){
                $identity_document_type = IdentityDocumentType::find($identity_document_type_id);
                if($identity_document_type){
                    $identity_document_type_description = $identity_document_type->description;
                }
            }
            $person_document = $dispatch_addresses->person_document;
            $person_telephone = $dispatch_addresses->person_telephone;
            $reason = $dispatch_addresses->reason;
            $location_id = $dispatch_addresses->location_id;
            if($location_id){
                $department_id = isset($location_id[0]) ? $location_id[0] : null;
                if($department_id){
                    $department_name = Department::find($department_id)->description;
                }
                $province_id = isset($location_id[1]) ? $location_id[1] : null;
                if($province_id){
                    $province_name = Province::find($province_id)->description;
                }
                $district_id = isset($location_id[2]) ? $location_id[2] : null;
                if($district_id){
                    $district_name = District::find($district_id)->description;
                }
            }
            $address = $dispatch_addresses->address;
        }
        $company = Company::active();
        $type = str_replace('-', '_', $type);
        $qr = null;
        // Definir tamaño del papel según el tipo
        if ($type === 'ticket_lima') {
            $paperSize = [0, 0, 198.43, 141.73]; // 70mm x 50mm
        } else {
            $paperSize = [0, 0, 283.46, 283.46]; // 100mm x 100mm
            $qrCode = new QrCodeGenerate();

            $qr = $qrCode->displayPNGBase64("https://campograndeperu.com/qr-ticket");
        }

        $pdf = Pdf::loadView('tenant.dispatch_orders.pdf.'.$type, compact(
            "company",
            "document",
            "person",
            "person_document",
            "person_telephone",
            "identity_document_type_description",
            "reason",
            "location_id",
            "address",
            "district_name",
            "description_payments",
            "pending",
            "agency",
            "department_name",
            "province_name",
            "customer_name",
            "customer_document",
            "qr"
        ))
            ->setPaper($paperSize, 'landscape');
        $filename = "Orden_de_despacho_{$document->series}-{$document->number}";

        return $pdf->stream($filename . '.pdf');

    }
    public function getRouteItems($id)
    {
        $route = DispatchOrderRoute::find($id);
        $items = $route->items->transform(function ($row) {
            $dispatch_order = DispatchOrder::find($row->dispatch_order_id);
            return $dispatch_order->getCollectionDataRoute();
        });
        return $items;
    }
    public function getWazeUrl(Request $request)
    {
        $url = $request->input('url');
        if (!$url) {
            return [
                'success' => false,
                'message' => 'No se encontró la URL'
            ];
        }
        $finalUrl = $this->getCoordinates($url);
        if (!$finalUrl) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener la URL efectiva'
            ];
        }
        $waze_url = $this->transformUrl($finalUrl);
        if (!$waze_url) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener la URL de Waze'
            ];
        }
        return [
            'success' => true,
            'data' => $waze_url
        ];
    }
    function transformUrl($url)
    {
        // Decodificar la URL por si tiene caracteres codificados
        $decodedUrl = urldecode($url);
        
        // Primero intentar obtener coordenadas del parámetro data=
        if (strpos($decodedUrl, 'data=') !== false) {
            if (preg_match('/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/', $decodedUrl, $matches)) {
                $latitude = (float) $matches[1];
                $longitude = (float) $matches[2];
                
                if ($latitude >= -90 && $latitude <= 90 && 
                    $longitude >= -180 && $longitude <= 180) {
                    
                    $waze_url = "https://www.waze.com/ul?ll=$latitude,$longitude&navigate=yes";
                    return $waze_url;
                }
            }
        }
        
        // Si no se encontraron coordenadas en data=, intentar con los patrones anteriores
        $patterns = [
            // Patrón para URLs directas de Google Maps
            '/(?:@|search\/|\/place\/)(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/i',
            
            // Patrón básico para coordenadas
            '/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/i',
            
            // Patrón que incluye el signo + explícitamente
            '/(-?\d+\.?\d*)\s*,\s*([+-]\d+\.?\d*)/i'
        ];
        
        foreach ($patterns as $regex) {
            if (preg_match($regex, $decodedUrl, $matches)) {
                $latitude = (float) $matches[1];
                $longitude = (float) $matches[2];
                
                if ($latitude >= -90 && $latitude <= 90 && 
                    $longitude >= -180 && $longitude <= 180) {
                    
                    $waze_url = "https://www.waze.com/ul?ll=$latitude,$longitude&navigate=yes";
                    return $waze_url;
                }
            }
        }
        
        return null;
    }

    function getCoordinates($url)
    {

        try {
            $response = Http::withOptions(['allow_redirects' => true])
                ->get($url);

            // Obtener la URL efectiva
            $finalUrl = (string) $response->effectiveUri(); // Método para obtener la URI efectiva
        
            return $finalUrl;
        } catch (\Exception $e) {
            return null;
        }
    }
    public function getRoutes(Request $request)
    {
        $description = $request->input('description');
        $date = $request->input('date');

        $records = DispatchOrderRoute::query();
        if ($description) {
            $records->where('description', 'like', "%{$description}%");
        }
        if ($date) {
            $records->whereDate('created_at', $date);
        }
        $records->orderBy('created_at', 'desc');
        return new DispatchOrderRouteCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function columns2()
    {
        $locations = [];
        $locations = func_get_locations();
        $locations_filter = array_values(array_filter($locations, function ($location) {
            return $location['value'] != '07';
        }));
        $location_15 = array_values(array_filter($locations_filter, function ($location) {
            return $location['value'] == '15';
        }));

        // Eliminar el primer hijo del elemento con el valor 15
        if (!empty($location_15) && !empty($location_15[0]['children'])) {
            array_shift($location_15[0]['children']);
        }

        // Reconstruir el array original con el elemento modificado
        $locations_filter = array_map(function ($location) {
            if ($location['value'] == '15' && !empty($location['children'])) {
                array_shift($location['children']);
            }
            return $location;
        }, $locations_filter);



        $location_15 = array_values(array_filter($locations, function ($location) {
            return $location['value'] == '15';
        }));
        $location_07 =  array_values(array_filter($locations, function ($location) {
            return $location['value'] == '07';
        }));
        $location_15 = $location_15[0]['children'][0];
        $location_07 = $location_07[0]['children'][0];
        $locations_filter2 = [
            "value" => "1",
            "label" => "Lima y Callao",
            "children" => [
                $location_15,
                $location_07
            ]
        ];
        $locations_filter3 = [
            'value' => '2',
            'label' => 'Provincias',
            'children' => $locations_filter
        ];
        $all_filters = [
            $locations_filter2,
            $locations_filter3

        ];
        $status = StateDispatchOrder::all();
        return [
            'locations' => $all_filters,
            'status' => $status

        ];
    }
    public function saveRoute(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $id = $request->input('id');
            $dispatch_order_route = DispatchOrderRoute::firstOrNew(['id' => $id]);
            $dispatch_order_route->items()->delete();
            $dispatch_order_route->fill($request->all());
            $dispatch_order_route->save();
            $items = $request->input('items');
            $dispatch_order_route->items()->createMany($items);
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Ruta guardada con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function getDispatchOrder(Request $request)
    {
        $number = $request->input('number');
        $dispatch_order = DispatchOrder::where('number', $number)->first();
        if (!$dispatch_order) {
            return [
                'success' => false,
                'message' => 'No se encontró la orden de despacho'
            ];
        }
        $location = "";
        $customer = $dispatch_order->customer;
        $address_dispatch = DispatchAddress::where('person_id', $dispatch_order->customer_id)->first();
        if ($address_dispatch) {
            $location_dispatch = $address_dispatch->location_id;
            if ($location_dispatch) {
                $district_id = $location_dispatch[2];
                if ($district_id) {
                    $location = func_get_location($district_id);
                }
                
            }
        }
        if ($customer && $location == "") {
            $district_id = $customer->district_id;
            if ($district_id) {
                $location = func_get_location($district_id);
            }
        }
        $dispatch_order->location = $location;
        return [
            'success' => true,
            'data' => $dispatch_order
        ];
    }

    public function index_list()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::select('ticket_58')->first();

        return view('tenant.dispatch_orders.index_list', compact('soap_company', 'configuration'));
    }
    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::select('ticket_58')->first();

        return view('tenant.dispatch_orders.index', compact('soap_company', 'configuration'));
    }

    public function responsibles()
    {
        $responsibles = User::where('type', '!=', 'admin')->select('id', 'name')->get();
        return compact('responsibles');
    }

    public function generateFromProductionOrder(Request $request, $production_order_id)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $observation = $request->input('observation');
            $responsible_id = $request->input('responsible_id');
            $date_of_issue = $request->input('date_of_issue');
            $package_number = $request->input('package_number');
            $dispatch_order = DispatchOrder::where('production_order_id', $production_order_id)->first();
            $production_order = ProductionOrder::find($production_order_id);
            $is_update = false;
            if ($dispatch_order) {
                $is_update = true;
                $this->dispatch_order = $dispatch_order;
                $this->deleteAllPayments($this->dispatch_order->payments);
                $this->deleteAllItems($this->dispatch_order->items);
            } else {
                $dispatch_order = new DispatchOrder;
            }
            $dispatch_order->fill($production_order->toArray());
            $dispatch_order->prefix = 'OD';
            $dispatch_order->state_type_id = '01';
            $dispatch_order->package_number = $package_number;
            $dispatch_order->sale_note_id = $production_order->sale_note_id;
            $dispatch_order->production_order_id = $production_order_id;
            if (!$is_update) {
                $dispatch_order->id = null;
                $dispatch_order->date_of_issue = $date_of_issue;
                $dispatch_order->observation = $observation;
                $dispatch_order->responsible_id = $responsible_id;
            }
            $dispatch_order->save();
            $this->dispatch_order = $dispatch_order;
            foreach ($production_order->items as $item) {
                $dispatch_order_item = new DispatchOrderItem;
                $dispatch_order_item->fill($item->toArray());
                $dispatch_order_item->id = null;
                $dispatch_order_item->dispatch_order_id = $dispatch_order->id;
                $dispatch_order_item->save();
            }

            foreach ($production_order->payments as $payment) {
                $dispatch_order_payment = new DispatchOrderPayment;
                $dispatch_order_payment->fill($payment->toArray());
                $dispatch_order_payment->id = null;
                $dispatch_order_payment->dispatch_order_id = $dispatch_order->id;
                $dispatch_order_payment->save();
            }
            $this->setFilename();
            $this->createPdf($dispatch_order, "a4");
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Documento generado'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function create($id = null)
    {
        $cashid = null;
        if ($id != null) {
            $salenote = DispatchOrder::find($id);
            $cash_open = Cash::where('user_id', $salenote->user_id)->where('state', true)->first();
            if ($cash_open != null) {
                $cashid = $cash_open->id;
            }
        } else {
            $cash_open = Cash::where('user_id', User::getUserCashId())->where('state', true)->first();
            if ($cash_open != null) {
                $cashid = $cash_open->id;
            }
        }
        return view('tenant.dispatch_orders.form', compact('id', 'cashid'));
    }

    public function killDocument($id)
    {
        $dispatch_order = DispatchOrder::find($id);
        CashDocument::where('dispatch_order_id', $id)->delete();
        // CashDocumentCredit::where('dispatch_order_id', $id)->delete();
        // Dispatch::where('reference_dispatch_order_id', $id)->delete();
        // DispatchSaleNote::where('dispatch_order_id', $id)->delete();
        // Document::where('dispatch_order_id', $id)->update(['dispatch_order_id' => null]);
        // GuideFile::where('dispatch_order_id', $id)->delete();
        // Kardex::where('dispatch_order_id', $id)->delete();
        // SaleNotePayment::where('dispatch_order_id', $id)->delete();
        // Orden::where('dispatch_order_id', $id)->delete();
        // SuscriptionPayment::where('dispatch_order_id', $id)->delete();
        $items = DispatchOrderItem::where('dispatch_order_id', $id)->get();
        foreach ($items as $item) {
            $item->restoreStock();
            ItemSeller::where('dispatch_order_id', $item->id)->delete();
            $item->delete();
        }
        // $dispatch_order->inventory_kardex()->delete();

        $dispatch_order->delete();
        return [
            'success' => true,
            'message' => 'Documento eliminado'
        ];
    }

    /**
     * Busca el texto $search en la cadena de caracteres $text
     * @param $search
     * @param $text
     * @return bool
     */
    public function searchInString($search, $text)
    {
        return !(strpos($text, $search) === false);
    }


    public function columns()
    {
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        if ($is_integrate_system) {
            return [
                'number' => 'N° Orden de despacho',
                'telephone' => 'N° de celular',
                'customer' => 'Documento / Nombre del cliente',
            ];
        }
        return [
            'date_of_issue' => 'Fecha de emisión',
            'customer' => 'Cliente',
        ];
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Tenant\SaleNoteCollection
     */
    public function records(Request $request)
    {

        $records = $this->getRecords($request);

        return new DispatchOrderCollection($records->paginate(config('tenant.items_per_page')));
    }


    /**
     * @param $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getRecords($request)
    {
        $records = DispatchOrder::query();



        if ($request->column == 'customer' && $request->value != null) {
            $records->whereHas('customer', function ($query) use ($request) {
                $query
                    ->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'telephone' && $request->value != null) {
            $records->whereHas('customer', function ($query) use ($request) {
                $query->where('telephone', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'quotation_number' && $request->value != null) {
            // $records->where('quotation_number', 'like', "%{$request->value}%")
            $records->whereHas('sale_note', function ($query) use ($request) {
                $query->whereHas('quotation', function ($query) use ($request) {
                    $query->where('number', 'like', "%{$request->value}%");
                });
            })
                ->latest();
        } else {
            if ($request->value != null) {
                $records->where($request->column, 'like', "%{$request->value}%")->latest('id');
            } else {
                $records->latest('id');
            }
        }
        $location_id = $request->location_id;
        $status_id = $request->status_id;
        $state_sale_note_order_id = $request->state_sale_note_order_id;
        if ($location_id) {
            $records->whereHas('customer', function ($query) use ($location_id) {
                $query->whereHas('dispatch_addresses', function ($query) use ($location_id) {
                    if ($location_id == '1') {
                        // Lima y Callao
                        $query->where(function ($q) {
                            // department_id = '07' (Callao) en location_id[0]
                            $q->whereRaw("JSON_EXTRACT(location_id, '$[0]') = ?", ['07'])
                                ->orWhere(function ($q2) {
                                    // department_id = '15' (Lima) y province_id = '1501' (Lima) en location_id[0] y location_id[1]
                                    $q2->whereRaw("JSON_EXTRACT(location_id, '$[0]') = ?", ['15'])
                                        ->whereRaw("JSON_EXTRACT(location_id, '$[1]') = ?", ['1501']);
                                });
                        });
                    } else {
                        // Provincias (todo lo que no sea Lima y Callao)
                        $query->where(function ($q) {
                            $q->whereRaw("JSON_EXTRACT(location_id, '$[0]') != ?", ['07'])
                                ->where(function ($q2) {
                                    $q2->whereRaw("JSON_EXTRACT(location_id, '$[0]') != ?", ['15'])
                                        ->orWhere(function ($q3) {
                                            $q3->whereRaw("JSON_EXTRACT(location_id, '$[0]') = ?", ['15'])
                                                ->whereRaw("JSON_EXTRACT(location_id, '$[1]') != ?", ['1501']);
                                        });
                                });
                        });
                    }
                });
            });
        }
        if ($state_sale_note_order_id) {
            $records->whereHas('person', function ($query) use ($state_sale_note_order_id) {
                $query->whereHas('dispatch_addresses', function ($query) use ($state_sale_note_order_id) {
                    $query->where('reason', $state_sale_note_order_id);
                });
            });
        }
        if ($status_id) {
            $records->where('dispatch_order_state_id', $status_id);
        }
        if ($request->series) {
            $records->where('series', 'like', '%' . $request->series . '%');
        }
        if ($request->number) {
            $records->where('number', 'like', '%' . $request->number . '%');
        }
        if ($request->total_canceled != null) {
            $records->where('total_canceled', $request->total_canceled);
        }

        if ($request->purchase_order) {
            $records->where('purchase_order', $request->purchase_order);
        }
        if ($request->license_plate) {
            $records->where('license_plate', $request->license_plate);
        }
        return $records;
    }


    public function searchCustomers(Request $request)
    {
        $driver = filter_var($request->driver ?? "false", FILTER_VALIDATE_BOOLEAN);
        $customers = Person::query();
        if ($driver) {
            $customers = Person::where('barcode', 'like', "%{$request->input}%");
        } else {
            $customers = Person::where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%");
        }
        $customers = $customers->whereType('customers')->orderBy('name')
            ->whereIsEnabled()
            ->where('is_driver', $driver)
            ->get()->transform(function (Person $row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'seller_id' => $row->seller_id,
                    'seller' => $row->seller,
                    'person_type_id' => $row->person_type_id,
                    'name' => $row->name,
                    'number' => $row->number,
                    'barcode' => $row->barcode,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code
                ];
            });

        return compact('customers');
    }
    public function getWidthTicket($format_pdf)
    {
        $width = 0;

        if (config('tenant.enabled_template_ticket_80')) {
            $width = 76;
        } else {
            switch ($format_pdf) {
                case 'ticket_58':
                    $width = 56;
                    break;
                case 'ticket_50':
                    $width = 45;
                    break;
                default:
                    $width = 78;
                    break;
            }
        }

        return $width;
    }
    public function paymentdestinations($user_id)
    {
        $payment_destinations = $this->getPaymentDestinations($user_id);
        return compact('payment_destinations');
    }
    public function tables($user_id = null)
    {
        $user = new User();
        if (Auth::user()) {
            $user = Auth::user();
        }
        $establishment_id =  $user->establishment_id;
        $userId =  $user->id;
        $customers = $this->table('customers');
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        $currency_types = CurrencyType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $global_charge_types = ChargeDiscountType::whereIn('id', ['50'])->get();
        $company = Company::active();
        // $payment_method_types = PaymentMethodType::where('active', true)->get();
        //obtiene los payment_method_types que en su descripcion  no tengan la palabra "Factura"
        $payment_method_types = PaymentMethodType::where('description', 'not like', '%Factura%')->get();
        $series = collect(Series::all())->transform(function ($row) {
            return [
                'id' => $row->id,
                'contingency' => (bool) $row->contingency,
                'document_type_id' => $row->document_type_id,
                'establishment_id' => $row->establishment_id,
                'number' => $row->number
            ];
        });
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::select('destination_sale', 'ticket_58')->first();
        // $sellers = User::GetSellers(false)->get();
        $sellers = User::getSellersToNvCpe($establishment_id, $userId);
        $global_discount_types = ChargeDiscountType::getGlobalDiscounts();

        return compact(
            'customers',
            'establishments',
            'currency_types',
            'discount_types',
            'configuration',
            'charge_types',
            'company',
            'payment_method_types',
            'series',
            'payment_destinations',
            'sellers',
            'global_charge_types',
            'global_discount_types'
        );
    }



    public function item_tables()
    {
        // $items = $this->table('items');
        $items = SearchItemController::getItemsToSaleNote();
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();

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
            'operation_types',
            'is_client'
        );
    }

    public function record($id)
    {
        $record = new DispatchOrderResource(DispatchOrder::findOrFail($id));

        return $record;
    }
    public function savePayments($sale_note, $payments, $cash_id = null)
    {

        $total = $sale_note->total;
        $balance = $total - collect($payments)->sum('payment');

        $search_cash = ($balance < 0) ? collect($payments)->firstWhere('payment_method_type_id', '01') : null;

        $this->apply_change = false;

        if ($balance < 0 && $search_cash) {

            $payments = collect($payments)->map(function ($row) use ($balance) {

                $change = null;
                $payment = $row['payment'];

                if ($row['payment_method_type_id'] == '01' && !$this->apply_change) {

                    $change = abs($balance);
                    $payment = $row['payment'] - abs($balance);
                    $this->apply_change = true;
                }

                return [
                    "id" => null,
                    "document_id" => null,
                    "sale_note_id" => null,
                    "date_of_payment" => $row['date_of_payment'],
                    "payment_method_type_id" => $row['payment_method_type_id'],
                    "reference" => $row['reference'],
                    "payment_destination_id" => isset($row['payment_destination_id']) ? $row['payment_destination_id'] : null,
                    "payment_filename" => isset($row['payment_filename']) ? $row['payment_filename'] : null,
                    "change" => $change,
                    "payment" => $payment
                ];
            });
        }



        foreach ($payments as $row) {

            if ($balance < 0 && !$this->apply_change) {
                $row['change'] = abs($balance);
                $row['payment'] = $row['payment'] - abs($balance);
                $this->apply_change = true;
            }

            $record_payment = $sale_note->payments()->create($row);

            if (isset($row['payment_destination_id'])) {
                $this->createGlobalPayment($record_payment, $row);
            }

            if (isset($row['payment_filename'])) {
                $record_payment->payment_file()->create([
                    'filename' => $row['payment_filename']
                ]);
            }

            // para carga de voucher
            $this->saveFilesFromPayments($row, $record_payment, 'sale_notes');
        }
    }
    private function setDataPointSystemToValues(&$values, $inputs)
    {
        $configuration = Configuration::getDataPointSystem();

        $created_from_pos = $inputs['created_from_pos'] ?? false;

        if ($created_from_pos && $configuration->enabled_point_system) {
            $values['point_system'] = $configuration->enabled_point_system;
            $values['point_system_data'] = [
                'point_system_sale_amount' => $configuration->point_system_sale_amount,
                'quantity_of_points' => $configuration->quantity_of_points,
                'round_points_of_sale' => $configuration->round_points_of_sale,
            ];
        }
    }
    public function store(DispatchOrderRequest $request)
    {
        $configuration = Configuration::first();
        $type_user = auth()->user()->type;

        return $this->storeWithData($request->all());
    }


    public function storeWithData($inputs)
    {

        DB::connection('tenant')->beginTransaction();
        try {
            if (!isset($inputs['id'])) {
                $inputs['id'] = false;
            }
            $data = $this->mergeData($inputs);
            $this->dispatch_order =  DispatchOrder::query()->updateOrCreate(['id' => $inputs['id']], $data);

            $this->deleteAllPayments($this->dispatch_order->payments);

            //se elimina los items para activar el evento deleted del modelo y controlar el inventario
            $this->deleteAllItems($this->dispatch_order->items);

            $configuration = Configuration::first();
            foreach ($data['items'] as $row) {

                // $item_id = isset($row['id']) ? $row['id'] : null;
                $item_id = isset($row['record_id']) ? $row['record_id'] : null;
                $dispatch_order_item = DispatchOrderItem::query()->firstOrNew(['id' => $item_id]);

                if (isset($row['item']['lots'])) {
                    $row['item']['lots'] = isset($row['lots']) ? $row['lots'] : $row['item']['lots'];
                }
                $this->setIdLoteSelectedToItem($row);
                $this->setSizesSelectedToItem($row);
                $dispatch_order_item->fill($row);
                $dispatch_order_item->dispatch_order_id = $this->dispatch_order->id;
                $dispatch_order_item->save();

                if (isset($row['lots'])) {

                    foreach ($row['lots'] as $lot) {
                        $record_lot = ItemLot::query()->findOrFail($lot['id']);
                        $record_lot->has_sale = true;
                        $record_lot->update();
                    }
                }
                // control de lotes
                $id_lote_selected = $this->getIdLoteSelectedItem($row);
                // si tiene lotes y no fue generado a partir de otro documento (pedido...)
                if ($id_lote_selected && !$this->dispatch_order->isGeneratedFromExternalRecord()) {
                    if (is_array($id_lote_selected)) {
                        // presentacion - factor de lista de precios
                        $quantity_unit = isset($dispatch_order_item->item->presentation->quantity_unit) ? $dispatch_order_item->item->presentation->quantity_unit : 1;
                        $inventory_configuration = InventoryConfiguration::first();
                        $inventory_configuration->stock_control;
                        foreach ($id_lote_selected as $item) {
                            $lot = ItemLotsGroup::query()->find($item['id']);
                            $lot->quantity = $lot->quantity - ($quantity_unit * $item['compromise_quantity']);
                            if ($inventory_configuration->stock_control) {
                                $this->validateStockLotGroup($lot, $dispatch_order_item);
                            }
                            $lot->save();
                        }
                    } else {

                        $quantity_unit = 1;
                        if (isset($row['item']) && isset($row['item']['presentation']) && isset($row['item']['presentation']['quantity_unit'])) {
                            $quantity_unit = $row['item']['presentation']['quantity_unit'];
                        }
                        $lot = ItemLotsGroup::find($id_lote_selected);
                        $lot->quantity = ($lot->quantity - ($row['quantity'] * $quantity_unit));
                        $lot->save();
                    }
                }
                $configuration = Configuration::first();
                if ($configuration->college) {
                    $dispatch_order_id = $this->dispatch_order->id;
                    $periods = Functions::valueKeyInArray($data, 'months');
                    $client_id = Functions::valueKeyInArray($data, 'customer_id'); //$data['customer_id'];
                    $child_id = Functions::valueKeyInArray($data, 'child_id'); //$data['child_id'];
                    if ($client_id && $child_id && $periods) {
                        SuscriptionPayment::where('dispatch_order_id', $dispatch_order_id)->delete();
                        foreach ($periods as  $period) {
                            $date = Carbon::createFromDate($period['year'], $period['value'], 1);
                            SuscriptionPayment::create([
                                'child_id' => $child_id,
                                'client_id' => $client_id,
                                'dispatch_order_id' => $dispatch_order_id,
                                'period' => $date,
                            ]);
                        }
                    }
                }
            }
            //pagos
            $this->savePayments($this->dispatch_order, $data['payments'], $data['cash_id']);

            $this->setFilename();
            $this->createPdf($this->dispatch_order, "a4", $this->dispatch_order->filename);
            $this->regularizePayments($data['payments']);
            DB::connection('tenant')->commit();
            $base_url = url('/');
            $external_id = $this->dispatch_order->external_id;
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $print_format = $establishment->print_format ?? 'ticket';
            $url_print = "{$base_url}/sale-notes/print/{$external_id}/$print_format";
            return [
                'success' => true,
                'data' => [
                    'id' => $this->dispatch_order->id,
                    'printer'  => $this->printerName(auth()->user()->id),
                    'number_full' => $this->dispatch_order->number_full,
                    'url_print' => $url_print,
                ],
            ];
        } catch (Exception $e) {
            $this->generalWriteErrorLog($e);

            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function regularizePayments($payments)
    {

        $total_payments = collect($payments)->sum('payment');

        $balance = $this->dispatch_order->total - $total_payments;

        if ($balance <= 0) {

            // $this->dispatch_order->total_canceled = true;
            $this->dispatch_order->save();
        } else {

            // $this->dispatch_order->total_canceled = false;
            $this->dispatch_order->save();
        }
    }
    /**
     *
     * Obtener lote seleccionado
     *
     * @todo regularizar lots_group, no se debe guardar en bd, ya que tiene todos los lotes y no los seleccionados, reemplazar por IdLoteSelected
     *
     * @param  array $row
     * @return array
     */
    private function getIdLoteSelectedItem($row)
    {
        $id_lote_selected = null;

        if (isset($row['IdLoteSelected'])) {
            $id_lote_selected = $row['IdLoteSelected'];
        } else {
            if (isset($row['item']['lots_group'])) {
                $id_lote_selected = collect($row['item']['lots_group'])->where('compromise_quantity', '>', 0)->toArray();
            }
        }

        return $id_lote_selected;
    }


    /**
     *
     * Asignar lote a item (regularizar propiedad en json item)
     *
     * @param  array $row
     * @return void
     */
    private function setIdLoteSelectedToItem(&$row)
    {
        if (isset($row['IdLoteSelected'])) {
            $row['item']['IdLoteSelected'] = $row['IdLoteSelected'];
        } else {
            $row['item']['IdLoteSelected'] = isset($row['item']['IdLoteSelected']) ? $row['item']['IdLoteSelected'] : null;
        }
    }
    private function setSizesSelectedToItem(&$row)
    {
        if (isset($row['sizes_selected'])) {
            $row['item']['sizes_selected'] = $row['sizes_selected'];
        } else {
            $row['item']['sizes_selected'] = isset($row['item']['sizes_selected']) ? $row['item']['sizes_selected'] : null;
        }
    }




    public function destroy_sale_note_item($id)
    {
        $item = DispatchOrderItem::findOrFail($id);

        if (isset($item->item->lots)) {

            foreach ($item->item->lots as $lot) {
                $record_lot = ItemLot::findOrFail($lot->id);
                $record_lot->has_sale = false;
                $record_lot->update();
            }
        }

        $item->delete();

        return [
            'success' => true,
            'message' => 'eliminado'
        ];
    }

    public function mergeData($inputs)
    {

        $this->company = Company::active();

        $cash_id = Functions::valueKeyInArray($inputs, 'cash_id');
        if ($cash_id == null) {
            $cash_id = optional(Cash::where([['user_id', User::getUserCashId()], ['state', true]]))->first()->id;
        }
        // Para matricula, se busca el hijo en atributos
        $attributes = $inputs['attributes'] ?? [];
        $children = $attributes['children_customer_id'] ?? null;
        $type_period = isset($inputs['type_period']) ? $inputs['type_period'] : null;
        $quantity_period = isset($inputs['quantity_period']) ? $inputs['quantity_period'] : null;
        $d_of_issue = new Carbon($inputs['date_of_issue']);
        $automatic_date_of_issue = null;

        if ($type_period && $quantity_period > 0) {

            $add_period_date = ($type_period == 'month') ? $d_of_issue->addMonths($quantity_period) : $d_of_issue->addYears($quantity_period);
            $automatic_date_of_issue = $add_period_date->format('Y-m-d');
        }

        if (key_exists('series_id', $inputs)) {
            $series = Series::query()->find($inputs['series_id'])->number;
        } else {
            $series = $inputs['series'];
        }

        $number = null;

        if ($inputs['id']) {
            $number = $inputs['number'];
        } else {

            if (DispatchOrder::count() == 0) {
                $series_configuration = SeriesConfiguration::where([['document_type_id', "80"], ['series', $series]])->first();
                $number = $series_configuration->number ?? 1;
            } else {
                $document = DispatchOrder::query()
                    ->select('number')->where('soap_type_id', $this->company->soap_type_id)
                    ->where('series', $series)
                    ->orderBy('number', 'desc')
                    ->first();

                $number = ($document) ? $document->number + 1 : 1;
            }
        }
        $seller_id = isset($inputs['seller_id']) ? (int)$inputs['seller_id'] : 0;
        if ($seller_id == 0) {
            // $seller_id = $inputs['seller_id'];
        }
        $additional_information = isset($inputs['additional_information']) ? $inputs['additional_information'] : '';


        $values = [
            'additional_information' => $additional_information,
            'automatic_date_of_issue' => $automatic_date_of_issue,
            'user_id' => $seller_id == 0 ? auth()->user()->id : $seller_id,
            'seller_id' => $seller_id,
            'external_id' => Str::uuid()->toString(),
            'customer' => PersonInput::set($inputs['customer_id']),
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01',
            'series' => $series,
            'number' => $number,
            'cash_id' => $cash_id
        ];

        if (!empty($children)) {
            $customer = PersonInput::set($inputs['customer_id']);
            $customer['children'] = PersonInput::set($children);
            $values['customer'] = $customer;
        }

        $this->setDataPointSystemToValues($values, $inputs);


        unset($inputs['series_id']);
        $inputs = array_merge($inputs, $values);

        return $inputs;
    }

    function message($state_id)
    {
        $message = '';
        switch ($state_id) {
            case 2:
                $message = MessageIntegrateSystem::getMessage('dispatch_order.2');
                break;
            case 3:
                $message = MessageIntegrateSystem::getMessage('dispatch_order.3');
                break;
            case 4:
                $message = MessageIntegrateSystem::getMessage('dispatch_order.4');
                break;
            case 5:
                $message = MessageIntegrateSystem::getMessage('dispatch_order.5');
                break;
        }
        return $message;
    }

    public function testMail()
    {
        // $message = MessageIntegrateSystem::getMessage('production_order.2');
        // $customer_email = "darkent4ever@gmail.com";
        // $person = Person::find(1);
        // $mailable = new IntegrateSystemEmail($person, $message);
        // EmailController::SendMail($customer_email, $mailable, 0, 7);
        $person = Person::find(22);
        $optional_email = $person->optional_email;
        // $optional_email = json_decode($optional_email);
        $optional_email = unserialize($optional_email);
        return [
            'success' => true,
            'message' => 'Correo enviado',
            'optional_email' => $optional_email,
        ];
    }

    public function changeState($dispatch_order_id, $state_id)
    {

        $dispatch_order = DispatchOrder::find($dispatch_order_id);
        $dispatch_order->dispatch_order_state_id = $state_id;
        $dispatch_order->save();
        $customer = Person::find($dispatch_order->customer_id);
        $customer_email = $customer->email;
        $message = $this->message($state_id);
        if ($message != '' && $customer_email) {

            $mailable = new IntegrateSystemEmail($customer, $message);
            $id = $dispatch_order->id;
            EmailController::SendMail($customer_email, $mailable, $id, 7);
        }
        return [
            'success' => true,
            'message' => 'Estado actualizado'
        ];
    }

    //    public function recreatePdf($dispatch_order_id)
    //    {
    //        $this->dispatch_order = SaleNote::find($dispatch_order_id);
    //        $this->createPdf();
    //    }

    public function users()
    {
        $users = User::where('type', '!=', 'admin')->select('id', 'name')->get();
        return compact('users');
    }
    public function record2($dispatch_order_id)
    {
        $record = DispatchOrder::find($dispatch_order_id);
        return new DispatchOrderResource2($record);
    }

    public function changeValuesPdfTicket50(&$pdf_margin_right, &$pdf_margin_left, &$base_height)
    {
        $pdf_margin_right = 2;
        $pdf_margin_left = 2;
        $base_height = 90;
    }
    public function setResponsible($dispatch_order_id, $responsible_id)
    {
        $dispatch_order = DispatchOrder::find($dispatch_order_id);
        $dispatch_order->responsible_id = $responsible_id;
        $dispatch_order->save();

        return [
            'success' => true,
            'message' => 'Responsable actualizado'
        ];
    }
    public function states()
    {
        $states = DB::connection('tenant')->table('state_dispatch_orders')->get();

        return compact('states');
    }
    private function setFilename()
    {

        $name = [$this->dispatch_order->prefix, $this->dispatch_order->number ?? $this->dispatch_order->id, date('Ymd')];
        $this->dispatch_order->filename = join('-', $name);
        $this->dispatch_order->save();
    }


    public function toPrint($external_id, $format)
    {

        $dispatch_order = DispatchOrder::where('external_id', $external_id)->first();

        if (!$dispatch_order) throw new Exception("El código {$external_id} es inválido, no se encontro la nota de venta relacionada");

        $this->reloadPDF($dispatch_order, $format, $dispatch_order->filename);
        $temp = tempnam(sys_get_temp_dir(), 'dispatch_order');

        file_put_contents($temp, $this->getStorage($dispatch_order->filename, 'dispatch_order'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$dispatch_order->filename.'"'
        ];
        */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($dispatch_order->filename));
    }

    private function reloadPDF($dispatch_order, $format, $filename)
    {
        $this->createPdf($dispatch_order, $format, $filename);
    }






    public function createPdf($dispatch_order = null, $format_pdf = null, $filename = null, $output = 'pdf')
    {
        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();
        $pdf->shrink_tables_to_fit = 1;
        $this->company = ($this->company != null) ? $this->company : Company::active();
        $this->document = ($dispatch_order != null) ? $dispatch_order : $this->dispatch_order;

        $this->configuration = Configuration::first();
        // $configuration = $this->configuration->formats;
        $base_template = Establishment::find($this->document->establishment_id)->template_pdf;

        $html = $template->pdf($base_template, "dispatch_order", $this->company, $this->document, $format_pdf);

        $pdf_margin_top = 2;
        $pdf_margin_right = 5;
        $pdf_margin_bottom = 0;
        $pdf_margin_left = 5;

        // if (($format_pdf === 'ticket') OR ($format_pdf === 'ticket_58'))
        if (in_array($format_pdf, ['ticket', 'ticket_58', 'ticket_50'])) {
            // $width = ($format_pdf === 'ticket_58') ? 56 : 78 ;
            // if(config('tenant.enabled_template_ticket_80')) $width = 76;
            $width = $this->getWidthTicket($format_pdf);

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
            $base_height = 120;

            if ($format_pdf === 'ticket_50') $this->changeValuesPdfTicket50($pdf_margin_right, $pdf_margin_left, $base_height);

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    $base_height +
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
                'margin_top' => $pdf_margin_top,
                'margin_right' => $pdf_margin_right,
                'margin_bottom' => $pdf_margin_bottom,
                'margin_left' => $pdf_margin_left
            ]);
        } else if ($format_pdf === 'a5') {

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
            $discount_global = 0;
            foreach ($this->document->items as $it) {
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends           = $this->document->legends != '' ? '10' : '0';


            $alto = ($quantity_rows * 8) +
                ($discount_global * 3) +
                $company_name +
                $company_address +
                $company_number +
                $customer_name +
                $customer_address +
                $p_order +
                $legends +
                $total_exportation +
                $total_free +
                $total_unaffected +
                $total_exonerated +
                $total_taxed;
            $diferencia = 148 - (float)$alto;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    210,
                    $diferencia + $alto
                ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else {


            if (in_array($base_template, ['proforma_matricial'])) {

                $pdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => [
                        116,
                        140
                    ],
                    'margin_top' => 2,
                    'margin_right' => 3,
                    'margin_bottom' => 0,
                    'margin_left' => 3
                ]);
            }


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

        // para impresion automatica
        if ($output == 'html') return $this->getHtmlDirectPrint($pdf, $stylesheet, $html);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        if (config('tenant.pdf_template_footer')) {
            /* if (($format_pdf != 'ticket') AND ($format_pdf != 'ticket_58') AND ($format_pdf != 'ticket_50')) */
            if ($base_template != 'full_height') {
                $html_footer = $template->pdfFooter($base_template, $this->document);
            } else {
                $html_footer = $template->pdfFooter('default', $this->document);
            }
            $html_footer_legend = "";
            if ($base_template != 'legend_amazonia') {
                if ($this->configuration->legend_footer) {
                    $html_footer_legend = $template->pdfFooterLegend($base_template, $this->document);
                }
            }

            if (($format_pdf === 'ticket') || ($format_pdf === 'ticket_58') || ($format_pdf === 'ticket_50')) {
                $pdf->WriteHTML($html_footer . $html_footer_legend, HTMLParserMode::HTML_BODY);
            } else {
                $pdf->SetHTMLFooter($html_footer . $html_footer_legend);
            }
        }

        if ($base_template === 'brand') {

            if (($format_pdf === 'ticket') || ($format_pdf === 'ticket_58') || ($format_pdf === 'ticket_50')) {
                $pdf->SetHTMLHeader("");
                $pdf->SetHTMLFooter("");
            }
        }

        $helper_facturalo = new HelperFacturalo();

        if ($helper_facturalo->isAllowedAddDispatchTicket($format_pdf, 'dispatch-order', $this->document)) {
            $helper_facturalo->addDocumentDispatchTicket($pdf, $this->company, $this->document, [
                $template,
                $base_template,
                $width,
                ($quantity_rows * 8) + $extra_by_item_description
            ]);
        }


        $this->uploadFile($this->document->filename, $pdf->output('', 'S'), 'dispatch_order');
    }



    /**
     * 
     * Impresión directa en pos
     *
     * @param  int $id
     * @param  string $format
     * @return string
     */
    public function toTicket($id, $format = 'ticket')
    {
        $document = DispatchOrder::find($id);

        if (!$document) throw new Exception("El código {$id} es inválido, no se encontro documento relacionado");

        return $this->createPdf($document, $format, $document->filename, 'html');
    }


    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')
                    ->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function (Person $row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->number . ' - ' . $row->name,
                            'seller' => $row->seller,
                            'seller_id' => $row->seller_id,
                            'name' => $row->name,
                            'number' => $row->number,
                            'person_type_id' => $row->person_type_id,
                            'barcode' => $row->barcode,
                            'is_driver' => (bool) $row->is_driver,
                            'identity_document_type_id' => $row->identity_document_type_id,
                            'identity_document_type_code' => $row->identity_document_type->code
                        ];
                    });

                return $customers;

                break;

            case 'items':

                return SearchItemController::getItemsToSaleNote();
                $establishment_id = auth()->user()->establishment_id;
                $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
                // $warehouse_id = ($warehouse) ? $warehouse->id:null;

                $items_u = Item::whereWarehouse()->whereIsActive()->whereNotIsSet()->orderBy('description')->take(20)->get();

                $items_s = Item::where('unit_type_id', 'ZZ')->whereIsActive()->orderBy('description')->take(10)->get();

                $items = $items_u->merge($items_s);

                return collect($items)->transform(function ($row) use ($warehouse) {

                    /** @var Item $row */
                    return $row->getDataToItemModal($warehouse);
                    /* Movido al modelo */
                    $detail = $this->getFullDescription($row, $warehouse);
                    return [
                        'id' => $row->id,
                        'full_description' => $detail['full_description'],
                        'brand' => $detail['brand'],
                        'category' => $detail['category'],
                        'stock' => $detail['stock'],
                        'description' => $row->description,
                        'currency_type_id' => $row->currency_type_id,
                        'currency_type_symbol' => $row->currency_type->symbol,
                        'sale_unit_price' => round($row->sale_unit_price, 2),
                        'purchase_unit_price' => $row->purchase_unit_price,
                        'unit_type_id' => $row->unit_type_id,
                        'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                        'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                        'has_igv' => (bool) $row->has_igv,
                        'lots_enabled' => (bool) $row->lots_enabled,
                        'series_enabled' => (bool) $row->series_enabled,
                        'is_set' => (bool) $row->is_set,
                        'warehouses' => collect($row->warehouses)->transform(function ($row) {
                            return [
                                'warehouse_id' => $row->warehouse->id,
                                'warehouse_description' => $row->warehouse->description,
                                'stock' => $row->stock,
                                'checked' => ($row->warehouse_id == $this->warehouse_id) ? true : false,
                            ];
                        }),
                        'item_unit_types' => $row->item_unit_types,
                        'lots' => [],
                        // 'lots' => $row->item_lots->where('has_sale', false)->where('warehouse_id', $warehouse_id)->transform(function($row) {
                        //     return [
                        //         'id' => $row->id,
                        //         'series' => $row->series,
                        //         'date' => $row->date,
                        //         'item_id' => $row->item_id,
                        //         'warehouse_id' => $row->warehouse_id,
                        //         'has_sale' => (bool)$row->has_sale,
                        //         'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
                        //     ];
                        // }),
                        'lots_group' => collect($row->lots_group)->transform(function ($row) {
                            return [
                                'id'  => $row->id,
                                'code' => $row->code,
                                'quantity' => $row->quantity,
                                'date_of_due' => $row->date_of_due,
                                'checked'  => false
                            ];
                        }),
                        'lot_code' => $row->lot_code,
                        'date_of_due' => $row->date_of_due
                    ];
                });


                break;
            default:

                return [];

                break;
        }
    }


    public function searchItems(Request $request)
    {

        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        $warehouse_id = ($warehouse) ? $warehouse->id : null;
        $items = SearchItemController::getItemsToSaleNote($request);

        return compact('items');
    }


    public function searchItemById($id)
    {
        return  SearchItemController::getItemsToSaleNote(null, $id);
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        $search_item = $this->getItemsNotServicesById($id);

        if (count($search_item) == 0) {
            $search_item = $this->getItemsServicesById($id);
        }

        $items = collect($search_item)->transform(function ($row) use ($warehouse) {
            $detail = $this->getFullDescription($row, $warehouse);
            return [
                'id' => $row->id,
                'full_description' => $detail['full_description'],
                'brand' => $detail['brand'],
                'category' => $detail['category'],
                'stock' => $detail['stock'],
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => round($row->sale_unit_price, 2),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_igv' => (bool)$row->has_igv,
                'lots_enabled' => (bool)$row->lots_enabled,
                'series_enabled' => (bool)$row->series_enabled,
                'is_set' => (bool)$row->is_set,
                'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse) {
                    return [
                        'warehouse_id' => $row->warehouse->id,
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                        'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                    ];
                }),
                'item_unit_types' => $row->item_unit_types,
                'lots' => [],
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked' => false
                    ];
                }),
                'lot_code' => $row->lot_code,
                'date_of_due' => $row->date_of_due
            ];
        });

        return compact('items');
    }


    public function getFullDescription($row, $warehouse)
    {

        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? "{$row->category->name}" : "";
        $brand = ($row->brand) ? "{$row->brand->name}" : "";

        if ($row->unit_type_id != 'ZZ') {
            $warehouse_stock = ($row->warehouses && $warehouse) ? number_format($row->warehouses->where('warehouse_id', $warehouse->id)->first() != null ? $row->warehouses->where('warehouse_id', $warehouse->id)->first()->stock : 0, 2) : 0;
            $stock = ($row->warehouses && $warehouse) ? "{$warehouse_stock}" : "";
        } else {
            $stock = '';
        }


        $desc = "{$desc} - {$brand}";

        return [
            'full_description' => $desc,
            'brand' => $brand,
            'category' => $category,
            'stock' => $stock,
        ];
    }


    public function searchCustomerById($id)
    {
        return $this->searchClientById($id);
    }

    public function option_tables()
    {
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id', $establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->where('active', true)->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $sellers = User::GetSellers(false)->get();
        $configuration = Configuration::select(['restrict_sale_items_cpe', 'global_discount_type_id'])->first();
        $global_discount_types = ChargeDiscountType::getGlobalDiscounts();

        return compact('series', 'document_types_invoice', 'payment_method_types', 'payment_destinations', 'sellers', 'configuration', 'global_discount_types');
    }

    public function email(Request $request)
    {
        $company = Company::active();
        $record = DispatchOrder::find($request->input('id'));
        $customer_email = $request->input('customer_email');

        $email = $customer_email;
        $mailable = new SaleNoteEmail($company, $record);
        $id = (int) $request->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 2);
        /*
        Configuration::setConfigSmtpMail();
        $array_email = explode(',', $customer_email);
        if (count($array_email) > 1) {
            foreach ($array_email as $email_to) {
                $email_to = trim($email_to);
                if(!empty($email_to)) {
                    Mail::to($email_to)->send(new SaleNoteEmail($company, $record));
                }
            }
        } else {
            Mail::to($customer_email)->send(new SaleNoteEmail($company, $record));
        }*/

        return [
            'success' => true
        ];
    }










    public function downloadExternal($external_id, $format = 'a4')
    {
        $document = DispatchOrder::where('external_id', $external_id)->first();
        $this->reloadPDF($document, $format, null);
        return $this->downloadStorage($document->filename, 'dispatch_order');
    }




    /**
     * Proceso de duplicar una nota de venta por post
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function duplicate(Request $request)
    {
        // return $request->id;
        $obj = DispatchOrder::find($request->id);
        $this->dispatch_order = $obj->replicate();
        $this->dispatch_order->external_id = Str::uuid()->toString();
        $this->dispatch_order->state_type_id = '01';
        $this->dispatch_order->number = DispatchOrder::getLastNumberByModel($obj);
        $this->dispatch_order->unique_filename = null;

        $this->dispatch_order->changed = false;
        $this->dispatch_order->document_id = null;

        $this->dispatch_order->save();

        foreach ($obj->items as $row) {
            $new = $row->replicate();
            $new->dispatch_order_id = $this->dispatch_order->id;
            $new->save();
        }

        $this->setFilename();

        return [
            'success' => true,
            'data' => [
                'id' => $this->dispatch_order->id,
            ],
        ];
    }





    /**
     * Retorna items para generar json en checkout de hoteles
     *
     * @param Request $request
     * @return array
     */
    public function getItemsByIds(Request $request)
    {
        return SearchItemController::TransformToModalSaleNote(Item::whereIn('id', $request->ids)->get());
    }
}
