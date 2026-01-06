<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\DigemidItemCsvExport;
use Exception;
use Mpdf\Mpdf;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;
use Mpdf\HTMLParserMode;
use App\Exports\ItemExport;
use App\Models\Tenant\Item;
use Illuminate\Support\Str;
use App\Imports\ItemsImport;
use App\Traits\OfflineTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Exports\ItemExportWp;
use App\Imports\CatalogImport;
use App\Models\System\Digemid;
use App\Models\Tenant\Company;
use App\Models\Tenant\ItemTag;
use Modules\Item\Models\Brand;
use App\Models\Tenant\ItemImage;
use App\Models\Tenant\Warehouse;
use Modules\Item\Models\ItemLot;
use App\Models\Tenant\ItemSupply;
use Modules\Item\Models\Category;
use App\Exports\DigemidItemExport;
use App\Exports\ItemExportToImport;
use App\Models\Tenant\CatItemSize;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant\Catalogs\Tag;
use App\Models\Tenant\CategoryItem;
use App\Models\Tenant\ItemMovement;
use App\Models\Tenant\ItemUnitType;
use Modules\Account\Models\Account;
use Modules\Restaurant\Models\Area;
use Modules\Restaurant\Models\Food;
use App\Exports\ItemExtraDataExport;
use App\Exports\ItemPriceUpdatePersonTypeTemplate;
use App\Exports\ItemPriceUpdateWarehouseTemplate;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use Modules\Digemid\Models\CatDigemid;
use Modules\Item\Models\ItemLotsGroup;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\Catalogs\UnitType;
use App\Http\Requests\Tenant\ItemRequest;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\ItemWarehousePrice;
use App\Http\Resources\Tenant\ItemResource;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Item\Models\ItemLotsGroupState;
use App\Http\Resources\Tenant\ItemCollection;
use App\Http\Controllers\PdfUnionController;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\CatColorsItem;
use App\Models\Tenant\Catalogs\CatItemStatus;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\SystemIscType;
use Modules\Finance\Helpers\UploadFileHelper;
use App\Http\Controllers\SearchItemController;
use App\Http\Resources\Tenant\ItemLiteCollection;
use App\Models\Tenant\ApportionmentItemsStock;
use App\Models\Tenant\Catalogs\CatItemMoldCavity;
use App\Models\Tenant\Catalogs\PaymentMethodType;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CatItemMoldProperty;
use App\Models\Tenant\Catalogs\CatItemUnitBusiness;
use App\Models\Tenant\Catalogs\CatItemProductFamily;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Item\Exports\ItemMigrationExport;
use App\Models\Tenant\Catalogs\CatItemUnitsPerPackage;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\Catalogs\CatItemPackageMeasurement;
use App\Models\Tenant\ConfigurationEcommerce;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\InitStock;
use App\Models\Tenant\Inventory;
use App\Models\Tenant\InventoryKardex;
use App\Models\Tenant\ItemBonus;
use App\Models\Tenant\ItemFoodDealer;
use App\Models\Tenant\ItemPricePaymentCondition;
use Modules\Item\Models\ItemProperty;
use App\Models\Tenant\ItemSet;
use App\Models\Tenant\ItemSizeStock;
use App\Models\Tenant\PersonFoodDealer;
use App\Models\Tenant\PersonType;
use App\Models\Tenant\PersonTypePrice;
use App\Models\Tenant\Promotion;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\QuotationItem;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNoteItem;
use Barryvdh\Debugbar\Twig\Extension\Dump;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Inventory\Models\CostAverage;
use Modules\Inventory\Models\DevolutionItem;
use Modules\Inventory\Models\GuideItem;
use Modules\Inventory\Models\Inventory as ModelsInventory;
use Modules\Inventory\Models\InventoryTransferItem;
use Modules\Inventory\Models\ValidationWarehouseItem;
use Modules\Item\Exports\ItemMigrationExportV2;
use Modules\Item\Exports\ItemMigrationExportV3;
use Modules\Order\Models\OrderFormItem;
use Modules\Order\Models\OrderNoteItem;
use Modules\Preparation\Models\RegisterInputsMovement;
use Modules\Purchase\Models\PurchaseOrderItem;
use Modules\Purchase\Models\PurchaseQuotationItem;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Sale\Models\ContractItem;
use Modules\Sale\Models\SaleOpportunityItem;

class ItemController extends Controller
{
    use OfflineTrait;
    public function updateObservationApportionment(Request $request)
    {
        $apportionment_id = $request->apportionment_id;
        $observation = $request->observation;
        $apportionment = ApportionmentItemsStock::findOrFail($apportionment_id);
        $apportionment->observation = $observation;
        $apportionment->save();
        return response()->json([
            'success' => true,
            'message' => 'Observación actualizada'
        ]);
    }
    public function attributes(Request $request)
    {
        $records = ItemProperty::where('item_id', $request->item_id);

        if ($request->has_sale == true) {
            $records->where('has_sale', true);
        } else {
            $records->where('has_sale', false);
        }
        return response()->json([
            "data" => $records->get()
        ]);
    }

    public function itemAdjustment(Request $request)
    {
        try {
            $item_id = $request->item_id;
            $warehouse_id = $request->warehouse_id;

            // Validar parámetros requeridos
            if (!$item_id || !$warehouse_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'item_id y warehouse_id son requeridos'
                ], 400);
            }

            // Tipos de documentos válidos (ventas y compras)
            $types = [Document::class, SaleNote::class, Purchase::class];

            // Estados válidos
            $validStates = ['01', '03', '05'];

            // Obtener todos los registros de kardex para este item y almacén
            $inventoryKardexRecords = InventoryKardex::where('item_id', $item_id)
                ->where('warehouse_id', $warehouse_id)
                ->whereIn('inventory_kardexable_type', $types)
                ->get()
                ->groupBy('inventory_kardexable_type');

            $adjustedRecords = [];
            $deletedRecords = 0;
            $processedTypes = 0;
            $totalRecordsFound = 0;

            foreach ($inventoryKardexRecords as $type => $typeRecords) {
                $processedTypes++;

                // Agrupar por document_id dentro de cada tipo
                $recordsByDocument = $typeRecords->groupBy('inventory_kardexable_id');

                // Obtener los estados de todos los documentos de este tipo con una sola consulta
                $documentIds = $typeRecords->pluck('inventory_kardexable_id')->unique()->toArray();
                $tableName = '';

                if ($type === Document::class) {
                    $tableName = 'documents';
                } elseif ($type === SaleNote::class) {
                    $tableName = 'sale_notes';
                } elseif ($type === Purchase::class) {
                    $tableName = 'purchases';
                }

                // Consulta optimizada para obtener solo id y state_type_id
                $documentsStates = DB::connection('tenant')
                    ->table($tableName)
                    ->whereIn('id', $documentIds)
                    ->select('id', 'state_type_id')
                    ->get()
                    ->keyBy('id');

                foreach ($recordsByDocument as $documentId => $documentRecords) {
                    $totalRecordsFound += $documentRecords->count();

                    // Verificar si el documento existe y obtener su estado
                    $documentState = $documentsStates->get($documentId);

                    if (!$documentState) {
                        // Si no existe el documento, eliminar todos los registros
                        foreach ($documentRecords as $record) {
                            $record->delete();
                            $deletedRecords++;
                        }

                        $adjustedRecords["{$type}_{$documentId}"] = [
                            'action' => 'deleted_all',
                            'reason' => 'Document not found',
                            'deleted_records' => $documentRecords->pluck('id')->toArray(),
                            'final_quantity' => 0
                        ];
                        continue;
                    }

                    if (!in_array($documentState->state_type_id, $validStates)) {
                        // Estado inválido: eliminar todos los registros para este documento
                        foreach ($documentRecords as $record) {
                            $record->delete();
                            $deletedRecords++;
                        }

                        $adjustedRecords["{$type}_{$documentId}"] = [
                            'action' => 'deleted_all',
                            'reason' => 'Invalid document state: ' . $documentState->state_type_id,
                            'deleted_records' => $documentRecords->pluck('id')->toArray(),
                            'final_quantity' => 0
                        ];
                    } else {
                        // Estado válido: mantener solo un registro
                        if ($documentRecords->count() > 1) {
                            // Hay duplicados, ajustar
                            $originalQuantity = $documentRecords->last()->quantity;

                            // Determinar si es venta o compra para establecer el signo correcto
                            $isVenta = ($type === Document::class || $type === SaleNote::class);
                            $finalQuantity = $isVenta ? -abs($originalQuantity) : abs($originalQuantity);

                            // Mantener solo el último registro
                            $keepRecord = $documentRecords->last();
                            $recordsToDelete = $documentRecords->slice(0, -1);

                            // Actualizar la cantidad del registro que se mantiene
                            $keepRecord->update(['quantity' => $finalQuantity]);

                            // Eliminar los registros duplicados
                            foreach ($recordsToDelete as $recordToDelete) {
                                $recordToDelete->delete();
                                $deletedRecords++;
                            }

                            $adjustedRecords["{$type}_{$documentId}"] = [
                                'action' => 'adjusted',
                                'document_type' => class_basename($type),
                                'document_state' => $documentState->state_type_id,
                                'kept_record_id' => $keepRecord->id,
                                'original_quantity' => $documentRecords->sum('quantity'),
                                'final_quantity' => $finalQuantity,
                                'is_sale' => $isVenta,
                                'deleted_records' => $recordsToDelete->pluck('id')->toArray()
                            ];
                        }
                    }
                }
            }

            // Obtener el kardex actualizado para retornar
            $updatedInventoryKardex = InventoryKardex::where('item_id', $item_id)
                ->where('warehouse_id', $warehouse_id)
                ->whereIn('inventory_kardexable_type', $types)
                ->get()
                ->groupBy('inventory_kardexable_type');

