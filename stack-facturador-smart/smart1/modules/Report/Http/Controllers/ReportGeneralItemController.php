<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\GeneralItemExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Document;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\QuotationItem;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\StateType;
use App\Models\Tenant\Zone;
use App\Traits\JobReportTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Models\PurchaseOrderItem;
use Modules\Report\Http\Resources\GeneralItemCollection;
use Modules\Report\Http\Resources\GeneralItemTotalCollection;
use Modules\Report\Jobs\ProcessItemReport;
use Modules\Report\Traits\ReportTrait;


class ReportGeneralItemController extends Controller
{
    use ReportTrait, JobReportTrait;
    private $documents_excluded = ['11', '09'];
    public function __construct()
    {
    }

    public function filter()
    {
        $configuration = Configuration::first();
        $companies = [];
        if ($configuration->multi_companies) {
            $companies = Company::get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'website_id' => $row->website_id,
                ];
            });
        }

        $zones = Zone::all();
        $state_types = StateType::getStateTypes();
        $customers = $this->getPersons('customers');
        $suppliers = $this->getPersons('suppliers');
        $items = $this->getItems('items');
        $brands = $this->getBrands();
        $web_platforms = $this->getWebPlatforms();
        $document_types = DocumentType::whereIn('id', ['01', '03', '07', '80', 'COT'])->get();
        $categories = $this->getCategories();
        $ingredients = $this->getIngredients();
        $lines = $this->getLines();
        $users = $this->getUsers();
        $establishments = Establishment::where('active', 1)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
            ];
        });

        return compact(
            'zones',
            'document_types',
            'suppliers',
            'customers',
            'items',
            'web_platforms',
            'brands',
            'categories',
            'ingredients',
            'lines',
            'users',
            'companies',
            'state_types',
            'establishments'
        );
    }


    public function index(Request $request)
    {
        $apply_conversion_to_pen = $this->applyConversiontoPen($request);
        $configuration = Configuration::first();

        return view('report::general_items.index', compact('apply_conversion_to_pen', 'configuration'));
    }


    public function records(Request $request)
    {
        $records = $this->getRecordsItems($request->all())->latest('id');
        // $quantity = $records->sum("utility_item");
        return new GeneralItemCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function recordsTotal(Request $request)
    {

        $records = $this->getRecordsItems($request->all())->get();
        $total = new GeneralItemTotalCollection($records);
        $total->toArray($request);
        $totals = [

            "quantity" => $total->sum('quantity'),
            "total" => $total->sum('total'),
            "total_item_purchase" => $total->sum('total_item_purchase'),
            "utility_item" => $total->sum('utility_item'),
        ];
        return compact('totals');
    }
    public function getRecordsItems2($request)
    {
        $data_of_period = $this->getDataOfPeriod($request);
        $data_type = $this->getDataType($request);

        $document_type_id = $request['document_type_id'];
        $d_start = $data_of_period['d_start'];
        $d_end = $data_of_period['d_end'];
        $website_id = isset($request['company_id']) ? $request['company_id'] : null;
        $person_id = $request['person_id'];
        $type_person = $request['type_person'];
        $item_id = $request['item_id'];
        $brand_id = $request['brand_id'];
        $category_id = $request['category_id'];

        $user_id = $request['user_id'];
        $user_type = $request['user_type'] != null ? $request['user_type'] : 'VENDEDOR';
        $web_platform_id = $request['web_platform_id'];
        $zone_id = isset($request['zone_id']) ? $request['zone_id'] : null;

        $records = $this->dataItems2($d_start, $d_end, $document_type_id, $data_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type, $zone_id, $website_id);

        return $records;
    }
    public function getRecordsItems3($request)
    {
        $data_of_period = $this->getDataOfPeriod($request);
        $data_type = $this->getDataType($request);

        $document_type_id = $request['document_type_id'];
        $d_start = $data_of_period['d_start'];
        $state_type_id = $request['state_type_id'];
        $establishment_id = isset($request['establishment_id']) ? $request['establishment_id'] : null;
        $min_stock_lower_zero = isset($request['min_stock_lower_zero']) ? $request['min_stock_lower_zero'] == "true" : false;

        $d_end = $data_of_period['d_end'];
        $website_id = isset($request['company_id']) ? $request['company_id'] : null;
        $person_id = $request['person_id'];
        $type_person = $request['type_person'];
        $item_id = $request['item_id'];
        $brand_id = $request['brand_id'];
        $category_id = $request['category_id'];
        $ingredient_id = $request['ingredient_id'];
        $line_id = $request['line_id'];

        $user_id = $request['user_id'];
        $user_type = $request['user_type'] != null ? $request['user_type'] : 'VENDEDOR';
        $web_platform_id = $request['web_platform_id'];
        $zone_id = isset($request['zone_id']) ? $request['zone_id'] : null;

        $records = $this->getItemsResume($d_start, $d_end, $document_type_id, $data_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type, $zone_id, $website_id, $state_type_id, $establishment_id, $ingredient_id, $line_id, $min_stock_lower_zero);

        return $records;
    }
    //GeneralItemTotalCollection
    public function getRecordsItems($request)
    {
        $data_of_period = $this->getDataOfPeriod($request);
        $data_type = $this->getDataType($request);

        $document_type_id = $request['document_type_id'];
        $d_start = $data_of_period['d_start'];
        $state_type_id = $request['state_type_id'];
        $establishment_id = isset($request['establishment_id']) ? $request['establishment_id'] : null;
        $min_stock_lower_zero = isset($request['min_stock_lower_zero']) ? $request['min_stock_lower_zero'] == "true" : false;
        $d_end = $data_of_period['d_end'];
        $website_id = isset($request['company_id']) ? $request['company_id'] : null;
        $person_id = $request['person_id'];
        $type_person = $request['type_person'];
        $item_id = $request['item_id'];
        $brand_id = $request['brand_id'];
        $category_id = $request['category_id'];
        $ingredient_id = $request['ingredient_id'];
        $line_id = $request['line_id'];

        $user_id = $request['user_id'];
        $user_type = $request['user_type'] != null ? $request['user_type'] : 'VENDEDOR';
        $web_platform_id = $request['web_platform_id'];
        $zone_id = isset($request['zone_id']) ? $request['zone_id'] : null;

        $records = $this->dataItems($d_start, $d_end, $document_type_id, $data_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type, $zone_id, $website_id, $state_type_id, $establishment_id, $ingredient_id, $line_id, $min_stock_lower_zero);

        return $records;
    }
    private function getDocumentColumns(){
        $document_columns = [
            'document_items.id',
            'document_items.item_id',
            'document_items.item',
            'document_items.quantity',
            'document_items.unit_value', 
            'document_items.unit_price',
            'document_items.total',
            'document_items.total_value',
            'document_items.total_igv',
            'document_items.total_isc',
            'document_items.total_discount',
            'document_items.affectation_igv_type_id',
            'document_items.system_isc_type_id',
            'documents.seller_id as seller_id',
            'documents.company as company_name',
            'documents.user_id as user_id',
            'documents.series as document_series',
            'documents.number as document_number',
            DB::raw('NULL as document_prefix'),
            'documents.date_of_issue as document_date',
            'documents.state_type_id as document_state_type_id',
            'documents.document_type_id',
            'documents.customer as customer',
            'documents.customer_id as customer_id',
            'documents.additional_information as observation',
            'documents.currency_type_id as currency_type_id',
            'documents.exchange_rate_sale as exchange_rate_sale',
            'documents.purchase_order as purchase_order',
        ];
        return $document_columns;
    }

    private function getSaleNoteColumns(){

        $sale_note_columns = [
            'sale_note_items.id',
            'sale_note_items.item_id',
            'sale_note_items.item',
            'sale_note_items.quantity',
            'sale_note_items.unit_value', 
            'sale_note_items.unit_price',
            'sale_note_items.total',
            'sale_note_items.total_value',
            'sale_note_items.total_igv',
            'sale_note_items.total_isc',
            'sale_note_items.total_discount',
            'sale_note_items.affectation_igv_type_id',
            'sale_note_items.system_isc_type_id',
            'sale_notes.seller_id as seller_id',
            'sale_notes.company as company_name',
            'sale_notes.user_id as user_id',
            'sale_notes.series as document_series',
            'sale_notes.number as document_number',
            DB::raw('NULL as document_prefix'),
            DB::raw('sale_notes.date_of_issue as document_date'),
            'sale_notes.state_type_id as document_state_type_id',
            DB::raw("'80' as document_type_id"),
            'sale_notes.customer as customer',
            'sale_notes.customer_id as customer_id',
            'sale_notes.additional_information as observation',
            'sale_notes.currency_type_id as currency_type_id',
            'sale_notes.exchange_rate_sale as exchange_rate_sale',
            'sale_notes.purchase_order as purchase_order',
        ];
        return $sale_note_columns;
    }

    private function getQuotationColumns(){
        $quotation_columns = [
            'quotation_items.id',
            'quotation_items.item_id',
            'quotation_items.item',
            'quotation_items.quantity',
            'quotation_items.unit_value', 
            'quotation_items.unit_price',
            'quotation_items.total',
            'quotation_items.total_value',
            'quotation_items.total_igv',
            'quotation_items.total_isc',
            'quotation_items.total_discount',
            'quotation_items.affectation_igv_type_id',
            'quotation_items.system_isc_type_id',
            'quotations.seller_id as seller_id',
            'quotations.company as company_name',
            'quotations.user_id as user_id',
            DB::raw('NULL as document_series'),
            'quotations.number as document_number',
            'quotations.prefix as document_prefix',
            DB::raw('quotations.date_of_issue as document_date'),
            'quotations.state_type_id as document_state_type_id',
            DB::raw("'COT' as document_type_id"),
            'quotations.customer as customer',
            'quotations.customer_id as customer_id',
            'quotations.description as observation',
            'quotations.currency_type_id as currency_type_id',
            'quotations.exchange_rate_sale as exchange_rate_sale',
            DB::raw("CASE 
                WHEN purchase_orders.id IS NOT NULL 
                THEN CONCAT(purchase_orders.prefix, '-', purchase_orders.id) 
                ELSE NULL 
            END as purchase_order"),
        ];

        return $quotation_columns;
    }

    private function getPurchaseColumns(){
        $purchase_order_columns = [
            'purchase_items.id',
            'purchase_items.item_id',
            'purchase_items.item',
            'purchase_items.quantity',
            'purchase_items.unit_value', 
            'purchase_items.unit_price',
            'purchase_items.total',
            'purchase_items.total_value',
            'purchase_items.total_igv',
            'purchase_items.total_isc',
            'purchase_items.total_discount',
            'purchase_items.affectation_igv_type_id',
            'purchase_items.system_isc_type_id',
            'purchases.company as company_name',
            'purchases.user_id as user_id',
            'purchases.series as document_series',
            'purchases.number as document_number',
            DB::raw('NULL as document_prefix'),
            DB::raw('purchases.date_of_issue as document_date'),
            'purchases.state_type_id as document_state_type_id',
            'purchases.document_type_id as document_type_id',
            'purchases.supplier as supplier',
            'purchases.supplier_id as supplier_id',
            'purchases.currency_type_id as currency_type_id',
            'purchases.exchange_rate_sale as exchange_rate_sale',
            DB::raw('NULL as purchase_order'),
        ];
        return $purchase_order_columns;
    }

    private function getDocumentQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id){
        $documents = DB::connection('tenant')
        ->table('document_items')
        ->select(array_merge($this->getDocumentColumns(), [
            'documents.id as document_id',
            DB::raw('NULL as sale_note_id'),
            DB::raw('NULL as quotation_id'),
            'document_items.total_plastic_bag_taxes',
            'items.model as item_model',
            'brands.name as brand_name',
            'categories.name as category_name',
            'web_platforms.name as platform_name',
            'items.purchase_unit_price as purchase_unit_price_item',
            DB::raw("COALESCE((SELECT w.description FROM warehouses w WHERE w.establishment_id = documents.establishment_id LIMIT 1), '-') as warehouse_description")
        ]))
        ->join('documents', 'document_items.document_id', '=', 'documents.id')
        ->leftJoin('items', 'document_items.item_id', '=', 'items.id')
        ->leftJoin('brands', 'items.brand_id', '=', 'brands.id')
        ->leftJoin('warehouses', 'document_items.warehouse_id', '=', 'warehouses.id')
        ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
        ->leftJoin('web_platforms', 'items.web_platform_id', '=', 'web_platforms.id')
        ->whereBetween('documents.date_of_issue', [$date_start, $date_end])
        ->whereIn('documents.document_type_id', ['01', '03'])
        ->whereNotIn('documents.state_type_id', $this->documents_excluded);


        $this->applyOptimizedFilters($documents, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);

        return $documents;
    }

    private function getSaleNoteQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id){
        $sale_notes = DB::connection('tenant')
        ->table('sale_note_items')
        ->select(array_merge($this->getSaleNoteColumns(), [
            DB::raw('NULL as document_id'),
            'sale_notes.id as sale_note_id',
            DB::raw('NULL as quotation_id'),
            'sale_note_items.total_plastic_bag_taxes',
            'items.model as item_model',
            'brands.name as brand_name',
            'categories.name as category_name',
            'web_platforms.name as platform_name',
            'items.purchase_unit_price as purchase_unit_price_item',
            DB::raw("COALESCE((SELECT w.description FROM warehouses w WHERE w.establishment_id = sale_notes.establishment_id LIMIT 1), '-') as warehouse_description")
        ]))
        ->join('sale_notes', 'sale_note_items.sale_note_id', '=', 'sale_notes.id')
        ->leftJoin('items', 'sale_note_items.item_id', '=', 'items.id')
        ->leftJoin('brands', 'items.brand_id', '=', 'brands.id')
        ->leftJoin('warehouses', 'sale_note_items.warehouse_id', '=', 'warehouses.id')
        ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
        ->leftJoin('web_platforms', 'items.web_platform_id', '=', 'web_platforms.id')
        ->whereBetween('sale_notes.date_of_issue', [$date_start, $date_end])
        ->whereNotIn('sale_notes.state_type_id', $this->documents_excluded);
    
        $this->applyOptimizedFilters($sale_notes, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
    
        return $sale_notes;
    }

    private function getQuotationQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id){
        $quotations = DB::connection('tenant')
        ->table('quotation_items')
        ->select(array_merge($this->getQuotationColumns(), [
            DB::raw('NULL as document_id'),
            DB::raw('NULL as sale_note_id'),
            'quotations.id as quotation_id',
            DB::raw('0 as total_plastic_bag_taxes'),
            'items.model as item_model',
            'brands.name as brand_name',
            'categories.name as category_name',
            'web_platforms.name as platform_name',
            'items.purchase_unit_price as purchase_unit_price_item',
            DB::raw("COALESCE((SELECT w.description FROM warehouses w WHERE w.establishment_id = quotations.establishment_id LIMIT 1), '-') as warehouse_description")
        ]))
        ->join('quotations', 'quotation_items.quotation_id', '=', 'quotations.id')
        ->leftJoin('items', 'quotation_items.item_id', '=', 'items.id')
        ->leftJoin('brands', 'items.brand_id', '=', 'brands.id')
        ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
        ->leftJoin('warehouses', 'quotation_items.warehouse_id', '=', 'warehouses.id')
        ->leftJoin('web_platforms', 'items.web_platform_id', '=', 'web_platforms.id')
        ->leftJoin('purchase_orders', 'quotations.id', '=', 'purchase_orders.quotation_id')
        ->where('quotations.changed', 0)
        ->whereBetween('quotations.date_of_issue', [$date_start, $date_end])
        ->whereNotIn('quotations.state_type_id', $this->documents_excluded);

        $this->applyOptimizedFilters($quotations, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);

        return $quotations;
    }

    private function getPurchaseQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id){
        $purchase_orders = DB::connection('tenant')
        ->table('purchase_items')
        ->select(array_merge($this->getPurchaseColumns(), [
            DB::raw('NULL as document_id'),
            DB::raw('NULL as sale_note_id'),
            DB::raw('NULL as quotation_id'),
            'purchases.id as purchase_id',
            DB::raw('0 as total_plastic_bag_taxes'),
            'items.model as item_model',
            'brands.name as brand_name',
            'categories.name as category_name',
            'web_platforms.name as platform_name',
            'items.purchase_unit_price as purchase_unit_price_item',
            DB::raw("COALESCE((SELECT w.description FROM warehouses w WHERE w.establishment_id = purchases.establishment_id LIMIT 1), '-') as warehouse_description")
        ]))
        ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
        ->leftJoin('items', 'purchase_items.item_id', '=', 'items.id')
        ->leftJoin('brands', 'items.brand_id', '=', 'brands.id')
        ->leftJoin('warehouses', 'purchase_items.warehouse_id', '=', 'warehouses.id')
        ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
        ->leftJoin('web_platforms', 'items.web_platform_id', '=', 'web_platforms.id')
        ->whereBetween('purchases.date_of_issue', [$date_start, $date_end])
        ->whereIn('purchases.document_type_id', ['01', '03'])
        ->whereNotIn('purchases.state_type_id', $this->documents_excluded);
        $this->applyOptimizedFilters($purchase_orders, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
        return $purchase_orders;
    }

    /**
     * @param $date_start
     * @param $date_end
     * @param $document_type_id
     * @param $data_type
     * @param $person_id
     * @param $type_person
     * @param $item_id
     * @param $web_platform_id
     * @param $brand_id
     * @param $category_id
     * @param $user_id
     * @param $user_type
     *
     * @return \App\Models\Tenant\SaleNoteItem|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function dataItems2($date_start, $date_end, $document_type_id, $data_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type, $zone_id = null, $website_id = null)
    {
        $configuration = Configuration::first();
        $documents_excluded = ['11', '09'];

        // Si el modelo es "all", procesar todos los tipos de documentos
        if ($data_type['model'] === 'all') {
            // Columnas para documentos
    
            $documents = $this->getDocumentQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
            $sale_notes = $this->getSaleNoteQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
            $quotations = $this->getQuotationQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
        

            // Documentos
        

            // Notas de venta
        
            // Cotizaciones
        
            // Unir las consultas y obtener los resultados
            $results = $documents->unionAll($sale_notes)->unionAll($quotations);

            return $results;
        }
        switch ($data_type['model']) {
            case DocumentItem::class:
                return $this->getDocumentQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
            case SaleNoteItem::class:
                return $this->getSaleNoteQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
            case QuotationItem::class:
                return $this->getQuotationQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
            case PurchaseItem::class:
                return $this->getPurchaseQuery($date_start, $date_end, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id);
        }
        
    }

    private function applyOptimizedFilters($query, $configuration, $user_id, $user_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $zone_id)
    {
        // Usar joins en lugar de whereHas cuando sea posible
        if ($user_id && $user_type === 'CREADOR') {
            $query->join('users', 'documents.user_id', '=', 'users.id')
                  ->where('users.id', $user_id);
        }

        if ($user_id && $user_type === 'VENDEDOR') {
            if ($configuration->multi_sellers) {
                $query->join('document_sellers', 'documents.id', '=', 'document_sellers.document_id')
                      ->where('document_sellers.seller_id', $user_id);
            } else {
                $query->where('documents.seller_id', $user_id);
            }
        }

        if ($person_id && $type_person) {
            $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';
            $query->where('documents.'.$column, $person_id);
        }

        if ($item_id) {
            $query->where('item_id', $item_id);
        }

        // Usar joins para filtros relacionados
        if ($web_platform_id || $brand_id || $category_id) {
            $query->join('items', 'document_items.item_id', '=', 'items.id')
                  ->when($web_platform_id, function($q) use($web_platform_id) {
                      return $q->where('items.web_platform_id', $web_platform_id);
                  })
                  ->when($brand_id, function($q) use($brand_id) {
                      return $q->where('items.brand_id', $brand_id);
                  })
                  ->when($category_id, function($q) use($category_id) {
                      return $q->where('items.category_id', $category_id);
                  });
        }

        if ($zone_id) {
            $query->join('persons', 'documents.person_id', '=', 'persons.id')
                  ->where('persons.zone_id', $zone_id);
        }
    }
    private function getItemsResume($date_start, $date_end, $document_type_id, $data_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type, $zone_id = null, $website_id = null, $state_type_id = null, $establishment_id = null, $ingredient_id = null, $line_id = null, $min_stock_lower_zero = false)
    {
        $configuration = Configuration::first();
        if (!$configuration) {
            throw new \Exception('Configuration not found');
        }
        
        // Validate data_type parameter
        if (!is_array($data_type) || !isset($data_type['model'])) {
            return DocumentItem::select([
                'document_items.id', 
                'document_items.item_id', 
                'document_items.unit_price', 
                'document_items.unit_value', 
                'document_items.quantity', 
                'document_items.total',
                'document_items.item',
                DB::raw('NULL as date_of_issue')
            ])->whereRaw('1 = 0')
            ->join('items', 'document_items.item_id', '=', 'items.id')
            ->where('items.unit_type_id','!=', 'ZZ');
        }
        
        /* columna state_type_id */
        $documents_excluded = [
            '11', // Documentos anulados
            '09' // Documentos rechazados
        ];
        if ($document_type_id && $document_type_id == '80') {
            $relation = 'sale_note';

            $data = SaleNoteItem::select([
                'sale_note_items.id', 
                'sale_note_items.item_id', 
                'sale_note_items.unit_price', 
                'sale_note_items.unit_value', 
                'sale_note_items.quantity', 
                'sale_note_items.total',
                'sale_note_items.item',
                DB::raw('sale_notes.date_of_issue')
            ])
            ->join('sale_notes', 'sale_note_items.sale_note_id', '=', 'sale_notes.id')
            ->join('items', 'sale_note_items.item_id', '=', 'items.id')
            ->where('items.unit_type_id','!=', 'ZZ')
            ->whereBetween('sale_notes.date_of_issue', [$date_start, $date_end])
            ->whereNotIn('sale_notes.state_type_id', $documents_excluded);
            
            if (!empty($user_id)) {
                $data->where('sale_notes.user_id', $user_id);
            }
            if($establishment_id){
                $data->where('sale_notes.establishment_id', $establishment_id);
            }
            if($state_type_id){
                $data->where('sale_notes.state_type_id', $state_type_id);
            }
            if ($website_id) {
                $data->where('sale_notes.website_id', $website_id);
            }
            
            if ($configuration->multi_sellers && !empty($user_id)) {
                $data->whereHas('sellers', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
            if ($zone_id) {
                $data->join('persons', 'sale_notes.person_id', '=', 'persons.id')
                     ->where('persons.zone_id', $zone_id);
            }

            // Filtrar por stock mínimo
            if($min_stock_lower_zero){
                $data = $data->whereHas('relation_item', function ($q) use ($establishment_id) {
                    if ($establishment_id) {
                        // Filtrar por stock de almacén específico del establishment
                        $q->whereHas('item_warehouse', function ($qw) use ($establishment_id) {
                            $qw->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                              ->where('warehouses.establishment_id', $establishment_id)
                              ->whereRaw('item_warehouse.stock <= items.stock_min');
                        });
                    } else {
                        // Sumar stock de todos los almacenes y comparar con stock_min
                        $q->whereRaw('items.stock_min >= (SELECT COALESCE(SUM(iw.stock), 0) FROM item_warehouse iw WHERE iw.item_id = items.id)');
                    }
                });
            }
        } else if ($document_type_id == null && isset($data_type['model']) && $data_type['model'] == 'all') {

            $document_types = ['01', '03'];
            $documents = DocumentItem::select([
                'document_items.id', 
                'document_items.item_id', 
                'document_items.unit_price', 
                'document_items.unit_value', 
                'document_items.quantity', 
                'document_items.total',
                'document_items.item',
                DB::raw('documents.date_of_issue')
            ])
            ->join('documents', 'document_items.document_id', '=', 'documents.id')
            ->join('items', 'document_items.item_id', '=', 'items.id')
            ->where('items.unit_type_id','!=', 'ZZ')
            ->whereNull('documents.sale_note_id')

            ->whereBetween('documents.date_of_issue', [$date_start, $date_end])
            ->whereIn('documents.document_type_id', $document_types)
            ->whereNotIn('documents.state_type_id', $documents_excluded);
            
            if ($website_id) {
                $documents->where('documents.website_id', $website_id);
            }
            if($state_type_id){
                $documents->where('documents.state_type_id', $state_type_id);
            }
            if($establishment_id){
                $documents->where('documents.establishment_id', $establishment_id);
            }
            
            if ($user_id && $user_type === 'CREADOR') {
                $documents->where('documents.user_id', $user_id);
            }
            if ($zone_id) {
                $documents->join('persons', 'documents.person_id', '=', 'persons.id')
                          ->where('persons.zone_id', $zone_id);
            }
            if ($user_id && $user_type === 'VENDEDOR') {

                if ($configuration->multi_sellers && !empty($user_id)) {
                    $documents->whereHas('sellers', function ($query) use ($user_id) {

                        $query->where('seller_id', $user_id);
                    });
                } else {
                    $documents->where('documents.seller_id', $user_id);
                }
            }
            if ($person_id && $type_person) {

                $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';

                $documents = $documents->where('documents.'.$column, $person_id);
            }

            if ($item_id) {
                $documents =  $documents->where('document_items.item_id', $item_id);
            }

            if ($web_platform_id || $brand_id || $category_id) {
                $documents->join('items', 'document_items.item_id', '=', 'items.id');
                if ($web_platform_id) {
                    $documents->where('items.web_platform_id', $web_platform_id);
                }
                if ($brand_id) {
                    $documents->where('items.brand_id', $brand_id);
                }
                if ($category_id) {
                    $documents->where('items.category_id', $category_id);
                }
            }

            // Filtrar por ingredientes y líneas usando ItemAttribute
            if ($ingredient_id || $line_id) {
                $documents = $documents->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                    if ($ingredient_id) {
                        $q->where('cat_ingredient_id', $ingredient_id);
                    }
                    if ($line_id) {
                        $q->where('cat_line_id', $line_id);
                    }
                });
            }

            // Filtrar por stock mínimo
            if($min_stock_lower_zero){
                $documents = $documents->whereHas('relation_item', function ($q) use ($establishment_id) {
                    if ($establishment_id) {
                        // Filtrar por stock de almacén específico del establishment
                        $q->whereHas('item_warehouse', function ($qw) use ($establishment_id) {
                            $qw->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                              ->where('warehouses.establishment_id', $establishment_id)
                              ->whereRaw('item_warehouse.stock <= items.stock_min');
                        });
                    } else {
                        // Sumar stock de todos los almacenes y comparar con stock_min
                        $q->whereRaw('items.stock_min >= (SELECT COALESCE(SUM(iw.stock), 0) FROM item_warehouse iw WHERE iw.item_id = items.id)');
                    }
                });
            }

            $sale_notes = SaleNoteItem::select([
                'sale_note_items.id', 
                'sale_note_items.item_id', 
                'sale_note_items.unit_price', 
                'sale_note_items.unit_value', 
                'sale_note_items.quantity', 
                'sale_note_items.total',
                'sale_note_items.item',
                DB::raw('sale_notes.date_of_issue')
            ])
            ->join('sale_notes', 'sale_note_items.sale_note_id', '=', 'sale_notes.id')
            ->join('items', 'sale_note_items.item_id', '=', 'items.id')
            ->where('items.unit_type_id','!=', 'ZZ')
            ->whereBetween('sale_notes.date_of_issue', [$date_start, $date_end])
            ->whereNotIn('sale_notes.state_type_id', $documents_excluded);
            
            if (!empty($user_id)) {
                $sale_notes->where('sale_notes.user_id', $user_id);
            }
            if($state_type_id){
                $sale_notes->where('sale_notes.state_type_id', $state_type_id);
            }
            if($establishment_id){
                $sale_notes->where('sale_notes.establishment_id', $establishment_id);
            }
            if ($website_id) {
                $sale_notes->where('sale_notes.website_id', $website_id);
            }
            
            if ($configuration->multi_sellers && !empty($user_id)) {
                $sale_notes->whereHas('sellers', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
            if ($zone_id) {
                $sale_notes->join('persons', 'sale_notes.person_id', '=', 'persons.id')
                           ->where('persons.zone_id', $zone_id);
            }
            if ($person_id && $type_person) {

                $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';

                $sale_notes = $sale_notes->where('sale_notes.'.$column, $person_id);
            }

            if ($item_id) {
                $sale_notes =  $sale_notes->where('sale_note_items.item_id', $item_id);
            }

            if ($web_platform_id || $brand_id || $category_id) {
                $sale_notes->join('items', 'sale_note_items.item_id', '=', 'items.id');
                if ($web_platform_id) {
                    $sale_notes->where('items.web_platform_id', $web_platform_id);
                }
                if ($brand_id) {
                    $sale_notes->where('items.brand_id', $brand_id);
                }
                if ($category_id) {
                    $sale_notes->where('items.category_id', $category_id);
                }
            }

            // Filtrar por ingredientes y líneas usando ItemAttribute
            if ($ingredient_id || $line_id) {
                $sale_notes = $sale_notes->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                    if ($ingredient_id) {
                        $q->where('cat_ingredient_id', $ingredient_id);
                    }
                    if ($line_id) {
                        $q->where('cat_line_id', $line_id);
                    }
                });
            }

            // Filtrar por stock mínimo
            if($min_stock_lower_zero){
                $sale_notes = $sale_notes->whereHas('relation_item', function ($q) use ($establishment_id) {
                    if ($establishment_id) {
                        // Filtrar por stock de almacén específico del establishment
                        $q->whereHas('item_warehouse', function ($qw) use ($establishment_id) {
                            $qw->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                              ->where('warehouses.establishment_id', $establishment_id)
                              ->whereRaw('item_warehouse.stock <= items.stock_min');
                        });
                    } else {
                        // Sumar stock de todos los almacenes y comparar con stock_min
                        $q->whereRaw('items.stock_min >= (SELECT COALESCE(SUM(iw.stock), 0) FROM item_warehouse iw WHERE iw.item_id = items.id)');
                    }
                });
            }


            // Ensure we have valid queries before performing union operations
            $union_queries = [];

            if ($documents) {
                $union_queries[] = $documents;
            }
            
            if ($sale_notes) {
                $union_queries[] = $sale_notes;
            }
            
        
            
            if (empty($union_queries)) {
                // Return empty query builder with same structure
                $data = DocumentItem::select([
                    'id', 
                    'item_id', 
                    'unit_price', 
                    'unit_value', 
                    'quantity', 
                    'item',
                    DB::raw('NULL as date_of_issue')
                ])->whereRaw('1 = 0')
                ->join('items', 'document_items.item_id', '=', 'items.id')
                ->where('items.unit_type_id','!=', 'ZZ'); // This ensures empty result set
                return $data;
            }
            
            $data = array_shift($union_queries);
            foreach ($union_queries as $query) {
                $data = $data->union($query);
            }

            return $data;
        } else if ($document_type_id && $document_type_id == 'COT') {
            $relation = 'quotation';
            $data = QuotationItem::select([
                'quotation_items.id', 
                'quotation_items.item_id', 
                'quotation_items.unit_price', 
                'quotation_items.unit_value', 
                'quotation_items.quantity', 
                'quotation_items.total',
                'quotation_items.item',
                DB::raw('quotations.date_of_issue')
            ])
            ->join('quotations', 'quotation_items.quotation_id', '=', 'quotations.id')
            ->join('items', 'quotation_items.item_id', '=', 'items.id')
            ->where('items.unit_type_id','!=', 'ZZ')
            ->where('quotations.changed', 0)
            ->whereBetween('quotations.date_of_issue', [$date_start, $date_end])
            ->whereNotIn('quotations.state_type_id', $documents_excluded);
            
            if (!empty($user_id)) {
                $data->where('quotations.user_id', $user_id);
            }
            if($state_type_id){
                $data->where('quotations.state_type_id', $state_type_id);
            }
            if($establishment_id){
                $data->where('quotations.establishment_id', $establishment_id);
            }
            
            if ($configuration->multi_sellers && !empty($user_id)) {
                $data->whereHas('sellers', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
            if ($zone_id) {
                $data->join('persons', 'quotations.person_id', '=', 'persons.id')
                     ->where('persons.zone_id', $zone_id);
            }

            // Filtrar por stock mínimo
            if($min_stock_lower_zero){
                $data = $data->whereHas('relation_item', function ($q) use ($establishment_id) {
                    if ($establishment_id) {
                        // Filtrar por stock de almacén específico del establishment
                        $q->whereHas('item_warehouse', function ($qw) use ($establishment_id) {
                            $qw->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                              ->where('warehouses.establishment_id', $establishment_id)
                              ->whereRaw('item_warehouse.stock <= items.stock_min');
                        });
                    } else {
                        // Sumar stock de todos los almacenes y comparar con stock_min
                        $q->whereRaw('items.stock_min >= (SELECT COALESCE(SUM(iw.stock), 0) FROM item_warehouse iw WHERE iw.item_id = items.id)');
                    }
                });
            }
        } else {
            
            if (!isset($data_type['model']) || !isset($data_type['relation'])) {
                return DocumentItem::select([
                    'id', 
                    'item_id', 
                    'unit_price', 
                    'unit_value', 
                    'quantity', 
                    'item',
                    DB::raw('NULL as date_of_issue')
                ])->whereRaw('1 = 0')
                ->join('items', 'document_items.item_id', '=', 'items.id')
                ->where('items.unit_type_id','!=', 'ZZ');
            }

            $model = $data_type['model'];

            $relation = $data_type['relation'];
            $document_types = $document_type_id ? [$document_type_id] : ['01', '03'];

            if ($model == DocumentItem::class) {
                $data = DocumentItem::select([
                    'document_items.id', 
                    'document_items.item_id', 
                    'document_items.unit_price', 
                    'document_items.unit_value', 
                    'document_items.quantity', 
                    'document_items.total',
                    'document_items.item',
                    DB::raw('documents.date_of_issue')
                ])
                ->join('documents', 'document_items.document_id', '=', 'documents.id')
                ->join('items', 'document_items.item_id', '=', 'items.id')
                ->where('items.unit_type_id','!=', 'ZZ')
                ->whereBetween('documents.date_of_issue', [$date_start, $date_end])
                ->whereIn('documents.document_type_id', $document_types)
                ->whereNotIn('documents.state_type_id', $documents_excluded);
                
                if($state_type_id){
                    $data->where('documents.state_type_id', $state_type_id);
                }
                if($establishment_id){
                    $data->where('documents.establishment_id', $establishment_id);
                }
                
                if ($user_id && $user_type === 'CREADOR') {
                    $data->where('documents.user_id', $user_id);
                }
                if ($user_id && $user_type === 'VENDEDOR') {
                    if ($configuration->multi_sellers && !empty($user_id)) {
                        $data->whereHas('sellers', function ($query) use ($user_id) {
                            $query->where('seller_id', $user_id);
                        });
                    } else {
                        $data->where('documents.seller_id', $user_id);
                    }
                }
            } else {
                // Para otros modelos, usar whereHas
                $data = $model::select(['id', 'item_id', 'unit_price', 'unit_value', 'quantity', 'item'])
                    ->whereHas($relation, function ($query) use ($date_start, $date_end, $document_types, $model, $documents_excluded, $state_type_id, $establishment_id) {
                        $query
                            ->whereBetween('date_of_issue', [$date_start, $date_end])
                            ->whereIn('document_type_id', $document_types)
                            ->latest()
                            ->whereTypeUser();
                        if ($model == 'App\Models\Tenant\DocumentItem') {
                            $query->whereNotIn('state_type_id', $documents_excluded);
                        }
                        if($state_type_id){
                            $query->where('state_type_id', $state_type_id);
                        }
                        if($establishment_id){
                            $query->where('establishment_id', $establishment_id);
                        }
                    })
                    ->join('items', 'document_items.item_id', '=', 'items.id')
                    ->where('items.unit_type_id','!=', 'ZZ');
                if ($user_id && $user_type === 'CREADOR') {
                    $data = $data->whereHas($relation . '.user', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    });
                }
                if ($user_id && $user_type === 'VENDEDOR') {

                    if ($configuration->multi_sellers && $model == DocumentItem::class && !empty($user_id)) {
                        $data = $data->whereHas('sellers', function ($query) use ($user_id) {

                            $query->where('seller_id', $user_id);
                        });
                    } else {
                        $data = $data->whereHas($relation . '.seller', function ($query) use ($user_id) {
                            $query->where('seller_id', $user_id);
                        });
                    }
                }
            }
        }


        if ($person_id && $type_person && isset($relation)) {

            $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';

            $data =  $data->whereHas($relation, function ($query) use ($column, $person_id) {
                $query->where($column, $person_id);
            });
        }

        if ($item_id) {
            $data =  $data->where('item_id', $item_id);
        }

        if ($web_platform_id || $brand_id || $category_id) {
            $data = $data->whereHas('relation_item', function ($q) use ($web_platform_id, $brand_id, $category_id) {
                if ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                }
                if ($brand_id) {
                    $q->where('brand_id', $brand_id);
                }
                if ($category_id) {
                    $q->where('category_id', $category_id);
                }
            });
        }

        // Filtrar por stock mínimo
        if($min_stock_lower_zero){
            $data = $data->whereHas('relation_item', function ($q) use ($establishment_id) {
                if ($establishment_id) {
                    // Filtrar por stock de almacén específico del establishment
                    $q->whereHas('item_warehouse', function ($qw) use ($establishment_id) {
                        $qw->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                          ->where('warehouses.establishment_id', $establishment_id)
                          ->whereRaw('item_warehouse.stock <= items.stock_min');
                    });
                } else {
                    // Sumar stock de todos los almacenes y comparar con stock_min
                    $q->whereRaw('items.stock_min >= (SELECT COALESCE(SUM(iw.stock), 0) FROM item_warehouse iw WHERE iw.item_id = items.id)');
                }
            });
        }

        // Final validation before returning data
        if (!isset($data)) {
            // Return empty query builder with appropriate structure
            return DocumentItem::select([
                'document_items.id', 
                'document_items.item_id', 
                'document_items.unit_price', 
                'document_items.unit_value', 
                'document_items.quantity', 
                'document_items.total',
                'document_items.item',
                DB::raw('NULL as date_of_issue')
            ])->whereRaw('1 = 0')
            ->join('items', 'document_items.item_id', '=', 'items.id')
            ->where('items.unit_type_id','!=', 'ZZ');
        }

        return $data;
    }

    private function dataItems($date_start, $date_end, $document_type_id, $data_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type, $zone_id = null, $website_id = null, $state_type_id = null, $establishment_id = null, $ingredient_id = null, $line_id = null, $min_stock_lower_zero = false)
    {
        $configuration = Configuration::first();
        /* columna state_type_id */
        $documents_excluded = [
            '11', // Documentos anulados
            '09' // Documentos rechazados
        ];

        if ($document_type_id && $document_type_id == '80') {
            $relation = 'sale_note';

            $data = SaleNoteItem::whereHas('sale_note', function ($query) use ($date_start, $date_end, $user_id, $documents_excluded, $website_id, $state_type_id, $establishment_id, $min_stock_lower_zero) {
                $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->latest()
                    ->whereTypeUser();
                if (!empty($user_id)) {
                    $query->where('user_id', $user_id);
                }
                if($establishment_id){
                    $query->where('establishment_id', $establishment_id);
                }
                if($state_type_id){
                    $query->where('state_type_id', $state_type_id);
                }

                $query->whereNotIn('state_type_id', $documents_excluded);

                if ($website_id) {
                    $query->where('website_id', $website_id);
                }
            
            });
            if ($configuration->multi_sellers && !empty($user_id)) {
                $data->whereHas('sellers', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
            if ($zone_id) {
                $data = $data->whereHas('sale_note', function ($query) use ($zone_id) {
                    $query->whereHas('person', function ($query) use ($zone_id) {
                        $query->where('zone_id', $zone_id);
                    });
                });
            }

            // Filtrar por ingredientes y líneas usando ItemAttribute
            if ($ingredient_id || $line_id) {
                $data = $data->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                    if ($ingredient_id) {
                        $q->where('cat_ingredient_id', $ingredient_id);
                    }
                    if ($line_id) {
                        $q->where('cat_line_id', $line_id);
                    }
                });
            }
            if($min_stock_lower_zero){
                $data = $data->whereHas('relation_item', function ($q) use ($establishment_id) {
                    if ($establishment_id) {
                        // Filtrar por stock de almacén específico del establishment
                        $q->whereHas('item_warehouse', function ($qw) use ($establishment_id) {
                            $qw->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                              ->where('warehouses.establishment_id', $establishment_id)
                              ->whereRaw('item_warehouse.stock <= items.stock_min');
                        });
                    } else {
                        // Sumar stock de todos los almacenes y comparar con stock_min
                        $q->whereRaw('items.stock_min >= (SELECT COALESCE(SUM(iw.stock), 0) FROM item_warehouse iw WHERE iw.item_id = items.id)');
                    }
                });
            }
        } else if ($document_type_id == null && isset($data_type['model']) && $data_type['model'] == 'all') {
            $document_types = ['01', '03'];
            $documents = DocumentItem::select(
                'id',
                DB::raw('NULL as quotation_id'), // Columna ficticia para document_id
                DB::raw('NULL as sale_note_id'), // Columna ficticia para document_id
                'document_id',
                'item_id',
                'item',
                'quantity',
                'unit_value',
                'total',
                'unit_price',

            )->whereHas('document', function ($query) use ($date_start, $date_end, $document_types, $documents_excluded, $website_id, $state_type_id, $establishment_id) {
                $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->whereIn('document_type_id', $document_types)
                    ->latest()
                    ->whereTypeUser();
                $query->whereNotIn('state_type_id', $documents_excluded);

                if ($website_id) {
                    $query->where('website_id', $website_id);
                }
                if($state_type_id){
                    $query->where('state_type_id', $state_type_id);
                }
                if($establishment_id){
                    $query->where('establishment_id', $establishment_id);
                }
                $query->doesntHave('note2');
            });
            if ($user_id && $user_type === 'CREADOR') {
                $documents = $documents->whereHas('document' . '.user', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
            }
            if ($zone_id) {
                $documents = $documents->whereHas('document', function ($query) use ($zone_id) {
                    $query->whereHas('person', function ($query) use ($zone_id) {
                        $query->where('zone_id', $zone_id);
                    });
                });
            }
            if ($user_id && $user_type === 'VENDEDOR') {

                if ($configuration->multi_sellers && !empty($user_id)) {
                    $documents = $documents->whereHas('sellers', function ($query) use ($user_id) {

                        $query->where('seller_id', $user_id);
                    });
                } else {
                    $documents = $documents->whereHas('document' . '.seller', function ($query) use ($user_id) {
                        $query->where('seller_id', $user_id);
                    });
                }
            }
            if ($person_id && $type_person) {

                $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';

                $data =  $documents->whereHas('document', function ($query) use ($column, $person_id) {
                    $query->where($column, $person_id);
                });
            }

            if ($item_id) {
                $documents =  $documents->where('item_id', $item_id);
            }

            if ($web_platform_id || $brand_id || $category_id) {
                $documents = $documents->whereHas('relation_item', function ($q) use ($web_platform_id, $brand_id, $category_id) {
                    if ($web_platform_id) {
                        $q->where('web_platform_id', $web_platform_id);
                    }
                    if ($brand_id) {
                        $q->where('brand_id', $brand_id);
                    }
                    if ($category_id) {
                        $q->where('category_id', $category_id);
                    }
                });
            }

            // Filtrar por ingredientes y líneas usando ItemAttribute
            if ($ingredient_id || $line_id) {
                $documents = $documents->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                    if ($ingredient_id) {
                        $q->where('cat_ingredient_id', $ingredient_id);
                    }
                    if ($line_id) {
                        $q->where('cat_line_id', $line_id);
                    }
                });
            }

            $documents = $documents->with(['relation_item', 'quotation', 'sale_note', 'document']);
            $sale_notes = SaleNoteItem::select(
                'id',
                DB::raw('NULL as quotation_id'), // Columna ficticia para document_id
                'sale_note_id',
                DB::raw('NULL as document_id'), // Columna ficticia para document_id
                'item_id',
                'item',
                'quantity',
                'unit_value',
                'total',
                'unit_price',
            )->whereHas('sale_note', function ($query) use ($date_start, $date_end, $user_id, $documents_excluded, $website_id, $state_type_id, $establishment_id) {
                $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->latest()
                    ->whereTypeUser();
                if (!empty($user_id)) {
                    $query->where('user_id', $user_id);
                }
                if($state_type_id){
                    $query->where('state_type_id', $state_type_id);
                }
                if($establishment_id){
                    $query->where('establishment_id', $establishment_id);
                }
                if ($website_id) {
                    $query->where('website_id', $website_id);
                }

                $query->whereNotIn('state_type_id', $documents_excluded);
            });
            if ($configuration->multi_sellers && !empty($user_id)) {
                $sale_notes->whereHas('sellers', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
            if ($zone_id) {
                $sale_notes = $sale_notes->whereHas('sale_note', function ($query) use ($zone_id) {
                    $query->whereHas('person', function ($query) use ($zone_id) {
                        $query->where('zone_id', $zone_id);
                    });
                });
            }
            if ($person_id && $type_person) {

                $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';

                $sale_notes =  $sale_notes->whereHas('sale_note', function ($query) use ($column, $person_id) {
                    $query->where($column, $person_id);
                });
            }

            if ($item_id) {
                $sale_notes =  $sale_notes->where('item_id', $item_id);
            }

            if ($web_platform_id || $brand_id || $category_id) {
                $sale_notes = $sale_notes->whereHas('relation_item', function ($q) use ($web_platform_id, $brand_id, $category_id) {
                    if ($web_platform_id) {
                        $q->where('web_platform_id', $web_platform_id);
                    }
                    if ($brand_id) {
                        $q->where('brand_id', $brand_id);
                    }
                    if ($category_id) {
                        $q->where('category_id', $category_id);
                    }
                });
            }

            // Filtrar por ingredientes y líneas usando ItemAttribute
            if ($ingredient_id || $line_id) {
                $sale_notes = $sale_notes->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                    if ($ingredient_id) {
                        $q->where('cat_ingredient_id', $ingredient_id);
                    }
                    if ($line_id) {
                        $q->where('cat_line_id', $line_id);
                    }
                });
            }

            $sale_notes = $sale_notes->with(['relation_item', 'quotation', 'sale_note', 'document']);
            $quotations = QuotationItem::select(
                'id',
                'quotation_id',
                DB::raw('NULL as sale_note_id'), // Columna ficticia para document_id
                DB::raw('NULL as document_id'), // Columna ficticia para document_id
                'item_id',
                'item',
                'quantity',
                'unit_value',
                'total',
                'unit_price',
            )->whereHas('quotation', function ($query) use ($date_start, $date_end, $user_id, $documents_excluded, $website_id, $state_type_id, $establishment_id) {
                $query
                    ->where('changed', 0)
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->latest()
                    ->whereTypeUser();
                if (!empty($user_id)) {
                    $query->where('user_id', $user_id);
                }
                if ($website_id) {
                    $query->where('website_id', $website_id);
                }
                if($state_type_id){
                    $query->where('state_type_id', $state_type_id);
                }
                if($establishment_id){
                    $query->where('establishment_id', $establishment_id);
                }
                $query->whereNotIn('state_type_id', $documents_excluded);
            });
            if ($configuration->multi_sellers && !empty($user_id)) {
                $quotations->whereHas('sellers', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
            if ($person_id && $type_person) {

                $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';

                $quotations =  $quotations->whereHas('quotation', function ($query) use ($column, $person_id) {
                    $query->where($column, $person_id);
                });
            }
            if ($zone_id) {
                $quotations = $quotations->whereHas('quotation', function ($query) use ($zone_id) {
                    $query->whereHas('person', function ($query) use ($zone_id) {
                        $query->where('zone_id', $zone_id);
                    });
                });
            }
            if ($item_id) {
                $quotations =  $quotations->where('item_id', $item_id);
            }

            if ($web_platform_id || $brand_id || $category_id) {
                $quotations = $quotations->whereHas('relation_item', function ($q) use ($web_platform_id, $brand_id, $category_id) {
                    if ($web_platform_id) {
                        $q->where('web_platform_id', $web_platform_id);
                    }
                    if ($brand_id) {
                        $q->where('brand_id', $brand_id);
                    }
                    if ($category_id) {
                        $q->where('category_id', $category_id);
                    }
                });
            }

            // Filtrar por ingredientes y líneas usando ItemAttribute
            if ($ingredient_id || $line_id) {
                $quotations = $quotations->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                    if ($ingredient_id) {
                        $q->where('cat_ingredient_id', $ingredient_id);
                    }
                    if ($line_id) {
                        $q->where('cat_line_id', $line_id);
                    }
                });
            }

            $quotations = $quotations->with(['relation_item', 'quotation', 'sale_note', 'document']);
            $data = $documents->union($sale_notes)->union($quotations);
            return $data;
        } else if ($document_type_id && $document_type_id == 'COT') {
            $relation = 'quotation';
            $data = QuotationItem::whereHas('quotation', function ($query) use ($date_start, $date_end, $user_id, $documents_excluded, $state_type_id, $establishment_id) {
                $query
                    ->where('changed', 0)
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->latest()
                    ->whereTypeUser();
                if (!empty($user_id)) {
                    $query->where('user_id', $user_id);
                }
                if($state_type_id){
                    $query->where('state_type_id', $state_type_id);
                }
                if($establishment_id){
                    $query->where('establishment_id', $establishment_id);
                }
                $query->whereNotIn('state_type_id', $documents_excluded);


            });
            if ($configuration->multi_sellers && !empty($user_id)) {
                $data->whereHas('sellers', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
            if ($zone_id) {
                $data = $data->whereHas('quotation', function ($query) use ($zone_id) {
                    $query->whereHas('person', function ($query) use ($zone_id) {
                        $query->where('zone_id', $zone_id);
                    });
                });
            }

            // Filtrar por ingredientes y líneas usando ItemAttribute
            if ($ingredient_id || $line_id) {
                $data = $data->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                    if ($ingredient_id) {
                        $q->where('cat_ingredient_id', $ingredient_id);
                    }
                    if ($line_id) {
                        $q->where('cat_line_id', $line_id);
                    }
                });
            }
        } else {

            $model = $data_type['model'];

            $relation = $data_type['relation'];
            $document_types = $document_type_id ? [$document_type_id] : ['01', '03'];

            $data = $model::whereHas($relation, function ($query) use ($date_start, $date_end, $document_types, $model, $documents_excluded, $state_type_id, $establishment_id) {
                $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->whereIn('document_type_id', $document_types)
                    ->latest()
                    ->whereTypeUser();
                if ($model == 'App\Models\Tenant\DocumentItem') {
                    $query->whereNotIn('state_type_id', $documents_excluded);
                }
                if($state_type_id){
                    $query->where('state_type_id', $state_type_id);
                }
                if($establishment_id){
                    $query->where('establishment_id', $establishment_id);
                }
            });
            if ($user_id && $user_type === 'CREADOR') {
                $data = $data->whereHas($relation . '.user', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
            }
            if ($user_id && $user_type === 'VENDEDOR') {

                if ($configuration->multi_sellers && $model == DocumentItem::class && !empty($user_id)) {
                    $data = $data->whereHas('sellers', function ($query) use ($user_id) {

                        $query->where('seller_id', $user_id);
                    });
                } else {
                    $data = $data->whereHas($relation . '.seller', function ($query) use ($user_id) {
                        $query->where('seller_id', $user_id);
                    });
                }
            }
        }


        if ($person_id && $type_person && isset($relation)) {

            $column = ($type_person == 'customers') ? 'customer_id' : 'supplier_id';

            $data =  $data->whereHas($relation, function ($query) use ($column, $person_id) {
                $query->where($column, $person_id);
            });
        }

        if ($item_id) {
            $data =  $data->where('item_id', $item_id);
        }

        if ($web_platform_id || $brand_id || $category_id) {
            $data = $data->whereHas('relation_item', function ($q) use ($web_platform_id, $brand_id, $category_id) {
                if ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                }
                if ($brand_id) {
                    $q->where('brand_id', $brand_id);
                }
                if ($category_id) {
                    $q->where('category_id', $category_id);
                }
            });
        }
        if($min_stock_lower_zero){
            $data = $data->whereHas('relation_item', function ($q) use ($establishment_id) {
                if ($establishment_id) {
                    // Filtrar por stock de almacén específico del establishment
                    $q->whereHas('item_warehouse', function ($qw) use ($establishment_id) {
                        $qw->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                          ->where('warehouses.establishment_id', $establishment_id)
                          ->whereRaw('item_warehouse.stock <= items.stock_min');
                    });
                } else {
                    // Sumar stock de todos los almacenes y comparar con stock_min
                    $q->whereRaw('items.stock_min >= (SELECT COALESCE(SUM(iw.stock), 0) FROM item_warehouse iw WHERE iw.item_id = items.id)');
                }
            });
        }

        // Filtrar por ingredientes y líneas usando ItemAttribute
        if ($ingredient_id || $line_id) {
            $data = $data->whereHas('relation_item.item_attributes', function ($q) use ($ingredient_id, $line_id) {
                if ($ingredient_id) {
                    $q->where('cat_ingredient_id', $ingredient_id);
                }
                if ($line_id) {
                    $q->where('cat_line_id', $line_id);
                }
            });
        }


        return $data;
    }
    private function getDataType($request)
    {

        if ($request['type'] == 'sale') {
            if ($request['document_type_id'] == '80') {
                $data['model'] = SaleNoteItem::class;
                $data['relation'] = 'sale_note';
            } else if ($request['document_type_id'] == '01' || $request['document_type_id'] == '03') {
                $data['model'] = DocumentItem::class;
                $data['relation'] = 'document';
            } else if ($request['document_type_id'] == 'COT') {
                $data['model'] = QuotationItem::class;
                $data['relation'] = 'quotation';
            } else {
                $data['model'] = "all";
                $data['relation'] = 'all';
            }

        } else {

            $data['model'] = PurchaseItem::class;
            $data['relation'] = 'purchase';
        }

        return $data;
    }
    public function pdfResume(Request $request)
    {
        ini_set('memory_limit', '4026M');
        ini_set("pcre.backtrack_limit", "5000000");
        $data_of_period = $this->getDataOfPeriod($request);
        
        // Usar el método original para obtener los datos
        $records = $this->getRecordsItems3($request->all())->get(['item_id', 'unit_price', 'unit_value', 'quantity', 'total', 'item', 'date_of_issue']);
        
        // DEBUG: Ver datos originales
        
        
        // Agrupar datos por item_id usando PHP (más confiable)
        $groupedData = [];
        
        foreach ($records as $record) {
            $itemId = $record->item_id;
            $quantity = $record->quantity;
            $originalTotal = $record->total;
            
            // Verificar si el item tiene presentation y quantity_unit
            $item = $record->item;
            $quantityMultiplier = 1;
            
            if ($item && is_object($item)) {
                $presentation = $item->presentation ?? null;
                
                if ($presentation && !empty($presentation)) {
                    if (is_object($presentation)) {
                        $quantityUnit = $presentation->quantity_unit ?? 0;
                        if ($quantityUnit > 0) {
                            $quantityMultiplier = $quantityUnit;
                        }
                    }
                }
            }
            
            $adjustedQuantity = $quantity * $quantityMultiplier;
            
            if (!isset($groupedData[$itemId])) {
                $groupedData[$itemId] = [
                    'item_id' => $itemId,
                    'item' => $item,
                    'total_quantity' => 0,
                    'total_amount' => 0,
                    'unit_value' => $record->unit_value,
                    'most_recent_date' => $record->date_of_issue,
                    'records_count' => 0
                ];
            }
            
            // Sumar cantidades ajustadas y totales originales
            $groupedData[$itemId]['total_quantity'] += $adjustedQuantity;
            $groupedData[$itemId]['total_amount'] += $originalTotal;
            $groupedData[$itemId]['records_count']++;
            
            // Verificar si este registro es más reciente para tomar su unit_value
            if ($record->date_of_issue > $groupedData[$itemId]['most_recent_date']) {
                $groupedData[$itemId]['unit_value'] = $record->unit_value;
                $groupedData[$itemId]['most_recent_date'] = $record->date_of_issue;
            }
        }
        
        // Convertir a array indexado
        $finalRecords = array_values($groupedData);
    
        
        if (empty($finalRecords)) {
            $company = Company::first();
            $records = [];
            $pdf = PDF::loadView('report::general_items.report_pdf_resume', compact("records", "data_of_period", "company"))->setPaper('a4', 'portrait');
            $filename = 'Reporte_General_Resumen_Ventas_Productos_' . Carbon::now();
            return $pdf->stream($filename . '.pdf');
        }
        
        // Obtener totales de compras para los mismos items
        $itemIds = array_column($finalRecords, 'item_id');
        $purchaseTotals = [];
        
        if (!empty($itemIds)) {
            
            $purchases = PurchaseItem::select([
                'purchase_items.item_id',
                'purchase_items.quantity',
                'purchase_items.total'
            ])
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->whereIn('purchase_items.item_id', $itemIds)
            ->whereBetween('purchases.date_of_issue', [$data_of_period['d_start'], $data_of_period['d_end']])
            ->whereIn('purchases.document_type_id', ['01', '03'])
            ->whereNotIn('purchases.state_type_id', $this->documents_excluded)
            ->get();
            
            // Agrupar compras por item_id
            foreach ($purchases as $purchase) {
                $itemId = $purchase->item_id;
                
                if (!isset($purchaseTotals[$itemId])) {
                    $purchaseTotals[$itemId] = [
                        'total_purchase_quantity' => 0,
                        'total_purchase_amount' => 0
                    ];
                }
                
                $purchaseTotals[$itemId]['total_purchase_quantity'] += $purchase->quantity;
                $purchaseTotals[$itemId]['total_purchase_amount'] += $purchase->total;
            }
        }
        
        // Agregar datos de compras a los records finales
        foreach ($finalRecords as &$record) {
            $itemId = $record['item_id'];
            $record['purchase_quantity'] = $purchaseTotals[$itemId]['total_purchase_quantity'] ?? 0;
            $record['purchase_amount'] = $purchaseTotals[$itemId]['total_purchase_amount'] ?? 0;
            
            // Calcular utilidad (venta - compra)
            $record['profit_amount'] = $record['total_amount'] - $record['purchase_amount'];
        }

        $company = Company::first();
        $records = $finalRecords; // Usar los datos finales procesados

        $pdf = PDF::loadView('report::general_items.report_pdf_resume', compact("records", "data_of_period", "company"))->setPaper('a4', 'portrait');

        $filename = 'Reporte_General_Resumen_Ventas_Productos_' . Carbon::now();

        return $pdf->stream($filename . '.pdf');
    }

    public function pdf(Request $request)
    {
        ini_set('memory_limit', '4026M');
        ini_set("pcre.backtrack_limit", "5000000");
        $records = $this->getRecordsItems($request->all())->latest('id')->get();
        $type_name = ($request->type == 'sale') ? 'Ventas_' : 'Compras_';
        $type = $request->type;
        $document_type_id = $request['document_type_id'];
        $request_apply_conversion_to_pen = $request['apply_conversion_to_pen'];

        $pdf = PDF::loadView('report::general_items.report_pdf', compact("records", "type", "document_type_id", "request_apply_conversion_to_pen"))->setPaper('a4', 'landscape');

        $filename = 'Reporte_General_Productos_' . $type_name . Carbon::now();

        return $pdf->download($filename . '.pdf');
    }


    public function excelJob(Request $request)
    {
        $host = $request->getHost();
        $format = $request->input('format');
        $website = $this->getTenantWebsite();
        $user = $this->getCurrentUser();
        $tray = $this->createDownloadTray($user->id, 'REPORT', 'excel', 'Reporte Productos - Ventas');

        $filters = $request->all();

        ProcessItemReport::dispatch($tray->id, $website->id, $filters);

        return $this->getJobResponse();
    }
    public function excel(Request $request)
    {
        ini_set('memory_limit', '4026M');
        ini_set("pcre.backtrack_limit", "5000000");
        $records = $this->getRecordsItems($request->all())->latest('id')->get();
        $type = ($request->type == 'sale') ? 'Ventas_' : 'Compras_';
        $document_type_id = $request['document_type_id'];
        $request_apply_conversion_to_pen = $request['apply_conversion_to_pen'];

        $generalItemExport = new GeneralItemExport();
        $generalItemExport
            ->records($records)
            ->type($request->type)
            ->document_type_id($document_type_id)
            ->request_apply_conversion_to_pen($request_apply_conversion_to_pen);

        return $generalItemExport->download('Reporte_General_Productos_' . $type . Carbon::now() . '.xlsx');
    }
}
