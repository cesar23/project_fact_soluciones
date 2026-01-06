<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Helpers\Template\ReportHelper;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\Exports\PaymentExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use App\Http\Requests\Tenant\DocumentEmailRequest;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Requests\Tenant\DocumentUpdateRequest;
use App\Http\Resources\Tenant\DocumentCollection;
use App\Http\Resources\Tenant\DocumentResource;
use App\Imports\DocumentImportExcelFormat;
use App\Imports\DocumentsImport;
use App\Imports\DocumentsImportTwoFormat;
use App\Mail\Tenant\DocumentEmail;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\CatColorsItem;
use App\Models\Tenant\Catalogs\CatItemMoldCavity;
use App\Models\Tenant\Catalogs\CatItemMoldProperty;
use App\Models\Tenant\Catalogs\CatItemPackageMeasurement;
use App\Models\Tenant\Catalogs\CatItemProductFamily;
use App\Models\Tenant\Catalogs\CatItemStatus;
use App\Models\Tenant\Catalogs\CatItemUnitBusiness;
use App\Models\Tenant\Catalogs\CatItemUnitsPerPackage;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\NoteCreditType;
use App\Models\Tenant\Catalogs\NoteDebitType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\CatItemSize;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\PaymentCondition;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Series;
use App\Models\Tenant\StateType;
use App\Models\Tenant\User;
use App\Models\Tenant\NameDocument;
use App\Traits\OfflineTrait;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Finance\Helpers\UploadFileHelper;
use Modules\Finance\Traits\FinanceTrait;
use App\Traits\PrinterTrait;
use Modules\Inventory\Models\Warehouse as ModuleWarehouse;
use Modules\Item\Http\Requests\BrandRequest;
use Modules\Item\Http\Requests\CategoryRequest;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Modules\Document\Helpers\DocumentHelper;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Exports\DocumentExportConcar;
use App\Exports\DocumentExportSystem;
use App\Exports\DocumentExportTable;
use App\Http\Resources\Tenant\DocumentForNoteCollection;
use App\Http\Resources\Tenant\DocumentLiteCollection;
use App\Http\Resources\Tenant\DocumentToDeleteCollection;
use App\Imports\DocumentMassiveEmitImport;
use App\Models\System\Client as SystemClient;
use App\Models\Tenant\AuditorHistory;
use App\Models\Tenant\CashDocument;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\ItemSeller;
use App\Models\Tenant\ItemSizeStock;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\Kardex;
use App\Models\Tenant\Note;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\DetractionType;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\SummaryDocument;
use App\Models\Tenant\VoidedDocument;
use App\Models\Tenant\NameQuotations;
use App\Models\Tenant\PersonDispatcher;
use App\Models\Tenant\PersonPacker;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\Voided;
use App\Models\Tenant\Warehouse;
use App\Providers\InventoryServiceProvider;
use App\Services\PseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\BusinessTurn\Models\DocumentHotel;
use Modules\BusinessTurn\Models\DocumentTransport;
use Modules\Dispatch\Models\Dispatcher;
use Modules\Hotel\Models\HotelRent;
use Modules\Inventory\Models\{
    InventoryConfiguration,
    InventoryKardex
};
use Modules\Inventory\Providers\InventoryKardexServiceProvider;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Services\Http\Controllers\ServiceController;
use Modules\Store\Http\Controllers\StoreController;
use Modules\Suscription\Models\Tenant\SuscriptionNames;
use Modules\Suscription\Models\Tenant\SuscriptionPayment;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use App\Exports\DocumentMassiveEmitExport;
use App\Http\Resources\Tenant\PersonLiteCollection;
use App\Imports\DocumentsSaleImport;
use App\Models\Tenant\ConditionBlockPaymentMethod;
use App\Models\Tenant\DiscountType;
use App\Models\Tenant\DiscountTypeItem;
use App\Models\Tenant\ItemLabelColor;
use Modules\Item\Models\ItemProperty;
use App\Models\Tenant\PlateNumberDocument;
use App\Models\Tenant\QuotationServicesNotServices;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\UserDefaultDocumentType;
use App\Services\ImageService;
use App\Traits\CacheTrait;
use Closure;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Modules\Item\Http\Resources\ItemPropertyCollection;
use Modules\Item\Http\Resources\ItemPropertyCollection2;
use Modules\Item\Models\StateDelivery;
use Modules\Item\Models\WebPlatform;
use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Traits\OrderTrait;
use ReflectionFunction;

class DocumentController extends Controller
{
    use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;
    use PrinterTrait;
    use InventoryTrait;
    use CacheTrait;
    use OrderTrait;
    private $max_count_payment = 0;
    protected $document;
    protected $apply_change;
    public function __construct()
    {
        $this->middleware('input.request:document,web', ['only' => ['store', 'preview']]);
        $this->middleware('input.request:documentUpdate,web', ['only' => ['update']]);
    }

    public function ajustDocumentFee($id)
    {
        $document = Document::find($id);
        $document->ajustDocumentFee();
        return response()->json([
            'success' => true,
            'message' => 'Document fee ajusted'
        ]);
    }

