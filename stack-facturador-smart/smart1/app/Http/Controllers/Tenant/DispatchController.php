<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Requests\Inputs\DispatchInput;
use App\Exports\DispatchExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DispatchRequest;
use App\Http\Resources\Tenant\DispatchCollection;
use App\Models\System\Client;
use App\Models\Tenant\AuditorHistory;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\TransferReasonType;
use App\Models\Tenant\Catalogs\TransportModeType;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Series;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ApiPeruDev\Http\Controllers\ServiceDispatchController;
use Modules\Dispatch\Http\Controllers\DispatcherController;
use Modules\Dispatch\Http\Controllers\DriverController;
use Modules\Dispatch\Http\Controllers\OriginAddressController;
use Modules\Dispatch\Http\Controllers\TransportController;
use Modules\Dispatch\Models\DeliveryAddress;
use Modules\Dispatch\Models\Dispatcher;
use Modules\Dispatch\Models\Driver;
use Modules\Dispatch\Models\OriginAddress;
use Modules\Dispatch\Models\Transport;
use Modules\Document\Traits\SearchTrait;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Inventory\Models\Warehouse as ModuleWarehouse;
use Modules\Order\Http\Resources\DispatchResource;
use Modules\Order\Mail\DispatchEmail;
use Modules\Order\Models\OrderNote;
use App\Models\Tenant\PaymentCondition;
use App\Models\Tenant\Catalogs\RelatedDocumentType;
use App\Models\Tenant\DispatchOrder;
use App\Models\Tenant\InventoryReference;
use App\Models\Tenant\ProductionOrder;
use App\Models\Tenant\StateType;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use App\Services\PseServiceDispatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Modules\BusinessTurn\Models\BusinessTurn;

/**
 * Class DispatchController
 *
 * @package App\Http\Controllers\Tenant
 * @mixin Controller
 */
class DispatchController extends Controller
{
    use FinanceTrait;
    use SearchTrait;
    use StorageDocument;

    public function __construct()
    {
        $this->middleware('input.request:dispatch,web', ['only' => ['store']]);
    }
    public function message_whatsapp($document_id)
    {
        $document = Dispatch::find($document_id);
        $message = "Estimd@: *" . $document->customer->name . "* \n";
        $message .= "Informamos que su comprobante electrónico ha sido emitido exitosamente.\n";
        $message .= "Los datos de su comprobante electrónico son:\n";
        $message .= "Razón social: *" . $document->customer->name . "* \n";
        $message .= "Fecha de emisión: *" . \Carbon\Carbon::parse($document->date_of_issue)->format('d-m-Y') . "* \n";
        $message .= "Nro. de comprobante: *" . $document->series . "-" . $document->number . "* \n";
        $message .= "Total: *" . number_format($document->total, 2, ".", "") . "*";
        return [
            "message" => $message,
            "success" => true,
        ];
    }
    public function index(Request $request)
    {
        $type = $request->type;
        $configuration = Configuration::getPublicConfig();
        $isAuditor = auth()->user()->type == 'superadmin';
        $document_state_types = StateType::getStateTypes();
        return view('tenant.dispatches.index', compact('configuration', 'isAuditor', 'document_state_types', 'type'));
    }

    public function columns()
    {
        return [
            'number' => 'Número'
        ];
    }

