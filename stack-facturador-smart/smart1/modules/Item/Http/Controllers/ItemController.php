<?php

namespace Modules\Item\Http\Controllers;

use App\Imports\ItemsImport;
use App\Imports\ItemsImportDispatch;
use App\Imports\ItemsImportDocument;
use App\Imports\ItemsImportDocumentNews;
use App\Imports\PriceUpdatePersonTypeImport;
use App\Imports\PriceUpdatePresentationImport;
use App\Imports\PriceUpdateWarehouseImport;
use App\Models\System\Digemid;
use \Exception;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemLabelColor;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\ApportionmentItemsStock;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use Modules\Item\Http\Resources\ItemHistoryPurchasesCollection;
use Modules\Item\Http\Resources\ItemHistorySalesCollection;
use Modules\Item\Http\Resources\ItemLotCollection;
use Modules\Item\Imports\ItemListPriceImport;
use Modules\Item\Imports\ItemListWithExtraData;
use Modules\Item\Models\ItemLot;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Item\Exports\ItemDocumentErrorExport;
use Modules\Item\Imports\{
    ItemListSizeImport,
    ItemUpdatePriceImport
};


class ItemController extends Controller
{
    public function updateStockItem(Request $request, $id)
    {
        $stock = $request->stock;
        $warehouse_id = $request->warehouse_id;
        $item = Item::findOrFail($id);
        if ($warehouse_id) {
            $item_warehouse = ItemWarehouse::where('item_id', $id)->where('warehouse_id', $warehouse_id)->first();
            if ($item_warehouse) {
                $item_warehouse->stock = $stock;
                $item_warehouse->save();
            } else {
                $item_warehouse = ItemWarehouse::where('item_id', $id)->first();
                $item_warehouse->stock = $stock;
                $item_warehouse->save();
            }
        } else {
            $item_warehouse = ItemWarehouse::where('item_id', $id)->first();
            $item_warehouse->stock = $stock;
            $item_warehouse->save();
        }
        $stock = 0;
        $items_warehouses = ItemWarehouse::where('item_id', $id)->get();
        foreach ($items_warehouses as $item_warehouse) {
            $stock += $item_warehouse->stock;
        }
        $item->stock = $stock;
        $item->save();
        return [
            'success' => true,
            'message' => 'Stock actualizado'
        ];
    }
    public function updateInfoItem(Request $request, $id){
        $item = Item::findOrFail($id);
        $item->sale_unit_price = $request->sale_unit_price;
        $item->unit_type_id = $request->unit_type_id;
        $item->save();
        return [
            'success' => true,
            'message' => 'Información actualizada'
        ];
    }
    public function updatePurchaseUnitPrice(Request $request, $id)
    {
        $purchase_unit_price = $request->purchase_unit_price;
        $item = Item::findOrFail($id);
        $item->purchase_unit_price = $purchase_unit_price;
        $item->save();
        return [
            'success' => true,
            'message' => 'Precio de compra actualizado'
        ];
    }
    public function ItemsDocumentErrors($hash)
    {

        $exists_data = Cache::has($hash);

        if (!$exists_data) {
            return [
                'success' => false,
                'message' => 'No se encontró los datos del documento'
            ];
        }

        $data = Cache::get($hash);

        return (new ItemDocumentErrorExport)
            ->records($data)
            ->download('Productos_nuevos_' . Carbon::now() . '.xlsx');
    }
    public function uploadItemsDocumentNews(Request $request)
    {
        $type = $request->type;
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImportDocumentNews();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
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
    public function uploadItemsDispatch(Request $request){
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImportDispatch();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
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
    public function uploadItemsDocument(Request $request)
    {
        
        $type = $request->type;
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImportDocument();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
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


    public function set_internal_code()
    {
        Item::whereNull('internal_id')->chunk(50, function ($items) {
            foreach ($items as $item) {
                $item->internal_id = $this->create_internal_code($item->description);
                $item->save();
            }
        });

        return [
            'success' => true,
            'message' => 'Códigos internos generados correctamente'
        ];
    }

    function create_internal_code($description)
    {
        $description = mb_strtoupper($description, 'UTF-8');
        //remove special characters
        $description = preg_replace('/[^A-Za-z0-9\-]/', ' ', $description);
        $words = explode(' ', $description);
        $length = count($words);

        if ($length == 1) {
            $code = substr($words[0], 0, 3);
        } elseif ($length == 2) {
            $code = substr($words[0], 0, 2) . substr($words[1], 0, 2);
        } else {
            $code = '';
            if ($length > 5) {
                $words = array_slice($words, 0, 5);
            }
            foreach ($words as $word) {
                $code .= substr($word, 0, 1);
            }
        }

        if (strlen($code) < 5) {
            $missing = 5 - strlen($code);
            $code = $code . str_repeat('0', $missing);
        }

        $exists = Item::where('internal_id', $code)->exists();

        if ($exists) {
            while ($exists) {
                $last_letter = substr($code, -1);

                if (is_numeric($last_letter)) {
                    $code = substr($code, 0, -1) . ((int)substr($code, -1) + 1);
                } else {
                    $code .= '1';
                }
                $exists = Item::where('internal_id', $code)->exists();
            }
        }

        return $code;
    }

    public function codes_digemid(Request $request)
    {
        $input = $request->input;
        $input = '%' . str_replace(" ", "%", $input) . '%';
        $digemid_codes = Digemid::select('cod_prod', 'num_regsan', 'nom_prod', 'nom_form_farm_simplif', 'concent', 'nom_titular', 'fracciones')->where('cod_prod', 'like', $input)
            ->orWhere('nom_prod', 'like', $input)
            ->orWhere('nom_form_farm', 'like', $input)
            ->take(20)->get();
        return compact('digemid_codes');
    }
    public function generateBarcode($id)
    {

        $item = Item::findOrFail($id);

        $colour = [150, 150, 150];

        $generator = new BarcodeGeneratorPNG();

        $temp = tempnam(sys_get_temp_dir(), 'item_barcode');

        file_put_contents($temp, $generator->getBarcode($item->barcode, $generator::TYPE_CODE_128, 5, 70, $colour));

        $headers = [
            'Content-Type' => 'application/png',
        ];

        return response()->download($temp, "{$item->barcode}.png", $headers);
    }


    public function importItemSizeLists(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new ItemListSizeImport();
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
    public function importItemPriceLists(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new ItemListPriceImport();
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

    /**
     * @param Request $request
     *
     * @return array
     */
    public function importItemWithExtraData(Request $request): array
    {
        if ($request->hasFile('file')) {
            try {
                $import = new ItemListWithExtraData();
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

    public function getDataHistory($id)
    {

        $item = Item::findOrFail($id);

        $item_label_colors = ItemLabelColor::where('item_id', $id)->get();

        return [
            'sizes' => $item->sizes->transform(function ($row) {
                return [
                    'warehouse_id' => $row->warehouse->id,
                    'warehouse_description' => $row->warehouse->description,
                    'stock' => $row->stock,
                    'item_id' => $row->id,
                    'size' => $row->size,
                ];
            }),
            'warehouses' => $item->warehouses->transform(function ($row) use ($item, $item_label_colors) {
                return [
                    'warehouse_id' => $row->warehouse->id,
                    'warehouse_description' => $row->warehouse->description,
                    'stock' => $row->stock,
                    'item_id' => $item->id,
                    'series_enabled' => (bool)$item->series_enabled,
                    'label_color_id' => $item_label_colors->where('warehouse_id', $row->warehouse->id)->first()->label_color_id ?? null,
                ];
            })
        ];
    }


    public function availableSeriesRecords(Request $request)
    {

        $form = json_decode($request->form);

        $records = ItemLot::where('has_sale', false)
            ->where('item_id', $form->item_id)
            ->where('warehouse_id', $form->warehouse_id)
            ->latest();

        return new ItemLotCollection($records->paginate(config('tenant.items_per_page_simple_d_table_params')));
    }


    public function itemHistorySales(Request $request)
    {

        $form = json_decode($request->form);

        $sale_notes = SaleNoteItem::where(function ($query) use ($form) {
            $query->where('item_id', $form->item_id)->orWhereHas('relation_item', function ($query) use ($form) {
                $query->whereHas('sets', function ($query) use ($form) {
                    $query->where('individual_item_id', $form->item_id);
                });
            });
        })
            ->whereHas('sale_note', function ($query) {
                $query->whereDoesntHave('documents');
            })

            ->join('sale_notes', 'sale_note_items.sale_note_id', '=', 'sale_notes.id')
            ->join('persons', 'sale_notes.customer_id', '=', 'persons.id')
            ->join('items', 'sale_note_items.item_id', '=', 'items.id')
            ->join('state_types', 'sale_notes.state_type_id', '=', 'state_types.id')
            ->select(DB::raw("sale_note_items.id as id, sale_notes.series as series, sale_notes.number as number,
                                            items.web_platform_id as web_platform_id,
                                            items.is_set as is_set,
                                            sale_notes.state_type_id as state_type_id,
                                            state_types.description as state_type_description,
                                            sale_note_items.unit_price as price, sale_notes.date_of_issue as date_of_issue, sale_notes.total as total,
                                            persons.number as customer_number, persons.name as customer_name, sale_note_items.quantity as quantity,
                                            sale_notes.created_at as created_at"));

        $documents = DocumentItem::where(function ($query) use ($form) {
            $query->where('item_id', $form->item_id)->orWhereHas('relation_item', function ($query) use ($form) {
                $query->whereHas('sets', function ($query) use ($form) {
                    $query->where('individual_item_id', $form->item_id);
                });
            });
        })
            ->join('documents', 'document_items.document_id', '=', 'documents.id')
            ->join('persons', 'documents.customer_id', '=', 'persons.id')
            ->join('items', 'document_items.item_id', '=', 'items.id')
            ->join('state_types', 'documents.state_type_id', '=', 'state_types.id')
            ->select(DB::raw("document_items.id as id, documents.series as series, documents.number as number,
                                            items.web_platform_id as web_platform_id,
                                            items.is_set as is_set,
                                            documents.state_type_id as state_type_id,
                                            state_types.description as state_type_description,
                                            document_items.unit_price as price, documents.date_of_issue as date_of_issue, documents.total as total,
                                            persons.number as customer_number, persons.name as customer_name, document_items.quantity as quantity,
                                            documents.created_at as created_at"));
        return new ItemHistorySalesCollection($documents->union($sale_notes)->orderBy('created_at', 'desc')->paginate(config('tenant.items_per_page_simple_d_table_params')));
    }


    public function itemHistoryPurchases(Request $request)
    {

        $form = json_decode($request->form);

        $purchases = PurchaseItem::where('item_id', $form->item_id)
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('persons', 'purchases.supplier_id', '=', 'persons.id')
            ->select(DB::raw("purchase_items.id as id, purchases.series as series, purchases.number as number,
                                    purchases.exchange_rate_sale as exchange_rate_sale,
                                    purchases.currency_type_id as currency_type_id,
                                    purchases.was_dollar as was_dollar,
                                    purchases.supplier as supplier,purchase_items.unit_price as price, purchases.date_of_issue as date_of_issue, purchases.total as total,
                                    persons.number as supplier_number, persons.name as supplier_name, purchase_items.quantity as quantity,
                                    purchase_items.unit_price as unit_price,
                                    purchase_items.unit_price_apportioned as unit_price_apportioned,
                                    purchase_items.unit_value as unit_value,
                                    purchase_items.affectation_igv_type_id as affectation_igv_type_id,
                                    purchase_items.quantity_apportionment as quantity_apportionment,
                                    purchase_items.unit_price_apportioned_affected as unit_price_apportioned_affected,
                                    purchase_items.unit_value_apportioned_affected as unit_value_apportioned_affected,
                                    purchase_items.total_apportioned as total_apportioned,
                                    purchase_items.discount_apportioned as discount_apportioned,
                                    purchase_items.affected as affected,
                                    purchase_items.stock_before_apportionment as stock_before_apportionment,
                                    purchases.created_at as created_at"));


        return new ItemHistoryPurchasesCollection($purchases->orderBy('created_at', 'desc')->paginate(config('tenant.items_per_page_simple_d_table_params')));
    }

    public function itemtLastSale(Request $request)
    {

        $type_document = $request->type_document;
        $customer_id = $request->customer_id;
        $item_id = $request->item_id;

        $item = null;
        if ($type_document == 'CPE') {

            $item = DocumentItem::whereHas('document', function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })->orderBy('id', 'desc')->where('item_id', $item_id)->first();
        } else if ($type_document == 'NV') {

            $item = SaleNoteItem::whereHas('sale_note', function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })->orderBy('id', 'desc')->where('item_id', $item_id)->first();
        } else  if ($type_document == 'QUOTATION') {

            $document_cpe_item = DocumentItem::whereHas('document', function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })->orderBy('id', 'desc')->where('item_id', $item_id)->first();


            $sale_note_item = SaleNoteItem::whereHas('sale_note', function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })->orderBy('id', 'desc')->where('item_id', $item_id)->first();

            if ($document_cpe_item && $sale_note_item) {

                if (Carbon::parse($document_cpe_item->document->created_at)->gte(Carbon::parse($sale_note_item->sale_note->created_at))) {
                    $item = $document_cpe_item;
                } else {
                    $item = $sale_note_item;
                }
            } else {
                if ($document_cpe_item) {
                    $item = $document_cpe_item;
                } elseif ($sale_note_item) {
                    $item = $sale_note_item;
                }
            }
        }

        return [
            'unit_price' => $item ? $item->unit_price : null,
            'item_id' => $item ? $item->id : null,
        ];
    }

    /**
     * 
     * Importar excel para actualizar los precios de forma masiva
     * 
     * @param Request $request
     *
     * @return array
     */
    public function importItemUpdatePrices(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new ItemUpdatePriceImport();
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
    public function importItemUpdatePricesPersonType(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new PriceUpdatePersonTypeImport();
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
    public function importItemUpdatePricesWarehouses(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new PriceUpdateWarehouseImport();
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
    public function importItemUpdatePricesPresentation(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new PriceUpdatePresentationImport();
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

    /**
     * Get apportionment items stock records for a specific item
     */
    public function getApportionmentItemsStock($item_id)
    {
        try {
            $records = ApportionmentItemsStock::with(['purchase_item', 'warehouse'])
                ->where('item_id', $item_id)
                ->orderBy('created_at', 'desc')
                ->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'purchase_item_id' => $row->purchase_item_id,
                        'warehouse_id' => $row->warehouse_id,
                        'stock' => $row->stock,
                        'stock_remaining' => $row->stock_remaining,
                        'unit_price_apportioned' => $row->unit_price_apportioned,
                        'purchase' => $row->purchase_item->purchase->number_full,
                    ];
                });

            return response()->json($records);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los registros de distribución de stock'
            ], 500);
        }
    }

    /**
     * Delete an apportionment items stock record
     */
    public function deleteApportionmentItemsStock($id)
    {
        try {
            $record = ApportionmentItemsStock::findOrFail($id);
            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro'
            ], 500);
        }
    }
}