    public function uploadFileDocumentsSale(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new DocumentsSaleImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }
    public function summariesTotals(Request $request)
    {
        $d_start = $request->d_start;
        $d_end = $request->d_end;
        $summariesTotals = $this->getSummariesTotals($d_start, $d_end);
        return response()->json($summariesTotals);
    }
    private function getSummariesTotals($d_start, $d_end)
    {
        $state_types_id = ['01', '03', '05', '07', '13'];
        
        // Convertir a PEN: Si es USD multiplicar por exchange_rate_sale, si es PEN dejar como está
        $type_01 = Document::where('document_type_id', '01')
            ->whereIn('state_type_id', $state_types_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->sum(DB::raw("CASE WHEN currency_type_id = 'USD' THEN total * exchange_rate_sale ELSE total END"));
            
        $type_03 = Document::where('document_type_id', '03')
            ->whereIn('state_type_id', $state_types_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->sum(DB::raw("CASE WHEN currency_type_id = 'USD' THEN total * exchange_rate_sale ELSE total END"));
            
        $type_08 = Document::where('document_type_id', '08')
            ->whereIn('state_type_id', $state_types_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->sum(DB::raw("CASE WHEN currency_type_id = 'USD' THEN total * exchange_rate_sale ELSE total END"));
            
        $type_07 = Document::where('document_type_id', '07')
            ->whereIn('state_type_id', $state_types_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->sum(DB::raw("CASE WHEN currency_type_id = 'USD' THEN total * exchange_rate_sale ELSE total END"));
            
        $type_80 = SaleNote::whereIn('state_type_id', $state_types_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->sum(DB::raw("CASE WHEN currency_type_id = 'USD' THEN total * exchange_rate_sale ELSE total END"));
            
        // Documentos no pagados completamente - convertir a PEN
        $type_unpaid = DB::connection('tenant')->table('documents')
            ->whereIn('state_type_id', $state_types_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->join('document_payments', 'documents.id', '=', 'document_payments.document_id')
            ->whereRaw('documents.total > (SELECT SUM(payment) FROM document_payments dp WHERE dp.document_id = documents.id)')
            ->sum(DB::raw("CASE WHEN documents.currency_type_id = 'USD' THEN documents.total * documents.exchange_rate_sale ELSE documents.total END"));
            
        // Notas de venta no pagadas completamente - convertir a PEN
        $type_unpaid_sale_notes = DB::connection('tenant')->table('sale_notes')
            ->whereIn('state_type_id', $state_types_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->join('sale_note_payments', 'sale_notes.id', '=', 'sale_note_payments.sale_note_id')
            ->whereRaw('sale_notes.total > (SELECT SUM(payment) FROM sale_note_payments sp WHERE sp.sale_note_id = sale_notes.id)')
            ->sum(DB::raw("CASE WHEN sale_notes.currency_type_id = 'USD' THEN sale_notes.total * sale_notes.exchange_rate_sale ELSE sale_notes.total END"));
            
        // Pagos de documentos de otros períodos - convertir a PEN usando la moneda del documento
        $document_payments = DB::connection('tenant')->table('document_payments')
            ->join('documents', 'document_payments.document_id', '=', 'documents.id')
            ->whereBetween('document_payments.date_of_payment', [$d_start, $d_end])
            ->whereNotBetween('documents.date_of_issue', [$d_start, $d_end])
            ->sum(DB::raw("CASE WHEN documents.currency_type_id = 'USD' THEN document_payments.payment * documents.exchange_rate_sale ELSE document_payments.payment END"));


        return [
            '01' => number_format($type_01, 2),
            '03' => number_format($type_03, 2),
            '08' => number_format($type_08, 2),
            '07' => number_format($type_07, 2),
            'nv' => number_format($type_80, 2),
            'unpaid' => number_format($type_unpaid + $type_unpaid_sale_notes, 2),
            'paid_others' => number_format($document_payments, 2),
            'total' => number_format($type_01 + $type_03 + $type_08 - $type_07 + $type_80, 2),
        ];
        
    
    }
    public function searchItemAttributes(Request $request)
    {
    $input = $request->input !=null ? $request->input : "";
    
    $query = ItemProperty::query() ->where('chassis', 'like', "%{$input}%");
  
    $sale_note_item_id = $request->has('sale_note_item_id') ? $request->input('sale_note_item_id') : null;
    $document_item_id = $request->has('document_item_id') ? $request->input('document_item_id') : null;
    $warehouse_id = $request->has('warehouse_id') ? $request->input('warehouse_id') : null;

    if ($document_item_id) {
        $document_item = DocumentItem::query()
            ->findOrFail($document_item_id);        
        $lots = $document_item->item->item_atribute;
        $query->whereIn('id', collect($lots)->pluck('id')->toArray())
            ->where('has_sale', true)
            ->latest();
    
    } else if ($sale_note_item_id) {
        $query = $this->getRecordsForSaleNoteItemAttrib($query, $sale_note_item_id, $request);
       
    } else {
        if (is_null($warehouse_id)) {
            $warehouse = ModuleWarehouse::query()
                ->select('id')
                ->where('establishment_id', auth()->user()->establishment_id)
                ->first();
            $warehouse_id = $warehouse->id;
        }
        
        $query
            ->where('item_id', $request->input('item_id'))
            ->where('has_sale', false)
            ->where('warehouse_id', $warehouse_id)
            ->latest();
    }
   
    return new ItemPropertyCollection2($query->paginate(config('tenant.items_per_page')));
    }

    public function getRecordsForSaleNoteItemAttrib($records, $sale_note_item_id, $request)
    {
        // obtener series disponibles
        $records->scopeWhereAvailableAttribute($request->item_id)->latest();
        // obtener series vendidas en la nv
        $sale_note_item = SaleNoteItem::findOrFail($sale_note_item_id);
        $lots = $sale_note_item->item->attribute;
        $sale_lots = ItemProperty::whereIn('id', collect($lots)->pluck('id')->toArray())->where('has_sale', true)->latest();
        return $sale_lots->union($records);
    }
    public function attributes(Request $request)
    {
         
        $input = $request->input !=null ? $request->input : "";
        $warehouse = Warehouse::where('establishment_id',auth()->user()->establishment_id)->first();
        $query = ItemProperty::query()->where('warehouse_id',$warehouse->establishment_id) ->where('chassis', 'like', "%{$input}%");
        $query->where('item_id', $request->input('item_id'))->where('has_sale',false)->latest();
         return new ItemPropertyCollection($query->paginate(config('tenant.items_per_page')));
    }
    public function uploadFileMassiveEmit(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new DocumentMassiveEmitImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }
    public function getTableDataMassiveEmit()
    {
        $store = new StoreController;
        $establishment_id = auth()->user()->establishment_id;
        $date = now()->format('Y-m-d');
        $request = new Request([
            'establishment_id' => $establishment_id,
            'date' => $date,
        ]);
        $exchange_rate_sale = (new ServiceController)->exchange($date);
        if (isset($exchange_rate_sale['sale']) && $exchange_rate_sale['sale'] !== 0) {
            $exchange_rate_sale = $exchange_rate_sale['sale'];
        } else {
            $exchange_rate_sale = 1;
        }
        $percentage_igv = $store->getIgv($request);
        $affectation_igv_types = AffectationIgvType::all();
        $configuration = Configuration::first();
        $detraction_types = DetractionType::all();
        $cash = Cash::where('user_id', User::getUserCashId())->where('state', '1')->first();
        if ($cash) {
            $cash_id = $cash->id;
        } else {
            $cash_id = null;
        }
        $payment_method_type = PaymentMethodType::where('description', 'like', "%Efectivo%")->first();
        $payment_method_type_credit = PaymentMethodType::where('description', 'like', "Crédito%")->first();
        if ($payment_method_type) {
            $payment_method_type_id = $payment_method_type->id;
        } else {
            $payment_method_type_id = null;
        }
        if ($payment_method_type_credit) {
            $payment_method_type_id_credit = $payment_method_type_credit->id;
        } else {
            $payment_method_type_id_credit = null;
        }
        $company = Company::active();
        $bank_account = $company->detraction_account;
        return response()->json([
            'bank_account' => $bank_account,
            'payment_method_type_id' => $payment_method_type_id,
            'payment_method_type_id_credit' => $payment_method_type_id_credit,
            'cash_id' => $cash_id,
            'percentage_igv' => $percentage_igv,
            'affectation_igv_types' => $affectation_igv_types,
            'configuration' => $configuration,
            'exchange_rate_sale' => $exchange_rate_sale,
            'detraction_types' => $detraction_types,
        ]);
    }

    public function documentForMassiveNote(Request $request)
    {
        $customer_id = $request->customer_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $soap_type_id =  Company::active()->soap_type_id;
        $documents = Document::where(function ($query) {
            $query->whereDoesntHave('note2')
                ->orWhereHas('note2', function ($query) {
                    $query->whereHas('document', function ($query) {
                        $query->whereIn('state_type_id', ['11', '13', '09']);
                    });
                });
        })
            ->whereIn('document_type_id', ['01', '03'])
            ->where('state_type_id', '05')
            ->where('total_discount', 0)
            ->where('soap_type_id', $soap_type_id);
        if ($customer_id) {
            $documents->where('customer_id', $customer_id);
        }
        if ($start_date || $end_date) {
            if ($start_date && $end_date) {
                $documents->whereBetween('date_of_issue', [$start_date, $end_date]);
            } else {
                $documents->where('date_of_issue', $start_date ?? $end_date);
            }
        }


        return new DocumentForNoteCollection($documents->paginate(config('tenant.items_per_page')));
    }
    public function updateUser($user_id, $document_id)
    {
        $document = Document::find($document_id);
        $document->user_id = $user_id;
        $document->save();
        return [
            'success' => true,
            'message' => 'Usuario cambiado'
        ];
    }

    public function checkSeries(Request $request)
    {
        $lots = $request->lots;
        $errors = [];
        $lots_found = [];
        foreach ($lots as $lot) {
            $item_lot = ItemLot::where('series', $lot)->first();
            if ($item_lot) {
                if ($item_lot->has_sale == 1) {
                    $message = 'La serie ' . $lot . ' ya fue vendida.';
                    $errors[] = $message;
                } else {
                    $transformed = [
                        "date" => $item_lot->date,
                        "has_sale" => true,
                        "id" => $item_lot->id,
                        "item_id" => $item_lot->item_id,
                        "lot_code" => $item_lot->lot_code,
                        "series" => $item_lot->series,
                        "warehouse_id" => $item_lot->warehouse_id,
                    ];
                    $lots_found[] = $transformed;
                }
            } else {
                $message = 'La serie ' . $lot . ' no se encuentra registrada.';
                $errors[] = $message;
            }
        }
        return [
            'success' => (count($errors) === 0) ? true : false,
            'message' => (count($errors) === 0) ? 'Series verificadas' : $errors,
            'lots' => $lots_found,

        ];
    }
    public function voidedPdf($id)
    {
        $document = Document::find($id);
        $voided_document = VoidedDocument::where('document_id', $id)->first();
        $voided = Voided::where('id', $voided_document->voided_id)->first();
        $establishment = Establishment::find(auth()->user()->establishment_id);
        $company = Company::active();


        $pdf = Pdf::loadView('tenant.documents.voided_pdf', compact(
            "document",
            "company",
            "establishment",
            "voided",
            "voided_document"
        ))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("anulacion" . '.pdf');
    }
    public function changeSire($document_id, $appendix)
    {
        $document = Document::find($document_id);
        switch ($appendix) {

            case 2:
                $document->appendix_2 = !$document->appendix_2;
                break;
            case 3:
                $document->appendix_3 = !$document->appendix_3;
                break;
            case 4:
                $document->appendix_4 = !$document->appendix_4;
                break;
            default:
                $document->appendix_5 = !$document->appendix_5;
                break;
        }
        $document->save();
        return [
            'success' => true,
            'message' => 'Anexo ' . $appendix . ' cambiado'
        ];
    }
    private function document_item_restore(DocumentItem $document_item)
    {
        // si es nota credito tipo 13, no se asocia a inventario
        if ($document_item->document->isCreditNoteAndType13()) return;
        if ($document_item->document->no_stock) return;

        if (!$document_item->item->is_set) {
            $presentationQuantity = (!empty($document_item->item->presentation)) ? $document_item->item->presentation->quantity_unit : 1;
            $document = $document_item->document;
            $factor = ($document->document_type_id === '07') ? 1 : -1;
            $warehouse = ($document_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($document_item->warehouse_id)->establishment_id) : $this->findWarehouse();
            //$this->createInventory($document_item->item_id, $factor * $document_item->quantity, $warehouse->id);
            $this->createInventoryKardex($document_item->document, $document_item->item_id, ($factor * ($document_item->quantity * $presentationQuantity)), $warehouse->id);

            if (!$document_item->document->sale_note_id && !$document_item->document->order_note_id && !$document_item->document->dispatch_id && !$document_item->document->sale_notes_relateds) {
                $this->updateStock($document_item->item_id, ($factor * ($document_item->quantity * $presentationQuantity)), $warehouse->id);
            } else {
                if ($document_item->document->dispatch) {
                    if (!$document_item->document->dispatch->transfer_reason_type->discount_stock) {
                        $this->updateStock($document_item->item_id, ($factor * ($document_item->quantity * $presentationQuantity)), $warehouse->id);
                    }
                }
            }
        } else {

            $item = Item::findOrFail($document_item->item_id);

            foreach ($item->sets as $it) {
                /** @var Item $ind_item */
                $ind_item = $it->individual_item;
                $item_set_quantity = ($it->quantity) ? $it->quantity : 1;
                $presentationQuantity = 1;
                $document = $document_item->document;
                $factor = ($document->document_type_id === '07') ? 1 : -1;
                $warehouse = $this->findWarehouse();
                $this->createInventoryKardex($document_item->document, $ind_item->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);

                if (!$document_item->document->sale_note_id && !$document_item->document->order_note_id && !$document_item->document->dispatch_id && !$document_item->document->sale_notes_relateds) {
                    $this->updateStock($ind_item->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                } else {
                    if ($document_item->document->dispatch) {
                        if (!$document_item->document->dispatch->transfer_reason_type->discount_stock) {
                            $this->updateStock($ind_item->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                        }
                    }
                }
            }
        }

        /*
         * Calculando el stock por lote por factor según la unidad
         */

        if (!$document->isGeneratedFromExternalRecord()) {

            if (isset($document_item->item->IdLoteSelected)) {
                if ($document_item->item->IdLoteSelected != null) {
                    if (is_array($document_item->item->IdLoteSelected)) {
                        // presentacion - factor de lista de precios
                        $quantity_unit = isset($document_item->item->presentation->quantity_unit) ? $document_item->item->presentation->quantity_unit : 1;
                        $lotesSelecteds = $document_item->item->IdLoteSelected;
                        $document_factor = ($document->document_type_id === '07') ? 1 : -1;
                        $inventory_configuration = InventoryConfiguration::first();
                        $inventory_configuration->stock_control;
                        foreach ($lotesSelecteds as $item) {
                            $lot = ItemLotsGroup::query()->find($item->id);
                            $compromise_quantity = isset($item->compromise_quantity) ? $item->compromise_quantity : 1;
                            $lot->quantity = $lot->quantity + ($quantity_unit * $compromise_quantity * $document_factor);
                            if ($inventory_configuration->stock_control) {
                                $this->validateStockLotGroup($lot, $document_item);
                            }
                            $lot->save();
                        }
                    } else {

                        $lot = ItemLotsGroup::query()->find($document_item->item->IdLoteSelected);
                        try {
                            $quantity_unit = $document_item->item->presentation->quantity_unit;
                        } catch (Exception $e) {
                            $quantity_unit = 1;
                        }
                        if ($document->document_type_id === '07') {
                            $quantity = $lot->quantity + ($quantity_unit * $document_item->quantity);
                        } else {
                            $quantity = $lot->quantity - ($quantity_unit * $document_item->quantity);
                        }
                        $lot->quantity = $quantity;
                        $lot->save();
                    }
                }
            }
        }
        if (isset($document_item->item->sizes_selected)) {
            foreach ($document_item->item->sizes_selected as $size) {

                $item_size = ItemSizeStock::where('item_id', $document_item->item_id)->where('size', $size->size)->first();
                if ($item_size) {
                    $item_size->stock = $item_size->stock - $size->qty;
                    $item_size->save();
                }
            }
        }
        if (isset($document_item->item->lots)) {
            foreach ($document_item->item->lots as $it) {

                if ($it->has_sale == true) {
                    $r = ItemLot::find($it->id);
                    // $r->has_sale = true;
                    $r->has_sale = ($document->document_type_id === '07') ? false : true;
                    $r->save();
                }
            }
            /*if($document_item->item->IdLoteSelected != null)
            {
                $lot = ItemLotsGroup::find($document_item->item->IdLoteSelected);
                $lot->quantity = ($lot->quantity - $document_item->quantity);
                $lot->save();
            }*/
        }
    }
    function recalculateStock($item_id)
    {
        $total = 0;
        $item_warehouses = ItemWarehouse::where('item_id', $item_id)->get();
        foreach ($item_warehouses as $item_warehouse) {
            $total += $item_warehouse->stock;
        }
        $item = Item::find($item_id);
        $item->stock = $total;
        $item->save();
    }
    public function getToDelete(Request $request)
    {
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $records = Document::whereBetween('date_of_issue', [$date_start, $date_end]);
        return new DocumentToDeleteCollection($records->paginate(50));
    }
    public function deletes(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $ids = $request->ids;
            foreach ($ids as $id) {
                $document = Document::findOrFail($id);
                $document = Document::find($id);
                AuditorHistory::where('document_id', $id)->delete();

                //GuideFile
                $document->guide_files()->delete();
                HotelRent::where('document_id', $id)->delete();
                //DocumentPayment
                DocumentPayment::where('document_id', $id)->delete();
                //Dispatch
                Dispatch::where('reference_document_id', $id)->delete();
                //DocumentFee
                DocumentFee::where('document_id', $id)->delete();
                // CashDocument
                CashDocument::where('document_id', $id)->delete();
                // CashDocumentCredit
                CashDocumentCredit::where('document_id', $id)->delete();
                // Kardex
                Kardex::where('document_id', $id)->delete();
                // Note
                Note::where('document_id', $id)->delete();
                // SummaryDocument
                SummaryDocument::where('document_id', $id)->delete();
                // VoidedDocument
                VoidedDocument::where('document_id', $id)->delete();
                // DocumentHotel
                DocumentHotel::where('document_id', $id)->delete();
                // DocumentTransport
                DocumentTransport::where('document_id', $id)->delete();
                // SuscriptionPayment
                SuscriptionPayment::where('document_id', $id)->delete();

                $items = DocumentItem::where('document_id', $id)->get();
                foreach ($items as $item) {
                    $item->restoreStock();
                    ItemSeller::where('document_item_id', $item->id)->delete();
                    $item->delete();
                }
                $notes = $document->getNotes();
                foreach ($notes as $note) {
                    $note->delete();
                }


                $document->inventory_kardex()->delete();
                $document->delete();
            }
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Documentos eliminados con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function auditorHistory(Request $request)
    {
        $document_id = $request->document_id;
        $auditor_history = AuditorHistory::where('document_id', $document_id)
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
    public function change_state($state_id,  $document_id)

    {

        try {
            DB::connection('tenant')->beginTransaction();
            $document = Document::find($document_id);
            $user_id = auth()->id();
            $new_history = new AuditorHistory;
            $new_history->user_id = $user_id;
            $new_history->document_id = $document_id;
            $new_history->new_state_type_id = $state_id;
            $new_history->old_state_type_id = $document->state_type_id;
            $new_history->save();
            $document->state_type_id = $state_id;
            if ($state_id == '05') {
                $document_items = DocumentItem::where('document_id', $document_id)->get();
                foreach ($document_items as $item) {
                    $this->document_item_restore($item);
                    $this->recalculateStock($item->item_id);
                }
            }
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
    public function index(Request $request)
    {
        $is_optometry = BusinessTurn::isOptometry();
        $user = auth()->user();
        $is_comercial  = $user->integrate_user_type_id == 2;
        $to_anulate = $request->input('to_anulate') ?? false;
        $is_client = $this->getIsClient();
        $import_documents = config('tenant.import_documents');
        $import_documents_second = config('tenant.import_documents_second_format');
        $document_import_excel = config('tenant.document_import_excel');
        $configuration = Configuration::getPublicConfig();
        $is_auditor = $user->type == 'superadmin';
        $view_apiperudev_validator_cpe = config('tenant.apiperudev_validator_cpe');
        $view_validator_cpe = config('tenant.validator_cpe');
        $document_state_types = StateType::getStateTypes();

        return view(
            'tenant.documents.index',
            compact(
                'is_optometry',
                'is_comercial',
                'document_state_types',
                'is_auditor',
                'to_anulate',
                'is_client',
                'import_documents',
                'import_documents_second',
                'document_import_excel',
                'configuration',
                'view_apiperudev_validator_cpe',
                'view_validator_cpe'
            )
        );
    }

    public function killDocument($id)
    {
        $document = Document::find($id);

        //GuideFile
        $document->guide_files()->delete();

        $ordens = Orden::where('document_id', $id)->get();
        foreach ($ordens as $orden) {
            $orden->orden_items()->delete();
            $orden->delete();
        }

        AuditorHistory::where('document_id', $id)->delete();
        HotelRent::where('document_id', $id)->delete();
        //DocumentPayment
        DocumentPayment::where('document_id', $id)->delete();
        //Dispatch
        Dispatch::where('reference_document_id', $id)->delete();
        //DocumentFee
        DocumentFee::where('document_id', $id)->delete();
        // CashDocument
        CashDocument::where('document_id', $id)->delete();
        // CashDocumentCredit
        CashDocumentCredit::where('document_id', $id)->delete();
        // Kardex
        Kardex::where('document_id', $id)->delete();
        // Note
        Note::where('document_id', $id)->delete();
        // SummaryDocument
        SummaryDocument::where('document_id', $id)->delete();
        // VoidedDocument
        VoidedDocument::where('document_id', $id)->delete();
        // DocumentHotel
        DocumentHotel::where('document_id', $id)->delete();
        // DocumentTransport
        DocumentTransport::where('document_id', $id)->delete();
        // SuscriptionPayment
        SuscriptionPayment::where('document_id', $id)->delete();

        $items = DocumentItem::where('document_id', $id)->get();
        foreach ($items as $item) {
            $item->restoreStock();
            ItemSeller::where('document_item_id', $item->id)->delete();
            $item->delete();
        }
        foreach ($items as $item) {
            if (isset($item->item->idAttributeSelect) && !empty($item->item->idAttributeSelect)) {
                $firstAttribute = $item->item->idAttributeSelect[0];
                if (isset($firstAttribute->id)) {
                    ItemProperty::where('id', $firstAttribute->id)->update([
                        "has_sale" => false
                    ]);
                }
            }
            $item->restoreStock();
            ItemSeller::where('document_item_id', $item->id)->delete();
            $item->delete();
        }
        $notes = $document->getNotes();
        foreach ($notes as $note) {
            $note->delete();
        }

        // CashDocument::where('document_id', $id)->delete();

        $document->inventory_kardex()->delete();
        $document->delete();
        return [
            'success' => true,
            'message' => 'Documento eliminado'
        ];
    }
    public function hasUnpaid(Request $request, $customer_id)
    {

        $establishment_id = auth()->user()->establishment_id;
        $period = $request['period'] ?? null;
        $date_start = $request['date_start'] ?? null;
        $date_end = $request['date_end'] ?? null;
        $month_start = $request['month_start'] ?? null;
        $month_end = $request['month_end'] ?? null;
        // $customer_id = $request['customer_id'] ?? null;
        $user_id = $request['user_id'] ?? null;
        $purchase_order = $request['purchase_order'] ?? null;
        $zone_id = Functions::valueKeyInArray($request, 'zone_id');
        $payment_method_type_id = $request['payment_method_type_id'] ?? null;
        // Obtendrá todos los establecimientos
        $stablishmentUnpaidAll = $request['stablishmentUnpaidAll'] ?? 0;
        $user = auth()->user();
        if (null === $user) {
            $user = new \App\Models\Tenant\User();
        }
        $user_type = $user->type;
        $user_id_session = $user->id;
        $d_start = null;
        $d_end = null;

        /** @todo: Eliminar periodo, fechas y cambiar por

        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */ switch ($period) {
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
        /*
         * Documents
         */


        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select('document_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('document_id');
        $bills_of_exchanges_payments = DB::connection('tenant')
            ->table('bills_of_exchange_payments')
            ->select('bill_of_exchange_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('bill_of_exchange_id');
        $bills_of_exchanges_select = "bills_of_exchange.id as id, " .
            "DATE_FORMAT(bills_of_exchange.date_of_due, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "CONCAT(bills_of_exchange.series,'-',bills_of_exchange.number) AS number_full, " .
            "bills_of_exchange.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "bills_of_exchange.total - IFNULL(total_payment, 0)    as total_subtraction, " .
            "'bill_of_exchange' AS 'type', " .
            "bills_of_exchange.currency_type_id, " .
            "bills_of_exchange.exchange_rate_sale, " .
            " bills_of_exchange.user_id, " .
            "users.name as username";
        $document_select = "documents.id as id, " .
            "DATE_FORMAT(documents.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number) AS number_full, " .
            "documents.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "IFNULL(credit_notes.total_credit_notes, 0) as total_credit_notes, " .
            "documents.total - IFNULL(total_payment, 0)  - IFNULL(total_credit_notes, 0)  as total_subtraction, " .
            "'document' AS 'type', " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            " documents.user_id, " .
            "users.name as username";

        $sale_note_select = "sale_notes.id as id, " .
            "DATE_FORMAT(sale_notes.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "sale_notes.filename as number_full, " .
            "sale_notes.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "sale_notes.total - IFNULL(total_payment, 0)  as total_subtraction, " .
            "'sale_note' AS 'type', " .
            "sale_notes.currency_type_id, " .
            "sale_notes.exchange_rate_sale, " .
            " sale_notes.user_id, " .
            "users.name as username";
        $bills_of_exchange = DB::connection('tenant')
            ->table('bills_of_exchange')
            //->where('customer_id', $customer_id)
            ->join('persons', 'persons.id', '=', 'bills_of_exchange.customer_id')
            ->join('users', 'users.id', '=', 'bills_of_exchange.user_id')
            ->leftJoinSub($bills_of_exchanges_payments, 'payments', function ($join) {
                $join->on('bills_of_exchange.id', '=', 'payments.bill_of_exchange_id');
            })
            // ->leftJoinSub($bill_of_exchanges, 'bills', function ($join) {
            //     $join->on('documents.id', '=', 'bills.document_id');
            // })
            // ->leftJoinSub(Document::getQueryCreditNotes(), 'credit_notes', function ($join) {
            //     $join->on('documents.id', '=', 'credit_notes.affected_document_id');
            // })
            // ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            // ->whereIn('document_type_id', ['01', '03', '08'])
            ->select(DB::raw($bills_of_exchanges_select));
        $documents = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->leftJoinSub($document_payments, 'payments', function ($join) {
                $join->on('documents.id', '=', 'payments.document_id');
            })
            ->leftJoinSub(Document::getQueryCreditNotes(), 'credit_notes', function ($join) {
                $join->on('documents.id', '=', 'credit_notes.affected_document_id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('sale_notes.id'))
                    ->from('sale_notes')
                    ->whereRaw('sale_notes.id = documents.sale_note_id')
                    ->where(function ($query) {
                        $query->where('sale_notes.total_canceled', true)
                            ->orWhere('sale_notes.paid', true);
                    });
            })


            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->select(DB::raw($document_select));


        if ($stablishmentUnpaidAll !== 1 && $stablishmentUnpaidAll !== "1") {
            $documents->where('documents.establishment_id', $establishment_id);
        }

        if ($payment_method_type_id) {
            $documents->where('payment_method_type_id', $payment_method_type_id);
        }
        $documents->whereNull('bill_of_exchange_id');
        /*
         * Sale Notes
         */
        $sale_note_payments = DB::connection('tenant')

            ->table('sale_note_payments')
            ->select('sale_note_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('sale_note_id');


        $sale_notes = DB::connection('tenant')
            ->table('sale_notes')
            // ->where('customer_id', $customer_id)
            ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
            ->join('users', 'users.id', '=', 'sale_notes.user_id')
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->select(DB::raw($sale_note_select))
            ->where('sale_notes.changed', false)
            ->where('sale_notes.total_canceled', false);

        if ($stablishmentUnpaidAll !== 1 && $stablishmentUnpaidAll !== "1") {
            $sale_notes
                ->where('sale_notes.establishment_id', $establishment_id);
        }

        if ($user_type == 'seller') {
            $sale_notes->where('user_id', $user_id_session);
            $documents->where('user_id', $user_id_session);
        }
        if ($user_type == 'admin' && $user_id) {
            $sale_notes->whereIn('user_id', [$user_id_session, $user_id]);
            $documents->whereIn('user_id', [$user_id_session, $user_id]);
        }
        if ($customer_id) {
            $sale_notes->where('customer_id', $customer_id);
            $documents->where('customer_id', $customer_id);
        }
        if ($zone_id) {
            $sale_notes->where('persons.zone_id', $zone_id);
            $documents->where('persons.zone_id', $zone_id);
        }
        if ($d_start && $d_end) {
            $sale_notes->whereBetween('sale_notes.date_of_issue', [$d_start, $d_end]);
            $documents->whereBetween('documents.date_of_issue', [$d_start, $d_end]);
        }
        // return $documents->union($sale_notes);
        if ($purchase_order !== null) {
            $documents->where('purchase_order', $purchase_order);
            $sale_notes->where('purchase_order', $purchase_order);
        }

        // $records = $documents->union($sale_notes)
        //     ->union($bills_of_exchange)
        //     ->havingRaw('total_subtraction > 0');
        //     $config = Configuration::first();
        // return (new UnpaidCollection($records
        //     ->orderBy('date_of_issue', 'asc')
        //     ->paginate(config('tenant.items_per_page'))))->additional([
        //     'configuration' => $config->finances
        // ]);


        $hasUnpaid = $documents->union($sale_notes)
            ->union($bills_of_exchange)
            ->havingRaw('total_subtraction > 0')->count();
        $first_date = null;
        $document_first = $documents->first();
        $sale_note_first = $sale_notes->first();
        $bill_of_exchange_first = $bills_of_exchange->first();
        if ($document_first) {
            $first_date = $document_first->date_of_issue;
        }
        if ($sale_note_first) {
            if ($first_date == null) {
                $first_date = $sale_note_first->date_of_issue;
            } else {
                if ($first_date < $sale_note_first->date_of_issue) {
                    $first_date = $sale_note_first->date_of_issue;
                }
            }
        }

        if ($bill_of_exchange_first) {
            if ($first_date == null) {
                $first_date = $bill_of_exchange_first->date_of_issue;
            } else {
                if ($first_date < $bill_of_exchange_first->date_of_issue) {
                    $first_date = $bill_of_exchange_first->date_of_issue;
                }
            }
        }

        return [
            'success' => true,
            'hasUnpaid' => $hasUnpaid > 0,
            'first_date' => $first_date
        ];
        // return $documents->union($sale_notes)
        //     ->union($bills_of_exchange)
        //     ->havingRaw('total_subtraction > 0');

    }
    public function columns()
    {
        return [
            'number' => 'Número',
            'date_of_issue' => 'Fecha de emisión'
        ];
    }
    public function getRecordsDocuments($request)
    {

        $configuration = Configuration::getConfig();
        $d_end = $request->d_end;
        $ubigeo = $request->ubigeo;
        $department_id = null;
        $province_id = null;
        if ($ubigeo) {
            $ubigeo = explode(',', $ubigeo);
            $department_id = $ubigeo[0];
            if (count($ubigeo) > 1) {
                $province_id = $ubigeo[1];
            }
        }
        $website_id = $request->company_id;
        $web_platform_id = $request->web_platform_id;
        $state_delivery_id = $request->state_delivery_id;
        $d_start = $request->d_start;
        $date_of_issue = $request->date_of_issue;
        $document_type_id = $request->document_type_id;
        $state_type_id = $request->state_type_id;
        $lote_code = $request->lote_code;
        $number = $request->number;
        $series = $request->series;
        $website_id = $request->website_id;
        $pending_payment = ($request->pending_payment == "true") ? true : false;
        $customer_id = $request->customer_id;
        $item_id = $request->item_id;
        $time = $request->time;
        $category_id = $request->category_id;
        $purchase_order = $request->purchase_order;
        $guides = $request->guides;
        $plate_numbers = $request->plate_numbers;
        $establishment_id = $request->establishment_id;

        // Iniciar la consulta SIN el with()
        $records = Document::query();

        if ($d_start && $d_end) {
            $records->whereBetween('date_of_issue', [$d_start, $d_end]);
        }
        if ($date_of_issue) {
            $records = $records->where('date_of_issue', 'like', '%' . $date_of_issue . '%');
        }
        /** @var Builder $records */
        if ($document_type_id) {
            $records->where('document_type_id', 'like', '%' . $document_type_id . '%');
        }

        if ($series) {
            $records->where('series', 'like', '%' . $series . '%');
        }
        if ($time) {
            $explode_time = explode("-", $time);
            $start_time = trim($explode_time[0]);
            $end_time = trim($explode_time[1]);

            $records->whereBetween('time_of_issue', [$start_time, $end_time])->latest();
        }

        if ($department_id) {
            $records->whereHas('person', function ($query) use ($department_id, $province_id) {
                if ($province_id) {
                    $query->where('province_id', $province_id);
                } else {
                    $query->where('department_id', $department_id);
                }
            });
        }
        if ($website_id) {
            $records->where('website_id', $website_id);
        }
        if ($number) {
            $records->where('number', $number);
        }
        if ($establishment_id) {
            $records->where('establishment_id', $establishment_id);
        }
        if ($state_type_id) {
            $records->where('state_type_id', 'like', '%' . $state_type_id . '%');
        }
        if ($purchase_order) {
            $records->where('purchase_order', $purchase_order);
        }
        $records->whereTypeUser()->latest();

        if ($pending_payment) {
            $records->whereRaw('(SELECT COALESCE(SUM(payment), 0) FROM document_payments WHERE document_payments.document_id = documents.id) + COALESCE(JSON_UNQUOTE(JSON_EXTRACT(retention, "$.amount")), 0) < documents.total')
                ->whereNotIn('document_type_id', ['07', '08'])
                ->whereDoesntHave('note2');
        }

        if ($customer_id) {
            $records->where('customer_id', $customer_id);
        }

        if ($item_id) {
            $records->whereHas('items', function ($query) use ($item_id, $configuration) {
                $query->where('item_id', $item_id)
                    ->orWhereHas('relation_item', function ($q) use ($item_id) {
                        $q->whereHas('sets', function ($q2) use ($item_id) {
                            $q2->where('individual_item_id', $item_id);
                        });
                    });
            });
        }

        if ($lote_code) {
            $records->whereHas('items', function ($query) use ($lote_code) {
                $query->where(function ($q) use ($lote_code) {
                    $q->whereRaw("JSON_SEARCH(JSON_EXTRACT(item, '$.IdLoteSelected[*].code'), 'all', ?) IS NOT NULL", ["%{$lote_code}%"]);
                });
            });
        }
        if ($web_platform_id) {
            $records->whereHas('items', function ($query) use ($web_platform_id) {
                $query->whereHas('relation_item', function ($q) use ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                });
            });
        }
        if ($state_delivery_id) {
            if ($state_delivery_id == 1) {
                $records->where(function ($query) {
                    $query->where('state_delivery_id', 1)
                        ->orWhere('state_delivery_id', null);
                });
            } else {
                $records->where('state_delivery_id', $state_delivery_id);
            }
        }

        if ($category_id) {
            $records->whereHas('items', function ($query) use ($category_id) {
                $query->whereHas('relation_item', function ($q) use ($category_id) {
                    $q->where('category_id', $category_id);
                });
            });
        }
        if (!empty($guides)) {
            $records->where('guides', 'like', DB::raw("%\"number\":\"%") . $guides . DB::raw("%\"%"));
        }
        if ($plate_numbers) {
            if ($configuration->plate_number_config) {
                $records->whereHas('plateNumberDocument', function ($query) use ($plate_numbers) {
                    $query->whereHas('plateNumber', function ($q) use ($plate_numbers) {
                        $q->where('description', 'like', '%' . $plate_numbers . '%');
                    });
                });
            } else {
                $records->where('plate_number', 'like', '%' . $plate_numbers . '%');
            }
        }

        // APLICAR EL WITH() AL FINAL, después de todos los filtros
        $records = $records->with([
            'note2:id,document_id',
            'note2.document:id,series,number,total,state_type_id',
            'sale_note:id,document_id,paid,total_canceled,series,number,state_type_id',
            'sale_note.payments:id,payment,sale_note_id',
            'sale_note.state_type:id,description',
            'fee:id,date,document_id',
            'plateNumberDocument:id,document_id,plate_number_id',
            'plateNumberDocument.plateNumber:id,description',
            'items:id,document_id,item_id,attributes',
            'items.m_item.web_platform:id,name',
            'affected_documents:affected_document_id,document_id,note_type',
            'affected_documents.document:id,series,number',
            'affected_documents2:id,affected_document_id,data_affected_document',
            'affected_documents2.affected_document:id,document_type_id',
            'auditor_history:id',
            'reference_guides:id,reference_document_id,state_type_id,series,number',
            'dispatch:id,series,number',
            'soap_type:id,description',
            'document_type:id,description',
            'state_type:id,description',
            'user:id,name,email',
            'invoice:id,document_id,date_of_due',
            'no_stock_document:id,document_id,completed',
            'payments:id,document_id,payment,date_of_payment',
            'summary_document:id',
        ]);







        return $records;
    }

    public function records(Request $request)
    {

        $records = $this->getRecordsDocuments($request);

        return new DocumentCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function exportSystem(Request $request)
    {

        $records = $this->getRecords($request)->get();
        $company = Company::active();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        return (new DocumentExportSystem($records))
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->filters($request)
            ->download('Sistema_exportacion_' . Carbon::now() . '.xlsx');
    }
    public function exportTable(Request $request)
    {
        $records = $this->getRecords($request)->get();
        $company = Company::active();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $columns = [];
        if ($request->has('columns')) {
            $columns = explode(",", $request->columns);
        }

        return (new DocumentExportTable())
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->filters($request)
            ->columns($columns)
            ->download('Documentos_exportacion_' . Carbon::now() . '.xlsx');
    }

    public function exportConcar(Request $request)
    {

        $records = $this->getRecords($request)->get();
        $company = Company::active();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        return (new DocumentExportConcar($records))
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->filters($request)
            ->download('Concar_exportacion_' . Carbon::now() . '.xlsx');
    }

    /**
     * Devuelve los totales de la busqueda,
     *
     * Implementado en resources/js/views/tenant/documents/index.vue
     * @param Request $request
     *
     * @return array[]
     */
    public function recordsTotal(Request $request)
    {

        $FT_t = DocumentType::find('01');
        $BV_t = DocumentType::find('03');
        $NC_t = DocumentType::find('07');
        $ND_t = DocumentType::find('08');

        $BV = $this->getRecords($request)->where('state_type_id', '05')->where('document_type_id', $BV_t->id)->get()->sum(function ($row) {
            if ($row->currency_type_id !==  "PEN") {
                return $row->total * $row->exchange_rate_sale;
            } else {
                return $row->total;
            }
        });
        $FT = $this->getRecords($request)->where('state_type_id', '05')->where('document_type_id', $FT_t->id)->get()->sum(function ($row) {
            if ($row->currency_type_id !==  "PEN") {
                return $row->total * $row->exchange_rate_sale;
            } else {
                return $row->total;
            }
        });
        $NC = $this->getRecords($request)->where('state_type_id', '05')->where('document_type_id', $NC_t->id)->get()->sum(function ($row) {
            if ($row->currency_type_id !==  "PEN") {
                return $row->total * $row->exchange_rate_sale;
            } else {
                return $row->total;
            }
        });
        $ND = $this->getRecords($request)->where('state_type_id', '05')->where('document_type_id', $ND_t->id)->get()->sum(function ($row) {
            if ($row->currency_type_id !==  "PEN") {
                return $row->total * $row->exchange_rate_sale;
            } else {
                return $row->total;
            }
        });
        return [
            [
                'name' => $FT_t->description,
                'total' => "S/. " . ReportHelper::setNumber($FT),
            ],
            [
                'name' => $BV_t->description,
                'total' => "S/. " . ReportHelper::setNumber($BV),

            ],
            [
                'name' => $NC_t->description,
                'total' => "S/. " . ReportHelper::setNumber($NC),
            ],
            [
                'name' => $ND_t->description,
                'total' => "S/. " . ReportHelper::setNumber($ND),
            ],
        ];
    }
    public function searchCustomersLimit(Request $request)
    {
        //tru de boletas en env esta en true filtra a los con dni   , false a todos
        $identity_document_type_id = $this->getIdentityDocumentTypeId($request->document_type_id, $request->operation_type_id);
        //        $operation_type_id_id = $this->getIdentityDocumentTypeId($request->operation_type_id);
        $configuration = Configuration::select('search_by_phone')->first();
        $search_by_phone = $configuration->search_by_phone;
        if ($search_by_phone == true) {
            $customers = Person::where(function ($query) use ($request) {
                $query->where('number', 'like', "%{$request->input}%")
                    ->orWhere('name', 'like', "%{$request->input}%");
            })
                ->orWhere(function ($query) use ($request) {
                    $query->where('telephone', 'like', "%{$request->input}%")
                        ->orWhereHas('telephones', function ($query) use ($request) {
                            $query->where('telephone', 'like', "%{$request->input}%");
                        });
                });
        } else {
            $customers = Person::where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%");
        }
        $customers = $customers->limit(20);
        $customers = $customers->whereType('customers')->orderBy('name')
            ->whereIn('identity_document_type_id', $identity_document_type_id)
            ->whereIsEnabled()
            ->whereFilterCustomerBySeller('customers')
            ->get();

        return new PersonLiteCollection($customers);
    }

    public function searchCustomers(Request $request)
    {
        $is_from_api = request()->is('api/*');
        //tru de boletas en env esta en true filtra a los con dni   , false a todos
        $identity_document_type_id = $this->getIdentityDocumentTypeId($request->document_type_id, $request->operation_type_id);
        //        $operation_type_id_id = $this->getIdentityDocumentTypeId($request->operation_type_id);
        $configuration = Configuration::select('search_by_phone')->first();
        $search_by_phone = $configuration->search_by_phone;
        if ($search_by_phone == true) {
            $customers = Person::where(function ($query) use ($request) {
                $query->where('number', 'like', "%{$request->input}%")
                    ->orWhere('name', 'like', "%{$request->input}%");
            })
                ->orWhere(function ($query) use ($request) {
                    $query->where('telephone', 'like', "%{$request->input}%")
                        ->orWhereHas('telephones', function ($query) use ($request) {
                            $query->where('telephone', 'like', "%{$request->input}%");
                        });
                });
        } else {
            $customers = Person::where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%");
        }
        if (!$is_from_api) {
            $customers = $customers->limit(20);
        }
        $customers = $customers->whereType('customers')->orderBy('name')
            ->whereIn('identity_document_type_id', $identity_document_type_id)
            ->whereIsEnabled()
            ->whereFilterCustomerBySeller('customers')
            ->get()->transform(function ($row) {
                /** @var  Person $row */
                return $row->getCollectionData();
                /* Movido al modelo */
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'addresses' => $row->addresses,
                    'address' => $row->address
                ];
            });

        return compact('customers');
    }

    public function searchSuppliers(Request $request)
    {
        $is_from_api = request()->is('api/*');
        $configuration = Configuration::select('search_by_phone')->first();
        $search_by_phone = $configuration->search_by_phone;

        if ($search_by_phone == true) {
            $suppliers = Person::where(function ($query) use ($request) {
                $query->where('number', 'like', "%{$request->input}%")
                    ->orWhere('name', 'like', "%{$request->input}%");
            })
                ->orWhere(function ($query) use ($request) {
                    $query->where('telephone', 'like', "%{$request->input}%")
                        ->orWhereHas('telephones', function ($query) use ($request) {
                            $query->where('telephone', 'like', "%{$request->input}%");
                        });
                });
        } else {
            $suppliers = Person::where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%");
        }

        if (!$is_from_api) {
            $suppliers = $suppliers->limit(20);
        }

        $suppliers = $suppliers->whereType('suppliers')->orderBy('name')
            ->whereIsEnabled()
            ->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'addresses' => $row->addresses,
                    'address' => $row->address
                ];
            });

        return compact('suppliers');
    }

    public function searchCustomersLite(Request $request)
    {
        $customers = Person::where('number', 'like', "%{$request->input}%")
            ->orWhere('name', 'like', "%{$request->input}%");
        $customers = $customers->whereType('customers')->orderBy('name')
            ->whereIsEnabled()
            ->whereFilterCustomerBySeller('customers')
            ->get()->transform(function ($row) {

                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                ];
            });

        return compact('customers');
    }

    public function create()
    {
        $user = auth()->user();
        // $api_token = \App\Models\Tenant\Configuration::getApiServiceToken();
        $api_token = null;
        if ($user->type == 'integrator')
            return redirect('/documents');
        $date_of_issue = Carbon::now()->format('Y-m-d');
        $time_of_issue = Carbon::now()->format('H:i:s');
        $establishment_auth = Establishment::select('logo')->where('id', $user->establishment_id)->first();
        $is_contingency = 0;
        $suscriptionames = SuscriptionNames::create_new();
        $data = NameQuotations::first();
        $quotations_optional =  $data != null ? $data->quotations_optional : null;
        $quotations_optional_value =  $data != null ? $data->quotations_optional_value : null;
        $pos_lite = $user->pos_lite;
        $company = Company::getVcCompany();

        if ($pos_lite) {
            return view(
                'tenant.documents.form_lite',
                compact(
                    'suscriptionames',
                    'is_contingency',
                    'establishment_auth',
                    'quotations_optional',
                    'quotations_optional_value',
                    'api_token',
                    'date_of_issue',
                    'time_of_issue'
                )
            );
        }
        
        return view(
            'tenant.documents.form',
            compact('suscriptionames', 'is_contingency',  'establishment_auth', 'quotations_optional', 'quotations_optional_value', 'api_token', 'date_of_issue', 'time_of_issue')
        );
    }

    public function create_tensu()
    {
        if (auth()->user()->type == 'integrator')
            return redirect('/documents');

        $is_contingency = 0;
        return view('tenant.documents.form_tensu', compact('is_contingency'));
    }



    public function toPrintSplit($code, $format)
    {
        ini_set('memory_limit', '-1');
        $documents = Document::where('split_code', $code)->get();
        $name = "";
        $height_total = 0;
        $pdfData = [];

        // Obtener URLs y calcular altura total si el formato es 'ticket'
        foreach ($documents as $document) {
            $name .= $document->filename . '_';
            $url = url('') . "/print/document/{$document->external_id}/{$format}";
            $pdfContent = file_get_contents($url);
            $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
            file_put_contents($tempPdf, $pdfContent);

            if ($format == 'ticket') {
                $mpdfTemp = new \Mpdf\Mpdf();
                $pageCount = $mpdfTemp->setSourceFile($tempPdf);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $mpdfTemp->importPage($i);
                    $size = $mpdfTemp->getTemplateSize($templateId);
                    $height_total += $size['height'];
                }
            }

            $pdfData[] = [
                'url' => $url,
                'tempPdf' => $tempPdf,
            ];
        }

        if ($format == 'ticket') {
            $width = 78;
            $mpdfCombined = new \Mpdf\Mpdf(
                [
                    'format' => [
                        $width,
                        $height_total,
                    ],
                ]
            );
        } else {
            $mpdfCombined = new \Mpdf\Mpdf();
        }

        // Combinar PDFs
        foreach ($pdfData as $data) {
            $pageCount = $mpdfCombined->setSourceFile($data['tempPdf']);
            for ($i = 1; $i <= $pageCount; $i++) {
                $mpdfCombined->AddPage();
                $templateId = $mpdfCombined->importPage($i);
                $mpdfCombined->useTemplate($templateId);
            }

            unlink($data['tempPdf']);
        }

        return $mpdfCombined->Output($name . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
    public function tablesCompany($id)
    {

        $company = Company::where('website_id', $id)->first();

        $company_active = Company::active();
        $website_id = $company->website_id;
        $user = auth()->user()->id;
        $user_to_save = User::find($user);
        $establishment_id_user = $user_to_save->establishment_id;
        $user_to_save->company_active_id = $website_id;
        Log::info('user_to_save_id ' . $user_to_save->id);
        $user_to_save->save();
        $key = "cash_" . $company_active->name . "_" . $user;
        Cache::put($key, $website_id, 60);
        $payment_destinations = $this->getPaymentDestinations();
        if ($website_id && $company->id != $company_active->id) {
            $hostname = Hostname::where('website_id', $website_id)->first();
            $client = SystemClient::where('hostname_id', $hostname->id)->first();
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
        }
        Log::info('establishment_id_user ' . $establishment_id_user);
        $establishment = Establishment::find($establishment_id_user);
        $series = [];
        $establishment_info = [];
        if ($establishment) {

            $establishment_info = EstablishmentInput::set($establishment->id);
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
        }
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
    public function tables(Request $request)
    {
        $nv = $request->nv;
        $customers = $this->table('customers');
        $user = new User();
        if (Auth::user()) {
            $user = Auth::user();
        }
        $state_deliveries = [];
        $dispatchers = Dispatcher::where('is_active', true)->get();
        $unit_types = UnitType::all();
        $person_dispatchers = PersonDispatcher::all();
        $person_packers = PersonPacker::all();
        $document_id = $user->document_id;
        $series_id = $user->series_id;
        $establishment_id = $user->establishment_id;
        $userId = $user->id;
        $userType = $user->type;
        /** @var User $user */
        $series = $user->getSeries();
        $establishments = DB::connection('tenant')->table('establishments')->select('id')->where('id', $establishment_id)->get(); // Establishment::all();
        $all_establishments = DB::connection('tenant')->table('establishments')->select('id', 'description', 'customer_id')->where('active', 1)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                'customer_id' => $row->customer_id
            ];
        });
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->where('active', true)->get();
        $document_types_note = DocumentType::whereIn('id', ['07', '08'])->get();
        if ($nv) {
            $note_credit_types = NoteCreditType::whereActive()->whereNotLegalDocument()->orderByDescription()->get();
        } else {
            $note_credit_types = NoteCreditType::whereActive()->whereLegalDocument()->orderByDescription()->get();
        }
        $note_debit_types = NoteDebitType::whereActive()->orderByDescription()->get();
        $currency_types = CurrencyType::whereActive()->get();
        $operation_types = OperationType::getOperationTypesOrderByName();
        $discount_types = ChargeDiscountType::getGlobalDiscountsCache();
        $charge_types = ChargeDiscountType::getGlobalChargesCache();
        $company = Company::active();
        $document_type_03_filter = config('tenant.document_type_03_filter');

        $sellers = User::getSellersToNvCpe($establishment_id, $userId);
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');
        // $payment_method_types = $this->table('payment_method_types');
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods();
        $payment_method_types_credit = ConditionBlockPaymentMethod::getCreditPaymentMethods();
        $business_turns = BusinessTurn::where('active', true)->get();
        $enabled_discount_global = config('tenant.enabled_discount_global');
        $is_client = $this->getIsClient();
        $select_first_document_type_03 = config('tenant.select_first_document_type_03');
        $payment_conditions = PaymentCondition::all();

        $document_types_guide = DB::connection('tenant')->table('cat_document_types')->select('id', 'active', 'short', 'description')->whereIn('id', ['09', '31'])->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'active' => (bool)$row->active,
                'short' => $row->short,
                'description' => ucfirst(mb_strtolower(str_replace('REMITENTE ELECTRÓNICA', 'REMITENTE', $row->description))),
            ];
        });


        $payment_destinations = $this->getPaymentDestinations();
        $affectation_igv_types = AffectationIgvType::getAffectationIgvTypesOrderByName();
        $user = $userType;
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();
        $configuration = Configuration::select('multi_companies', 'type_discount', 'items_delivery_states')->first();
        if ($configuration->items_delivery_states) {
            $state_deliveries = StateDelivery::all();
        }
        $companies = [];
        if ($configuration->multi_companies) {
            $companies = Company::all();
        }
        $discounts_manual = [];
        $discounts_specific_items = false;
        $discounts_categories = [];
        $discounts_specific = [];
        $discounts_all = [];
        $discounts_brands = [];
        if ($configuration->type_discount) {
            $discounts_all = DB::connection('tenant')->table('discounts_types')->select('id', 'description', 'discount_value')->where('type', 'all')->where('active', true)->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'discount_value' => $row->discount_value,
                ];
            });
            $discounts_manual = DB::connection('tenant')->table('discounts_types')->select('id', 'description', 'discount_value', 'image')->where('type', 'manual')->where('active', true)->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'discount_value' => $row->discount_value,
                    'image' => url('') . '/storage/discounts/' . $row->image,
                ];
            });
            if (count($discounts_all) == 0) {
                $discounts_specific_items = DB::connection('tenant')->table('discounts_types')
                    ->where(function ($query) {
                        $query->where('type', 'specific')
                            ->orWhere('type', 'all');
                    })
                    ->limit(1)
                    ->exists();

                $discounts_categories = DiscountType::where('type', 'category')->where('active', true)->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->description,
                        'discount_value' => $row->discount_value,
                        'items' => $row->discount_type_items->transform(function ($item) {
                            return [
                                'id' => $item->id,
                                'category_id' => $item->category_id,
                            ];
                        }),
                    ];
                });

                $discounts_specific = DB::connection('tenant')->table('discounts_types')->select('id', 'description', 'discount_value')->where('type', 'specific')->where('active', true)->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->description,
                        'discount_value' => $row->discount_value,
                    ];
                });