    public function download_file($id)
    {
        $dispatch = Dispatch::find($id);
        $pse = new PseServiceDispatch($dispatch);
        $res = $pse->download_file();

        return $res;
    }
    public function auditorHistory(Request $request)
    {
        $document_id = $request->dispatch_id;
        $auditor_history = AuditorHistory::where('dispatch_id', $document_id)
            ->orderBy('created_at', 'desc')
            ->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'user_id' => $row->user_id,
                    'user_name' => $row->user->name,
                    'new_state_type_id' => $row->new_state_type_id,
                    'new_value' => $row->newStateType->description,
                    'old_state_type_id' => $row->old_state_type_id,
                    'old_value' => $row->oldStateType->description,
                    'is_edit' => (bool)$row->is_edit,
                    'is_recreate' => (bool)$row->is_recreate,
                    'is_anulate' => (bool)$row->is_anulate,
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return $auditor_history;
    }
    public function exportExcel(Request $request)
    {
        $records = $this->getRecords($request);
        $records = $records->get();

        return (new DispatchExport)
            ->records($records)
            ->download('Reporte_Guia_Remision_' . Carbon::now()->format('Y-m-d') . '.xlsx');
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        return new DispatchCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function internalVoided($id)
    {
        $dispatch = Dispatch::find($id);
        if($dispatch->state_type_id == '55'){
            $dispatch->state_type_id = '56';
        }else{
            $dispatch->state_type_id = '11';
        }
        $dispatch->save();
        $message = "Guia anulada internamente, recuerde hacer la anulación por el portal de SUNAT.";
        if($dispatch->state_type_id == '56'){
            $message = "Orden de entrega anulada internamente";
        }
        return [
            'success' => true,
            'message' => $message
        ];
    }
    public function change_state($state_id,  $document_id)

    {

        try {
            DB::connection('tenant')->beginTransaction();
            $document = Dispatch::find($document_id);
            $user_id = auth()->id();
            $new_history = new AuditorHistory;
            $new_history->user_id = $user_id;
            $new_history->dispatch_id = $document_id;
            $new_history->new_state_type_id = $state_id;
            $new_history->old_state_type_id = $document->state_type_id;
            $new_history->save();
            $document->state_type_id = $state_id;
            // if ($state_id == '05') {
            //     $document_items = DispatchItem::where('document_id', $document_id)->get();
            //     foreach ($document_items as $item) {
            //         $this->document_item_restore($item);
            //         $this->recalculateStock($item->item_id);
            //     }
            // }
            $document->auditor_state = 1;
            $document->save();
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Estado cambiado'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function getRecords($request)
    {
        $type = $request->type;
        $d_end = $request->d_end;
        $d_start = $request->d_start;
        $number = $request->number;
        $emission_date = $request->emission_date;
        $series = $request->series;
        $input = $request->input;
        $customer_id = $request->customer_id;
        $inventory_reference_id = $request->inventory_reference_id;
        $query = Dispatch::query();
        if ($type == 'internal') {
            $query->whereInternal();
        } else {
            $query->whereNotInternal();
        }
        if ($d_start && $d_end) {

            $query = $query->where('document_type_id', '09')
                ->where('series', 'like', '%' . $series . '%')
                ->whereBetween('date_of_issue', [$d_start, $d_end]);
        } else {

            $query = $query->where('document_type_id', '09')
                ->where('series', 'like', '%' . $series . '%');
        }
    
        if ($emission_date) {
            $query->where('date_of_issue', $emission_date);
        }
        if ($number) {
            $query->where('number', $number);
        }

        if ($inventory_reference_id) {
            $query->where('inventory_reference_id', $inventory_reference_id);
        }

        if ($customer_id) {
            $query->where('customer_id', $customer_id);
        }

        if ($input && $input != 'null' && $input != 'undefined' && $input != '') {
            $query->whereHas('person', function ($query) use ($input) {
                $query->where('name', 'like', "%{$input}%")
                    ->orWhere('number', 'like', "%{$input}%");
            });
        }

        return $query->latest();
    }

    public function ticket_dispatch($id)
    {
        $document = Dispatch::find($id);
        $company = Company::active();
        $establishment = Establishment::where('id', $document->establishment_id)->first();
        $pdf = Pdf::loadView('tenant.dispatches.dispatch_ticket', compact("document", "company", "establishment"));
        $filename = "Guia_Remision_" . $document->series . "-" . $document->number;
        $pdf->setPaper(array(0, 0, 249.45, 450), 'portrait')
            ->setOption('margin-top', 0);
        return $pdf->stream($filename . '.pdf');
    }
    public function send_pse($id)
    {
        $document = Dispatch::find($id);
        $pse = new PseServiceDispatch($document);

        $response = $pse->sendToPse();

        return $response;
    }
    public function json_pse($id)
    {
        $dispatch = Dispatch::find($id);
        $filename = $dispatch->getNumberFullAttribute() . '.json';
        $pse = new PseServiceDispatch($dispatch);


        $payload = $pse->payloadToJson();
        $response = response()->make($payload);
        $response->header('Content-Disposition', 'attachment; filename=' . $filename);
        $response->header('Content-Type', 'application/json');

        return $response;
    }
    public function data_table()
    {
        $customers = Person::whereType('customers')->orderBy('name')->take(20)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });

        $series = Series::where('document_type_id', '09')->get();
        $references = InventoryReference::all();
        return compact('customers', 'series', 'references');
    }


    public function create(Request $request, $document_id = null, $type = null, $dispatch_id = null)
    {
        $request_type = $request->type;
        $series = Series::where('document_type_id', "09")->where('establishment_id', auth()->user()->establishment_id)->first();
        $series_default = $series->number;
        if ($type == 'q') {
            $document = Quotation::find($document_id);
        } elseif ($type == 'on') {
            $document = OrderNote::find($document_id);
        } else {
            $type = 'i';
            $document = Document::find($document_id);
        }
        if (!$document) {
            return view('tenant.dispatches.create', compact('series_default', 'request_type'));
        }

        $configuration = Configuration::query()->first();


        $items = [];
        foreach ($document->items as $item) {
            $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->name_product_pdf) : null;
            $items[] = [
                'item_id' => $item->item_id,
                'item' => $item,
                'quantity' => $item->quantity,
                'description' => $item->item->description,
                'name_product_pdf' => $name_product_pdf
            ];
        }


        $dispatch = Dispatch::find($dispatch_id);
        return view('tenant.dispatches.form', compact('document', 'items', 'type', 'dispatch', 'series_default', 'request_type' ));
    }
    function getQuantity($document_id, $item_id)
    {
        $quantity = DispatchItem::whereHas('dispatch', function ($query) use ($document_id) {
            $query->where('reference_document_id', $document_id);
        })->where('item_id', $item_id)->sum('quantity');
        return -1 * $quantity;
    }
    function getQuantitySaleNote($sale_note_id, $item_id)
    {
        $quantity = DispatchItem::whereHas('dispatch', function ($query) use ($sale_note_id) {
            $query->where('reference_sale_note_id', $sale_note_id);
        })->where('item_id', $item_id)->sum('quantity');
        return -1 * $quantity;
    }
    public function createNewApi( $parentTable, $parentId)
    {
        $query = null;
        $reference_document_id = null;
        $reference_quotation_id = null;
        $reference_sale_note_id = null;
        $reference_order_form_id = null;
        $reference_order_note_id = null;
        $reference_dispatch_order_id = null;

        if ($parentTable === 'document') {
            $reference_document_id = $parentId;
            $query = Document::query();
        } elseif ($parentTable === 'quotation') {
            $reference_quotation_id = $parentId;
            $query = Quotation::query();
        } elseif ($parentTable === 'sale_note') {
            $reference_sale_note_id = $parentId;
            $query = SaleNote::query();
        } elseif ($parentTable === 'order_note') {
            $reference_order_note_id = $parentId;
            $query = OrderNote::query();
        } elseif ($parentTable === 'dispatch') {
            $query = Dispatch::query();
        } elseif ($parentTable === 'dispatch_order') {
            $reference_dispatch_order_id = $parentId;
            $query = DispatchOrder::query();
        }
        $document = $query->find($parentId);
        $configuration = Configuration::query()->first();
        $items = [];
        foreach ($document->items as $item) {
            $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->name_product_pdf) : null;
            if ($parentTable === 'document' && $document->no_stock) {
                $quantity = $item->quantity + $this->getQuantity($parentId, $item->item_id);
            } elseif ($parentTable === 'sale_note' && $document->no_stock) {
                $quantity = $item->quantity + $this->getQuantitySaleNote($parentId, $item->item_id);
            } else {
                $quantity = $item->quantity;
            }
            $weight =  null;

            try {
                if (isset($item->attributes)) {
                    foreach ($item->attributes as $attribute) {
                        if ($attribute->attribute_type_id == '5031') {
                            $weight += floatval($attribute->value) * floatval($quantity);
                        }
                    }
                }
            } catch (Exception $e) {
            }

            $items[] = [
                'id' => $item->item_id,
                'item_id' => $item->item_id,
                'item' => $item->item,
                'quantity' => $quantity,
                'description' => $item->item->description,
                'unit_type_id' => $item->item->unit_type_id,
                'name_product_pdf' => $name_product_pdf,
                'weight' => $weight,
            ];
        }