            return response()->json([
                'success' => true,
                'message' => "Kardex de item ajustado correctamente. Tipos procesados: {$processedTypes}, Registros eliminados: {$deletedRecords}",
                'item_id' => $item_id,
                'warehouse_id' => $warehouse_id,
                'valid_states' => $validStates,
                'processed_types' => $processedTypes,
                'total_records_found' => $totalRecordsFound,
                'deleted_records' => $deletedRecords,
                'adjustments' => $adjustedRecords,
                'inventoryKardex' => $updatedInventoryKardex
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ajustar kardex del item: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getFormatImport()
    {
        return (new ItemExportToImport())
            ->download('Plantilla_Importacion_de_Items.xlsx');
    }

    public function addItemWarehouse(Request $request)
    {
        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id;
        $stock = $request->stock;
        $inventory = new ModelsInventory();
        $inventory->type = 1;
        $inventory->description = 'Stock inicial';
        $inventory->item_id = $item_id;
        $inventory->warehouse_id = $warehouse_id;
        $inventory->quantity = $stock;
        $inventory->save();
        return response()->json([
            'success' => true,
            'message' => 'Almacen agregado correctamente'
        ]);
    }

    public function details($record_id)
    {
        $vc_company = Company::first();
        return view('tenant.items.details', compact('record_id', 'vc_company'));
    }
    public function index()
    {
        $is_food_dealer = BusinessTurn::isFoodDealer();
        $is_comercial  = auth()->user()->integrate_user_type_id == 2;
        $type = 'PRODUCTS';
        return view('tenant.items.index', compact('type', 'is_comercial', 'is_food_dealer'));
    }

    public function checkInternalId($internal_id)
    {
        $item = Item::where('internal_id', $internal_id)->exists();

        return [
            'success' => true,
            'exists' => $item
        ];
    }
    public function indexServices()
    {
        $is_food_dealer = BusinessTurn::isFoodDealer();
        $is_comercial  = auth()->user()->integrate_user_type_id == 2;
        $type = 'ZZ';
        return view('tenant.items.index', compact('type', 'is_comercial', 'is_food_dealer'));
    }

    public function templateUpdatePricesPersonType()
    {
        return (new ItemPriceUpdatePersonTypeTemplate())
            ->download('Plantilla_Actualizacion_Precios_Tipo_de_Cliente.xlsx');;
    }
    public function templateUpdatePricesPresentation()
    {
        return (new ItemPriceUpdatePresentationTemplate())
            ->download('Plantilla_Actualizacion_Precios_Presentaciones.xlsx');;
    }
    public function templateUpdatePricesWarehouses()
    {
        return (new ItemPriceUpdateWarehouseTemplate())
            ->download('Plantilla_Actualizacion_Precios_Almacenes.xlsx');;
    }
    public function index_ecommerce()
    {
        $has_woocommerce = false;
        $configuration = ConfigurationEcommerce::first();
        if ($configuration) {
            $has_woocommerce = $configuration->hasWoocommerce();
        }
        return view('tenant.items_ecommerce.index', compact('has_woocommerce'));
    }

    public function erase($item_id)
    {
        try {
            $item = Item::findOrFail($item_id);
            ValidationWarehouseItem::where('item_id', $item->id)->delete();
            DevolutionItem::where('item_id', $item->id)->delete();
            ItemPricePaymentCondition::where('item_id', $item->id)->delete();
            PurchaseOrderItem::where('item_id', $item->id)->delete();
            ItemSizeStock::where('item_id', $item->id)->delete();
            ItemSet::where('item_id', $item->id)->delete();
            Promotion::where('item_id', $item->id)->delete();
            ContractItem::where('item_id', $item->id)->delete();
            OrderFormItem::where('item_id', $item->id)->delete();
            PurchaseQuotationItem::where('item_id', $item->id)->delete();
            CostAverage::where('item_id', $item->id)->delete();
            InitStock::where('item_id', $item->id)->delete();
            OrderNoteItem::where('item_id', $item->id)->delete();
            QuotationItem::where('item_id', $item->id)->delete();
            GuideItem::where('item_id', $item->id)->delete();
            InventoryTransferItem::query()->delete();
            PersonFoodDealer::where('item_id', $item->id)->delete();
            ItemFoodDealer::where('item_id', $item->id)->delete();
            Food::where('item_id', $item->id)->delete();
            OrdenItem::where('item_id', $item->id)->delete();
            SaleOpportunityItem::where('item_id', $item->id)->delete();
            $items_sale_note = SaleNoteItem::where('item_id', $item->id)->get();
            foreach ($items_sale_note as $item_sale_note) {

                $item_sale_note->sellers()->delete();
            }
            SaleNoteItem::where('item_id', $item->id)->delete();
            // $item->sale_note_items()->delete();
            $item_lots = $item->item_lots();
            foreach ($item_lots as $item_lot) {
                InventoryTransferItem::where('item_lot_id', $item_lot->id)->delete();
            }
            $item->item_lots()->delete();

            $item->item_unit_types()->delete();
            $item->dispatch_items()->delete();
            // $item->inventory_kardex()->delete();
            $item->kardex()->delete();
            $item->cat_digemid()->delete();
            $item->purchase_item()->delete();
            $item->warehouses()->delete();
            // $item->guide_item()->delete();
            $item->tags()->delete();

            $item->sets()->delete();
            $item->item_lots()->delete();
            $item->images()->delete();
            $item->lots_group()->delete();
            $items_document = DocumentItem::where('item_id', $item->id)->get();
            foreach ($items_document as $item_document) {
                $item_document->sellers()->delete();
            }
            $item->document_items()->delete();
            DocumentItem::where('item_id', $item->id)->delete();
            $items_sale_note = $item->sale_note_items();
            foreach ($items_sale_note as $item_sale_note) {
                $item_sale_note->sellers()->delete();
            }
            $item->sale_note_items()->delete();
            $item->warehousePrices()->delete();
            $item->supplies_items()->delete();
            $item->item_movement_rel_extra()->delete();
            $item->technical_service_item()->delete();
            $item->supplies()->delete();
            InventoryKardex::where('item_id', $item->id)->chunk(100, function ($row) {
                foreach ($row as $key => $value) {
                    SaleNoteItem::where('inventory_kardex_id', $value->id)->delete();
                }
            });
            ItemProperty::where('item_id', $item->id)->delete();
            Inventory::where('item_id', $item->id)->delete();
            $item->delete();

            return [
                'success' => true,
                'message' => 'Item eliminado',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function columns()
    {
        $configuration = Configuration::select('is_pharmacy', 'item_complements', 'label_item_color')->firstOrFail();
        $factory_code = $configuration->is_pharmacy ? 'Principio activo' : ($configuration->item_complements ?  'Complementos' : 'Especificaciones');
        $to_return = [
            'description' => 'Nombre',
            'internal_id' => 'Código interno',
            'label_color' => 'Etiqueta',
            'unit_type_id' => 'Unidad de medida',
            'barcode' => 'Código de barras',
            'factory_code' => $factory_code,
            'model' => 'Modelo',
            'brand' => 'Marca',
            'date_of_due' => 'Fecha vencimiento',
            'lot_code' => 'Código lote',
            'category' => 'Categoria',
            'commission' => 'Comisión',
        ];
        if (!$configuration->label_item_color) {
            unset($to_return['label_color']);
        }
        return $to_return;
    }

    public function updateLabelColor(Request $request)
    {
        $item_id = $request->id;
        $label_color_id = $request->label_color_id;
        $warehouse_id = $request->warehouse_id;
        DB::connection('tenant')->table('item_label_colors')->where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->delete();
        DB::connection('tenant')->table('item_label_colors')->insert([
            'item_id' => $item_id,
            'label_color_id' => $label_color_id,
            'warehouse_id' => $warehouse_id,
        ]);

        return [
            'success' => true,
            'message' => 'Etiqueta actualizada'
        ];
    }

    public function updateStock(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $item_id = $request->id;
        $stock = $request->stock;
        $item = Item::find($item_id);
        if ($warehouse_id) {
            $item->warehouses()->where('warehouse_id', $warehouse_id)->update(['stock' => $stock]);
        } else {
            $item->warehouses()->first()->update(['stock' => $stock]);
        }
        return [
            'success' => true,
            'message' => 'Stock actualizado'
        ];
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new ItemLiteCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function records_restaurant(Request $request)
    {
        $records = $this->getInitialQueryRecords();
        switch ($request->column) {

            case 'brand':
                $records->whereHas('brand', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->value}%");
                });
                break;
            case 'category':
                $records->whereHas('category', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->value}%");
                });
                break;

            case 'active':
                $records->whereIsActive();
                break;

            case 'inactive':
                $records->whereIsNotActive();
                break;

            default:
                if ($request->has('column')) {
                    if ($this->applyAdvancedRecordsSearch() && $request->column === 'description') {
                        if ($request->value) $records->whereAdvancedRecordsSearch($request->column, $request->value);
                    } else {
                        $records->where($request->column, 'like', "%{$request->value}%");
                    }
                }
                break;
        }
        if ($request->type) {
            if ($request->type === 'PRODUCTS') {
                // listar solo productos en la lista de productos
                $records->whereNotService();
            } else {
                $records->whereService();
            }
        }

        $records->orderBy('frequent', 'desc');
        return new ItemCollection($records->paginate(config('tenant.items_per_page')));
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRecords(Request $request)
    {

        // $records = Item::whereTypeUser()->whereNotIsSet();
        $records = $this->getInitialQueryRecords();
        $configuration = Configuration::getConfig();
        $list_items_by_warehouse = $configuration->list_items_by_warehouse;
        $isInput = $request->isInput == "true";
        $show_desactive = $request->show_desactive == "true";
        $auth_user = auth()->user();
        $establishment_id = $auth_user->establishment_id;
        $warehouse_id = Warehouse::where('establishment_id', $establishment_id)->first()->id;
        if ($list_items_by_warehouse && $request->type === 'PRODUCTS') {
            $records = $records->whereHas('warehouses', function ($query) use ($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            });
        }
        $order_price = $request->order_price;
        $presentInEcommerce = $request->presentInEcommerce;
        if ($show_desactive) {
            $records->whereIsNotActive();
        } else {
            $records->whereIsActive();
        }
        $order_purchase_date = $request->order_purchase_date;
        $order_stock = $request->order_stock;
        switch ($request->column) {

            case 'commission':
                $records->where('commission_amount', 'like', "%{$request->value}%");
                break;
            //unit_type_id
            case 'unit_type_id':
                $records->where('unit_type_id', $request->value);
                break;
            case 'brand':
                $records->whereHas('brand', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->value}%");
                });
                break;
            case 'category':
                $records->whereHas('category', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->value}%");
                });
                break;


            case 'factory_code':
                $records->where('factory_code', 'like', "%{$request->value}%");
                break;

            case 'barcode':
                $records->where(function ($query) use ($request) {
                    $query->where('barcode', $request->value)
                        ->orWhereHas('item_unit_types', function ($q) use ($request) {
                            $q->where('barcode', $request->value);
                        });
                });
                break;


            case 'label_color':
                $records->whereHas('labelColor', function ($query) use ($request) {
                    $query->whereHas('labelColor', function ($query) use ($request) {
                        $query->where('description', 'like', "%{$request->value}%");
                    });
                });
                break;

            default:
                if ($request->has('column')) {
                    if ($this->applyAdvancedRecordsSearch() && $request->column === 'description') {
                        if ($request->value) $records->whereAdvancedRecordsSearch($request->column, $request->value);
                    } else {
                        $records->where($request->column, 'like', "%{$request->value}%");
                    }
                }
                break;
        }

        if ($isInput) {
            $records->where('is_input', true);
        }
        if ($presentInEcommerce !== null) {
            $records->where('apply_store', $presentInEcommerce);
        }
        if ($request->type) {
            if ($request->type === 'PRODUCTS') {
                // listar solo productos en la lista de productos
                $records->whereNotService();
            } else {
                $records->whereService();
            }
        }
        $isPharmacy = false;
        if ($request->has('isPharmacy')) {
            $isPharmacy = ($request->isPharmacy === 'true') ? true : false;
        }
        if ($isPharmacy == true) {
            $records->Pharmacy()
                ->with(['cat_digemid']);
        }
        if ($order_price) {
            $records = $records->orderBy('sale_unit_price', $order_price);
        } else if ($order_purchase_date) {
            $records = $records->leftJoin('purchase_items as pi', 'items.id', '=', 'pi.item_id')
                ->leftJoin('purchases as p', 'pi.purchase_id', '=', 'p.id')
                ->select('items.*', DB::raw('MAX(p.date_of_issue) as fecha_compra'))
                ->groupBy('items.id')
                ->orderBy(DB::raw('MAX(p.date_of_issue)'), $order_purchase_date);

            if ($warehouse_id) {
                $records->whereHas('warehouses', function ($query) use ($warehouse_id) {
                    $query->where('warehouse_id', $warehouse_id)
                        ->where('stock', '>', 0);
                });
            }
        } else if ($order_stock) {
            $records = $records->select('items.*', 'item_warehouse.stock as current_stock')
                ->leftJoin('item_warehouse', function ($join) use ($warehouse_id) {
                    $join->on('items.id', '=', 'item_warehouse.item_id')
                        ->where('item_warehouse.warehouse_id', '=', $warehouse_id);
                })
                ->orderBy('current_stock', $order_stock);
        } else {
            $records = $records->orderBy('description', 'asc');
        }