                $discounts_brands = DiscountType::where('type', 'brand')->where('active', true)->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->description,
                        'discount_value' => $row->discount_value,
                        'items' => $row->discount_type_items->transform(function ($item) {
                            return [
                                'id' => $item->id,
                                'brand_id' => $item->brand_id,
                            ];
                        }),
                    ];
                });
            }
        }
        return compact(
            'state_deliveries',
            'all_establishments',
            'discounts_all',
            'discounts_categories',
            'discounts_brands',
            'discounts_specific',
            'discounts_specific_items',
            'discounts_manual',
            'dispatchers',
            'unit_types',
            'person_dispatchers',
            'person_packers',
            'companies',
            'document_id',
            'series_id',
            'customers',
            'establishments',
            'series',
            'document_types_invoice',
            'document_types_note',
            'note_credit_types',
            'note_debit_types',
            'currency_types',
            'operation_types',
            'discount_types',
            'charge_types',
            'company',
            'document_type_03_filter',
            'document_types_guide',
            'user',
            'sellers',
            'payment_method_types',
            'payment_method_types_credit',
            'enabled_discount_global',
            'business_turns',
            'is_client',
            'select_first_document_type_03',
            'payment_destinations',
            'payment_conditions',
            'global_discount_types',
            'affectation_igv_types'
        );
    }

    public function message_whatsapp($document_id){
        $document = Document::find($document_id);
        $establishment=Establishment::where('id',auth()->user()->establishment_id)->first();
        $message = "Estimd@: *" . $document->customer->name . "* \n";
        $message .= "Informamos que su comprobante electrónico ha sido emitido exitosamente.\n";
        $message .= "Los datos de su comprobante electrónico son:\n";
      //  $message .= "Razón social: *" . $document->customer->name . "* \n";
        $message .= "Fecha de emisión: *" . \Carbon\Carbon::parse($document->date_of_issue)->format('d-m-Y') . "* \n";
        $message .= "Nro. de comprobante: *" . $document->series . "-" . $document->number . "* \n";
        $message .= "Total: *" . number_format($document->total,2,".","")."* \n";
        $message .= "Informes: *" . $establishment->telephone."*";
        return [
            "message" => $message,
            "success" =>true,
        ];
    }

    public function getSeriesCritial(Request $request){

        $request_all = $request->all();
        Log::debug("request_all", $request_all);
        /** @var User $user */
        $user = auth()->user();
        $series = $user->getSeries();

        return [
            'series' => $series,
        ];
    }

    /**
     * Critical data only - for immediate form display
     * Contains only essential data needed to show the basic form
     */
    public function tablesCritical(Request $request)
    {
        $user = Auth::user() ?: new User();
        $establishment_id = $user->establishment_id;
        $userId = $user->id;

        $user_default_document_types = UserDefaultDocumentType::where('user_id', $userId)->get();

        // Critical data for form display (optimized selects)
        $company = Company::first();

        // User establishments (critical for form)
        $establishments = DB::connection('tenant')
            ->table('establishments')
            ->select('id')
            ->where('id', $establishment_id)
            ->get();

        $all_establishments = DB::connection('tenant')
            ->table('establishments')
            ->select('id', 'description', 'customer_id')
            ->where('active', 1)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'customer_id' => $row->customer_id
                ];
            });

        // Document types (critical for document type select) - optimized
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])
            ->where('active', true)
            ->select('id', 'description')
            ->get();

        // Currency and operation types (needed for form defaults) - optimized
        $currency_types = CurrencyType::whereActive()
            ->select('id', 'description', 'symbol')
            ->get();
        $operation_types = OperationType::whereActive()
            ->select('id', 'description')
            ->get();

        // Sellers (critical for seller select)
        $sellers = User::getSellersToNvCpe($establishment_id, $userId);

        // Series (needed for form initialization)
        /** @var User $user */
        $series = $user->getSeries();

        // User data
        $document_id = $user->document_id;
        $series_id = $user->series_id;
        $userType = $user->type;

        // Basic configuration
        $document_type_03_filter = config('tenant.document_type_03_filter');
        $select_first_document_type_03 = config('tenant.select_first_document_type_03');

        // Multi-company check
        $configuration = Configuration::select('multi_companies')->first();
        $companies = [];
        if ($configuration->multi_companies) {
            $companies = Company::all();
        }

        return compact(
            'user_default_document_types',
            'company',
            'establishments',
            'all_establishments',
            'document_types_invoice',
            'currency_types',
            'operation_types',
            'sellers',
            'series',
            'document_id',
            'series_id',
            'user',
            'document_type_03_filter',
            'select_first_document_type_03',
            'companies'
        );
    }

    /**
     * Secondary data - loaded in background
     * Contains all additional data for full functionality
     */
    public function tablesSecondary(Request $request)
    {
        $nv = $request->nv;
        $user = auth()->user();
        $establishment_id = $user->establishment_id;
        $userId = $user->id;

        // Load all secondary data (optimized selects)
        $customers = $this->table('customers');
        $dispatchers = Dispatcher::where('is_active', true)
            ->select('id', 'name', 'number')
            ->get();
        $unit_types = UnitType::select('id', 'description')->get();
        $person_dispatchers = PersonDispatcher::select('id', 'name')->get();
        $person_packers = PersonPacker::select('id', 'name')->get();

        // Document types for notes (optimized)
        $document_types_note = DocumentType::whereIn('id', ['07', '08'])
            ->select('id', 'description')
            ->get();

        if ($nv) {
            $note_credit_types = NoteCreditType::whereActive()->whereNotLegalDocument()->orderByDescription()->select('id', 'description')->get();
        } else {
            $note_credit_types = NoteCreditType::whereActive()->whereLegalDocument()->orderByDescription()->select('id', 'description')->get();
        }
        $note_debit_types = NoteDebitType::whereActive()->orderByDescription()->select('id', 'description')->get();

        // Payment methods
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods();
        $payment_method_types_credit = ConditionBlockPaymentMethod::getCreditPaymentMethods();

        // Business data
        $business_turns = BusinessTurn::where('active', true)->get();
        $payment_conditions = PaymentCondition::select('id', 'name', 'days')->get();
        $payment_destinations = $this->getPaymentDestinations();

        // Configuration and types
        $discount_types = ChargeDiscountType::getGlobalDiscountsCache();
        $charge_types = ChargeDiscountType::getGlobalChargesCache();
        $enabled_discount_global = config('tenant.enabled_discount_global');
        $is_client = $this->getIsClient();
        $affectation_igv_types = AffectationIgvType::getAffectationIgvTypesOrderByName();
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->select('id', 'description')->get();

        // Document types guide
        $document_types_guide = DB::connection('tenant')
            ->table('cat_document_types')
            ->select('id', 'active', 'short', 'description')
            ->whereIn('id', ['09', '31'])
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'active' => (bool)$row->active,
                    'short' => $row->short,
                    'description' => ucfirst(mb_strtolower(str_replace('REMITENTE ELECTRÓNICA', 'REMITENTE', $row->description))),
                ];
            });

        // State deliveries
        $state_deliveries = [];
        $configuration = Configuration::select('type_discount', 'items_delivery_states')->first();
        if ($configuration->items_delivery_states) {
            $state_deliveries = StateDelivery::select('id', 'name')->get();
        }

        // Discount system (heavy queries)
        $discounts_manual = [];
        $discounts_specific_items = false;
        $discounts_categories = [];
        $discounts_specific = [];
        $discounts_all = [];
        $discounts_brands = [];

        if ($configuration->type_discount) {
            $discounts_all = DB::connection('tenant')
                ->table('discounts_types')
                ->select('id', 'description', 'discount_value')
                ->where('type', 'all')
                ->where('active', true)
                ->get()
                ->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->description,
                        'discount_value' => $row->discount_value,
                    ];
                });

            $discounts_manual = DB::connection('tenant')
                ->table('discounts_types')
                ->select('id', 'description', 'discount_value', 'image')
                ->where('type', 'manual')
                ->where('active', true)
                ->get()
                ->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->description,
                        'discount_value' => $row->discount_value,
                        'image' => url('') . '/storage/discounts/' . $row->image,
                    ];
                });

            if (count($discounts_all) == 0) {
                $discounts_specific_items = DB::connection('tenant')
                    ->table('discounts_types')
                    ->where(function ($query) {
                        $query->where('type', 'specific')
                            ->orWhere('type', 'all');
                    })
                    ->limit(1)
                    ->exists();

                $discounts_categories = DiscountType::where('type', 'category')
                    ->where('active', true)
                    ->get()
                    ->transform(function ($row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->description,
                            'discount_value' => $row->discount_value,
                            'items' => $row->discount_type_items->transform(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'category_id' => $item->category_id,
                                ];
                            }),
                        ];
                    });

                $discounts_specific = DB::connection('tenant')
                    ->table('discounts_types')
                    ->select('id', 'description', 'discount_value')
                    ->where('type', 'specific')
                    ->where('active', true)
                    ->get()
                    ->transform(function ($row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->description,
                            'discount_value' => $row->discount_value,
                        ];
                    });

                $discounts_brands = DiscountType::where('type', 'brand')
                    ->where('active', true)
                    ->get()
                    ->transform(function ($row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->description,
                            'discount_value' => $row->discount_value,
                            'items' => $row->discount_type_items->transform(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'brand_id' => $item->brand_id,
                                ];
                            }),
                        ];
                    });
            }
        }

        return compact(
            'state_deliveries',
            'discounts_all',
            'discounts_categories',
            'discounts_brands',
            'discounts_specific',
            'discounts_specific_items',
            'discounts_manual',
            'dispatchers',
            'unit_types',
            'person_dispatchers',
            'person_packers',
            'customers',
            'document_types_note',
            'note_credit_types',
            'note_debit_types',
            'discount_types',
            'charge_types',
            'document_types_guide',
            'payment_method_types',
            'payment_method_types_credit',
            'enabled_discount_global',
            'business_turns',
            'is_client',
            'payment_destinations',
            'payment_conditions',
            'global_discount_types',
            'affectation_igv_types'
        );
    }

    public function changeStateDelivery($document_id, $state_delivery_id)
    {
        $document = Document::find($document_id);
        $document->state_delivery_id = $state_delivery_id;
        $document->save();
        return [
            'success' => true,
            'message' => 'Estado de entrega actualizado'
        ];
    }
    public function item_tables()
    {
        // $items = $this->table('items');
        $items = SearchItemController::getItemsToDocuments();
        // $items = SearchItemController::getItemsToDocumentsOptimized();
        $currentWarehouseId = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first()->id;
        $validate_stock_add_item = InventoryConfiguration::getRecordIndividualColumn('validate_stock_add_item');
        $categories = Category::getCategoriesOrderByName();
        $brands = Brand::getBrandsOrderByName();
        $affectation_igv_types = AffectationIgvType::getAffectationIgvTypesOrderByName();
        $system_isc_types = SystemIscType::getSystemIscTypesOrderByName();
        $price_types = PriceType::getPriceTypesOrderByName();
        $operation_types = OperationType::getOperationTypesOrderByName();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::getAttributeTypesOrderByName();
        $is_client = $this->getIsClient();
        $configuration = Configuration::first();

        /** Informacion adicional */
        $colors = collect([]);
        $CatItemSize = $colors;
        $CatItemStatus = $colors;
        $CatItemUnitBusiness = $colors;
        $CatItemMoldCavity = $colors;
        $CatItemPackageMeasurement = $colors;
        $CatItemUnitsPerPackage = $colors;
        $CatItemMoldProperty = $colors;
        $CatItemProductFamily = $colors;
        if ($configuration->isShowExtraInfoToItem()) {

            $colors = CatColorsItem::all();
            $CatItemSize = CatItemSize::all();
            $CatItemStatus = CatItemStatus::all();
            $CatItemUnitBusiness = CatItemUnitBusiness::all();
            $CatItemMoldCavity = CatItemMoldCavity::all();
            $CatItemPackageMeasurement = CatItemPackageMeasurement::all();
            $CatItemUnitsPerPackage = CatItemUnitsPerPackage::all();
            $CatItemMoldProperty = CatItemMoldProperty::all();
            $CatItemProductFamily = CatItemProductFamily::all();
        }


        /** Informacion adicional */

        return compact(
            'currentWarehouseId',
            'items',
            'categories',
            'brands',
            'affectation_igv_types',
            'system_isc_types',
            'price_types',
            'operation_types',
            'discount_types',
            'charge_types',
            'attribute_types',
            'is_client',
            'colors',
            'CatItemSize',
            'CatItemMoldCavity',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemProductFamily',
            'validate_stock_add_item',
            'CatItemUnitsPerPackage'
        );
    }

    public function changePersonPacker($document_id, $person_packer_id)
    {
        $document = Document::find($document_id);
        $document->person_packer_id = $person_packer_id;
        $document->save();
        return [
            'success' => true,
            'message' => 'Empaquetador asignado'
        ];
    }
    public function changePersonDispatcher($document_id, $person_dispatcher_id)
    {
        $document = Document::find($document_id);
        $document->person_dispatcher_id = $person_dispatcher_id;
        $document->save();
        return [
            'success' => true,
            'message' => 'Repartidor asignado'
        ];
    }
    public function personPackersDispatchers()
    {
        $person_dispatchers = PersonDispatcher::all();
        $person_packers = PersonPacker::all();
        return compact('person_dispatchers', 'person_packers');
    }
    public function table($table)
    {
        if ($table === 'customers') {
            $cache_key = CacheTrait::getCacheKey('customers_documents');
            $customers = CacheTrait::getCache($cache_key);
            if (!$customers) {
                $customers = Person::with([
                    'addresses',
                    'identity_document_type',
                    'department',
                    'province',
                    'district',
                    'person_type',
                    'seller',
                    'zoneRelation',
                    'addresses.department',
                    'addresses.province',
                    'addresses.district',
                    'telephones',
                    'dispatch_addresses'
                ])
                    ->whereType('customers')
                    ->whereIsEnabled()
                    ->whereFilterCustomerBySeller('customers')
                    ->orderBy('name')
                    ->take(20)
                    ->get();


                $customers = $customers->transform(function ($row) {
                    /** @var Person $row */
                    return $row->getCollectionDataDocument();
                });
                CacheTrait::storeCache($cache_key, $customers);
            }
            return $customers;
        }

        if ($table === 'prepayment_documents') {
            $prepayment_documents = Document::whereHasPrepayment()->get()->transform(function ($row) {

                $total = round($row->pending_amount_prepayment, 2);
                $amount = ($row->affectation_type_prepayment == '10') ? round($total / 1.18, 2) : $total;

                return [
                    'id' => $row->id,
                    'description' => $row->series . '-' . $row->number,
                    'series' => $row->series,
                    'number' => $row->number,
                    'document_type_id' => ($row->document_type_id == '01') ? '02' : '03',
                    // 'amount' => $row->total_value,
                    // 'total' => $row->total,
                    'amount' => $amount,
                    'total' => $total,

                ];
            });
            return $prepayment_documents;
        }

        if ($table === 'payment_method_types') {

            return PaymentMethodType::getPaymentMethodTypes();
            /*
            $payment_method_types = PaymentMethodType::whereNotIn('id', ['05', '08', '09'])->get();
            $end_payment_method_types = PaymentMethodType::whereIn('id', ['05', '08', '09'])->get(); //by requirement
            return $payment_method_types->merge($end_payment_method_types);
            */
        }

        if ($table === 'items') {

            return SearchItemController::getItemsToDocuments();
            // return SearchItemController::getItemsToDocumentsOptimized();

            $establishment_id = auth()->user()->establishment_id;
            $warehouse = ModuleWarehouse::where('establishment_id', $establishment_id)->first();
            // $items_u = Item::whereWarehouse()->whereIsActive()->whereNotIsSet()->orderBy('description')->take(20)->get();
            $items_u = Item::with('warehousePrices')
                ->whereIsActive()
                ->orderBy('description');
            $items_s = Item::with('warehousePrices')
                ->where('items.unit_type_id', 'ZZ')
                ->whereIsActive()
                ->orderBy('description');
            $items_u = $items_u
                ->take(20)
                ->get();
            $items_s = $items_s
                ->take(10)
                ->get();
            $items = $items_u->merge($items_s);

            return collect($items)->transform(function ($row) use ($warehouse) {
                /** @var Item $row */
                return $row->getDataToItemModal($warehouse);
                $detail = $this->getFullDescription($row, $warehouse);
                return [
                    'id' => $row->id,
                    'full_description' => $detail['full_description'],
                    'model' => $row->model,
                    'brand' => $detail['brand'],
                    'warehouse_description' => $detail['warehouse_description'],
                    'category' => $detail['category'],
                    'stock' => $detail['stock'],
                    'internal_id' => $row->internal_id,
                    'can_edit_price' => (bool)$row->can_edit_price,
                    'description' => $row->description,
                    'currency_type_id' => $row->currency_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => Item::getSaleUnitPriceByWarehouse($row, $warehouse->id),
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
                            'range_min' => $row->range_min,
                            'range_max' => $row->range_max,
                            'warehouse_id' => $row->warehouse_id,
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
                            'checked' => false,
                            'warehouse_id' => $row->warehouse_id,
                            'warehouse' => $row->warehouse_id ? $row->warehouse->description : null,
                        ];
                    }),
                    'lots' => [],
                    'lots_enabled' => (bool)$row->lots_enabled,
                    'series_enabled' => (bool)$row->series_enabled,

                ];
            });
        }

        return [];
    }

    public function getFullDescription($row, $warehouse)
    {

        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? "{$row->category->name}" : "";
        $brand = ($row->brand) ? "{$row->brand->name}" : "";
        $location = ($row->location) ? "{$row->location}" : "";

        if ($row->unit_type_id != 'ZZ') {
            if (isset($row['stock'])) {
                $warehouse_stock = number_format($row['stock'], 2);
            } else {
                $warehouse_stock = ($row->warehouses && $warehouse) ?
                    number_format($row->warehouses->where('warehouse_id', $warehouse->id)->first()->stock, 2) :
                    0;
            }

            $stock = ($row->warehouses && $warehouse) ? "{$warehouse_stock}" : "";
        } else {
            $stock = '';
        }

        $desc = "{$desc} - {$brand} {$location}";

        return [
            'full_description' => $desc,
            'brand' => $brand,
            'category' => $category,
            'stock' => $stock,
            'warehouse_description' => $warehouse->description,
        ];
    }


    public function record($id)
    {
        $record = new DocumentResource(Document::findOrFail($id));

        return $record;
    }

    public function duplicate($id)
    {
        try {
            $document = Document::find($id);
            // $document->date_of_issue = date('Y-m-d');
            $res = $this->storeWithData_duplicate($document, true, 'invoice', 'a4');

            $document_id = $res['data']['id'];
            $documents = Document::find($document_id);
            //$this->associateDispatchesToDocument_duplicate( $documents, $document_id);
            //  $this->associateSaleNoteToDocument($documents, $document_id);
            if ($res['data']['document']->sale_note_id != null) {
                SaleNote::where('id', $documents->sale_note_id)
                    ->update(['document_id' => $document_id]);
            }

            return $res;
        } catch (Exception $e) {
            $this->generalWriteErrorLog($e);
            return $this->generalResponse(false, 'Ocurrió un error: ' . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine());
        }
    }
    public function store(DocumentRequest $request)
    {
        try {
            $validate = $this->validateDocument($request);
            if (!$validate['success']) return $validate;
            if (!in_array($request->document_type_id, ['07', '08'])) {
                $this->checkPurchaseOrden($request->purchase_order,  $request->id, Document::class, $request->items, $request->sale_note_id);
            }
            $res = $this->storeWithData($request->all());
            $document_id = $res['data']['id'];
            $this->associateDispatchesToDocument($request, $document_id);
            $this->associateSaleNoteToDocument($request, $document_id);
            if ($request->has('orden_id') && $request->orden_id != null) {
                $orden_id = $request->orden_id;
                $this->processOrder($orden_id, $document_id, $request->customer_id, false);
            }
            // Log::info('DocumentController.store: ' . json_encode($res));
            return $res;
        } catch (Exception $e) {
            $code = $e->getCode();
            $this->generalWriteErrorLog($e);
            $message = $e->getMessage();
            $message_has_unique_file_word = str_contains($message, 'unique_file');
            Log::error($e->getMessage() . " " . $e->getFile() . " " . $e->getLine());
            if ($code == '23000' && $message_has_unique_file_word) {
                $message = "Debe eliminar los comprobantes de prueba antes de empezar a emitir comprobantes con valor legal.";
                return $this->generalResponse(false, 'Ocurrió un error: ' . $message);
            } else {

                return $this->generalResponse(false, 'Ocurrió un error: ' . $e->getMessage() . " " . $e->getFile() . " " . $e->getLine());
            }
            // return $this->generalResponse(false, 'Ocurrió un error: ' . $e->getMessage());

        }
    }


    /**
     * Validaciones previas al proceso de facturacion
     *
     * @param array $request
     * @return array
     */
    public function validateDocument($request)
    {

        // validar nombre de producto pdf en xml - items
        foreach ($request->items as $item) {

            if ($item['name_product_xml']) {
                // validar error 2027 sunat
                if (mb_strlen($item['name_product_xml']) > 500) {
                    return [
                        'success' => false,
                        'message' => "El campo Nombre producto en PDF/XML no puede superar los 500 caracteres - Producto/Servicio: {$item['item']['description']}"
                    ];
                }
            }
        }

        return [
            'success' => true,
            'message' => ''
        ];
    }

    /**
     * Guarda los datos del hijo para el proceso de suscripcion. #952
     * Toma el valor de nota de venta y lo pasa para la boleta/factura
     *
     * @param $data
     */
    public static function setChildrenToData(&$data)
    {
        $request = request();
        if (
            $request != null &&
            $request->has('sale_note_id') &&
            $request->sale_note_id
        ) {
            $saleNote = SaleNote::find($request->sale_note_id);
            if ($saleNote != null && isset($data['customer'])) {
                $customer = $data['customer'];
                $customerNote = (array)$saleNote->customer;
                if (isset($customerNote['children'])) {
                    $customer['children'] = (array)$customerNote['children'];
                }
                $data['customer'] = $customer;
                $data['grade'] = $saleNote->getGrade();
                $data['section'] = $saleNote->getSection();
            }
        }
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Throwable
     */
    public function storeWithData($data, $duplicate = false, $type = 'invoice', $format = 'a4')
    {
        // Simular error 504 Gateway Timeout - Forzar headers
        // http_response_code(504);
        // header('Content-Type: application/json');
        // echo json_encode([
        //     'error' => 'Gateway Timeout - Simulated Error',
        //     'message' => 'The server timed out waiting for the request'
        // ]);
        // exit;

        self::setChildrenToData($data);
        $fact =  DB::connection('tenant')->transaction(function () use ($data, $duplicate, $type, $format) {
            $company_id = $data['company_id'];
            $plate_number_id = isset($data['plate_number_id']) ? $data['plate_number_id'] : null;
            $sale_note_payment_id = isset($data['sale_note_payment_id']) ? $data['sale_note_payment_id'] : null;
            $quotation_id = isset($data['quotation_id']) ? $data['quotation_id'] : null;
            $type_quotation = isset($data['type_quotation']) ? $data['type_quotation'] : null;
            $allPaymentsIds = isset($data['allPaymentsIds']) ? $data['allPaymentsIds'] : null;
            $key_code = isset($data['key_code']) ? $data['key_code'] : null;
            $operation_type = isset($data['operation_type']) ? $data['operation_type'] : null;
            if ($company_id) {
                $company = Company::where(
                    'website_id',
                    $company_id
                )->first();
                $facturalo = new Facturalo($company);
            } else {
                $facturalo = new Facturalo();
                $company = Company::active();
            }
            $result = $facturalo->save($data, $duplicate);
            if ($company_id) {


                $document_number = $company->document_number;
                $document_result = $result->getDocument();
                $series = $document_result->series;
                $number = $document_result->number;
                if ($document_number) {
                    $document_number->$series = $number + 1;
                } else {
                    $document_number = new \stdClass();
                    $document_number->$series = $number + 1;
                }
                $company->document_number = $document_number;
                $company->save();
            }
            $dc_result = $result->getDocument();
            $series_result = $dc_result->series;
            $series_db = DB::connection('tenant')->table('series')->where('number', $series_result)->first();
            $is_internal = $series_db->internal;

            $configuration = Configuration::first();
            if ($type == 'invoice' && $configuration->college) {
                $document_id = $result->getDocument()->id;
                $periods = $data['months'];
                $client_id = $data['customer_id'];
                $child_id = $data['child_id'];
                if ($client_id && $child_id && $periods) {
                    SuscriptionPayment::where('document_id', $document_id)->delete();
                    foreach ($periods as  $period) {
                        $date = Carbon::createFromDate($period['year'], $period['value'], 1);
                        SuscriptionPayment::create([
                            'child_id' => $child_id,
                            'client_id' => $client_id,
                            'document_id' => $document_id,
                            'period' => $date,
                        ]);
                    }
                }
            }
            //aqui
            if (!$is_internal) {
                if (isset($result->id) == true) {
                    $facturalo->createXmlUnsigned($result->id);
                } else {
                    $facturalo->createXmlUnsigned();
                }
                if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                    $facturalo->sendPseNew();
                } else {
                    $facturalo->signXmlUnsigned();
                    if ($duplicate == true) {
                        $configuration = Configuration::first();
                        $facturalo->setActions(['send_xml_signed' => (bool) $configuration->send_auto]);
                    }
                }
            }


            $facturalo->updateHash();
            $facturalo->updateQr();
            if ($duplicate == true) {
                $facturalo->createPdf($result, $type, $format);
            } else {
                $facturalo->createPdf();
            }
            $document_result = $result->getDocument();
            if(isset($document_result->quotation_id)){
                $document_result->auditGeneratedFrom('quotation', $document_result->quotation_id);
            }
            if(isset($document_result->sale_note_id)){
                $document_result->auditGeneratedFrom('sale_note', $document_result->sale_note_id);
            }
            if ($key_code && $operation_type) {
                (new AdminKeyController())->useKey(new Request([
                    'key_code' => $key_code,
                    'operation_type' => $operation_type,
                    'document_id' => $document_result->id,
                ]));
            }
            if ($quotation_id && $configuration->split_quotation_to_document_services_and_not_services) {
                if ($type_quotation) {
                    $field_name = $type_quotation == 'services' ? 'document_service_id' : 'document_not_service_id';
                    QuotationServicesNotServices::create([
                        'quotation_id' => $quotation_id,
                        $field_name => $document_result->id,
                    ]);
                } else {
                    QuotationServicesNotServices::create([
                        'quotation_id' => $quotation_id,
                        'document_service_id' => $document_result->id,
                        'document_not_service_id' => $document_result->id,
                    ]);
                }
            }
            if ($plate_number_id) {
                PlateNumberDocument::create([
                    'plate_number_id' => $plate_number_id,
                    'document_id' => $document_result->id,
                    'km' => $data['km'],
                ]);
            }
            if ($sale_note_payment_id) {
                $sale_note_payment = SaleNotePayment::find($sale_note_payment_id);
                $sale_note_id = $sale_note_payment->sale_note_id;
                $date_of_payment = $sale_note_payment->date_of_payment;
                SaleNotePayment::where('sale_note_id', $sale_note_id)
                    ->where('date_of_payment', $date_of_payment)
                    ->where('document_prepayment_id', null)
                    ->update(['document_prepayment_id' => $document_result->id]);
            }
            if ($allPaymentsIds && count($allPaymentsIds) > 0) {
                foreach ($allPaymentsIds as $paymentId) {
                    $sale_note_payment = SaleNotePayment::find($paymentId);
                    $sale_note_id = $sale_note_payment->sale_note_id;
                    $date_of_payment = $sale_note_payment->date_of_payment;
                    SaleNotePayment::where('sale_note_id', $sale_note_id)
                        ->where('date_of_payment', $date_of_payment)
                        ->where('document_prepayment_id', null)
                        ->update(['document_prepayment_id' => $document_result->id]);
                }
            }
            if($configuration->send_auto_email){
                try {
                    $customer_id = $document_result->customer_id;
                    $customer = Person::find($customer_id);
                    $optional_email = $customer->optional_email;
                    $optional_email = unserialize($optional_email);
    
                    $customer_mail = $customer->email;
    
                    if ($customer_mail) {
                        $request_email = new DocumentEmailRequest();
                        $request_email->replace(
                            [
                                'id' => $document_result->id,
                                'customer_email' => $customer_mail
                            ]
                        );
    
                        $res_email = (new DocumentController())->email($request_email);
                        if ($res_email['success']) {
                            $document_result->save();
                        }
                    }
                    if (isset($optional_email)) {
                        foreach ($optional_email as $email) {
                            $request_email = new DocumentEmailRequest();
                            $request_email->replace(
                                [
                                    'id' => $document_result->id,
                                    'customer_email' => $email['email']
                                ]
                            );
                            $res_email = (new DocumentController())->email($request_email);
                        }
                    }
                } catch (Exception $e) {
                    // Log::error("Error al enviar email: {$document_result->id}");
                }
            }
            
            if ((!$company->pse || ($company->pse && $company->type_send_pse != 2)) && $document_result->state_type_id != '55') {
                $facturalo->senderXmlSignedBill();
            }
            //hasta aqui

            return $facturalo;
        });

        $document = $fact->getDocument();
        //generar response
        $response = $fact->getResponse();
        $base_url = url('/');
        $external_id = $document->external_id;
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $print_format = $establishment->print_format ?? 'ticket';
        $url_print = "{$base_url}/print/document/{$external_id}/$print_format";
        return [
            'success' => true,
            'data' => [
                'document' => $document,
                'id' => $document->id,
                'number_full' => $document->number_full,
                'response' => $response,
                'url_print' => $url_print
            ]
        ];
    }
    public function getDocument($id)
    {
        $document = Document::with('invoice', 'items', 'fee', 'user', 'soap_type', 'state_type', 'document_type')->find($id);
        return $document;
    }
    public function anulatePse($id)
    {
        $document = Document::find($id);
        $pse = new PseService($document);
        $response = $pse->anulatePse();

        return $response;
    }
    public function anulatePseCheck($id)
    {
        $document = Document::find($id);
        $pse = new PseService($document);
        $response = $pse->check_anulate();
        return $response;
    }
    public function checkPse($id)
    {
        $document = Document::find($id);
        $pse = new PseService($document);
        $response = $pse->download_file();

        return $response;
    }
    public function jsonPse($id)
    {
        $document = Document::find($id);
        $filename = $document->getNumberFullAttribute() . '.json';
        $pse = new PseService($document);
        $response = $pse->payloadToJson();
        if ($response['success'] == false) {
            return $response;
        } else {
            $payload = $response['payload'];
            // Crear la respuesta con el contenido del archivo JSON
            $response = response()->make($payload);
            $response->header('Content-Disposition', 'attachment; filename=' . $filename);
            $response->header('Content-Type', 'application/json');

            return $response;
        }
    }

    public function sendPse($id)
    {
        $document = Document::find($id);
        $pse = new PseService($document);

        $response = $pse->sendToPse();

        return $response;
    }

    public function storeWithData_duplicate($data, $duplicate = false, $type = 'invoice', $format = 'a4')
    {
        self::setChildrenToData($data);
        $fact =  DB::connection('tenant')->transaction(function () use ($data, $duplicate, $type, $format) {
            $company_id = isset($data['company_id']) ? $data['company_id'] : null;
            if ($company_id) {
                $company = Company::where(
                    'website_id',
                    $company_id
                )->first();
                if (!$company) {
                    $company = Company::active();
                }
                $facturalo = new Facturalo($company);
            } else {
                $facturalo = new Facturalo();
                $company = Company::active();
            }
            // $facturalo = new Facturalo();
            $result = $facturalo->save($data, $duplicate);
            $configuration = Configuration::first();
            if ($type == 'invoice' && $configuration->college) {
                $document_id = $result->getDocument()->id;
                $periods = $data['months'];
                $client_id = $data['customer_id'];
                $child_id = $data['child_id'];
                if ($client_id && $child_id && $periods) {
                    SuscriptionPayment::where('document_id', $document_id)->delete();
                    foreach ($periods as  $period) {
                        $date = Carbon::createFromDate($period['year'], $period['value'], 1);
                        SuscriptionPayment::create([
                            'child_id' => $child_id,
                            'client_id' => $client_id,
                            'document_id' => $document_id,
                            'period' => $date,
                        ]);
                    }
                }
            }
            if (isset($result->id) == true) {
                $facturalo->createXmlUnsigned($result->id);
            } else {
                $facturalo->createXmlUnsigned();
            }
            if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                $facturalo->sendPseNew();
            } else {
                $facturalo->signXmlUnsigned();

                //hasta aqui
            }
            // $facturalo->signXmlUnsigned();
            if ($duplicate == true) {
                $configuration = Configuration::first();
                $facturalo->setActions(['send_xml_signed' => (bool) $configuration->send_auto]);
            }
            $facturalo->updateHash();
            // $facturalo->updateQr();
            if ($duplicate == true) {
                $facturalo->createPdf($result, $type, $format);
            } else {

                $facturalo->createPdf();
            }
            $facturalo->senderXmlSignedBill();

            return $facturalo;
        });

        $document = $fact->getDocument();
        $response = $fact->getResponse();

        return [
            'success' => true,
            'data' => [
                'document' => $document,
                'id' => $document->id,
                'number_full' => $document->number_full,
                'response' => $response
            ]
        ];
    }
    public function getDocumentStats()
    {
        $document = Document::whereNotSent()->count();
        $document_regularize_shipping = Document::whereRegularizeShipping()->count();
        $document_to_anulate = Document::whereToAnulate()->count();
        return [
            'document_to_send' => $document,
            'document_regularize_shipping' => $document_regularize_shipping,
            'document_to_anulate' => $document_to_anulate
        ];
    }
    private function associateSaleNoteToDocument(Request $request, int $documentId)
    {
        if ($request->sale_note_id) {
            SaleNote::where('id', $request->sale_note_id)
                ->update(['document_id' => $documentId]);
        }
        $notes = $request->sale_notes_relateds;
        if ($notes) {
            foreach ($notes as $note) {
                $sale_note_id = $note['id'] ?? null;
                if ($sale_note_id) {
                    $sale_note = SaleNote::find($sale_note_id);
                    if (!empty($sale_note)) {
                        $sale_note->document_id = $documentId;
                        $sale_note->push();
                    }
                }
            }
        }
    }
    public function sendInd($id)
    {
        $document = Document::find($id);
        $document->ticket_single_shipment = true;
        $document->force_send_by_summary = false;
        $document->save();
        return [
            "success" => true,
            "message" => "Cambiado a envío individual"
        ];
    }
    public function sendRes($id)
    {
        $document = Document::find($id);
        $document->ticket_single_shipment = false;
        $document->force_send_by_summary = true;
        $document->save();
        return [
            "success" => true,
            "message" => "Cambiado a envío por resumen"
        ];
    }
    // private function associateDispatchesToDocument_duplicate($document, int $documentId)
    // {
    //     $dispatches_relateds = $request->dispatches_relateds;

    //     foreach ($dispatches_relateds as $dispatch) {
    //         $dispatchToArray = explode('-', $dispatch);
    //         if (count($dispatchToArray) === 2) {
    //             Dispatch::where('series', $dispatchToArray[0])
    //                 ->where('number', $dispatchToArray[1])
    //                 ->update([
    //                     'reference_document_id' => $documentId,
    //                 ]);

    //             $document = Dispatch::where('series', $dispatchToArray[0])
    //                 ->where('number', $dispatchToArray[1])
    //                 ->first();

    //             if ($document) {
    //                 $facturalo = new Facturalo();
    //                 $facturalo->createPdf($document, 'dispatch', 'a4');
    //             }
    //         }
    //     }
    // }

    private function associateDispatchesToDocument(Request $request, int $documentId)
    {
        $dispatches_relateds = $request->dispatches_relateds;
        if ($dispatches_relateds) {
            foreach ($dispatches_relateds as $dispatch) {
                $dispatchToArray = explode('-', $dispatch);
                if (count($dispatchToArray) === 2) {
                    Dispatch::where('series', $dispatchToArray[0])
                        ->where('number', $dispatchToArray[1])
                        ->update([
                            'reference_document_id' => $documentId,
                        ]);

                    $document = Dispatch::where('series', $dispatchToArray[0])
                        ->where('number', $dispatchToArray[1])
                        ->first();

                    if ($document) {
                        $facturalo = new Facturalo();
                        $facturalo->createPdf($document, 'dispatch', 'a4');
                    }
                }
            }
        }
    }
    public function copy($documentId)
    {
        $configuration = Configuration::first();
        $is_contingency = 0;
        $isUpdate = false;

        $copy = true;
        return view('tenant.documents.copy', compact('is_contingency',  'configuration', 'documentId', 'isUpdate', 'copy'));
    }
    public function edit($documentId)
    {
        $api_token = \App\Models\Tenant\Configuration::getApiServiceToken();
        if (auth()->user()->type == 'integrator') {
            return redirect('/documents');
        }
        $suscriptionames = SuscriptionNames::create_new();
        $configuration = Configuration::first();
        $is_contingency = 0;
        $establishment = Establishment::whereActive()->get();
        $establishment_auth = Establishment::where('id', auth()->user()->establishment_id)->get();
        $isUpdate = true;
        $data = NameQuotations::first();
        $quotations_optional =  $data != null ? $data->quotations_optional : null;
        $quotations_optional_value =  $data != null ? $data->quotations_optional_value : null;


        return view('tenant.documents.form', compact('suscriptionames', 'quotations_optional', 'quotations_optional_value', 'is_contingency', 'establishment', 'establishment_auth', 'configuration', 'documentId', 'isUpdate', 'api_token'));
    }

    /**
     * @param \App\Http\Requests\Tenant\DocumentUpdateRequest $request
     * @param                                                 $id
     *
     * @return array
     * @throws \Throwable
     */
    public function update(DocumentUpdateRequest $request, $id)
    {
        $validate = $this->validateDocument($request);
        $this->checkPurchaseOrden($request->purchase_order,  $id, Document::class, $request->items);
        if (!$validate['success']) return $validate;
        $fact =  DB::connection('tenant')->transaction(function () use ($request, $id) {
            $facturalo = new Facturalo();
            $company = Company::active();
            $facturalo->update($request->all(), $id);

            $facturalo->createXmlUnsigned();
            if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                $facturalo->sendPseNew();
            } else {
                $facturalo->signXmlUnsigned();

                //hasta aqui
            }
            // $facturalo->signXmlUnsigned();
            $facturalo->updateHash();
            $facturalo->updateQr();
            $facturalo->createPdf();

            return $facturalo;
        });

        $document = $fact->getDocument();
        $configuration = Configuration::first();

        if ($configuration->plate_number_config) {
            PlateNumberDocument::where('document_id', $id)->delete();

            if ($request->plate_number_id) {
                PlateNumberDocument::create([
                    'plate_number_id' => $request->plate_number_id,
                    'document_id' => $document->id,
                    'km' => $request->km,
                ]);
            }
        }
        $response = $fact->getResponse();

        $document->auditUpdated(null, $document->total, $document->total);
        if ($response == null) {
            return [
                'success' => true,
                'sent' => true,
                'description' => 'El documento se editó exitosamente.',
                'data' => [
                    'success' => true,
                    'sent' => true,
                    'id' => $document->id,
                    'description' => 'El documento se editó exitosamente.',
                ],
            ];
        }
        return [
            'success' => true,
            'data' => [
                'id' => $document->id,
                'response' => $response,
            ],
        ];
    }

    public function show($documentId)
    {
        $configuration = Configuration::first();
        $document = Document::with(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'group', 'items', 'invoice', 'note', 'payments', 'fee', 'quotation', 'related_document'])->findOrFail($documentId);
        if ($configuration->college) {
            $suscriptions = SuscriptionPayment::where('document_id', $documentId)->get();
            $document->periods = $suscriptions;
        }
        $item_ids = $document->items->pluck('item_id')->toArray();
        $label_color_item = ItemLabelColor::whereIn('item_id', $item_ids)->get();
        if (!$label_color_item) {
            $label_color_item = collect([]);
        }
        foreach ($document->items as &$item) {
            $discounts = [];
            $item_id = $item->item_id;
            $label_color_item = null;
            if ($label_color_item && $configuration->label_item_color) {
                $label_color_item = $label_color_item->where('item_id', $item_id)->first();
                if ($label_color_item) {
                    $item->label_color = $label_color_item->labelColor;
                }
            }
            if ($item->discounts) {

                foreach ($item->discounts as $discount) {
                    $discount_type = ChargeDiscountType::query()->find($discount->discount_type_id);
                    $discounts[] = [
                        'amount' => $discount->amount,
                        'base' => $discount->base,
                        'description' => $discount->description,
                        'discount_type_id' => $discount->discount_type_id,
                        'factor' => $discount->factor,
                        'percentage' => $discount->factor * 100,
                        'is_amount' => false,
                        'discount_type' => $discount_type
                    ];
                }
            }
            $item->discounts = $discounts;
            $item->stock = Item::find($item->item_id)->getStockByWarehouse($document->establishment_id);
        }

        return response()->json([
            'data' => $document,
            'success' => true,
        ], 200);
    }

    public function reStore($document_id)
    {
        $fact =  DB::connection('tenant')->transaction(function () use ($document_id) {
            $document = Document::find($document_id);
            $old_state_type_id = $document->state_type_id;
            $type = 'invoice';
            if ($document->document_type_id === '07') {
                $type = 'credit';
            }
            if ($document->document_type_id === '08') {
                $type = 'debit';
            }
            $configuration = Configuration::first();
            $company = null;
            $multi_companies = $configuration->multi_companies;
            if ($multi_companies) {
                $website_id = $document->website_id;
                if ($website_id) {
                    $company = Company::where('website_id', $website_id)->first();
                }
            } else {
                $company = Company::active();
            }
            if ($company) {
                $facturalo = new Facturalo($company);
            } else {
                $facturalo = new Facturalo();
            }
            $facturalo->setDocument($document);
            $facturalo->setType($type);
            $facturalo->createXmlUnsigned();
            if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                $facturalo->sendPseNew();
            } else {
                $facturalo->signXmlUnsigned();
            }
            $facturalo->updateHash();
            $facturalo->updateQr();
            $facturalo->updateSoap('02', $type);
            if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                // $facturalo->sendPseDispatch();
            } else {
                $old_state_type_id = $document->state_type_id;
                $facturalo->updateState('01');
                if (in_array($old_state_type_id, ['09', '11'])) {
                    $document_items = $document->items;
                    foreach ($document_items as $item) {
                        $this->document_item_restore($item);
                        $this->recalculateStock($item->item_id);
                    }
                }
            }
            $format = $configuration->paper_size_modal_documents;
            $facturalo->createPdf($document, $type, $format);
            $new_state_type_id = $document->state_type_id;
            $new_history = new AuditorHistory;
            $new_history->user_id = auth()->id();
            $new_history->document_id = $document_id;
            $new_history->new_state_type_id = $new_state_type_id;
            $new_history->old_state_type_id = $old_state_type_id;
            $new_history->is_recreate = true;
            $new_history->save();
            //            $facturalo->senderXmlSignedBill();
        });

        //        $document = $fact->getDocument();
        //        $response = $fact->getResponse();

        return [
            'success' => true,
            'message' => 'El documento se volvio a generar.',
        ];
    }

    public function email(DocumentEmailRequest $request)
    {
        try {
            $company = Company::active();
            $configuration = Configuration::first();
            $paper_size_modal_documents = $configuration->paper_size_modal_documents;
            $document = Document::find($request->input('id'));
            (new Facturalo)->createPdf($document, 'invoice', $paper_size_modal_documents);
            $customer_email = $request->input('customer_email');
            if ($customer_email == null || $customer_email == '') {
                return [
                    'success' => false,
                    'message' => 'El correo electrónico es requerido'
                ];
            }
            $email = $customer_email;

            // Obtén las cuentas bancarias desde la base de datos
            $bank_accounts = BankAccount::where('status', 1)
                ->where('show_in_documents', 1)
                ->get();

            // Asegúrate de pasar las cuentas bancarias al Mailable
            $mailable = new DocumentEmail($company, $document, $bank_accounts);
            $id = (int)$request->input('id');
            $sendIt = EmailController::SendMail($email, $mailable, $id, 1);

            return [
                'success' => $sendIt
            ];
        } catch (Exception $e) {
            // Log the full exception details
            Log::error('Error enviando email: ' . $e->getMessage(), ['exception' => $e]);

            // Return the error message with more details (for debugging purposes, only in development)
            return response()->json(['message' => 'Error enviando email.', 'error' => $e->getMessage()], 500);
        }
    }

    public function massiveUpdateDateIssueToCdr(Request $request)
    {
        $d_start = $request->input('d_start');
        $d_end = $request->input('d_end');
        $documents = Document::whereBetween('date_of_issue', [$d_start, $d_end])
            ->whereIn('state_type_id', ['05'])
            ->get();

        $count_success = 0;
        $count_error = 0;
        $count_updated = 0;
        foreach ($documents as $document) {
            $response = $this->updateDateIssueToCdr($document->id);
            if ($response['success']) {
                $count_success++;
                $count_updated++;
            } else {
                $count_error++;
            }
        }
        return [
            'success' => true,
            'message' => "Se verificó exitosamente {$count_success} documentos y hubo {$count_error} errores. Se actualizó el estado de {$count_updated} documentos.",
        ];
    }
    public function massiveVerifyCdrAndTags(Request $request)
    {
        $d_start = $request->input('d_start');
        $d_end = $request->input('d_end');
        $documents = Document::whereBetween('date_of_issue', [$d_start, $d_end])
            ->whereIn('state_type_id', ['05'])
            ->get();
        $count_success = 0;
        $count_error = 0;
        $count_updated = 0;
        foreach ($documents as $document) {
            $response = $this->verifyCdrAndTags($document->id);
            if ($response['has_updated']) {
                $count_updated++;
            }
            if ($response['success']) {
                $count_success++;
            } else {
                $count_error++;
            }
        }

        return [
            'success' => true,
            'message' => "Se verificó exitosamente {$count_success} documentos y hubo {$count_error} errores. Se actualizó el estado de {$count_updated} documentos.",
        ];
    }
    public function updateDateIssueToCdr($document_id)
    {
        $document = Document::find($document_id);
        $company = Company::active();
        $configuration = Configuration::first();
        if ($configuration->multi_companies && $document->website_id) {
            $company = Company::where('website_id', $document->website_id)->first();
        }
        $facturalo = new Facturalo($company);
        $facturalo->setDocument($document);
        $response = $facturalo->updateDateIssueToCdr();
        return $response;
    }
    public function verifyCdrAndTags($document_id)
    {
        $document = Document::find($document_id);
        $company = Company::active();
        $configuration = Configuration::first();
        if ($configuration->multi_companies && $document->website_id) {
            $company = Company::where('website_id', $document->website_id)->first();
        }
        $facturalo = new Facturalo($company);
        $facturalo->setDocument($document);
        $response = $facturalo->verifyCdrAndTags();

        return $response;
    }

    public function send($document_id)
    {
        $document = Document::find($document_id);

        $fact =  DB::connection('tenant')->transaction(function () use ($document) {
            $configuration = Configuration::first();
            $multi_companies = $configuration->multi_companies;
            if ($multi_companies) {
                $company = Company::where('website_id', $document->website_id)->first();
                $facturalo = new Facturalo($company);
            } else {
                $company = Company::active();
                $facturalo = new Facturalo();
            }

            $facturalo->setDocument($document);
            if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                $facturalo->sendPseNewAuto();
            } else {
                $facturalo->loadXmlSigned();
                $facturalo->onlySenderXmlSignedBill();
            }
            return $facturalo;
        });
        $success = true;
        $response = $fact->getResponse();
        $document = $fact->getDocument();
        $state_type_id = $document->state_type_id;
        

        if (!isset($response['description'])) {
            if (isset($response['message'])) {
                $response['description'] = $response['message'];
            }
        }
        if (isset($response['success'])) {
            $success = $response['success'];
        }
        return [
            'success' => $success,
            'message' => $response['description'],
            'state_type_id' => $state_type_id,
        ];
    }

    public function consultCdr($document_id)
    {
        $document = Document::find($document_id);

        $fact =  DB::connection('tenant')->transaction(function () use ($document) {
            $facturalo = new Facturalo();
            $facturalo->setDocument($document);
            $facturalo->consultCdr();
        });

        $response = $fact->getResponse();

        return [
            'success' => true,
            'message' => $response['description'],
        ];
    }

    public function sendServer($document_id, $query = false)
    {
        $document = Document::find($document_id);

        $bearer = $this->getTokenServer();
        $api_url = $this->getUrlServer();
        $client = new Client(['base_uri' => $api_url, 'verify' => false]);

        // $zipFly = new ZipFly();
        if (!$document->data_json) throw new Exception("Campo data_json nulo o inválido - Comprobante: {$document->fullnumber}");

        $data_json = (array)$document->data_json;
        $data_json['numero_documento'] = $document->number;
        $data_json['external_id'] = $document->external_id;
        $data_json['hash'] = $document->hash;
        $data_json['qr'] = $document->qr;
        $data_json['query'] = $query;
        $data_json['file_xml_signed'] = base64_encode($this->getStorage($document->filename, 'signed'));
        $data_json['file_pdf'] = base64_encode($this->getStorage($document->filename, 'pdf'));
        $res = $client->post('/api/documents_server', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $bearer,
                'Accept' => 'application/json',
            ],
            'form_params' => $data_json
        ]);

        $response = json_decode($res->getBody()->getContents(), true);

        if ($response['success']) {
            $document->send_server = true;
            $document->save();
        }

        return $response;
    }

    public function checkServer($document_id)
    {
        $document = Document::find($document_id);
        $bearer = $this->getTokenServer();
        $api_url = $this->getUrlServer();

        $client = new Client(['base_uri' => $api_url, 'verify' => false]);

        $res = $client->get('/api/document_check_server/' . $document->external_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $bearer,
                'Accept' => 'application/json',
            ],
        ]);

        $response = json_decode($res->getBody()->getContents(), true);

        if ($response['success']) {
            $state_type_id = $response['state_type_id'];
            $document->state_type_id = $state_type_id;
            $document->save();

            if ($state_type_id === '05') {
                $this->uploadStorage($document->filename, base64_decode($response['file_cdr']), 'cdr');
            }
        }

        return $response;
    }

    public function searchCustomerById($id)
    {

        $customers = Person::with('addresses')->whereType('customers')
            ->where('id', $id)
            // ->whereFilterCustomerBySeller('customers')
            ->get()->transform(function ($row) {
                /** @var  Person $row */
                return $row->getCollectionData();
                /* Movido al modelo */
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'addresses' => $row->addresses,
                    'address' => $row->address
                ];
            });

        return compact('customers');
    }

    public function getIdentityDocumentTypeId($document_type_id, $operation_type_id)
    {

        // if($operation_type_id === '0101' || $operation_type_id === '1001') {

        if (in_array($operation_type_id, ['0101', '1001', '1004'])) {

            if ($document_type_id == '01') {
                $identity_document_type_id = [6];
            } else {
                if (config('tenant.document_type_03_filter')) {
                    $identity_document_type_id = [1];
                } else {
                    $identity_document_type_id = [1, 4, 6, 7, 0];
                }
            }
        } else {
            $identity_document_type_id = [1, 4, 6, 7, 0];
        }

        return $identity_document_type_id;
    }

    public function changeToRegisteredStatus($document_id)
    {
        $document = Document::find($document_id);
        if ($document->state_type_id === '01') {
            $document->state_type_id = '05';
            $document->save();

            return [
                'success' => true,
                'message' => 'El estado del documento fue actualizado.',
            ];
        }
    }

    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new DocumentsImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }

    public function importTwoFormat(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new DocumentsImportTwoFormat();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }

    public function messageLockedEmission()
    {

        $exceed_limit = DocumentHelper::exceedLimitDocuments();

        if ($exceed_limit['success']) {
            return [
                'success' => false,
                'message' => $exceed_limit['message'],
            ];
        }

        // $configuration = Configuration::first();
        // $quantity_documents = Document::count();
        // $quantity_documents = $configuration->quantity_documents;

        // if($configuration->limit_documents !== 0 && ($quantity_documents > $configuration->limit_documents))
        //     return [
        //         'success' => false,
        //         'message' => 'Alcanzó el límite permitido para la emisión de comprobantes',
        //     ];


        return [
            'success' => true,
            'message' => '',
        ];
    }

    public function getImageFromTicket($document_id)
    {
        $document = Document::select('external_id')->find($document_id);
        // $url = url('')."/print/document/{$document->external_id}/ticket";
        $url = "https://demo.facturadorsmart.pe/print/document/466f6238-cf89-4d28-9537-219a64f83920/ticket";
        $imageService = new ImageService();
        $image = $imageService->getImageFromTicket($url);
        return $image;
    }
    public function getRecords($request)
    {

        $configuration = Configuration::getConfig();
        $d_end = $request->d_end;
        $ubigeo = $request->ubigeo;
        $department_id = null;
        $province_id = null;
        if ($ubigeo) {
            $ubigeo = explode(',', $ubigeo);
            $department_id = $ubigeo[0];
            if (count($ubigeo) > 1) {
                $province_id = $ubigeo[1];
            }
        }
        $website_id = $request->company_id;
        $web_platform_id = $request->web_platform_id;
        $state_delivery_id = $request->state_delivery_id;
        $d_start = $request->d_start;
        $date_of_issue = $request->date_of_issue;
        $document_type_id = $request->document_type_id;
        $state_type_id = $request->state_type_id;
        $lote_code = $request->lote_code;
        $number = $request->number;
        $series = $request->series;
        $website_id = $request->website_id;
        $pending_payment = ($request->pending_payment == "true") ? true : false;
        $customer_id = $request->customer_id;
        $item_id = $request->item_id;
        $time = $request->time;
        $category_id = $request->category_id;
        $purchase_order = $request->purchase_order;
        $guides = $request->guides;
        $plate_numbers = $request->plate_numbers;
        $establishment_id = $request->establishment_id;

        $records = Document::query();
        if ($d_start && $d_end) {
            $records->whereBetween('date_of_issue', [$d_start, $d_end]);
        }
        if ($date_of_issue) {
            $records = Document::where('date_of_issue', 'like', '%' . $date_of_issue . '%');
        }
        /** @var Builder $records */
        if ($document_type_id) {
            $records->where('document_type_id', 'like', '%' . $document_type_id . '%');
        }

        if ($series) {
            $records->where('series', 'like', '%' . $series . '%');
        }
        if ($time) {
            $explode_time = explode("-", $time);
            $start_time = trim($explode_time[0]);
            $end_time = trim($explode_time[1]);

            $records->whereBetween('time_of_issue', [$start_time, $end_time])->latest();
        }
        if ($department_id) {
            $records->whereHas('person', function ($query) use ($department_id, $province_id) {
                if ($province_id) {
                    $query->where('province_id', $province_id);
                } else {
                    $query->where('department_id', $department_id);
                }
            });
        }
        if ($website_id) {
            $records->where('website_id', $website_id);
        }
        if ($number) {
            $records->where('number', $number);
        }
        if ($establishment_id) {
            $records->where('establishment_id', $establishment_id);
        }
        if ($state_type_id) {
            $records->where('state_type_id', 'like', '%' . $state_type_id . '%');
        }
        if ($purchase_order) {
            $records->where('purchase_order', $purchase_order);
        }
        $records->whereTypeUser()->latest();
        if ($pending_payment) {
            $records->whereRaw('(SELECT COALESCE(SUM(payment), 0) FROM document_payments WHERE document_payments.document_id = documents.id) + COALESCE(JSON_UNQUOTE(JSON_EXTRACT(retention, "$.amount")), 0) < documents.total')
                ->whereNotIn('document_type_id', ['07', '08'])
                ->whereDoesntHave('note2');
        }
        // if ($pending_payment) {
        //     $records->where('total_canceled', false)
        //         ->whereNotIn('document_type_id', ['07', '08']);
        // }

        if ($customer_id) {
            $records->where('customer_id', $customer_id);
        }

        if ($item_id) {
            $records->whereHas('items', function ($query) use ($item_id, $configuration) {
                $query->where('item_id', $item_id)
                    ->orWhereHas('relation_item', function ($q) use ($item_id) {
                        $q->whereHas('sets', function ($q2) use ($item_id) {
                            $q2->where('individual_item_id', $item_id);
                        });
                    });
            });
        }

        if ($lote_code) {
            $records->whereHas('items', function ($query) use ($lote_code) {
                $query->where(function ($q) use ($lote_code) {
                    $q->whereRaw("JSON_SEARCH(JSON_EXTRACT(item, '$.IdLoteSelected[*].code'), 'all', ?) IS NOT NULL", ["%{$lote_code}%"]);
                });
            });
        }
        if ($web_platform_id) {
            $records->whereHas('items', function ($query) use ($web_platform_id) {
                $query->whereHas('relation_item', function ($q) use ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                });
            });
        }
        if ($state_delivery_id) {
            if ($state_delivery_id == 1) {
                $records->where(function ($query) {
                    $query->where('state_delivery_id', 1)
                        ->orWhere('state_delivery_id', null);
                });
            } else {
                $records->where('state_delivery_id', $state_delivery_id);
            }
        }

        if ($category_id) {
            $records->whereHas('items', function ($query) use ($category_id) {
                $query->whereHas('relation_item', function ($q) use ($category_id) {
                    $q->where('category_id', $category_id);
                });
            });
        }
        if (!empty($guides)) {
            $records->where('guides', 'like', DB::raw("%\"number\":\"%") . $guides . DB::raw("%\"%"));
        }
        if ($plate_numbers) {
            if ($configuration->plate_number_config) {
                $records->whereHas('plateNumberDocument', function ($query) use ($plate_numbers) {
                    $query->whereHas('plateNumber', function ($q) use ($plate_numbers) {
                        $q->where('description', 'like', '%' . $plate_numbers . '%');
                    });
                });
            } else {
                $records->where('plate_number', 'like', '%' . $plate_numbers . '%');
            }
        }
        return $records;
    }


    public function records_massive_update(Request $request)
    {
        $records = $this->getRecords($request);

        return new DocumentLiteCollection($records->paginate(50));
    }
    public function update_massive(Request $request)
    {
        try {
            DB::beginTransaction();

            $updates = [];
            $count_recreate = 0;

            // Preparar actualizaciones si hay fecha de emisión
            if ($request->date_of_issue) {
                $updates['date_of_issue'] = $request->date_of_issue;
            }

            // Preparar actualizaciones si hay estado
            if ($request->state_type_id) {
                $updates['state_type_id'] = $request->state_type_id;
            }

            // Ejecutar actualizaciones en batch si hay campos para actualizar
            if (!empty($updates)) {
                Document::whereIn('id', $request->ids)
                    ->update($updates);
            }

            // Recrear documentos si se solicita
            if ($request->is_recreate) {
                $count_recreate = collect($request->ids)
                    ->map(function ($id) {
                        $response = $this->reStore($id);
                        return $response['success'] ? 1 : 0;
                    })
                    ->sum();
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Documentos actualizados con éxito',
                'count_recreate' => $count_recreate,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Error al actualizar documentos: ' . $e->getMessage()
            ];
        }
    }
    public function data_table_update_massive()
    {


        $customers = $this->table('customers');
        $state_types = StateType::get();
        $document_types = DocumentType::whereIn('id', ['01', '03',])->get();
        $series = Series::whereIn('document_type_id', ['01', '03',])->get();
        $establishments = Establishment::whereActive()->get();
        $companies = Company::get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'number' => $row->number,
                'website_id' => $row->website_id,
            ];
        });
        return compact(
            'companies',
            'customers',
            'state_types',
            'document_types',
            'series',
            'establishments'
        );
    }
    public function data_table()
    {

        $customers = $this->table('customers');
        $items = $this->getItems();
        $web_platforms = [];
        $categories = Category::orderBy('name')->get();
        $state_types = StateType::get();
        $document_types = DocumentType::whereIn('id', ['01', '03', '07', '08'])->get();
        $series = Series::whereIn('document_type_id', ['01', '03', '07', '08'])->get();
        $user = auth('api')->user() ?? auth()->user();
        $departments = Department::all();
        $provinces = Province::all();
        $companies = [];
        $configuration = Configuration::first();
        if ($configuration->items_delivery_states) {
            $web_platforms = WebPlatform::all();
        }
        if ($configuration->multi_companies) {
            $companies = Company::get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'website_id' => $row->website_id,
                ];
            });
        }
        $establishments = Establishment::query();
        if ($user->type != 'admin' && $user->type != 'superadmin') {
            $establishments->where('id', $user->establishment_id);
        }
        $establishments = $establishments->whereActive()->get();

        return compact(
            'web_platforms',
            'companies',
            'departments',
            'provinces',
            'customers',
            'document_types',
            'series',
            'establishments',
            'state_types',
            'items',
            'categories'
        );
    }
    public function items($id)
    {
        $document_items = DocumentItem::where('document_id', $id)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->item->description,
                'name_product_pdf' => $row->name_product_pdf,
                'quantity' => $row->quantity,
            ];
        });
        return compact('document_items');
    }

    public function getItems()
    {

        $items = Item::orderBy('description')->take(20)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => ($row->internal_id) ? "{$row->internal_id} - {$row->description}" : $row->description,
            ];
        });

        return $items;
    }


    public function getDataTableItem(Request $request)
    {

        $items = Item::where('description', 'like', "%{$request->input}%")
            ->orWhere('internal_id', 'like', "%{$request->input}%")
            ->orderBy('description')
            ->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => ($row->internal_id) ? "{$row->internal_id} - {$row->description}" : $row->description,
                ];
            });

        return $items;
    }


    private function updateMaxCountPayments($value)
    {
        if ($value > $this->max_count_payment) {
            $this->max_count_payment = $value;
        }
        // $this->max_count_payment = 20 ;//( $value > $this->max_count_payment) ? $value : $this->$max_count_payment;
    }

    private function transformReportPayment($resource)
    {

        $records = $resource->transform(function ($row) {

            $total_paid = collect($row->payments)->sum('payment');
            $total = $row->total;
            $total_difference = round($total - $total_paid, 2);

            $this->updateMaxCountPayments($row->payments->count());

            return (object)[

                'id' => $row->id,
                'ruc' => $row->customer->number,
                // 'date' =>  $row->date_of_issue->format('Y-m-d'),
                // 'date' =>  $row->date_of_issue,
                'date' => $row->date_of_issue->format('d/m/Y'),
                'invoice' => $row->number_full,
                'comercial_name' => $row->customer->trade_name,
                'business_name' => $row->customer->name,
                'zone' => $row->customer->department->description,
                'total' => number_format($row->total, 2, ".", ""),

                'payments' => $row->payments,

                /*'payment1' =>  ( isset($row->payments[0]) ) ?  number_format($row->payments[0]->payment, 2) : '',
                'payment2' =>  ( isset($row->payments[1]) ) ?  number_format($row->payments[1]->payment, 2) : '',
                'payment3' =>   ( isset($row->payments[2]) ) ?  number_format($row->payments[2]->payment, 2) : '',
                'payment4' =>   ( isset($row->payments[3]) ) ?  number_format($row->payments[3]->payment, 2) : '', */

                'balance' => $total_difference,
                'person_type' => isset($row->person->person_type->description) ? $row->person->person_type->description : '',
                'department' => $row->customer->department->description,
                'district' => $row->customer->district->description,

                /*'reference1' => ( isset($row->payments[0]) ) ?  $row->payments[0]->reference : '',
                'reference2' =>  ( isset($row->payments[1]) ) ?  $row->payments[1]->reference : '',
                'reference3' =>  ( isset($row->payments[2]) ) ?  $row->payments[2]->reference : '',
                'reference4' =>  ( isset($row->payments[3]) ) ?  $row->payments[3]->reference : '', */
            ];
        });

        return $records;
    }

    public function report_payments(Request $request)
    {
        // $month_format = Carbon::parse($month)->format('m');

        if ($request->anulled == 'true') {
            $records = Document::whereBetween('date_of_issue', [$request->date_start, $request->date_end])->get();
        } else {
            $records = Document::whereBetween('date_of_issue', [$request->date_start, $request->date_end])->where('state_type_id', '!=', '11')->get();
        }

        $source = $this->transformReportPayment($records);

        return (new PaymentExport)
            ->records($source)
            ->payment_count($this->max_count_payment)
            ->download('Reporte_Pagos_' . Carbon::now() . '.xlsx');
    }

    public function destroyDocument($document_id)
    {
        try {

            DB::connection('tenant')->transaction(function () use ($document_id) {

                $record = Document::findOrFail($document_id);
                $this->deleteAllPayments($record->payments);
                $record->delete();
            });

            return [
                'success' => true,
                'message' => 'Documento eliminado con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => 'El Documento esta siendo usada por otros registros, no puede eliminar'] : ['success' => false, 'message' => 'Error inesperado, no se pudo eliminar el Documento'];
        }
    }

    public function storeCategories(CategoryRequest $request)
    {
        $id = $request->input('id');
        $category = Category::firstOrNew(['id' => $id]);
        $category->fill($request->all());
        $category->save();


        return [
            'success' => true,
            'message' => ($id) ? 'Categoría editada con éxito' : 'Categoría registrada con éxito',
            'data' => $category

        ];
    }

    public function storeBrands(BrandRequest $request)
    {
        $id = $request->input('id');
        $brand = Brand::firstOrNew(['id' => $id]);
        $brand->fill($request->all());
        $brand->save();


        return [
            'success' => true,
            'message' => ($id) ? 'Marca editada con éxito' : 'Marca registrada con éxito',
            'data' => $brand
        ];
    }

    public function searchExternalId(Request $request)
    {
        return response()->json(Document::where('external_id', $request->external_id)->first());
    }

    public function importExcelFormat(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new DocumentImportExcelFormat();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();

                return [
                    'success' => true,
                    'message' =>  'Se importaron ' . $data['registered'] . ' de ' . $data['total_records'] . ' registros',
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function importExcelTables()
    {
        $document_types = DocumentType::query()
            ->whereIn('id', ['01', '03'])
            ->get();

        $series = Series::query()
            ->whereIn('document_type_id', ['01', '03'])
            ->where('establishment_id', auth()->user()->establishment_id)
            ->get();

        return [
            'document_types' => $document_types,
            'series' => $series,
        ];
    }

    public function retention($document_id)
    {
        $document = Document::query()
            ->select('id', 'series', 'number', 'retention')
            ->where('id', $document_id)->first();

        if ($document->retention) {
            $retention = $document->retention;
            $amount = $retention->amount;
            if ($retention->currency_type_id === 'USD') {
                $amount = $amount * $retention->exchange_rate;
            }
            $amount = round($amount, 0);
            return [
                'success' => true,
                'form' => [
                    'document_id' => $document_id,
                    'document_number' => $document->number_full,
                    'amount' => $amount,
                    'voucher_date_of_issue' => $retention->voucher_date_of_issue ?: null,
                    'voucher_number' => $retention->voucher_number ?: null,
                    'voucher_amount' => $retention->voucher_amount ?: $amount,
                    'voucher_filename' => $retention->voucher_filename ?: null,
                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'No existe retención'
        ];
    }

    public function retentionStore(Request $request)
    {
        try {
            $voucher_filename = $request->input('voucher_filename');
            $temp_path = $request->input('temp_path');

            if ($temp_path) {
                $file_name_old_array = explode('.', $voucher_filename);
                $file_content = file_get_contents($temp_path);
                $extension = $file_name_old_array[1];
                $voucher_filename = Str::slug('r_' . $file_name_old_array[0]) . '_' . date('YmdHis') . '.' . $extension;
                Storage::disk('tenant')->put('document_payment' . DIRECTORY_SEPARATOR . $voucher_filename, $file_content);
            }

            $document_id = $request->input('document_id');
            $voucher_number = $request->input('voucher_number');
            $voucher_date_of_issue = $request->input('voucher_date_of_issue');
            $voucher_amount = $request->input('voucher_amount');

            Document::query()
                ->where('id', $document_id)->update([
                    'retention->voucher_date_of_issue' => $voucher_date_of_issue,
                    'retention->voucher_number' => $voucher_number,
                    'retention->voucher_amount' => $voucher_amount,
                    'retention->voucher_filename' => $voucher_filename
                ]);

            return [
                'success' => true,
                'message' => 'Retención actualizada satisfactoriamente',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    private function savePayments($document, $payments)
    {
        $total = $document->total;
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
                    "change" => $change,
                    "payment" => $payment,
                    "payment_received" => isset($row['payment_received']) ? $row['payment_received'] : null,
                ];
            });
        }

        foreach ($payments as $row) {
            if ($balance < 0 && !$this->apply_change) {
                $row['change'] = abs($balance);
                $row['payment'] = $row['payment'] - abs($balance);
                $this->apply_change = true;
            }

            $record = $document->payments()->create(
                [
                    'document_id' => $row->document_id,
                    'date_of_payment' => $row->date_of_payment,
                    'payment_method_type_id' => $row->payment_method_type_id,
                    'has_card' => $row->has_card,
                    'payment_received'  => $row->payment_received,
                    'payment'  => $row->payment,
                ]
            );

            // para carga de voucher
            $this->saveFilesFromPayments($row, $record, 'documents');

            //considerar la creacion de una caja chica cuando recien se crea el cliente
            if (isset($row['payment_destination_id'])) {
                $this->createGlobalPayment($record, $row);
            }
        }
    }
    // public function message_whatsapp($document_id)
    // {
    //     $document = Document::find($document_id);
    //     $message = "Estimd@: *" . $document->customer->name . "* \n";
    //     $message .= "Informamos que su comprobante electrónico ha sido emitido exitosamente.\n";
    //     $message .= "Los datos de su comprobante electrónico son:\n";
    //     $message .= "Razón social: *" . $document->customer->name . "* \n";
    //     $message .= "Fecha de emisión: *" . \Carbon\Carbon::parse($document->date_of_issue)->format('d-m-Y') . "* \n";
    //     $message .= "Nro. de comprobante: *" . $document->series . "-" . $document->number . "* \n";
    //     $message .= "Total: *" . number_format($document->total, 2, ".", "") . "*";

    //     return [
    //         "message" => $message,
    //         "success" => true,
    //     ];
    // }
    public function retentionUpload(Request $request)
    {
        try {
            $validate_upload = UploadFileHelper::validateUploadFile($request, 'file');

            if (!$validate_upload['success']) {
                return $validate_upload;
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $temp = tempnam(sys_get_temp_dir(), 'document_retention');
                file_put_contents($temp, file_get_contents($file));

                return [
                    'success' => true,
                    'data' => [
                        'filename' => $file->getClientOriginalName(),
                        'temp_path' => $temp,
                    ]
                ];
            }
            return [
                'success' => false,
                'message' => __('app.actions.upload.error'),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // Agregar estos nuevos métodos

    public function getPseCompaniesStates()
    {
        $companies = Company::where('pse', true)
            ->select('name', 'website_id')
            ->get();

        $states = StateType::whereIn('id', ['01', '03', '05', '07', '09', '11', '13'])
            ->select('id', 'description')
            ->get();

        return [
            'companies' => $companies,
            'states' => $states
        ];
    }

    public function searchPse(Request $request)
    {
        $website_id = $request->website_id;
        $state_type_id = $request->state_type_id;
        $date_start = $request->date_start;
        $date_end = $request->date_end;

        $documents = Document::query()
            ->when($website_id, function ($query) use ($website_id) {
                return $query->where('website_id', $website_id);
            })
            ->when($state_type_id, function ($query) use ($state_type_id) {
                return $query->where('state_type_id', $state_type_id);
            })
            ->when($date_start && $date_end, function ($query) use ($date_start, $date_end) {
                return $query->whereBetween('date_of_issue', [$date_start, $date_end]);
            })
            ->with(['state_type', 'person'])
            ->select([
                'id',
                'date_of_issue',
                'series',
                'number',
                'customer_id',
                'total',
                'state_type_id'
            ])
            ->orderBy('id', 'asc')
            ->take(100)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'state_type_id' => $row->state_type_id,
                    'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                    'number_full' => $row->series . '-' . $row->number,
                    'customer_name' => $row->person->name,
                    'total' => number_format($row->total, 2),
                    'state_type_description' => $row->state_type->description
                ];
            });

        return $documents;
    }
    public function checkReference(Request $request)
    {
        // Obtener las referencias del request y asegurarse que sea un array
        $references = $request->input('references', []);

        // Convertir todas las referencias a minúsculas y asegurar que sean strings
        $references = array_map(function ($reference) {
            return strtolower(strval($reference));
        }, $references);

        // Usar una subconsulta con LOWER() para comparar en minúsculas
        $document = DocumentPayment::whereIn(DB::raw('LOWER(reference)'), $references)->first();

        if ($document) {
            return response()->json(['success' => false, 'message' => 'Referencia ya existe en el documento ' . $document->document->series . '-' . $document->document->number]);
        }
        return response()->json(['success' => true, 'message' => 'Referencia válida']);
    }
    public function saveOrUpdateBox(Request $request)
    {

        //se recibe el id del documento
        $id = $request->input('id');
        //si no viene no se guarda nada porque debe haber un documento de por medio
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'No se encontró el documento']);
        }
        $box = $request->input('box');

        $document = Document::findOrFail($id);
        $message = 'Box actualizado correctamente';
        $document->box = $box;
        $document->save();

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function massiveEmitExport(Request $request)
    {
        $data = $request->input('data');
        $data = json_decode($data, true);
        $results = $request->input('results');
        $results = json_decode($results, true);
        return ExcelFacade::download(
            new DocumentMassiveEmitExport($data, $results),
            'resultados_emision_masiva.xlsx'
        );
    }

    public function adjustKardex($id)
    {
        try {
            $type = Document::class;

            // Obtener el documento para verificar su estado usando consulta optimizada
            $document = DB::connection('tenant')
                ->table('documents')
                ->where('id', $id)
                ->select('id', 'state_type_id')
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Estados válidos que deben mantener registro en kardex
            $validStates = ['01', '03', '05'];

            // Obtener todos los registros de kardex para este documento
            $inventoryKardexRecords = InventoryKardex::where('inventory_kardexable_id', $id)
                ->where('inventory_kardexable_type', $type)
                ->get()
                ->groupBy('item_id');

            $adjustedRecords = [];
            $deletedRecords = 0;
            $adjustedItems = 0;

            foreach ($inventoryKardexRecords as $itemId => $records) {
                $adjustedItems++;

                if (!in_array($document->state_type_id, $validStates)) {
                    // Estado inválido: eliminar todos los registros para este item
                    foreach ($records as $record) {
                        $record->delete();
                        $deletedRecords++;
                    }

                    $adjustedRecords[$itemId] = [
                        'action' => 'deleted_all',
                        'reason' => 'Invalid document state',
                        'deleted_records' => $records->pluck('id')->toArray(),
                        'final_quantity' => 0
                    ];
                } else {
                    // Estado válido (01, 03, 05): mantener solo un registro con cantidad negativa

                    // Obtener la cantidad original del último registro para determinar la cantidad a restar
                    $originalQuantity = abs($records->last()->quantity);
                    $finalQuantity = -$originalQuantity; // Siempre negativa para restar stock

                    // Mantener solo el último registro
                    $keepRecord = $records->last();
                    $recordsToDelete = $records->slice(0, -1);

                    // Actualizar la cantidad del registro que se mantiene (siempre negativa)
                    $keepRecord->update(['quantity' => $finalQuantity]);

                    // Eliminar los registros duplicados
                    foreach ($recordsToDelete as $recordToDelete) {
                        $recordToDelete->delete();
                        $deletedRecords++;
                    }

                    $adjustedRecords[$itemId] = [
                        'action' => 'adjusted',
                        'kept_record_id' => $keepRecord->id,
                        'original_quantity' => $records->sum('quantity'),
                        'final_quantity' => $finalQuantity,
                        'deleted_records' => $recordsToDelete->pluck('id')->toArray()
                    ];
                }
            }

            // Obtener el kardex actualizado para retornar
            $updatedInventoryKardex = InventoryKardex::where('inventory_kardexable_id', $id)
                ->where('inventory_kardexable_type', $type)
                ->get()
                ->groupBy('item_id');

            $documentStateStatus = in_array($document->state_type_id, $validStates) ? 'valid' : 'invalid';

            return response()->json([
                'success' => true,
                'message' => "Kardex depurado correctamente. Items procesados: {$adjustedItems}, Registros eliminados: {$deletedRecords}",
                'document_state' => $document->state_type_id,
                'document_state_status' => $documentStateStatus,
                'valid_states' => $validStates,
                'adjusted_items' => $adjustedItems,
                'deleted_records' => $deletedRecords,
                'adjustments' => $adjustedRecords,
                'inventoryKardex' => $updatedInventoryKardex
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al depurar kardex: ' . $e->getMessage()
            ], 500);
        }
    }

    public function preview(DocumentRequest $request)
    {
        $validate = $this->validateDocument($request);
        if (!$validate['success']) return $validate;
        $facturalo = new Facturalo();
        $inputs = $request->all();
        $facturalo->setActions(array_key_exists('actions', $inputs) ? $inputs['actions'] : []);

        $document = new Document($inputs);

        $facturalo->setPaymentsPreview($document, $inputs['payments']);
        $facturalo->setFeePreview($document, $inputs['fee']);

        foreach ($inputs['items'] as $row) {
            $item = new \App\Models\Tenant\DocumentItem($row);
            $document->items[] = $item;
        }

        if ($inputs['hotel']) {
            $hotel = new \Modules\BusinessTurn\Models\DocumentHotel($inputs['hotel']);
            $document->hotel = $hotel;
        }

        if ($inputs['transport']) {
            $transport = new \Modules\BusinessTurn\Models\DocumentTransport($inputs['transport']);
            $document->transport = $transport;
        }

        $invoice = new \App\Models\Tenant\Invoice($inputs['invoice']);
        $document->invoice = $invoice;
        $facturalo->previewPdf($document, $inputs['type']);
    }
}