        if ($parentTable === 'dispatch') {
            $transport_id = $document->transport_id;
            if ($transport_id == null) {
                $transport_data = $document->transport_data;
                if ($transport_data) {
                    $plate_number = $transport_data['plate_number'];
                    $model = $transport_data['model'];
                    $brand = $transport_data['brand'];
                    $transport = Transport::where('plate_number', $plate_number)->where('model', $model)->where('brand', $brand)->first();
                    if ($transport) {
                        $transport_id = $transport->id;
                    }
                }
            }
            $data = [
                'id' => $document->id,
                'series' => $document->series,
                'number' => $document->number,
                'establishment_id' => $document->establishment_id,
                'customer_id' => $document->customer_id,
                'items' => $items,
                'date_of_issue' => $document->date_of_issue->format('Y-m-d'),
                'date_of_shipping' => $document->date_of_shipping->format('Y-m-d'),
                'packages_number' => $document->packages_number,
                'total_weight' => $document->total_weight,
                'transfer_reason_type_id' => $document->transfer_reason_type_id,
                'transfer_reason_description' => $document->transfer_reason_description,
                'transport_mode_type_id' => $document->transport_mode_type_id,
                'transshipment_indicator' => $document->transshipment_indicator,
                'unit_type_id' => $document->unit_type_id,
                'observations' => $document->observations,
                'driver_id' => $document->driver_id,
                'dispatcher_id' => $document->dispatcher_id,
                'transport_id' => $transport_id,
                'origin_address_id' => $document->origin_address_id,
                'delivery_address_id' => $document->delivery_address_id,
                'is_transport_category_m1l' => $document->is_transport_category_m1l,
                'plate_number' => $document->plate_number,
                'website_id' => $document->website_id,
            ];
        } else {
            $data = [
                'website_id' =>  isset($document->website_id) ? $document->website_id : null,
                'purchase_order' => isset($document->purchase_order) ? $document->purchase_order : null,
                'establishment_id' => $document->establishment_id,
                'customer_id' => $document->customer_id,
                'customer_number' => $document->customer->number,
                'items' => $items,
                'reference_document_id' => $reference_document_id,
                'reference_quotation_id' => $reference_quotation_id,
                'reference_sale_note_id' => $reference_sale_note_id,
                'reference_order_form_id' => $reference_order_form_id,
                'reference_order_note_id' => $reference_order_note_id,
                'reference_dispatch_order_id' => $reference_dispatch_order_id,
            ];
        }