        return $records;
    }


    /**
     *
     * Aplicar filtros iniciales a la consulta
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getInitialQueryRecords()
    {

        // if (Configuration::getRecordIndividualColumn('list_items_by_warehouse')) {
        // $records = Item::whereWarehouse()->whereNotIsSet();
        // } else {
        $records = Item::whereTypeUser()->whereNotIsSet();
        // }

        return $records;
    }


    public function create()
    {
        return view('tenant.items.form');
    }

    public function tables()
    {
        $configuration = Configuration::first();
        $list_items_by_warehouse = $configuration->list_items_by_warehouse;
        $areas = Area::all();
        $clothes_shoes = BusinessTurn::where('value', 'clothes_shoes')->first();
        // $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();
        $payment_method_types = PaymentMethodType::all();
        $category = CategoryItem::all();
        $configuration = Configuration::select('affectation_igv_type_id')->firstOrFail();
        $digemid_codes = Digemid::select('cod_prod', 'num_regsan', 'nom_prod', 'nom_form_farm_simplif', 'concent', 'nom_titular', 'fracciones')->take(20)->get();
        $unit_types = UnitType::whereActive()->orderByDescription()->get();
        $currency_types = CurrencyType::whereActive()->orderByDescription()->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $system_isc_types = SystemIscType::whereActive()->orderByDescription()->get();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $warehouses = Warehouse::where('active', true);
        if($list_items_by_warehouse){
            $warehouses = $warehouses->where('establishment_id', auth()->user()->establishment_id);
        }
        $warehouses = $warehouses->get();
        $customer_types = PersonType::all();
        $establishments = Establishment::find(auth()->user()->establishment_id);
        $warehouse_id = Warehouse::where('establishment_id', $establishments->id)->first()->id;
        $accounts = Account::all();
        $tags = Tag::all();
        $categories = Category::all();
        $brands = Brand::all();
        $ingredients = \Modules\Item\Models\IngredientAttributeItem::where('active', true)->orderBy('name')->get();
        $lines = \Modules\Item\Models\LineAttributeItem::where('active', true)->orderBy('name')->get();
        $is_majolica = false;
        $business_turn = BusinessTurn::where('value', 'majolica')->first();
        if ($business_turn) {

            $is_majolica = (bool) $business_turn->active;
        }
        /** Informacion adicional */
        $colors = collect([]);
        $CatItemStatus = $colors;
        $CatItemUnitBusiness = $colors;
        $CatItemMoldCavity = $colors;
        $CatItemPackageMeasurement = $colors;
        $CatItemUnitsPerPackage = $colors;
        $CatItemMoldProperty = $colors;
        $CatItemProductFamily = $colors;
        $CatItemSize = $colors;
        if ($configuration->isShowExtraInfoToItem()) {
            $colors = CatColorsItem::all();
            $CatItemStatus = CatItemStatus::all();
            $CatItemSize = CatItemSize::all();
            $CatItemUnitBusiness = CatItemUnitBusiness::all();
            $CatItemMoldCavity = CatItemMoldCavity::all();
            $CatItemPackageMeasurement = CatItemPackageMeasurement::all();
            $CatItemUnitsPerPackage = CatItemUnitsPerPackage::all();
            $CatItemMoldProperty = CatItemMoldProperty::all();
            $CatItemProductFamily = CatItemProductFamily::all();
        }
        /** Informacion adicional */
        //$configuration = $configuration->getCollectionData();
        $inventory_configuration = InventoryConfiguration::firstOrFail();
        /*
        $configuration = Configuration::select(
            'affectation_igv_type_id',
            'is_pharmacy',
            'show_extra_info_to_item'
        )->firstOrFail();
        */
        $warehouse_user_id = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first()->id;
        
        $clothesShoes = BusinessTurn::isClothesShoes();
        $is_weapon_tracking = BusinessTurn::isWeaponTracking();
        return compact(
            'warehouse_user_id',
            'warehouse_id',
            'is_weapon_tracking',
            'is_majolica',
            'customer_types',
            'clothesShoes',
            'areas',
            'payment_method_types',
            'category',
            'digemid_codes',
            'unit_types',
            'currency_types',
            'attribute_types',
            'system_isc_types',
            'affectation_igv_types',
            'warehouses',
            'accounts',
            'tags',
            'categories',
            'brands',
            'ingredients',
            'lines',
            'configuration',
            'colors',
            'CatItemSize',
            'CatItemMoldCavity',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemProductFamily',
            'CatItemUnitsPerPackage',
            'inventory_configuration'
        );
    }


    public function restrictStockOriginal($id)
    {
        $establishment_id = auth()->user()->establishment_id;
        $configuration = Configuration::first();
        if (!$configuration->show_restrict_stock_in_items_form) {
            return [
                'success' => true,
                'message' => 'El stock no está restringido',
            ];
        }
        $warehouse_id = Warehouse::where('establishment_id', $establishment_id)->first()->id;
        $item = Item::findOrFail($id);
        $is_set = $item->is_set;
        $has_bonus = $item->hasBonus();
        if ($is_set) {
            $ids = ItemSet::where('item_id', $id)->pluck('individual_item_id')->toArray();
            $item_wahouse = ItemWarehouse::whereIn('item_id', $ids)->where('warehouse_id', $warehouse_id)->get();
            $items_with_restrictions = [];
            foreach ($item_wahouse as $item) {
                $restrict_stock_quantity = $item->restrict_stock_quantity;
                $stock = $item->stock;
                $stock_net = null;
                if ($restrict_stock_quantity) {
                    $stock_net = $stock - $restrict_stock_quantity;
                }
                $items_with_restrictions[] = [
                    'item_id' => $item->item_id,
                    'restrict_stock_quantity' => $restrict_stock_quantity,
                    'item_stock' => $stock,
                    'item_stock_net' => $stock_net,
                    'pass' => $restrict_stock_quantity <= $stock
                ];
            }

            return [
                'is_set' => true,
                'success' => true,
                'items_with_restrictions' => $items_with_restrictions
            ];
        } else if ($has_bonus) {
            $item_bonus = ItemBonus::where('item_id', $id)->pluck('item_bonus_id')->toArray();
            $item_wahouse = ItemWarehouse::whereIn('item_id', $item_bonus)->where('warehouse_id', $warehouse_id)->get();
            $items_with_restrictions = [];
            foreach ($item_wahouse as $item) {
                $restrict_stock_quantity = $item->restrict_stock_quantity;
                $stock = $item->stock;
                $stock_net = null;
                if ($restrict_stock_quantity) {
                    $stock_net = $stock - $restrict_stock_quantity;
                }
                $items_with_restrictions[] = [
                    'item_id' => $item->item_id,
                    'restrict_stock_quantity' => $restrict_stock_quantity,
                    'item_stock' => $stock,
                    'item_stock_net' => $stock_net,
                    'pass' => $restrict_stock_quantity <= $stock
                ];
            }
            $bonus_item = ItemBonus::where('item_id', $id)->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'item_id' => $row->item_bonus_id,
                    'quantity' => $row->quantity,
                    'internal_id' => $row->bonus_item->internal_id,
                    'is_bonus' => true,
                ];
            });
            return [
                'bonus_items' => $bonus_item,
                'is_bonus' => true,
                'success' => true,
                'items_with_restrictions' => $items_with_restrictions
            ];
        } else {
            $item_wahouse = ItemWarehouse::where('item_id', $id)->where('warehouse_id', $warehouse_id)->first();
            $restrict_stock_quantity = null;
            $stock = 0;
            $stock_net = null;
            if ($item_wahouse) {
                $restrict_stock_quantity = $item_wahouse->restrict_stock_quantity;
                $stock = $item_wahouse->stock;

                $stock_net = null;
                if ($restrict_stock_quantity) {
                    $stock_net = $stock - $restrict_stock_quantity;
                }
            }
            return [
                'has_bonus' => false,
                'is_set' => false,
                'success' => true,
                'restrict_stock_quantity' => $restrict_stock_quantity,
                'item_stock' => $stock,
                'item_stock_net' => $stock_net,
                'pass' => $restrict_stock_quantity <= $stock
            ];
        }
    }

    public function restrictStock($id)
    {
        $establishment_id = auth()->user()->establishment_id;

        // Direct queries - no cache to avoid tenant data mixing
        $configuration = Configuration::first();
        if (!$configuration->show_restrict_stock_in_items_form) {
            return [
                'success' => true,
                'message' => 'El stock no está restringido',
            ];
        }

        // Direct query for warehouse - tenant-safe
        $warehouse_id = Warehouse::where('establishment_id', $establishment_id)->first()->id;

        $item = Item::findOrFail($id);
        $is_set = $item->is_set;
        $has_bonus = $item->hasBonus();

        if ($is_set) {
            return $this->handleItemSet($id, $warehouse_id);
        } else if ($has_bonus) {
            return $this->handleItemBonus($id, $warehouse_id);
        } else {
            return $this->handleSingleItem($id, $warehouse_id);
        }
    }

    private function handleItemSet($item_id, $warehouse_id)
    {
        // Use single query with eager loading instead of N+1
        $item_warehouse_data = ItemWarehouse::select('item_id', 'stock', 'restrict_stock_quantity')
            ->whereIn('item_id', function ($query) use ($item_id) {
                $query->select('individual_item_id')
                    ->from('item_sets')
                    ->where('item_id', $item_id);
            })
            ->where('warehouse_id', $warehouse_id)
            ->get();

        $items_with_restrictions = $item_warehouse_data->map(function ($item) {
            $restrict_stock_quantity = $item->restrict_stock_quantity;
            $stock = $item->stock;
            $stock_net = $restrict_stock_quantity ? $stock - $restrict_stock_quantity : null;

            return [
                'item_id' => $item->item_id,
                'restrict_stock_quantity' => $restrict_stock_quantity,
                'item_stock' => $stock,
                'item_stock_net' => $stock_net,
                'pass' => $restrict_stock_quantity <= $stock
            ];
        })->toArray();

        return [
            'is_set' => true,
            'success' => true,
            'items_with_restrictions' => $items_with_restrictions
        ];
    }

    private function handleItemBonus($item_id, $warehouse_id)
    {
        // Single query with eager loading for bonus items
        $bonus_items_with_warehouse = ItemBonus::with(['bonus_item:id,internal_id'])
            ->select('id', 'item_bonus_id', 'quantity')
            ->where('item_id', $item_id)
            ->get();

        $bonus_item_ids = $bonus_items_with_warehouse->pluck('item_bonus_id')->toArray();

        // Single query for all warehouse data
        $item_warehouse_data = ItemWarehouse::select('item_id', 'stock', 'restrict_stock_quantity')
            ->whereIn('item_id', $bonus_item_ids)
            ->where('warehouse_id', $warehouse_id)
            ->get()
            ->keyBy('item_id');

        $items_with_restrictions = collect($bonus_item_ids)->map(function ($bonus_item_id) use ($item_warehouse_data) {
            $warehouse_item = $item_warehouse_data->get($bonus_item_id);
            if (!$warehouse_item) return null;

            $restrict_stock_quantity = $warehouse_item->restrict_stock_quantity;
            $stock = $warehouse_item->stock;
            $stock_net = $restrict_stock_quantity ? $stock - $restrict_stock_quantity : null;

            return [
                'item_id' => $bonus_item_id,
                'restrict_stock_quantity' => $restrict_stock_quantity,
                'item_stock' => $stock,
                'item_stock_net' => $stock_net,
                'pass' => $restrict_stock_quantity <= $stock
            ];
        })->filter()->values()->toArray();

        $bonus_items = $bonus_items_with_warehouse->map(function ($row) {
            return [
                'id' => $row->id,
                'item_id' => $row->item_bonus_id,
                'quantity' => $row->quantity,
                'internal_id' => $row->bonus_item->internal_id ?? null,
                'is_bonus' => true,
            ];
        })->toArray();

        return [
            'bonus_items' => $bonus_items,
            'is_bonus' => true,
            'success' => true,
            'items_with_restrictions' => $items_with_restrictions
        ];
    }

    private function handleSingleItem($item_id, $warehouse_id)
    {
        $item_warehouse = ItemWarehouse::select('stock', 'restrict_stock_quantity')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->first();

        $restrict_stock_quantity = null;
        $stock = 0;
        $stock_net = null;

        if ($item_warehouse) {
            $restrict_stock_quantity = $item_warehouse->restrict_stock_quantity;
            $stock = $item_warehouse->stock;
            $stock_net = $restrict_stock_quantity ? $stock - $restrict_stock_quantity : null;
        }

        return [
            'has_bonus' => false,
            'is_set' => false,
            'success' => true,
            'restrict_stock_quantity' => $restrict_stock_quantity,
            'item_stock' => $stock,
            'item_stock_net' => $stock_net,
            'pass' => $restrict_stock_quantity <= $stock
        ];
    }
    public function record($id)
    {
        $record = new ItemResource(Item::findOrFail($id));

        return $record;
    }
    public function store(ItemRequest $request)
    {

        try {
            DB::connection('tenant')->beginTransaction();
            $id = $request->input('id');


            $image  = $request->input('image');

            $item = Item::firstOrNew(['id' => $id]);
            if (!$image && $id) {
                $image = $item->image;
                $request->merge(['image' => $image]);
            }

            $warehouse_id = $request->warehouse_id;
            $item->item_type_id = '01';
            $item->amount_plastic_bag_taxes = Configuration::firstOrFail()->amount_plastic_bag_taxes;
            if ($request->has('date_of_due')) {
                $time = $request->date_of_due;
                $date = null;
                if (isset($time['date'])) {
                    $date = $time['date'];
                    if (!empty($date)) {
                        $request->merge(['date_of_due' => Carbon::createFromFormat('Y-m-d H:i:s.u', $date)]);
                    }
                }
            }

            $current_lot = null;
            if (!empty($item->id)) {
                $current_lot = ItemLotsGroup::where([
                    'code' => $item->lot_code,
                    'item_id' => $item->id
                ])->first();
            }

            $item->fill($request->all());

            $item->id_cupones = $request->id_cupones;

            $item->genero = $request->input('genero');

            $temp_path = $request->input('temp_path');
            if ($temp_path) {

                $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR;

                $slug_name = Str::slug($item->description);
                $prefix_name = Str::limit($slug_name, 20, '');
                if ($item->internal_id) {
                    $prefix_name = $item->internal_id;
                }

                $file_name_old = $request->input('image');
                $file_name_old_array = explode('.', $file_name_old);
                $file_content = file_get_contents($temp_path);
                $datenow = date('YmdHis');
                $file_name = $prefix_name . '-' . $datenow . '.' . $file_name_old_array[1];

                UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);

                Storage::put($directory . $file_name, $file_content);
                $item->image = $file_name;

                //--- IMAGE SIZE MEDIUM
                $image = Image::make($temp_path);
                $file_name = $prefix_name . '-' . $datenow . '_medium' . '.' . $file_name_old_array[1];
                $image->resize(512, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                Storage::put($directory . $file_name,  (string) $image->encode('jpg', 30));
                $item->image_medium = $file_name;

                //--- IMAGE SIZE SMALL
                $image = Image::make($temp_path);
                $file_name = $prefix_name . '-' . $datenow . '_small' . '.' . $file_name_old_array[1];
                $image->resize(256, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                Storage::put($directory . $file_name,  (string) $image->encode('jpg', 20));
                $item->image_small = $file_name;
            } else if (!$request->input('image') && !$request->input('temp_path') && !$request->input('image_url')) {
                if (!$id) {

                    $item->image = 'imagen-no-disponible.jpg';
                }
            }

            $item->save();

            // Manejar atributos de ingrediente y línea
            if ($request->has('ingredient_id') || $request->has('line_id')) {
                // Eliminar atributos existentes si estamos editando
                if ($id) {
                    \Modules\Item\Models\ItemAttribute::where('item_id', $item->id)->delete();
                }

                // Crear nuevo atributo si se especificó alguno
                if ($request->ingredient_id || $request->line_id) {
                    \Modules\Item\Models\ItemAttribute::create([
                        'item_id' => $item->id,
                        'cat_ingredient_id' => $request->ingredient_id ?: null,
                        'cat_line_id' => $request->line_id ?: null,
                    ]);
                }
            }

            // Asignar etiqueta
            $item->labelColor()->where('warehouse_id', $warehouse_id)->delete();
            if (isset($request->label_color_id)) {
                $item->labelColor()->create([
                    'item_id' => $item->id,
                    'label_color_id' => $request->label_color_id,
                    'warehouse_id' => $warehouse_id
                ]);
            }

            $item->payment_conditions()->delete();
            if (isset($request->payment_conditions)) {
                $item->payment_conditions()->createMany($request->payment_conditions);
            }
            $item_id =  $item->id;


            /* */
            $cod_digemid = $request->cod_digemid;
            if ($cod_digemid) {
                $register_digemid = Digemid::where('cod_prod', $cod_digemid)->first();
                if ($register_digemid) {
                    CatDigemid::updateOrCreate(
                        ['item_id' => $item->id],
                        [
                            'cod_digemid' => $cod_digemid,
                            'nom_prod' => $register_digemid->nom_prod,
                            'concent' => $register_digemid->concent,
                            'nom_form_farm' => $register_digemid->nom_form_farm,
                            'nom_form_farm_simplif' => $register_digemid->nom_form_farm_simplif,
                            'presentac' => $register_digemid->presentac,
                            'fracciones' => $register_digemid->fracciones,
                            'fec_vcto_reg_sanitario' => $register_digemid->fec_vcto_reg_sanitario,
                            'num_reg_san' => $register_digemid->num_regsan,
                            'nom_titular' => $register_digemid->nom_titular,
                            'active' => 1,
                            'prices' => $item->sale_unit_price,
                        ]
                    );
                }
            }
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $warehouse = Warehouse::where('establishment_id', $establishment->id)->first();
            if ($request->attribute_enabled) {
                if (isset($request->attributes_list)) {
                    foreach ($request->attributes_list as $row) {
                        $attributes = [
                            'attribute'    => $row['attribute'],
                            'attribute2'   => $row['attribute2'],
                            'attribute3'   => $row['attribute3'],
                            'attribute4'   => $row['attribute4'],
                            'attribute5'   => $row['attribute5'],
                            'sales_price'  => $row['sales_price'],
                            'chassis'      => $row['chassis'],
                            'state'        => 'Activo',
                        ];

                        // Establecer condiciones de búsqueda
                        if (!empty($row['id'])) {
                            // Actualizar si existe ID
                            $conditions = ['id' => $row['id']];
                        } else {
                            // Crear nuevo si no hay ID
                            $conditions = [
                                'item_id' => $item->id,
                                'attribute' => $row['chassis'], // opcional si es necesario evitar duplicados
                            ];
                            $attributes['item_id'] = $item->id;
                        }

                        // Agregar warehouse_id si corresponde
                        if ($request->change_warehouse == true) {
                            $attributes['warehouse_id'] = $request->warehouse_id;
                        } elseif (!isset($attributes['warehouse_id'])) {
                            $attributes['warehouse_id'] = $item->warehouse_id;
                        }

                        ItemProperty::updateOrCreate($conditions, $attributes);
                    }
                }
            }
            if (isset($request->sizes)) {
                foreach ($request->sizes as $size) {
                    $item_size_stock = ItemSizeStock::firstOrNew(['id' => $size['id']]);
                    $item_size_stock->item_id = $item->id;
                    // $item_size_stock->establishment_id = auth()->user()->establishment_id;
                    $item_size_stock->warehouse_id = $warehouse ? $warehouse->id : null;
                    $item_size_stock->stock = $size['stock'];
                    $item_size_stock->size = $size['size'];
                    $item_size_stock->save();
                }
            }
            if (isset($request->item_unit_types)) {
                foreach ($request->item_unit_types as $value) {

                    $item_unit_type = ItemUnitType::firstOrNew(['id' => $value['id']]);
                    $item_unit_type->item_id = $item->id;
                    $item_unit_type->description = $value['description'];
                    $item_unit_type->unit_type_id = $value['unit_type_id'];
                    $item_unit_type->quantity_unit = $value['quantity_unit'];
                    $item_unit_type->price1 = $value['price1'];
                    $item_unit_type->price2 = $value['price2'];
                    $item_unit_type->price3 = $value['price3'];
                    $item_unit_type->range_min = isset($value['range_min']) ? $value['range_min'] : null;
                    $item_unit_type->range_max = isset($value['range_max']) ? $value['range_max'] : null;
                    $item_unit_type->price_default = $value['price_default'];
                    $item_unit_type->default_price_store = isset($value['default_price_store']) ? $value['default_price_store'] : false;
                    $item_unit_type->factor_default = isset($value['factor_default']) ? $value['factor_default'] : false;
                    $item_unit_type->warehouse_id = isset($value['warehouse_id']) ? $value['warehouse_id'] : null;
                    $item_unit_type->save();

                    // migracion desarrollo sin terminar #1401
                    if (!isset($value['barcode'])) {
                        $item_unit_type->barcode = $item_unit_type->id . $item_unit_type->unit_type_id . $item_unit_type->quantity_unit;
                        $item_unit_type->save();
                    } else {
                        $item_unit_type->barcode = $value['barcode'];
                        $item_unit_type->save();
                    }
                }
            }

            if (isset($request->supplies)) {
                foreach ($request->supplies as $value) {

                    if (!isset($value['item_id'])) $value['item_id'] = $item->id;
                    $itemSupply = ItemSupply::firstOrCreate(['item_id' => $value['id']], $value);
                    $itemSupply->fill($value);
                    $itemSupply->save();
                }
            }

            ItemBonus::where('item_id', $item->id)->delete();
            if (isset($request->bonus_items)) {
                foreach ($request->bonus_items as $value) {

                    $value['item_id'] = $item->id;
                    $value['item_bonus_id'] = $value['item_bonus_id'];
                    $itemBonus = new ItemBonus;
                    $itemBonus->fill($value);
                    $itemBonus->save();
                }
            }
            ItemFoodDealer::where('item_id', $item->id)->delete();
            if (isset($request->start) && isset($request->end)) {
                ItemFoodDealer::create([
                    'item_id' => $item->id,
                    'start_time' => Carbon::parse($request->start)
                        ->timezone('America/Lima')->format('H:i:s'),
                    'end_time' => Carbon::parse($request->end)
                        ->timezone('America/Lima')->format('H:i:s'),
                ]);
            }

            $configuration = Configuration::first();
            if ($configuration->isShowExtraInfoToItem()) {
                // Extra data
                if ($request->has('colors')) {
                    $item->setItemColor($request->colors);
                }
                if ($request->has('CatItemUnitsPerPackage')) {
                    $item->setItemUnitsPerPackage($request->CatItemUnitsPerPackage);
                }
                if ($request->has('CatItemMoldCavity')) {
                    $item->setItemMoldCavity($request->CatItemMoldCavity);
                }
                if ($request->has('CatItemMoldProperty')) {
                    $item->setItemMoldProperty($request->CatItemMoldProperty);
                }
                if ($request->has('CatItemUnitBusiness')) {
                    $item->setItemUnitBusiness($request->CatItemUnitBusiness);
                }
                if ($request->has('CatItemStatus')) {
                    $item->setItemStatus($request->CatItemStatus);
                }
                if ($request->has('CatItemPackageMeasurement')) {
                    $item->setItemPackageMeasurement($request->CatItemPackageMeasurement);
                }
                if ($request->has('CatItemProductFamily')) {
                    $item->setItemProductFamily($request->CatItemProductFamily);
                }
                if ($request->has('CatItemSize')) {
                    $item->setItemSize($request->CatItemSize);
                }
                // Extra data
            }



            if ($request->tags_id) {
                ItemTag::destroy(ItemTag::where('item_id', $item->id)->pluck('id'));
                foreach ($request->tags_id as $value) {
                    ItemTag::create(['item_id' => $item->id,  'tag_id' => $value]);
                    //$tag = ItemTag::where('item_id', $item->id)->where('tag_id', $value)->first();
                }
            }

            if (!$id) {

                // $item->lots()->delete();
                if ($warehouse_id) {
                    $warehouse = Warehouse::where('id', $warehouse_id)->first();
                } else {
                    $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
                    $warehouse = Warehouse::where('establishment_id', $establishment->id)->first();
                }

                //$warehouse = WarehouseModule::find(auth()->user()->establishment_id);

                $v_lots = isset($request->lots) ? $request->lots : [];
                foreach ($v_lots as $lot) {
                    $item->lots()->create([
                        'date' => $lot['date'],
                        'date_incoming' => isset($lot['date_incoming']) ? $lot['date_incoming'] : null,
                        'series' => $lot['series'],
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouse ? $warehouse->id : null,
                        'has_sale' => false,
                        'state' => $lot['state'],
                    ]);
                }
                $lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled : false;
                $stock = (int)$request->stock;

                if ($lots_enabled && $stock > 0) {
                    $state = null;
                    $apr_state = ItemLotsGroupState::where('description', 'like', 'aprobado')->first();
                    if ($apr_state) {
                        $state = $apr_state->id;
                    }
                    ItemLotsGroup::create([
                        'code'  => $request->lot_code,
                        'quantity'  => $request->stock,
                        'date_of_due'  => $request->date_of_due,
                        'item_id' => $item->id,
                        'state_id' => $state,
                        'warehouse_id' => $warehouse ? $warehouse->id : null,
                    ]);
                }
            } else {

                $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
                $warehouse = Warehouse::where('establishment_id', $establishment->id)->first();
                $v_lots = isset($request->lots) ? $request->lots : [];
                foreach ($v_lots as $lot) {
                    /**
                     * @var  ItemLot $temp_serie
                     * @var Int $lot_id
                     * @var Bool $delete
                     */
                    $lot_id = isset($lot['id']) ? (int) $lot['id'] : 0;
                    $delete = isset($lot['deleted']) ? (bool)$lot['deleted'] : false;
                    if ($lot_id != 0) {
                        $temp_serie = ItemLot::find($lot_id);
                        if (!empty($temp_serie)) {
                            if ($delete == true) {
                                $temp_serie->delete();
                            } else {
                                $temp_serie
                                    ->setDate($lot['date'])
                                    ->setSeries($lot['series'])
                                    ->setState($lot['state'])
                                    ->push();
                            }
                        }
                    } else {
                        $temp_serie = new ItemLot([
                            'date' => $lot['date'],
                            'series' => $lot['series'],
                            'item_id' => $item->id,
                            'warehouse_id' => $warehouse ? $warehouse->id : null,
                            'has_sale' => false,
                            'state' => $lot['state'],
                        ]);
                        $temp_serie->push();
                    }
                }

                $lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled : false;
                if ($lots_enabled and !empty($request->lot_code)) {
                    if (empty($current_lot)) {
                        $current_lot = new ItemLotsGroup([
                            'code' => $item->lot_code,
                            'item_id' => $item->id,
                            'quantity' => $request->stock,
                            'date_of_due' => $request->date_of_due,
                        ]);
                        $current_lot->push();
                    } else {
                        $lotes = ItemLotsGroup::where([
                            'code' => $current_lot->code,
                            // 'quantity',
                            // 'date_of_due',
                            'item_id' => $item->id
                        ])->get();
                        /** @var ItemLotsGroup $lot */
                        foreach ($lotes as $lot) {
                            $lot
                                ->setCode($request->lot_code)
                                ->setDateOfDue($request->date_of_due)
                                ->push();
                        }
                    }
                }
            }

            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR;

            $multi_images = isset($request->multi_images) ? $request->multi_images : [];

            foreach ($multi_images as $im) {

                $file_name = $im['filename'];
                UploadFileHelper::checkIfValidFile($file_name, $im['temp_path'], true);

                $file_content = file_get_contents($im['temp_path']);
                Storage::put($directory . $file_name, $file_content);

                ItemImage::create(['item_id' => $item->id, 'image' => $file_name]);
            }

            if (!$item->barcode) {
                $item->barcode = str_pad($item->id, 12, '0', STR_PAD_LEFT);
            }

            $item->update();

            // migracion desarrollo sin terminar #1401
            // $inventory_configuration = InventoryConfiguration::firstOrFail();

            // if($inventory_configuration->generate_internal_id == 1) {
            //     if(!$item->internal_id) {
            //         $items = Item::count();
            //         $item->internal_id = (string)($items + 1);
            //         $item->save();
            //     }
            // }

            $this->generateInternalId($item);

            /********************************* SECCION PARA PRECIO POR ALMACENES ******************************************/

            // Precios por almacenes
            // $warehouses = $request->warehouses;

            $this->createItemWarehousePrices($request, $item);
            $this->createItemPersonTypePrices($request, $item);

            // if($request->warehouse_id!=null){
            //     $itemwarehouse = ItemWarehouse::where('item_id', $item->id)->where('warehouse_id', $item->warehouse_id)->first();
            //     if($itemwarehouse!=null){
            //         $itemwarehouse->shared = $request->shared;
            //         $itemwarehouse->save();
            //     }

            // }
            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => ($id) ? 'Producto editado con éxito' : 'Producto registrado con éxito',
                'id' => $item->id,
                'internal_id' => $item->internal_id
            ];
        } catch (Exception $e) {
            //imprime el archivo y la linea del error
            Log::error($e->getMessage() . ' ' . $e->getLine());
            //el traceback del error
            Log::error($e->getTraceAsString());

            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function storeInput(Request $request)
    {

        try {
            DB::connection('tenant')->beginTransaction();
            $id = $request->input('id');


            $image  = $request->input('image');

            $item = Item::firstOrNew(['id' => $id]);
            if (!$image && $id) {
                $image = $item->image;
                $request->merge(['image' => $image]);
            }

            $warehouse_id = $request->warehouse_id;
            $supplier_id = $request->supplier_id;
            $item->item_type_id = '01';
            $item->amount_plastic_bag_taxes = Configuration::firstOrFail()->amount_plastic_bag_taxes;
            if ($request->has('date_of_due')) {
                $time = $request->date_of_due;
                $date = null;
                if (isset($time['date'])) {
                    $date = $time['date'];
                    if (!empty($date)) {
                        $request->merge(['date_of_due' => Carbon::createFromFormat('Y-m-d H:i:s.u', $date)]);
                    }
                }
            }

            $current_lot = null;
            if (!empty($item->id)) {
                $current_lot = ItemLotsGroup::where([
                    'code' => $item->lot_code,
                    'item_id' => $item->id
                ])->first();
            }

            $item->fill($request->all());

            $item->id_cupones = $request->id_cupones;

            $item->genero = $request->input('genero');

            $temp_path = $request->input('temp_path');
            if ($temp_path) {

                $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR;

                $slug_name = Str::slug($item->description);
                $prefix_name = Str::limit($slug_name, 20, '');
                if ($item->internal_id) {
                    $prefix_name = $item->internal_id;
                }

                $file_name_old = $request->input('image');
                $file_name_old_array = explode('.', $file_name_old);
                $file_content = file_get_contents($temp_path);
                $datenow = date('YmdHis');
                $file_name = $prefix_name . '-' . $datenow . '.' . $file_name_old_array[1];

                UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);

                Storage::put($directory . $file_name, $file_content);
                $item->image = $file_name;

                //--- IMAGE SIZE MEDIUM
                $image = Image::make($temp_path);
                $file_name = $prefix_name . '-' . $datenow . '_medium' . '.' . $file_name_old_array[1];
                $image->resize(512, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                Storage::put($directory . $file_name,  (string) $image->encode('jpg', 30));
                $item->image_medium = $file_name;

                //--- IMAGE SIZE SMALL
                $image = Image::make($temp_path);
                $file_name = $prefix_name . '-' . $datenow . '_small' . '.' . $file_name_old_array[1];
                $image->resize(256, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                Storage::put($directory . $file_name,  (string) $image->encode('jpg', 20));
                $item->image_small = $file_name;
            } else if (!$request->input('image') && !$request->input('temp_path') && !$request->input('image_url')) {
                if (!$id) {

                    $item->image = 'imagen-no-disponible.jpg';
                }
            }

            $item->save();

            // Manejar atributos de ingrediente y línea
            if ($request->has('ingredient_id') || $request->has('line_id')) {
                // Eliminar atributos existentes si estamos editando
                if ($id) {
                    \Modules\Item\Models\ItemAttribute::where('item_id', $item->id)->delete();
                }

                // Crear nuevo atributo si se especificó alguno
                if ($request->ingredient_id || $request->line_id) {
                    \Modules\Item\Models\ItemAttribute::create([
                        'item_id' => $item->id,
                        'cat_ingredient_id' => $request->ingredient_id ?: null,
                        'cat_line_id' => $request->line_id ?: null,
                    ]);
                }
            }

            if (!$id && $item->stock > 0) {
                RegisterInputsMovement::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse_id,
                    'quantity' => $item->stock,
                    'date_of_issue' => Carbon::now(),
                    'person_id' => $supplier_id,
                    'lot_code' => $item->lot_code,
                    'observation' => 'Stock inicial',
                ]);
            }

            // Asignar etiqueta
            $item->labelColor()->where('warehouse_id', $warehouse_id)->delete();
            if (isset($request->label_color_id)) {
                $item->labelColor()->create([
                    'item_id' => $item->id,
                    'label_color_id' => $request->label_color_id,
                    'warehouse_id' => $warehouse_id
                ]);
            }

            $item->payment_conditions()->delete();
            if (isset($request->payment_conditions)) {
                $item->payment_conditions()->createMany($request->payment_conditions);
            }
            $item_id =  $item->id;


            /* */
            $cod_digemid = $request->cod_digemid;
            if ($cod_digemid) {
                $register_digemid = Digemid::where('cod_prod', $cod_digemid)->first();
                if ($register_digemid) {
                    CatDigemid::updateOrCreate(
                        ['item_id' => $item->id],
                        [
                            'cod_digemid' => $cod_digemid,
                            'nom_prod' => $register_digemid->nom_prod,
                            'concent' => $register_digemid->concent,
                            'nom_form_farm' => $register_digemid->nom_form_farm,
                            'nom_form_farm_simplif' => $register_digemid->nom_form_farm_simplif,
                            'presentac' => $register_digemid->presentac,
                            'fracciones' => $register_digemid->fracciones,
                            'fec_vcto_reg_sanitario' => $register_digemid->fec_vcto_reg_sanitario,
                            'num_reg_san' => $register_digemid->num_regsan,
                            'nom_titular' => $register_digemid->nom_titular,
                            'active' => 1,
                            'prices' => $item->sale_unit_price,
                        ]
                    );
                }
            }
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $warehouse = Warehouse::where('establishment_id', $establishment->id)->first();
            if (isset($request->sizes)) {
                foreach ($request->sizes as $size) {
                    $item_size_stock = ItemSizeStock::firstOrNew(['id' => $size['id']]);
                    $item_size_stock->item_id = $item->id;
                    // $item_size_stock->establishment_id = auth()->user()->establishment_id;
                    $item_size_stock->warehouse_id = $warehouse ? $warehouse->id : null;
                    $item_size_stock->stock = $size['stock'];
                    $item_size_stock->size = $size['size'];
                    $item_size_stock->save();
                }
            }
            if (isset($request->item_unit_types)) {
                foreach ($request->item_unit_types as $value) {

                    $item_unit_type = ItemUnitType::firstOrNew(['id' => $value['id']]);
                    $item_unit_type->item_id = $item->id;
                    $item_unit_type->description = $value['description'];
                    $item_unit_type->unit_type_id = $value['unit_type_id'];
                    $item_unit_type->quantity_unit = $value['quantity_unit'];
                    $item_unit_type->price1 = $value['price1'];
                    $item_unit_type->price2 = $value['price2'];
                    $item_unit_type->price3 = $value['price3'];
                    $item_unit_type->range_min = isset($value['range_min']) ? $value['range_min'] : null;
                    $item_unit_type->range_max = isset($value['range_max']) ? $value['range_max'] : null;
                    $item_unit_type->price_default = $value['price_default'];
                    $item_unit_type->default_price_store = isset($value['default_price_store']) ? $value['default_price_store'] : false;
                    $item_unit_type->factor_default = isset($value['factor_default']) ? $value['factor_default'] : false;
                    $item_unit_type->warehouse_id = isset($value['warehouse_id']) ? $value['warehouse_id'] : null;
                    $item_unit_type->save();

                    // migracion desarrollo sin terminar #1401
                    if (!isset($value['barcode'])) {
                        $item_unit_type->barcode = $item_unit_type->id . $item_unit_type->unit_type_id . $item_unit_type->quantity_unit;
                        $item_unit_type->save();
                    } else {
                        $item_unit_type->barcode = $value['barcode'];
                        $item_unit_type->save();
                    }
                }
            }

            if (isset($request->supplies)) {
                foreach ($request->supplies as $value) {

                    if (!isset($value['item_id'])) $value['item_id'] = $item->id;
                    $itemSupply = ItemSupply::firstOrCreate(['item_id' => $value['id']], $value);
                    $itemSupply->fill($value);
                    $itemSupply->save();
                }
            }

            ItemBonus::where('item_id', $item->id)->delete();
            if (isset($request->bonus_items)) {
                foreach ($request->bonus_items as $value) {

                    $value['item_id'] = $item->id;
                    $value['item_bonus_id'] = $value['item_bonus_id'];
                    $itemBonus = new ItemBonus;
                    $itemBonus->fill($value);
                    $itemBonus->save();
                }
            }
            ItemFoodDealer::where('item_id', $item->id)->delete();
            if (isset($request->start) && isset($request->end)) {
                ItemFoodDealer::create([
                    'item_id' => $item->id,
                    'start_time' => Carbon::parse($request->start)
                        ->timezone('America/Lima')->format('H:i:s'),
                    'end_time' => Carbon::parse($request->end)
                        ->timezone('America/Lima')->format('H:i:s'),
                ]);
            }

            $configuration = Configuration::first();
            if ($configuration->isShowExtraInfoToItem()) {
                // Extra data
                if ($request->has('colors')) {
                    $item->setItemColor($request->colors);
                }
                if ($request->has('CatItemUnitsPerPackage')) {
                    $item->setItemUnitsPerPackage($request->CatItemUnitsPerPackage);
                }
                if ($request->has('CatItemMoldCavity')) {
                    $item->setItemMoldCavity($request->CatItemMoldCavity);
                }
                if ($request->has('CatItemMoldProperty')) {
                    $item->setItemMoldProperty($request->CatItemMoldProperty);
                }
                if ($request->has('CatItemUnitBusiness')) {
                    $item->setItemUnitBusiness($request->CatItemUnitBusiness);
                }
                if ($request->has('CatItemStatus')) {
                    $item->setItemStatus($request->CatItemStatus);
                }
                if ($request->has('CatItemPackageMeasurement')) {
                    $item->setItemPackageMeasurement($request->CatItemPackageMeasurement);
                }
                if ($request->has('CatItemProductFamily')) {
                    $item->setItemProductFamily($request->CatItemProductFamily);
                }
                if ($request->has('CatItemSize')) {
                    $item->setItemSize($request->CatItemSize);
                }
                // Extra data
            }



            if ($request->tags_id) {
                ItemTag::destroy(ItemTag::where('item_id', $item->id)->pluck('id'));
                foreach ($request->tags_id as $value) {
                    ItemTag::create(['item_id' => $item->id,  'tag_id' => $value]);
                    //$tag = ItemTag::where('item_id', $item->id)->where('tag_id', $value)->first();
                }
            }

            if (!$id) {

                // $item->lots()->delete();
                if ($warehouse_id) {
                    $warehouse = Warehouse::where('id', $warehouse_id)->first();
                } else {
                    $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
                    $warehouse = Warehouse::where('establishment_id', $establishment->id)->first();
                }

                //$warehouse = WarehouseModule::find(auth()->user()->establishment_id);

                $v_lots = isset($request->lots) ? $request->lots : [];

                foreach ($v_lots as $lot) {
                    $item->lots()->create([
                        'date' => $lot['date'],
                        'series' => $lot['series'],
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouse ? $warehouse->id : null,
                        'has_sale' => false,
                        'state' => $lot['state'],
                    ]);
                }
                $lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled : false;
                $stock = (int)$request->stock;

                if ($lots_enabled && $stock > 0) {
                    $state = null;
                    $apr_state = ItemLotsGroupState::where('description', 'like', 'aprobado')->first();
                    if ($apr_state) {
                        $state = $apr_state->id;
                    }
                    ItemLotsGroup::create([
                        'code'  => $request->lot_code,
                        'quantity'  => $request->stock,
                        'date_of_due'  => $request->date_of_due,
                        'item_id' => $item->id,
                        'state_id' => $state,
                        'warehouse_id' => $warehouse ? $warehouse->id : null,
                    ]);
                }
            } else {

                $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
                $warehouse = Warehouse::where('establishment_id', $establishment->id)->first();
                $v_lots = isset($request->lots) ? $request->lots : [];
                foreach ($v_lots as $lot) {
                    /**
                     * @var  ItemLot $temp_serie
                     * @var Int $lot_id
                     * @var Bool $delete
                     */
                    $lot_id = isset($lot['id']) ? (int) $lot['id'] : 0;
                    $delete = isset($lot['deleted']) ? (bool)$lot['deleted'] : false;
                    if ($lot_id != 0) {
                        $temp_serie = ItemLot::find($lot_id);
                        if (!empty($temp_serie)) {
                            if ($delete == true) {
                                $temp_serie->delete();
                            } else {
                                $temp_serie
                                    ->setDate($lot['date'])
                                    ->setSeries($lot['series'])
                                    ->setState($lot['state'])
                                    ->push();
                            }
                        }
                    } else {
                        $temp_serie = new ItemLot([
                            'date' => $lot['date'],
                            'series' => $lot['series'],
                            'item_id' => $item->id,
                            'warehouse_id' => $warehouse ? $warehouse->id : null,
                            'has_sale' => false,
                            'state' => $lot['state'],
                        ]);
                        $temp_serie->push();
                    }
                }

                $lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled : false;
                if ($lots_enabled and !empty($request->lot_code)) {
                    if (empty($current_lot)) {
                        $current_lot = new ItemLotsGroup([
                            'code' => $item->lot_code,
                            'item_id' => $item->id,
                            'quantity' => $request->stock,
                            'date_of_due' => $request->date_of_due,
                        ]);
                        $current_lot->push();
                    } else {
                        $lotes = ItemLotsGroup::where([
                            'code' => $current_lot->code,
                            // 'quantity',
                            // 'date_of_due',
                            'item_id' => $item->id
                        ])->get();
                        /** @var ItemLotsGroup $lot */
                        foreach ($lotes as $lot) {
                            $lot
                                ->setCode($request->lot_code)
                                ->setDateOfDue($request->date_of_due)
                                ->push();
                        }
                    }
                }
            }

            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR;

            $multi_images = isset($request->multi_images) ? $request->multi_images : [];

            foreach ($multi_images as $im) {

                $file_name = $im['filename'];
                UploadFileHelper::checkIfValidFile($file_name, $im['temp_path'], true);

                $file_content = file_get_contents($im['temp_path']);
                Storage::put($directory . $file_name, $file_content);

                ItemImage::create(['item_id' => $item->id, 'image' => $file_name]);
            }

            if (!$item->barcode) {
                $item->barcode = str_pad($item->id, 12, '0', STR_PAD_LEFT);
            }

            $item->update();

            // migracion desarrollo sin terminar #1401
            // $inventory_configuration = InventoryConfiguration::firstOrFail();

            // if($inventory_configuration->generate_internal_id == 1) {
            //     if(!$item->internal_id) {
            //         $items = Item::count();
            //         $item->internal_id = (string)($items + 1);
            //         $item->save();
            //     }
            // }

            $this->generateInternalId($item);

            /********************************* SECCION PARA PRECIO POR ALMACENES ******************************************/

            // Precios por almacenes
            // $warehouses = $request->warehouses;

            $this->createItemWarehousePrices($request, $item);
            $this->createItemPersonTypePrices($request, $item);

            // if($request->warehouse_id!=null){
            //     $itemwarehouse = ItemWarehouse::where('item_id', $item->id)->where('warehouse_id', $item->warehouse_id)->first();
            //     if($itemwarehouse!=null){
            //         $itemwarehouse->shared = $request->shared;
            //         $itemwarehouse->save();
            //     }

            // }
            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => ($id) ? 'Producto editado con éxito' : 'Producto registrado con éxito',
                'id' => $item->id,
                'internal_id' => $item->internal_id
            ];
        } catch (Exception $e) {
            //imprime el archivo y la linea del error
            Log::error($e->getMessage() . ' ' . $e->getLine());
            //el traceback del error
            Log::error($e->getTraceAsString());

            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    public function deleteSupply($id)
    {

        ItemSupply::where('id', $id)->delete();

        return [
            'success' => true,
            'message' => 'Registro eliminado con éxito'
        ];
    }
    /**
     *
     * Generar codigo interno de forma automatica
     *
     * @param  Item $item
     * @return void
     */
    public function generateInternalId(Item &$item)
    {
        $inventory_configuration = InventoryConfiguration::select('generate_internal_id')->firstOrFail();

        if ($inventory_configuration->generate_internal_id && !$item->internal_id) {
            $item->internal_id = str_pad($item->id, 5, '0', STR_PAD_LEFT);
            $item->save();
        }
    }



    /**
     * @param ItemRequest|null $request
     * @param null $item
     * @throws Exception
     */
    private function createItemWarehousePrices(Request $request = null, Item $item = null)
    {
        if ($request !== null && $request->has('item_warehouse_prices') && $item !== null) {
            foreach ($request->item_warehouse_prices as $item_warehouse_price) {
                if ($item_warehouse_price['price'] && $item_warehouse_price['price'] != '') {
                    ItemWarehousePrice::updateOrCreate([
                        'item_id' => $item->id,
                        'warehouse_id' => $item_warehouse_price['warehouse_id'],
                    ], [
                        'price' => $item_warehouse_price['price'],
                    ]);
                } else {
                    if ($item_warehouse_price['id']) {
                        ItemWarehousePrice::findOrFail($item_warehouse_price['id'])->delete();
                    }
                }
                if (isset($item_warehouse_price['restrict_stock_quantity']) && $item_warehouse_price['restrict_stock_quantity'] != null) {
                    ItemWarehouse::where('item_id', $item->id)->where('warehouse_id', $item_warehouse_price['warehouse_id'])->update(['restrict_stock_quantity' => $item_warehouse_price['restrict_stock_quantity']]);
                } else {
                    $item_warehouse = ItemWarehouse::where('item_id', $item->id)->where('warehouse_id', $item_warehouse_price['warehouse_id'])->first();
                    if ($item_warehouse) {
                        $item_warehouse->restrict_stock_quantity = null;
                        $item_warehouse->save();
                    }
                }
            }
        }
    }
    /**
     * @param ItemRequest|null $request
     * @param null $item
     * @throws Exception
     */
    private function createItemPersonTypePrices(Request $request = null, Item $item = null)
    {
        if ($request !== null && $request->has('item_customer_prices') && $item !== null) {
            foreach ($request->item_customer_prices as $item_customer_price) {
                if ($item_customer_price['price'] && $item_customer_price['price'] != '') {
                    PersonTypePrice::updateOrCreate([
                        'item_id' => $item->id,
                        'person_type_id' => $item_customer_price['person_type_id'],
                    ], [
                        'price' => $item_customer_price['price'],
                    ]);
                } else {
                    if ($item_customer_price['id']) {
                        PersonTypePrice::findOrFail($item_customer_price['id'])->delete();
                    }
                }
            }
        }
    }

    /**
     * Eliminar item
     *
     * Usado en:
     * Modules\MobileApp\Http\Controllers\Api\ItemController
     *
     * @param  int $id
     * @return array
     *
     */
    public function destroy($id)
    {
        try {

            $item = Item::findOrFail($id);
            ItemProperty::where('item_id', $id)->delete();
            $this->deleteRecordInitialKardex($item);
            $item->delete();

            return [
                'success' => true,
                'message' => 'Producto eliminado con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => 'El producto esta siendo usado por otros registros, no puede eliminar'] : ['success' => false, 'message' => 'Error inesperado, no se pudo eliminar el producto'];
        }
    }

    public function destroyItemUnitType($id)
    {
        $item_unit_type = ItemUnitType::findOrFail($id);
        $item_unit_type->delete();

        return [
            'success' => true,
            'message' => 'Registro eliminado con éxito'
        ];
    }

    public function import(Request $request)
    {
        // $request->validate([
        //     'warehouse_id' => 'required|numeric|min:1'
        // ]);
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImport();
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
                    'stack' => $e->getTrace(),
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function catalog(Request $request)
    {
        $request->validate([
            'catalog_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $old_digemid = CatDigemid::setInactiveMassive();
                $import = new CatalogImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $updated  = $import->getUpdated();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => count($updated),
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

    public function upload(Request $request)
    {

        $type = $request->input('type');
        if ($type == 'item_lot_group') {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg']);
            $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,pdf', $isImage);
        } else {
            $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg');
        }
        if (!$validate_upload['success']) {
            return $validate_upload;
        }
        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];
            return $this->upload_image($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    function upload_image($request)
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

    private function deleteRecordInitialKardex($item)
    {

        if ($item->kardex->count() == 1) {
            ($item->kardex[0]->type == null) ? $item->kardex[0]->delete() : false;
        }
    }


    public function visibleStore(Request $request)
    {
        $item = Item::find($request->id);

        if (!$item->internal_id && $request->apply_store) {
            return [
                'success' => false,
                'message' => 'Para habilitar la visibilidad, debe asignar un codigo interno al producto',
            ];
        }

        $visible = $request->apply_store == true ? 1 : 0;
        $item->apply_store = $visible;
        $item->save();

        return [
            'success' => true,
            'message' => ($visible > 0) ? 'El Producto ya es visible en tienda virtual' : 'El Producto ya no es visible en tienda virtual',
            'id' => $request->id
        ];
    }

    public function duplicate(Request $request)
    {
        // return $request->id;
        $obj = Item::find($request->id);

        if ($obj->lots_enabled) {
            $obj->date_of_due = null;
            $obj->lot_code = null;
            $obj->stock = 0;
        }

        $new = $obj->setDescription($obj->getDescription() . ' (Duplicado)')->replicate();
        $new->save();

        return [
            'success' => true,
            'data' => [
                'id' => $new->id,
            ],
        ];
    }

    public function disable($id)
    {
        try {

            $item = Item::findOrFail($id);
            $item->active = 0;
            $item->save();

            return [
                'success' => true,
                'message' => 'Producto inhabilitado con éxito'
            ];
        } catch (Exception $e) {

            return  ['success' => false, 'message' => 'Error inesperado, no se pudo inhabilitar el producto'];
        }
    }

    public function images($item)
    {
        $records = ItemImage::where('item_id', $item)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'image' => $row->image,
                'name' => $row->image,
                'url' => asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image)
            ];
        });
        return [
            'success' => true,
            'data' => $records
        ];
    }

    public function delete_images($id)
    {
        $record = ItemImage::findOrFail($id);
        $record->delete();

        return [
            'success' => true,
            'message' => 'Imagen eliminada con éxito'
        ];
    }


    public function enable($id)
    {
        try {

            $item = Item::findOrFail($id);
            $item->active = 1;
            $item->save();

            return [
                'success' => true,
                'message' => 'Producto habilitado con éxito'
            ];
        } catch (Exception $e) {

            return  ['success' => false, 'message' => 'Error inesperado, no se pudo habilitar el producto'];
        }
    }
    public function export_migration_v3(Request $request)
    {
        $d_start = null;
        $d_end = null;
        $period = $request->period;
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start . '-01')->startOfDay()->format('Y-m-d H:i:s');
                $d_end = Carbon::parse($request->month_start . '-01')->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start . '-01')->startOfDay()->format('Y-m-d H:i:s');
                $d_end = Carbon::parse($request->month_end . '-01')->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
        }
        $items = Item::query();


        if ($period !== 'all') {
            $items->whereBetween('items.created_at', [$d_start, $d_end]);
        }

        $records =  $items->get();

        return (new ItemMigrationExportV3())
            ->records($records)
            ->download('Item_Migracion_V3_' . Carbon::now() . '.xlsx');
    }

    public function export_migration(Request $request)
    {
        $d_start = null;
        $d_end = null;
        $period = $request->period;
        $warehouse_id = $request->warehouse_id;
        $v2 = $request->v2 ?? false;
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start . '-01')->startOfDay()->format('Y-m-d H:i:s');
                $d_end = Carbon::parse($request->month_start . '-01')->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start . '-01')->startOfDay()->format('Y-m-d H:i:s');
                $d_end = Carbon::parse($request->month_end . '-01')->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
        }
        $items = Item::whereHas('item_warehouse', function ($query) use ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        });


        if ($period !== 'all') {
            $items->whereBetween('items.created_at', [$d_start, $d_end]);
        }

        $records =  $items->get();
        if ($v2) {
            return (new ItemMigrationExportV2())
                ->records($records)
                ->warehouse_id($warehouse_id)
                ->download('Item_Migracion_' . Carbon::now() . '.xlsx');
        }
        return (new ItemMigrationExport())
            ->v2($v2)
            ->records($records)
            ->warehouse_id($warehouse_id)
            ->download('Item_Migracion_' . Carbon::now() . '.xlsx');
    }
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $d_start = null;
        $d_end = null;
        $period = $request->period;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start . '-01')->startOfDay()->format('Y-m-d H:i:s');
                $d_end = Carbon::parse($request->month_start . '-01')->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start . '-01')->startOfDay()->format('Y-m-d H:i:s');
                $d_end = Carbon::parse($request->month_end . '-01')->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
        }

        // $date = $request->month_start.'-01';
        // $start_date = Carbon::parse($date);
        // $end_date = Carbon::parse($date)->addMonth()->subDay();

        $items = Item::whereTypeUser()->whereNotIsSet();
        $extradata = [];
        $isPharmacy = false;
        if ($request->has('isPharmacy')) {
            $isPharmacy = ($request->isPharmacy === 'true') ? true : false;
        }
        if ($isPharmacy == true) {
            $extradata[] = 'sanitary';
            $extradata[] = 'cod_digemid';
            $items->Pharmacy();
        }

        if ($period !== 'all') {
            $items->whereBetween('items.created_at', [$d_start, $d_end]);
        }
        $warehouses = Warehouse::where('active', true)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description
            ];
        });

        $records =  $items->get();
        return (new ItemExport())
            ->setExtraData($extradata)
            ->records($records)
            ->warehouses($warehouses)
            ->download('Reporte_Items_' . Carbon::now() . '.xlsx');
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportWp(Request $request)
    {
        $date = $request->month_start . '-01';
        $start_date = Carbon::parse($date);
        $end_date = Carbon::parse($date)->addMonth()->subDay();

        $records = Item::whereBetween('created_at', [$start_date, $end_date]);
        $extradata = [];
        $isPharmacy = $request->isPharmacy == 'true' ? true : false;
        if ($request->has('isPharmacy') && $isPharmacy == true) {
            $extradata[] = 'sanitary';
            $extradata[] = 'cod_digemid';
            $records->Pharmacy();
        }
        $records = $records->get();
        return (new ItemExportWp())
            ->setExtraData($extradata)
            ->records($records)
            ->download('Reporte_Items_' . Carbon::now() . '.csv', Excel::CSV);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadExtraDataPdf(Request $request)
    {
        $field = '';
        $records = $this->exportExtraItem($request, $field);


        $pdf = PDF::loadView(
            'tenant.items.exports.items_extra_data',
            compact("records", "field")
        )
            ->setPaper('a4', 'landscape');

        $filename = 'Reporte_Items_Extra_Data_' . Carbon::now() . '.xlsx';

        return $pdf->download($filename . '.pdf');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|mixed|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExtraDataItemsExcel(Request $request)
    {
        $field = '';
        $items = $this->exportExtraItem($request, $field);
        $excel = new ItemExtraDataExport();
        $excel->setRecords($items)->setField($field);
        $filename = 'Reporte_Items_Extra_Data_' . Carbon::now() . '.xlsx';

        return $excel->download($filename);
        return $excel->view();
    }

    /**
     * Obtiene lo smovimientos de inventario para la categoria correspondiente,
     * se implementa en pdf y excel por igual
     *
     * @param Request $request
     * @param         $field
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function exportExtraItem(Request $request, &$field)
    {

        $stockByAttribute = ItemMovement::getQueryToStockWithOutItemId(auth()->user()->establishment_id)->distinct();
        $field = $request->fields ?? '';
        if ($field == 'colors') {
            $stockByAttribute->where('item_movement_rel_extra.item_color_id', '!=', 0);
        } elseif ($field == 'CatItemMoldProperty') {
            $stockByAttribute->where('item_movement_rel_extra.item_mold_properties_id', '!=', 0);
        } elseif ($field == 'CatItemUnitBusiness') {
            $stockByAttribute->where('item_movement_rel_extra.item_unit_business_id', '!=', 0);
        } elseif ($field == 'CatItemStatus') {
            $stockByAttribute->where('item_movement_rel_extra.item_status_id', '!=', 0);
        } elseif ($field == 'CatItemPackageMeasurement') {
            $stockByAttribute->where('item_movement_rel_extra.item_package_measurements_id', '!=', 0);
        } elseif ($field == 'CatItemProductFamily') {
            $stockByAttribute->where('item_movement_rel_extra.item_product_family_id', '!=', 0);
        } elseif ($field == 'CatItemSize') {
            $stockByAttribute->where('item_movement_rel_extra.item_size_id', '!=', 0);
        } elseif ($field == 'CatItemUnitsPerPackage') {
            $stockByAttribute->where('item_movement_rel_extra.item_units_per_package_id', '!=', 0);
        } elseif ($field == 'CatItemMoldCavity') {
            $stockByAttribute->where('item_movement_rel_extra.item_mold_cavities_id', '!=', 0);
        }
        $itemsIds = $stockByAttribute->get()->pluck('item_id')->unique();
        $items = Item::wherein('id', $itemsIds)->get()->transform(function (Item $row) {
            return $row->getCollectionData();
        });
        return $items;
    }
    public function exportBarCode(Request $request)
    {

        ini_set("pcre.backtrack_limit", "50000000");

        $start = $request[0];
        $end = $request[1];

        $records = Item::whereBetween('id', [$start, $end]);
        $extradata = [];
        $isPharmacy = false;
        if ($request->has('isPharmacy')) {
            $isPharmacy = ($request->isPharmacy === 'true') ? true : false;
        }
        if ($isPharmacy == true) {
            $extradata[] = 'sanitary';
            $extradata[] = 'cod_digemid';
            $records->Pharmacy();
        }
        $extra_data = $extradata;
        $records = $records->get();
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                104.1,
                101.6
            ],
            'margin_top' => 2,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);
        $html = view('tenant.items.exports.items-barcode', compact('records', 'extra_data'))->render();

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_' . now()->format('Y_m_d') . '.pdf', 'I');
    }

    /**
     * Genera los codigos de barra por archivo para los items que tengan internal_id o barcode
     * Se prioriza barcode, sino se genera internal_id
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function exportBarCodeFull(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");

        $start = $request[0];
        $end = $request[1];

        $records = Item::whereBetween('id', [$start, $end])
            ->where(function ($q) {
                $q->orwhere('barcode', '!=', '');
                $q->orwhere('internal_id', '!=', '');
            })
            // ->wherenotnull('barcode')
        ;
        $extradata = [];
        $establishment = \Auth::user()->establishment;
        $isPharmacy = false;
        if ($request->has('isPharmacy')) {
            $isPharmacy = ($request->isPharmacy === 'true') ? true : false;
        }
        if ($isPharmacy == true) {
            $extradata[] = 'sanitary';
            $extradata[] = 'cod_digemid';
            $records->Pharmacy();
        }
        $extra_data = $extradata;
        $records = $records->get();
        $height = 23;

        $width = 48;
        $pdfj = new Fpdi();
        /** @var Item $item */
        foreach ($records as $item) {
            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    $height
                ],
                'margin_top' => 2,
                'margin_right' => 2,
                'margin_bottom' => 0,
                'margin_left' => 2
            ]);
            $html = view('tenant.items.exports.items-barcode-full', compact('item', 'extra_data', 'establishment'))->render();
            $pdf->AddPage();
            $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
            PdfUnionController::addFpi($pdfj, $pdf);
        }

        return PdfUnionController::ResponseAsFile($pdfj, 'bar_code_full');
    }
    /**
     * Exporta items al formato de DIGEMID
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportDigemidCsv()
    {
        ini_set('max_execution_time', 0);
        $company = Company::first();
        $company_cod_digemid = $company->cod_digemid;
        if ($company_cod_digemid == null) {
            return [
                "success" => false,
                "message" => "Debe registrar un codigo de Digemid"
            ];
        }

        $records = CatDigemid::where('active', 1);
        $max_prices = $records->max('max_prices');
        if ($max_prices == null) {
            $max_prices = 0;
        }
        $records = $records->get();
        $export = new DigemidItemCsvExport();

        $export->setRecords($records)->setCompanyCodDigemid($company_cod_digemid);

        return $export->download('Reporte_Items_Digemid_' . Carbon::now() . '.csv', Excel::CSV);
    }
    public function exportDigemid(Request $request)
    {
        ini_set('max_execution_time', 0);
        $company = Company::first();
        $company_cod_digemid = $company->cod_digemid;
        if ($company_cod_digemid == null) {
            return [
                "success" => false,
                "message" => "Debe registrar un codigo de Digemid"
            ];
        }

        $records = CatDigemid::where('active', 1);
        $max_prices = $records->max('max_prices');
        if ($max_prices == null) {
            $max_prices = 0;
        }
        $records = $records->get();
        $export = new DigemidItemExport();

        $export->setRecords($records)->setCompanyCodDigemid($company_cod_digemid)->setMaxPrice($max_prices);

        return $export->download('Reporte_Items_Digemid_' . Carbon::now() . '.xlsx');
    }

    public function printBarCode(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->id;

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if ($item_warehouse->stock < 1) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                104.1,
                24
            ],
            'margin_top' => 2,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);
        $html = view('tenant.items.exports.items-barcode-id', compact('record', 'stock'))->render();

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_' . now()->format('Y_m_d') . '.pdf', 'I');
    }
    public function printBarCodeE(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->input('id');
        $format = $request->input('format');

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if ($item_warehouse->stock < 1) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $width = 50;
        $height = 25;

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                $width,
                $height
            ],
            'margin_top' => 0,
            'margin_right' => 0,
            'margin_bottom' => 1,
            'margin_left' => 0,
        ]);
        $pdf->shrink_tables_to_fit = 1;
        $html = view('tenant.items.exports.items-barcode-e', compact('record', 'stock', 'format'))->render();

        //  return $html;

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_1x1_d_' . now()->format('Y_m_d') . '.pdf', 'I');
    }
    public function printBarCodeD(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->input('id');
        $format = $request->input('format');

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if ($item_warehouse->stock < 1) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $width = 50;
        $height = 25;

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                $width,
                $height
            ],
            'margin_top' => 0,
            'margin_right' => 0,
            'margin_bottom' => 1,
            'margin_left' => 0,
        ]);
        $pdf->shrink_tables_to_fit = 1;
        $html = view('tenant.items.exports.items-barcode-d', compact('record', 'stock', 'format'))->render();

        //  return $html;

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_1x1_d_' . now()->format('Y_m_d') . '.pdf', 'I');
    }
    public function printBarCode2d(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->input('id');
        $format = $request->input('format');

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if ($item_warehouse->stock < 1) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $width = ($format == 1 || $format == 4) ? 80 : 104.1;
        $height = ($format == 1 || $format == 4) ? 26 : 24;

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                $width,
                $height
            ],
            'margin_top' => 0,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);
        $pdf->shrink_tables_to_fit = 1;
        $html = view('tenant.items.exports.items-barcode-2-b', compact('record', 'stock', 'format'))->render();

        //  return $html;

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_1x2b_' . $format . '_' . now()->format('Y_m_d') . '.pdf', 'I');
    }
    public function printBarCode0(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->input('id');
        $format = $request->input('format');

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if ($item_warehouse->stock < 1) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $width = ($format == 1 || $format == 4) ? 80 : 104.1;
        $height = ($format == 1 || $format == 4) ? 26 : 24;

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                $width,
                $height
            ],
            'margin_top' => $format == 4 ? 0 : 2,
            'margin_right' => $format == 4 ? 0 : 2,
            'margin_bottom' => 0,
            'margin_left' => $format == 4 ? 0 : 2
        ]);
        $pdf->shrink_tables_to_fit = 1;
        $html = view('tenant.items.exports.items-barcode-inventory', compact('record', 'stock'))->render();

        //  return $html;

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_1x' . $format . '_' . now()->format('Y_m_d') . '.pdf', 'I');
    }

    public function printBarCodeX(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->input('id');
        $format = $request->input('format');

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if ($item_warehouse->stock < 1) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $width = ($format == 1 || $format == 4) ? 80 : 104.1;
        $height = ($format == 1 || $format == 4) ? 26 : 24;

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                $width,
                $height
            ],
            'margin_top' => $format == 4 ? 0 : 2,
            'margin_right' => $format == 4 ? 0 : 2,
            'margin_bottom' => 0,
            'margin_left' => $format == 4 ? 0 : 2
        ]);
        $pdf->shrink_tables_to_fit = 1;
        $html = view('tenant.items.exports.items-barcode-x', compact('record', 'stock', 'format'))->render();

        //  return $html;

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_1x' . $format . '_' . now()->format('Y_m_d') . '.pdf', 'I');
    }

    public function itemLast()
    {
        $record = Item::latest()->first();
        if ($record != null) {
            return json_encode(['data' => $record->id]);
        }
    }

    public function tablesImport()
    {
        $user = auth()->user();
        $warehouses = Warehouse::select('id', 'description');
        if ($user->type !== 'admin') {
            $warehouses = $warehouses->where('id', $user->establishment_id);
        }

        return response()->json([
            'warehouses' => $warehouses->get(),
        ], 200);
    }

    /**
     * Obtiene una lista de items del sistema
     *
     * @param \Illuminate\Http\Request $r
     *
     * @return \App\Http\Resources\Tenant\ItemCollection
     */
    public function getAllItems(Request $r)
    {
        $records = $this->getRecords($r);
        return new ItemCollection($records->paginate(50));
    }


    public function searchItemById($id)
    {
        // $items = SearchItemController::searchByIdToModal($id);
        $items = SearchItemController::getItemsToSupply(null, $id);
        return compact('items');
    }


    public function searchItems(Request $request)
    {

        $items = SearchItemController::getItemsToSupply($request);

        return compact('items');
    }
    public function item_tables_index()
    {
        $clothesShoes = BusinessTurn::isClothesShoes();
        $is_majolica = BusinessTurn::isMajolica();
        $warehouses = Warehouse::all();

        return compact('clothesShoes', 'is_majolica', 'warehouses');
    }

    public function item_tables()
    {
        // $items = $this->table('items');
        $items = SearchItemController::getItemsToDocuments();
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $operation_types = OperationType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $is_client = $this->getIsClient();
        $clothesShoes = BusinessTurn::isClothesShoes();
        $is_majolica = BusinessTurn::isMajolica();
        $configuration = Configuration::first();
        $warehouses = Warehouse::all();
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
            'warehouses',
            'is_majolica',
            'clothesShoes',
            'items',
            'categories',
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
            'CatItemUnitsPerPackage'
        );
    }

    /**
     * Actualiza las descripciones de items que contienen el carácter '<'
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fixDescriptionsWithHtmlTags()
    {
        try {
            DB::connection('tenant')->beginTransaction();

            $chunkSize = 1000;
            $totalAffected = 0;

            DB::connection('tenant')->table('items')
                ->whereRaw('description LIKE ? AND description LIKE ?', ['%<%', '%>%'])
                ->whereNotNull('name')
                ->orderBy('id')
                ->chunkById($chunkSize, function ($items) use (&$totalAffected) {
                    $ids = $items->pluck('id')->toArray();

                    $affected = DB::table('items')
                        ->whereIn('id', $ids)
                        ->update(['description' => DB::raw('name')]);

                    $totalAffected += $affected;
                });

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Se actualizaron ' . $totalAffected . ' registros correctamente',
                'affected_records' => $totalAffected
            ], 200);
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las descripciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los lotes/series de un item específico
     */
    public function getItemLots($id)
    {
        try {
            $item = Item::findOrFail($id);
            $lots = $item->item_lots()
                ->orderBy('series', 'asc')
                ->get(['id', 'series', 'date', 'warehouse_id']);
            
            return response()->json($lots);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los lotes: ' . $e->getMessage()
            ], 500);
        }
    }
}