        return [
            'document' => $data,
            'parentTable' => $parentTable,
            'parentId' => $parentId
        ];
    }
    public function createNew(Request $request,$parentTable, $parentId)
    {
        $query = null;
        $type = $request->type;
        $reference_document_id = null;
        $reference_quotation_id = null;
        $reference_sale_note_id = null;
        $reference_order_form_id = null;
        $reference_order_note_id = null;
        $reference_dispatch_order_id = null;

        if ($parentTable === 'document') {
            $reference_document_id = $parentId;
            $query = Document::query();
        } elseif ($parentTable === 'quotation') {
            $reference_quotation_id = $parentId;
            $query = Quotation::query();
        } elseif ($parentTable === 'sale_note') {
            $reference_sale_note_id = $parentId;
            $query = SaleNote::query();
        } elseif ($parentTable === 'order_note') {
            $reference_order_note_id = $parentId;
            $query = OrderNote::query();
        } elseif ($parentTable === 'dispatch') {
            $query = Dispatch::query();
        } elseif ($parentTable === 'dispatch_order') {
            $reference_dispatch_order_id = $parentId;
            $query = DispatchOrder::query();
        }
        $document = $query->find($parentId);
        $configuration = Configuration::query()->first();
        $items = [];
        foreach ($document->items as $item) {
            $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->name_product_pdf) : null;
            if ($parentTable === 'document' && $document->no_stock) {
                $quantity = $item->quantity + $this->getQuantity($parentId, $item->item_id);
            } elseif ($parentTable === 'sale_note' && $document->no_stock) {
                $quantity = $item->quantity + $this->getQuantitySaleNote($parentId, $item->item_id);
            } else {
                $quantity = $item->quantity;
            }
            $weight =  null;

            try {
                if (isset($item->attributes)) {
                    foreach ($item->attributes as $attribute) {
                        if ($attribute->attribute_type_id == '5031') {
                            $weight += floatval($attribute->value) * floatval($quantity);
                        }
                    }
                }
            } catch (Exception $e) {
            }

            $items[] = [
                'id' => $item->item_id,
                'item_id' => $item->item_id,
                'item' => $item->item,
                'quantity' => $quantity,
                'description' => $item->item->description,
                'unit_type_id' => $item->item->unit_type_id,
                'name_product_pdf' => $name_product_pdf,
                'weight' => $weight,
            ];
        }

        if ($parentTable === 'dispatch') {
            $transport_id = $document->transport_id;
            if ($transport_id == null) {
                $transport_data = $document->transport_data;
                if ($transport_data) {
                    $plate_number = $transport_data['plate_number'];
                    $model = $transport_data['model'];
                    $brand = $transport_data['brand'];
                    $transport = Transport::where('plate_number', $plate_number)->where('model', $model)->where('brand', $brand)->first();
                    if ($transport) {
                        $transport_id = $transport->id;
                    }
                }
            }
            $data = [
                'id' => $document->id,
                'series' => $document->series,
                'number' => $document->number,
                'establishment_id' => $document->establishment_id,
                'customer_id' => $document->customer_id,
                'items' => $items,
                'date_of_issue' => $document->date_of_issue->format('Y-m-d'),
                'date_of_shipping' => $document->date_of_shipping->format('Y-m-d'),
                'packages_number' => $document->packages_number,
                'total_weight' => $document->total_weight,
                'transfer_reason_type_id' => $document->transfer_reason_type_id,
                'transfer_reason_description' => $document->transfer_reason_description,
                'transport_mode_type_id' => $document->transport_mode_type_id,
                'transshipment_indicator' => $document->transshipment_indicator,
                'unit_type_id' => $document->unit_type_id,
                'observations' => $document->observations,
                'driver_id' => $document->driver_id,
                'dispatcher_id' => $document->dispatcher_id,
                'transport_id' => $transport_id,
                'origin_address_id' => $document->origin_address_id,
                'delivery_address_id' => $document->delivery_address_id,
                'is_transport_category_m1l' => $document->is_transport_category_m1l,
                'plate_number' => $document->plate_number,
                'website_id' => $document->website_id,
            ];
        } else {
            $data = [
                'website_id' =>  isset($document->website_id) ? $document->website_id : null,
                'purchase_order' => isset($document->purchase_order) ? $document->purchase_order : null,
                'establishment_id' => $document->establishment_id,
                'customer_id' => $document->customer_id,
                'items' => $items,
                'reference_document_id' => $reference_document_id,
                'reference_quotation_id' => $reference_quotation_id,
                'reference_sale_note_id' => $reference_sale_note_id,
                'reference_order_form_id' => $reference_order_form_id,
                'reference_order_note_id' => $reference_order_note_id,
                'reference_dispatch_order_id' => $reference_dispatch_order_id,
            ];
        }
        $series = Series::where('document_type_id', "09")->where('establishment_id', auth()->user()->establishment_id)->first();

        return view('tenant.dispatches.form', [
            'document' => $data,
            'series_default' => $series->number,
            'parentTable' => $parentTable,
            'parentId' => $parentId,
            'type' => $type
        ]);
    }

    public function generate($sale_note_id)
    {
        $sale_note = SaleNote::findOrFail($sale_note_id);
        $type = null;
        $document = $sale_note;
        $dispatch = null;
        $configuration = Configuration::query()->first();
        $items = [];
        foreach ($document->items as $item) {
            $name_product_pdf = ($configuration->show_pdf_name) ? strip_tags($item->name_product_pdf) : null;
            $items[] = [
                'item_id' => $item->item_id,
                'item' => $item,
                'quantity' => $item->quantity,
                'description' => $item->item->description,
                'name_product_pdf' => $name_product_pdf
            ];
        }
        //
        return view('tenant.dispatches.form', compact('document', 'type', 'dispatch', 'items'));
    }

    public function sendDispatchToSunat(Dispatch $document)
    {

        $data = [
            'sent' => false,
            'code' => null,
            'description' => "El elemento ya fue enviado",
        ];
        if (!$document->wasSend()) {
            $facturalo = $document->getFacturalo();

            $facturalo
                ->setActions(['send_xml_signed' => true])
                ->loadXmlSigned()
                ->senderXmlSignedBill();
            $data = $facturalo->getResponse();
        }

        return json_encode($data);
    }
    function checkQuantity($request)
    {
        $reference_document_id = $request->reference_document_id;
        if ($reference_document_id == null) {
            return;
        }
        $document = Document::find($reference_document_id);
        if ($document->no_stock == false) {
            return;
        }
        $items = $request->items;
        foreach ($items as $item) {
            $quantity = $item['quantity'];
            $item_id = $item['item_id'];
            $quantity_document_item = $document->items()->where('item_id', $item_id)->first()->quantity;
            $quantity_dispatch = DispatchItem::whereHas('dispatch', function ($query) use ($reference_document_id) {
                $query->where('reference_document_id', $reference_document_id);
            })->where('item_id', $item_id)->sum('quantity');
            if ($quantity_document_item < $quantity + $quantity_dispatch) {
                $description = $item["item"]["description"];
                throw new Exception("La cantidad del producto {$description} no puede ser mayor a la cantidad del documento {$document->series}-{$document->number}");
            }
        }
    }
    public function store(DispatchRequest $request)
    {


        // $company = Company::query()
        //     ->select(['soap_type_id', 'pse'])
        //     ->first();
        $configuration = Configuration::first();
        $company_id = $request->input('website_id');
        if ($company_id) {
            $company = Company::where('website_id', $company_id)->first();
        } else {
            $company = Company::active();
        }

        $res = [];
        if ($request->series[0] == 'T') {
            try {
                $this->checkQuantity($request);
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
            /** @var Facturalo $fact */
            $fact = DB::connection('tenant')->transaction(function () use ($request, $company) {
                $facturalo = new Facturalo($company);
                $facturalo->save($request->all());
                $document = $facturalo->getDocument();
                $data = (new ServiceDispatchController())->getData($document->id);
                $facturalo->setXmlUnsigned((new ServiceDispatchController())->createXmlUnsigned($data));
                if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                    $facturalo->sendPseNewDispatch();
                } else {
                    $facturalo->signXmlUnsigned();
                }
                $facturalo->createPdf();

                return $facturalo;
            });

            $document = $fact->getDocument();
            //            if ($company->soap_type_id === '02') {
            //                $res = ((new ServiceDispatchController())->send($document->external_id));
            //            }
            // $response = $fact->getResponse();
        } else {
            /** @var Facturalo $fact */
            $fact =  DB::connection('tenant')->transaction(function () use ($request) {
                $facturalo = new Facturalo();
                $facturalo->save($request->all());
                $facturalo->createPdf();

                return $facturalo;
            });

            $document = $fact->getDocument();
            // $response = $fact->getResponse();
        }

        if (!empty($document->reference_document_id) && $configuration->getUpdateDocumentOnDispaches()) {
            $reference = Document::find($document->reference_document_id);
            if (!empty($reference)) {
                $reference->updatePdfs();
            }
        }

        $message = "Se creo la guía de remisión {$document->series}-{$document->number}";

        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $document->id,
                'external_id' => $document->external_id,
                'send_sunat' => $configuration->auto_send_dispatchs_to_sunat && (!$company->pse || ($company->pse && $company->type_send_pse == 1)),
            ],
        ];
    }
    public function tablesCompany($id)
    {
        $company = Company::where('website_id', $id)->first();
        $company_active = Company::active();
        $document_number = $company->document_number;
        $website_id = $company->website_id;
        $user = auth()->user()->id;
        $user_to_save = User::find($user);
        $user_to_save->company_active_id = $website_id;
        $user_to_save->save();
        $payment_destinations = $this->getPaymentDestinations();
        if ($website_id && $company->id != $company_active->id) {
            $hostname = Hostname::where('website_id', $website_id)->first();
            $client = Client::where('hostname_id', $hostname->id)->first();
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
        }
        $establishment = Establishment::find(1);
        $establishment_info = EstablishmentInput::set($establishment->id);
        // $series = Series::where('establishment_id', $establishment->id)->get();
        $series = Series::where('establishment_id', $establishment->id)->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'contingency' => (bool)$row->contingency,
                    'document_type_id' => $row->document_type_id,
                    'establishment_id' => $row->establishment_id,
                    'number' => $row->number,
                ];
            });
        // $series = Series::FilterSeries(1)
        //     ->get()
        //     ->transform(function ($row)  use ($document_number) {
        //         /** @var Series $row */
        //         return $row->getCollectionData2($document_number);
        //     })->where('disabled', false);
        return [
            'success' => true,
            'data' => $company,
            'payment_destinations' => $payment_destinations,
            'series' => $series,
            'establishment' => $establishment_info,
        ];
    }
    /**
     * Tables
     *
     * @param Request $request
     *
     * @return array
     */
    public function tables(Request $request)
    {
        $current_user = auth()->user() ?? auth('api')->user();
        $is_from_api = request()->is('api/*');
        $requestType = $request->requestType;
        $is_seller = $current_user->type == 'seller';
        $customer_default_id = null;
        $customer = Person::where('type', 'customers')->where('name', 'LIKE', "clientes varios")->first();
        if ($customer) {
            $customer_default_id = $customer->id;
        } else {
            $customer = Person::where('type', 'customers')->first();
            $customer_default_id = $customer->id;
        }
        $itemsFromSummary = null;
        if ($request->itemIds) {
            $itemsFromSummary = Item::query()
                ->with('lots_group')
                ->whereIn('id', $request->itemIds)
                ->where('item_type_id', '01')
                ->orderBy('description')
                ->get()
                ->transform(function ($row) {
                    $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;

                    return [
                        'id' => $row->id,
                        'attribute_enabled' => $row->attribute_enabled,
                        'full_description' => $full_description,
                        'description' => $row->description,
                        'model' => $row->model,
                        'internal_id' => $row->internal_id,
                        'currency_type_id' => $row->currency_type_id,
                        'currency_type_symbol' => $row->currency_type->symbol,
                        'sale_unit_price' => $row->sale_unit_price,
                        'purchase_unit_price' => $row->purchase_unit_price,
                        'unit_type_id' => $row->unit_type_id,
                        'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                        'attributes' => $row->attributes ? $row->attributes : [],
                        'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                        'has_igv' => $row->has_igv,
                        'lots_group' => $row->lots_group->each(function ($lot) {
                            return [
                                'id' => $lot->id,
                                'code' => $lot->code,
                                'quantity' => $lot->quantity,
                                'date_of_due' => $lot->date_of_due,
                                'checked' => false,
                                'warehouse_id' => $lot->warehouse_id,
                                'warehouse' => $lot->warehouse_id ? $lot->warehouse->description : null,
                            ];
                        }),
                        'lots' => [],
                        'lots_enabled' => (bool)$row->lots_enabled,
                    ];
                });
        }
        $currentItem = $request->current_item;
        $items = Item::query()
            ->with('lots_group')
            ->where('item_type_id', '01')
            ->orderBy('description')
            ->take(20)
            ->get()
            ->transform(function ($row) {
                $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
                return [
                    'id' => $row->id,
                    'attribute_enabled' => $row->attribute_enabled,
                    'full_description' => $full_description,
                    'description' => $row->description,
                    'model' => $row->model,
                    'internal_id' => $row->internal_id,
                    'currency_type_id' => $row->currency_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => $row->sale_unit_price,
                    'purchase_unit_price' => $row->purchase_unit_price,
                    'unit_type_id' => $row->unit_type_id,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'attributes' => $row->attributes ? $row->attributes : [],
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'has_igv' => $row->has_igv,
                    'lots_group' => $row->lots_group->each(function ($lot) {
                        return [
                            'id' => $lot->id,
                            'code' => $lot->code,
                            'quantity' => $lot->quantity,
                            'date_of_due' => $lot->date_of_due,
                            'checked' => false
                        ];
                    }),
                    'lots' => [],
                    'lots_enabled' => (bool)$row->lots_enabled,
                    'warehouses' => $row->getDataWarehouses(),
                ];
            });
        if ($currentItem) {
            $exist = $items->firstWhere('id', $currentItem);
            if (!$exist) {
                $newItem = Item::find($currentItem);

                if ($newItem) {
                    $full_description = ($newItem->internal_id) ? $newItem->internal_id . ' - ' . $newItem->description : $newItem->description;

                    $newItemData = [
                        'id' => $newItem->id,
                        'full_description' => $full_description,
                        'description' => $newItem->description,
                        'model' => $newItem->model,
                        'internal_id' => $newItem->internal_id,
                        'currency_type_id' => $newItem->currency_type_id,
                        'currency_type_symbol' => $newItem->currency_type->symbol,
                        'sale_unit_price' => $newItem->sale_unit_price,
                        'purchase_unit_price' => $newItem->purchase_unit_price,
                        'unit_type_id' => $newItem->unit_type_id,
                        'sale_affectation_igv_type_id' => $newItem->sale_affectation_igv_type_id,
                        'attributes' => $newItem->attributes ? $newItem->attributes : [],
                        'purchase_affectation_igv_type_id' => $newItem->purchase_affectation_igv_type_id,
                        'has_igv' => $newItem->has_igv,
                        'lots_group' => $newItem->lots_group->each(function ($lot) {
                            return [
                                'id' => $lot->id,
                                'code' => $lot->code,
                                'quantity' => $lot->quantity,
                                'date_of_due' => $lot->date_of_due,
                                'checked' => false
                            ];
                        }),
                        'lots' => [],
                        'lots_enabled' => (bool)$newItem->lots_enabled,
                        'warehouses' => $newItem->getDataWarehouses(),
                    ];

                    $items->push($newItemData);
                }
            }
        }

        $identities = ['6', '4', '1', '0'];

        // $dni_filter = config('tenant.document_type_03_filter');
        // if($dni_filter){
        //     array_push($identities, '1');
        // }

        $customers = Person::with('addresses')
            ->whereIn('identity_document_type_id', $identities)
            ->whereType('customers')
            ->orderBy('name')
            ->whereIsEnabled();
            if(!$is_from_api){
                $customers = $customers->take(20);
            }
            $customers = $customers->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'trade_name' => $row->trade_name,
                    'country_id' => $row->country_id,
                    'address' => $row->address,
                    'addresses' => $row->addresses,
                    'email' => $row->email,
                    'telephone' => $row->telephone,
                    'number' => $row->number,
                    'district_id' => $row->district_id,
                    'department_id' => $row->department_id,
                    'province_id' => $row->province_id,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code
                ];
            });
        $suppliers = Person::with('addresses')
            ->whereIn('identity_document_type_id', $identities)
            ->whereType('suppliers')
            ->orderBy('name')
            ->whereIsEnabled()
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'trade_name' => $row->trade_name,
                    'country_id' => $row->country_id,
                    'address' => $row->address,
                    'addresses' => $row->addresses,
                    'email' => $row->email,
                    'telephone' => $row->telephone,
                    'number' => $row->number,
                    'district_id' => $row->district_id,
                    'department_id' => $row->department_id,
                    'province_id' => $row->province_id,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code
                ];
            });
        $countries = func_get_countries();
        $locations = func_get_locations();
        $identityDocumentTypes = func_get_identity_document_types();

        $transferReasonTypes = TransferReasonType::whereActive()->get();
        $transportModeTypes = TransportModeType::whereActive()->get();
        $unitTypes = UnitType::query()
            ->where('active', true)
            ->whereIn('id', ['KGM', 'TNE'])->get()->transform(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => func_str_to_upper_utf8($r->description)
                ];
            });
        $configuration = Configuration::select('multi_companies', 'seller_establishments_all')->first();
        $establishments = $is_seller && !$configuration->seller_establishments_all ? Establishment::where('id', $current_user->establishment_id)->get() : Establishment::whereActive()->get();
        $series = $is_seller && !$configuration->seller_establishments_all ? Series::where('establishment_id', $current_user->establishment_id) : Series::query();
        if ($requestType == 'internal') {
            $series = $series->where('internal', 1)->get();
        }else{
            $series = $series->where('internal', 0)->get();
        }
        $company = Company::select('number', 'pse')->first();
        $drivers = (new DriverController())->getOptions();
        $transports = (new TransportController())->getOptions();
        $dispatchers = (new DispatcherController())->getOptions();
        $related_document_types = RelatedDocumentType::get();
        $references = InventoryReference::all();
        $companies = [];
        if ($configuration->multi_companies) {
            $companies = Company::all();
        }
        $user_establishment_id = $current_user->establishment_id;
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        return compact(
            'user_establishment_id',
            'customer_default_id',
            'is_integrate_system',
            'companies',
            'references',
            'establishments',
            'customers',
            'series',
            'transportModeTypes',
            'transferReasonTypes',
            'unitTypes',
            'countries',
            'suppliers',
            // 'departments',
            // 'provinces',
            // 'districts',
            'identityDocumentTypes',
            'items',
            'locations',
            'company',
            'drivers',
            'dispatchers',
            'transports',
            'related_document_types',
            'itemsFromSummary'
        );
    }
    public function dispatchSeries()
    {
        $series = Series::whereIn('document_type_id', ['09', '31'])->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'contingency' => $row->contingency,
                    'document_type_id' => $row->document_type_id,
                    'establishment_id' => $row->establishment_id,
                    'number' => $row->number,
                ];
            });

        return [
            'success' => true,
            'data' => $series,
        ];
    }
    public function downloadExternal($type, $external_id)
    {
        $retention = Dispatch::where('external_id', $external_id)->first();

        if (!$retention) {
            throw new Exception("El código {$external_id} es inválido, no se encontro documento relacionado");
        }

        switch ($type) {
            case 'pdf':
                $folder = 'pdf';
                break;
            case 'xml':
                $folder = 'signed';
                break;
            case 'cdr':
                $folder = 'cdr';
                break;
            default:
                throw new Exception('Tipo de archivo a descargar es inválido');
        }

        return $this->downloadStorage($retention->filename, $folder);
    }

    public function record($id)
    {
        $record = new DispatchResource(Dispatch::findOrFail($id));

        return $record;
    }

    public function email(Request $request)
    {
        $record = Dispatch::find($request->input('id'));
        $customer_email = $request->input('customer_email');
        $email = $customer_email;
        $mailable = new DispatchEmail($record);
        $id = $request->input('id');
        $model = __FILE__ . ";;" . __LINE__;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 4);
        /*
        Configuration::setConfigSmtpMail();
        $array_email = explode(',', $customer_email);
        if (count($array_email) > 1) {
            foreach ($array_email as $email_to) {
                $email_to = trim($email_to);
                if(!empty($email_to)) {
                    Mail::to($email_to)->send(new DispatchEmail($record));
                }
            }
        } else {
            Mail::to($customer_email)->send(new DispatchEmail($record));
        }
        */
        return [
            'success' => true
        ];
    }

    public function generateDocumentTables($id)
    {

        $configuration = Configuration::first();
        $dispatch = Dispatch::findOrFail($id);
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $establishment_id = $establishment->id;
        $warehouse = ModuleWarehouse::where('establishment_id', $establishment_id)->first();
        $relation_external_document = $dispatch->getRelationExternalDocument();
        $set_unit_price_dispatch_related_record = Configuration::getUnitPriceDispatchRelatedRecord();

        $itemsId = $dispatch->items->pluck('item_id')->all();

        $items = Item::whereIn('id', $itemsId)->get()->transform(function ($row) use ($warehouse, $dispatch, $relation_external_document, $set_unit_price_dispatch_related_record) {

            $detail = $this->getFullDescription($row, $warehouse);

            $sale_unit_price = $this->getDispatchSaleUnitPrice($row, $dispatch, $relation_external_document, $set_unit_price_dispatch_related_record);

            return [
                'id' => $row->id,
                'full_description' => $detail['full_description'],
                'model' => $row->model,
                'brand' => $detail['brand'],
                'category' => $detail['category'],
                'stock' => $detail['stock'],
                'internal_id' => $row->internal_id,
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format($sale_unit_price, 4, '.', ''),
                // 'sale_unit_price'                  => number_format($row->sale_unit_price, 4, '.', ''),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'calculate_quantity' => (bool)$row->calculate_quantity,
                'has_igv' => (bool)$row->has_igv,
                'has_plastic_bag_taxes' => (bool)$row->has_plastic_bag_taxes,
                'amount_plastic_bag_taxes' => $row->amount_plastic_bag_taxes,
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
                    ];
                }),
                'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse) {
                    return [
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                        'warehouse_id' => $row->warehouse_id,
                        'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                    ];
                }),
                'attributes' => $row->attributes ? $row->attributes : [],
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked' => false
                    ];
                }),
                'lots' => [],
                'lots_enabled' => (bool)$row->lots_enabled,
                'series_enabled' => (bool)$row->series_enabled,
            ];
        });
        if ($configuration->seller_establishments_all) {
            $series = Series::where('contingency', false)->get();
        } else {
            $series = Series::where('establishment_id', $establishment->id)->get();
        }
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])
            ->where('active', true)
            ->get();
        // $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $payment_conditions = PaymentCondition::get();
        $user_establishment_id = auth()->user()->establishment_id;
        return response()->json([
            'user_establishment_id' => $user_establishment_id,
            'dispatch' => $dispatch,
            'document_types_invoice' => $document_types_invoice,
            'establishments' => $establishment,
            'payment_destinations' => $payment_destinations,
            'series' => $series,
            'success' => true,
            'payment_method_types' => $payment_method_types,
            'items' => $items,
            'affectation_igv_types' => $affectation_igv_types,
            'payment_conditions' => $payment_conditions,
        ], 200);
    }


    /**
     * Obtener precio unitario desde registro relacionado a la guia - convertir guia a cpe
     *
     * @param Item $item
     * @param Dispatch $dispatch
     * @param mixed $relation_external_document
     * @param bool $set_unit_price_dispatch_related_record
     * @return float
     */
    public function getDispatchSaleUnitPrice($item, $dispatch, $relation_external_document, $set_unit_price_dispatch_related_record)
    {
        if ($dispatch->isGeneratedFromExternalDocument($relation_external_document) && $set_unit_price_dispatch_related_record) {
            $exist_item = $relation_external_document->items->where('item_id', $item->id)->first();
            if ($exist_item) return $exist_item->unit_price;
        }

        return $item->sale_unit_price;
    }

    public function setDocumentId($id)
    {
        request()->validate(['document_id' => 'required|exists:tenant.documents,id']);
        DB::connection('tenant')->beginTransaction();
        try {
            Dispatch::where('id', $id)
                ->update([
                    'reference_document_id' => request('document_id')
                ]);

            $dispatch = Dispatch::findOrFail($id);
            $facturalo = new Facturalo();
            $facturalo->createPdf($dispatch, 'dispatch', 'a4');

            DB::connection('tenant')->commit();
            return response()->json([
                'success' => true,
                'message' => 'Información actualiza'
            ], 200);
        } catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al asociar la guía con el comprobante. Detalles: ' . $th->getMessage()
            ], 500);
        }
    }

    public function dispatchesByClient(Request $request, $clientId)
    {

        $isCarrier = $request->isCarrier === 'true' ? true : false;
        $records = Dispatch::without([
            'user', 'soap_type', 'state_type', 'document_type', 'unit_type', 'transport_mode_type',
            'transfer_reason_type', 'items', 'reference_document'
        ])
        ->leftJoin('channels_documents', 'channels_documents.dispatch_id', '=', 'dispatches.id')
            ->select(
                'dispatches.series',
                'dispatches.number',
                'dispatches.id',
                'dispatches.date_of_issue',
                'dispatches.soap_shipping_response',
                'dispatches.receiver_id',
                'channels_documents.channel_reg_id as channel_id'
            )->whereDoesntHave('generate_document');
        if ($isCarrier) {
            $records->where('sender_id', $clientId);
        } else {
            $records->where('customer_id', $clientId);
        }
        $records = $records
            ->whereNull('reference_document_id')
            ->whereStateTypeAccepted()
            ->orderBy('series')
            ->orderBy('date_of_issue', 'asc');

        $paginated = $records->paginate(20);
        return [
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'from' => $paginated->firstItem(),
                'last_page' => $paginated->lastPage(), 
                'path' => $paginated->path(),
                'per_page' => $paginated->perPage(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total()
            ]
        ];
    }
    public function dispatchesByClientTransfers(Request $request)
    {
        $isCarrier = $request->isCarrier === 'true' ? true : false;
        $user_id = $request->user_id;
        $date_of_issue = $request->date_of_issue;
        $clientId = $request->client_id;
        $serie = $request->serie;
        $number = $request->number;
        $records = Dispatch::without([
            'user', 'soap_type', 'state_type', 'document_type', 'unit_type', 'transport_mode_type',
            'transfer_reason_type', 'items', 'reference_document'
        ])
            ->select(
                'series',
                'number',
                'id',
                'date_of_issue',
                'soap_shipping_response',
                'receiver_id'
            )->whereDoesntHave('transfers');
        if($clientId) {
            if ($isCarrier) {
                $records->where('sender_id', $clientId);
            } else {
                $records->where('customer_id', $clientId);
            }
        }
        if ($user_id) {
            $records->where('user_id', $user_id);
        }
        if ($date_of_issue) {
            $records->where('date_of_issue', $date_of_issue);
        }
        if ($serie) {
            $records->where('series', $serie);
        }
        if ($number) {
            $records->where('number', $number);
        }
        $records = $records
            ->whereNull('reference_document_id')
            ->whereStateTypeAccepted()
            ->orderBy('series')
            ->orderBy('date_of_issue', 'asc');

        $paginated = $records->paginate(20);
        return [
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'from' => $paginated->firstItem(),
                'last_page' => $paginated->lastPage(), 
                'path' => $paginated->path(),
                'per_page' => $paginated->perPage(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total()
            ]
        ];
    }
    public function getTablesTransfers(){
        $warehouses = Warehouse::where('active',true)->get()->transform(function($row){
            return [
                'id' => $row->id,
                'description' => $row->description,
            ];
        });
        $user = auth()->user();
        $establishment_id = $user->establishment_id;
        $type = $user->type;
        $series = Series::where('document_type_id', '09')->when($type !== 'admin' || $type !== 'superadmin', function($query) use ($establishment_id){
            $query->where('establishment_id', $establishment_id);
        })->get()->transform(function($row){
            return [
                'id' => $row->id,
                'number' => $row->number,
            ];
        });
        $users = User::get()->transform(function($row){
            return [
                'id' => $row->id,
                'description' => $row->name,
                'establishment_id' => $row->establishment_id,
            ];
        });
        return response()->json([
            'success' => true,
            'data' => [
                'warehouses' => $warehouses,
                'users' => $users,
                'series' => $series,
            ],
        ], 200);

    }
    public function getItemsFromDispatches(Request $request)
    {
        $request->validate([
            'dispatches_id' => 'required|array',
        ]);

        $items = DispatchItem::whereIn('dispatch_id', $request->dispatches_id)
            ->select('item_id', 'quantity')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200);
    }

    /**
     * Devuelve un conjuto de tipo de documento 9 y 31 para Guías
     *
     * @return DocumentType[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getDocumentTypeToDispatches()
    {
        $doc_type = ['09', '31'];
        $document_types_guide = DocumentType::whereIn('id', $doc_type)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'active' => (bool)$row->active,
                'short' => $row->short,
                'description' => ucfirst(mb_strtolower(str_replace('REMITENTE ELECTRÓNICA', 'REMITENTE', $row->description))),
            ];
        });

        return $document_types_guide;
    }

    public function getOriginAddresses($id)
    {
        $records = [];
        $record = Establishment::query()
            ->find($id);
        $records[] = [
            'id' => 0,
            'location_id' => [
                $record->department_id,
                $record->province_id,
                $record->district_id,
            ],
            'address' => $record->address,
        ];

        $origin_addresses = OriginAddress::query()
            ->where('is_active', true)
            ->get();
        foreach ($origin_addresses as $row) {
            $records[] = [
                'id' => $row->id,
                'address' => $row->address,
                'location_id' => $row->location_id,
            ];
        }

        return $records;
    }

    public function getDeliveryAddresses($id)
    {
        $records = [];
        $record = Person::query()
            //            ->with('person_addresses')
            ->find($id);
        $records[] = [
            'id' => 0,
            'location_id' => [
                $record->department_id,
                $record->province_id,
                $record->district_id,
            ],
            'address' => $record->address,
        ];
        //        foreach ($record->person_addresses as $row) {
        //            $records[] = [
        //                'id' => $row->id,
        //                'location_id' => [
        //                    $row->department_id,
        //                    $row->province_id,
        //                    $row->district_id,
        //                ],
        //                'address' => $row->address,
        //            ];
        //        }

        $delivery_addresses = DeliveryAddress::query()
            ->where('person_id', $id)
            ->where('is_active', true)
            ->get();
        foreach ($delivery_addresses as $row) {
            $records[] = [
                'id' => $row->id,
                'address' => $row->address,
                'location_id' => $row->location_id,
            ];
        }

        return $records;
    }

    public function preview(DispatchRequest $request)
    {
        

        $facturalo = new Facturalo();
        $inputs = $request->all();

        $inputs['state_type_id'] = '01';
        $inputs['establishment_id'] = auth()->user()->establishment_id;
        $inputs = DispatchInput::set($inputs);

        $document = new Dispatch($inputs);
        

        foreach ($inputs['items'] as $row) {
            $item = new \App\Models\Tenant\DispatchItem($row);
            $document->items[] = $item;
        }

    
        $format = isset($inputs['actions']['format_pdf']) ? $inputs['actions']['format_pdf'] : 'a4';
        $model = isset($inputs['actions']['model']) ? $inputs['actions']['model'] : 'dispatch';
        $facturalo->previewPdf($document, $model,$format);
    }
}
