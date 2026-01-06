<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Catalogs\CatColorsItem;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemSupply;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Traits\InventoryTrait;

/**
 * Tener en cuenta como base modules/Document/Traits/SearchTrait.php
 * Class SearchItemController
 *
 * @package App\Http\Controllers
 * @mixin Controller
 */
class SearchItemController extends Controller
{

    // use InventoryTrait;

    /**
     * Devuelve una lista de items unido entre service y no service.
     *
     * @param Request|null $request
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed
     */
    public static function getAllItem(Request $request = null)
    {

        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();

        self::validateRequest($request);
        $notService = self::getNotServiceItem($request);
        $Service = self::getServiceItem($request);
        $notService->merge($Service);
        return $notService->transform(function ($row) use ($warehouse) {
            /** @var Item $row */

            return $row->getDataToItemModal($warehouse);
        });
    }

    /**
     * @param Request|null $request
     */
    protected static function validateRequest(&$request)
    {
        if ($request == null) $request = new Request();
    }

    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getNotServiceItem(Request $request = null, $id = 0)
    {
        $url = request()->url();
        $user = auth()->user();
        $from_items_document = strpos($url, 'items-document') !== false;
        self::validateRequest($request);
        $search_by_barcode = $request->has('search_by_barcode') && (bool)$request->search_by_barcode;
        $input = self::setInputByRequest($request);
        $item = self::getAllItemBase($request, false, $id);
        $configuration = Configuration::getConfig();

        if (($configuration->list_items_by_warehouse == true || $configuration->list_items_by_warehouse == 1) && !$from_items_document) {
            self::SetWarehouseToUser($item);
        }

        return $item->orderBy('description')->get();
    }


    /**
     *
     * No aplica filtro por almacén
     *
     * @param Request|null $request
     * @param int          $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getNotServiceItemWithOutWarehouse(Request $request = null, $id = 0)
    {

        self::validateRequest($request);
        // $search_by_barcode = $request->has('search_by_barcode') && (bool)$request->search_by_barcode;
        // $input = self::setInputByRequest($request);
        $item = self::getAllItemBase($request, false, $id);

        return $item->orderBy('description')->get();
    }

    /**
     * Busca la propiedad input o input_item para generar busquedas
     *
     * @param Request|null $request
     *
     * @return mixed|null
     */
    protected static function setInputByRequest(Request $request = null)
    {
        if (!empty($request)) {
            $input = ($request->has('input')) ? $request->input : null;
            if (empty($input) && $request->has('input_item')) {
                $input = ($request->has('input_item')) ? $request->input_item : null;
            }
        }
        return $input;
    }

    /**
     * @param Request|null $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getAllItemBase(Request $request = null, $service = false, $id = 0)
    {

        self::validateRequest($request);
        $configuration = Configuration::getConfig();
        $search_item_by_series = $configuration->isSearchItemBySeries();
        // $search_by_series_and_all = Configuration::first()->search_by_series_and_all;
        $production = (bool)($request->production ?? false);
        $filter_categorie = $request->input('filter_categorie');
        $filter_brand = $request->input('filter_brand');
        $favorite = $request->input('favorite');
        $advance = $request->input('advance');
        $search_by_location = $request->input('location') == "true";
        $order_search_price = $request->input('order_search_price');
        $order_search_stock = $request->input('order_search_stock');
        $items_id = ($request->has('items_id')) ? $request->items_id : null;
        $id = (int)$id;
        $search_by_barcode = $request->has('search_by_barcode') && (bool)$request->search_by_barcode;
        $input = self::setInputByRequest($request);
        $search_item_by_barcode_presentation = $request->has('search_item_by_barcode_presentation') && (bool)$request->search_item_by_barcode_presentation;
        // $item = Item:: whereIsActive();
        $url_request = $request->url();
        $from_purchases = in_array(true, [
            strpos($url_request, 'purchases') !== false,
            strpos($url_request, 'purchases-order') !== false,
            strpos($url_request, 'purchases-quotation') !== false,
            strpos($url_request, 'inventory') !== false
        ]);
        if ($from_purchases) {
            $item = Item::query();
        } else {

            $item = Item::ItemIsNotInput();
        }


        if ($advance) {
            $item
                ->where('lots_enabled', 0)
                ->where('has_sizes', 0)
                ->where('series_enabled', 0);
        }
        if ($configuration->pharmacy_control) {
            $item = $item->hasStateInLotGroup();
        }
        $ItemToSearchBySeries = Item::whereIsActive();
        //
        if ($service == false) {
            $item->WhereNotService();
            $ItemToSearchBySeries->WhereNotService();
        } else {
            $item->WhereService()
                ->whereNotIsSet();
            $ItemToSearchBySeries->WhereService()
                ->whereNotIsSet();
            //         
        }
        if ($configuration->show_filters_set_items_for_users) {
            /** @var User $user */
            $user = auth()->user();
            $user_filter = $user->getFilterItemByUser();

            if ($user_filter && $user_filter->filter_active) {
                $is_set = $user_filter->filter_name === 'pack' ? 1 : 0;
                $item->where('is_set', $is_set);
                $ItemToSearchBySeries->where('is_set', $is_set);
            }
        }
        if ($filter_categorie) {
            $item->where('category_id', $filter_categorie);
            $ItemToSearchBySeries->where('category_id', $filter_categorie);
        }
        if ($filter_brand) {
            $item->where('brand_id', $filter_brand);
            $ItemToSearchBySeries->where('brand_id', $filter_brand);
        }


        if ($production !== false) {
            // busqueda de insumos, no se lista por codigo de barra o por series
            $search_item_by_series = false;
        } else {
            $item->with('warehousePrices');
            $ItemToSearchBySeries->with('warehousePrices');
        }

        $alt_item = $item;

        $bySerie = null;
        if ($search_item_by_series == true) {
            self::validateRequest($request);
            $warehouse = Warehouse::select('id')->where('establishment_id', auth()->user()->establishment_id)->first();
            $input = self::setInputByRequest($request);
            if (!empty($input)) {
                $ItemToSearchBySeries->WhereHas('item_lots', function ($query) use ($warehouse, $input) {
                    $query->where('has_sale', false);
                    $query->where('warehouse_id', $warehouse->id);
                    $query->where('series', $input);
                    // return $query;
                })->take(1);

                //Busca el item con relacion al almacen

                if ($configuration->list_items_by_warehouse == true || $configuration->list_items_by_warehouse == 1) {
                    self::SetWarehouseToUser($item);
                    self::SetWarehouseToUser($ItemToSearchBySeries);
                }
                $bySerie = $ItemToSearchBySeries->first();
                if ($bySerie !== null) {
                    //Si existe un dato, devuelve la busqueda por serie.
                    $item->WhereHas('item_lots', function ($query) use ($warehouse, $input) {
                        $query->where('has_sale', false);
                        $query->where('warehouse_id', $warehouse->id);
                        $query->where('series', $input);
                    })->take(1);
                }
            }
        }

        if ($bySerie === null) {

            if ($items_id != null) {
                $item->whereIn('id', $items_id);
            } elseif ($id != 0) {
                $item->where('id', $id);
            } else {

                if ($search_by_barcode === true) {

                    if ($search_item_by_barcode_presentation) {
                        $item->filterItemUnitTypeBarcode($input)->limit(1);
                    } else {
                        $item
                            ->where('barcode', $input)
                            ->limit(1);
                    }
                } else {
                    if ($search_by_location === true) {
                        $item->where('location', 'like', '%' . $input . '%');
                    } else {

                        if ($search_item_by_series == false) {
                            self::setFilter($item, $request);
                        } else {
                            $item->where('id', 0);
                        }
                    }
                }
            }
        }
        if ($favorite == 1) {
            $item->where('frequent', true);
        }


        $item->whereIsActive();
        $item->orderByDesc('frequent');

        $bindigns = $item->getBindings();

        if ($order_search_price || $order_search_stock) {

            if ($order_search_price) {
                $item->orderBy('sale_unit_price', 'desc');
            }
            if ($order_search_stock) {
                $item->orderBy('stock', 'desc');
            }
            return $item;
        } else {

            return $item->orderBy('description');
        }
    }

    /**
     * Establece que solo se mostraria los item donde el usuario se encuentra
     *
     * @param $item
     */
    public static function SetWarehouseToUser(&$item)
    {
        /** @var Item $item */
        // En este caso, se desestima esta configuracion ya que debe filtrase por el almacen del usuario
        // dejando sin efecto por el issue #1046
        //   $configuration =  Configuration::first()-> isShowItemsOnlyUserStablishment();
        //   if($configuration == true) {
        $item->whereWarehouse();
        //   }

    }

    /**
     * @param              $item
     * @param Request|null $request
     */
    protected static function setFilter(&$item, Request $request = null)
    {
        /** @var Builder $item */

        $input = self::setInputByRequest($request);
        $configuration = Configuration::getConfig();
        $all_products = $configuration->all_products;
        $search_by_series_and_all = $configuration->search_by_series_and_all;
        $search_factory_code_items = $request->has('search_factory_code_items') && (bool) $request->search_factory_code_items;
        if (!empty($input)) {
            $whereItem[] = ['description', 'like', '%' . str_replace(' ', '%', $input) . '%'];
            $whereItem[] = ['internal_id', 'like', '%' . $input . '%'];
            $whereItem[] = ['barcode', '=', $input];

            $whereExtra[] = ['name', 'like', '%' .  str_replace(' ', '%', $input) . '%'];

            if ($search_factory_code_items) $whereItem[] = ['factory_code', 'like', '%' . $input . '%'];

            $item->where(function ($query) use ($whereItem, $whereExtra, $input, $configuration, $request) {
                foreach ($whereItem as $index => $wItem) {
                    if ($index < 1) {
                        $query->Where([$wItem]);
                    } else {
                        $query->orWhere([$wItem]);
                    }
                }

                $request_type = null;
                $user_id = auth() ? auth()->id() : 1;
                if (Cache::has('request_type_' . $user_id)) {
                    $request_type = Cache::get('request_type_' . $user_id);
                }
                if ($configuration->search_by_series_and_all && $request_type == 'document') {
                    $query->orWhereHas('item_lots', function ($subQuery) use ($input) {
                        $subQuery->where('series', $input);
                    });
                }
                if (!empty($whereExtra)) {
                    $query
                        ->orWhereHas('brand', function ($subQuery) use ($whereExtra) {
                            $subQuery->where($whereExtra);
                        })
                        ->orWhereHas('category', function ($subQuery) use ($whereExtra) {
                            $subQuery->where($whereExtra);
                        });
                }

                $query->OrWhereJsonContains('attributes', ['value' => $input]);
            });
            //  Limita los resultados de busqueda, inicial 250, puede modificarse en el .env con NUMBER_SEARCH_ITEMS
            // if (!$all_products) {
            $item->take(\Config('extra.number_items_in_search'));
            // }
        } else {
            // Si no se filtran datos, entonces se toman 20, puede añadirse en el env la variable NUMBER_ITEMS
            if (!$all_products || DB::connection('tenant')->table('items')->count() > 100) {
                $item->take(\Config('extra.number_items_at_start'));
            }
        }
    }

    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed
     */
    public static function getServiceItem(Request $request = null, $id = 0)
    {
        $configuration = Configuration::getConfig();
        self::validateRequest($request);
        $search_by_barcode = $request->has('search_by_barcode') && (bool)$request->search_by_barcode;
        $input = self::setInputByRequest($request);
        /** @var Item $item */
        $item = self::getAllItemBase($request, true, $id);
        if ($search_by_barcode === false && $input != null) {

            if ($configuration->list_items_by_warehouse == true || $configuration->list_items_by_warehouse == 1) {
                self::SetWarehouseToUser($item);
            }
        }




        return $item->orderBy('description')->get();
    }

    /**
     * @param Request|null $request
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function getNotServiceItemToModal(Request $request = null, $id = 0)
    {
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        self::validateRequest($request);
        return self::getNotServiceItem($request, $id)->transform(function ($row) use ($warehouse) {
            /** @var Item $row */

            return $row->getDataToItemModal($warehouse);
        });
    }

    /**
     * Reaqliza una busqueda de item por id, Intenta por item, luego por servicio
     * Devuelve un standar de modal
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function searchByIdToModal($id = 0)
    {
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();

        $items = self::searchById($id)->transform(function ($row) use ($warehouse) {
            /** @var Item $row */
            return $row->getDataToItemModal(
                $warehouse,
                true,
                null,
                false,
                true
            );
        });
        return $items;
    }

    /**
     * @param int $id
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed
     */
    public static function searchById($id = 0)
    {
        $search_item = self::getNotServiceItem(null, $id);
        if (count($search_item) == 0) {
            $search_item = self::getServiceItem(null, $id);
        }
        return $search_item;
    }

    /**
     * @param Request $request
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed
     */
    public static function searchByRequest(Request $request)
    {
        $search_item = self::getNotServiceItem($request);
        if (count($search_item) == 0) {
            $search_item = self::getServiceItem($request);
        }
        return $search_item;
    }

    /**
     * @param int $id
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed
     */
    public static function searchByIdToPurchase($id = 0)
    {
        $search_item = self::getNotServiceItemToPurchase(null, $id);
        if (count($search_item) == 0) {
            $search_item = self::getServiceItemToPurchase(null, $id);
        }
        return $search_item;
    }

    /**
     * Devuelve el conjunto para ventas sin los pack o productos compuestos
     *
     * @param Request|null $request
     * @param int          $id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getNotServiceItemToPurchase(Request $request = null, $id = 0)
    {

        self::validateRequest($request);
        $search_by_barcode = $request->has('search_by_barcode') && (bool)$request->search_by_barcode;
        $input = self::setInputByRequest($request);

        $item = self::getAllItemBase($request, false, $id);

        $item->WhereNotIsSet();


        if ($search_by_barcode === false && $input != null) {
            $configuration = Configuration::first();
            if ($configuration->list_items_by_warehouse == true || $configuration->list_items_by_warehouse == 1) {
                self::SetWarehouseToUser($item);
            }
        }


        return $item->orderBy('description')->get();
    }

    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed
     */
    public static function getServiceItemToPurchase(Request $request = null, $id = 0)
    {
        self::validateRequest($request);
        $search_by_barcode = $request->has('search_by_barcode') && (bool)$request->search_by_barcode;
        $input = self::setInputByRequest($request);
        /** @var Item $item */
        $item = self::getAllItemBase($request, true, $id);
        $item->WhereNotIsSet();

        if ($search_by_barcode === false && $input != null) {
            $configuration = Configuration::first();
            if ($configuration->list_items_by_warehouse == true || $configuration->list_items_by_warehouse == 1) {
                self::SetWarehouseToUser($item);
            }
        }


        return $item->orderBy('description')->get();
    }

    /**
     * @param Request $request
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed
     */
    public static function searchByRequestToPurchase(Request $request)
    {
        $search_item = self::getNotServiceItemToPurchase($request);
        if (count($search_item) == 0) {
            $search_item = self::getServiceItemToPurchase($request);
        }
        return $search_item;
    }


    /**
     * Retorna la coleccion de items par Documento y Boleta.
     *  Usado en app/Http/Controllers/Tenant/DocumentController.php::250
     *  Usado en app/Http/Controllers/Tenant/DocumentController.php::370
     *  Usado en modules/Document/Http/Controllers/DocumentController.php::297
     *
     * @param Request| null $request
     * @param int           $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function getItemsToSupply(Request $request = null, $id = 0)
    {

        self::validateRequest($request);
        $search_by_barcode = $request->has('search_by_barcode') && (bool)$request->search_by_barcode;
        $input = self::setInputByRequest($request);
        $item = self::getAllItemBase($request, false, $id);

        /*
            if ($search_by_barcode === false && $input != null) {
                self::SetWarehouseToUser($item);
            }
            */
        $item->ForProductionSupply();
        // $item->wherein('id',ItemSupply::select('individual_item_id')->pluck('individual_item_id'));
        return self::TransformToModalAndSupply($item->orderBy('description')->get());
    }


    /**
     * Retorna la coleccion de items par Documento y Boleta.
     *  Usado en app/Http/Controllers/Tenant/DocumentController.php::250
     *  Usado en app/Http/Controllers/Tenant/DocumentController.php::370
     *  Usado en modules/Document/Http/Controllers/DocumentController.php::297
     *
     * @param Request| null $request
     * @param int           $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function getItemsToDocuments(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);
        return self::TransformToModal($items_not_services->merge($items_services));


    
    }
    public static function getItemsToDocumentsOptimized(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);

        $records = $items_not_services->merge($items_services);
        return Item::getItemDataToDocuments($records);
    }
    public static function getItemsToDocumentsLite(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);
        return self::TransformToModalLite($items_not_services->merge($items_services))->take(10);
    }
    public static function TransformToModalLite($items, Warehouse $warehouse = null)
    {
        /** @var Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed $items */
        return $items->transform(function ($row) use ($warehouse) {
            /** @var Item $row */
            return $row->getDataToItemModalLite($warehouse);
        });
    }
    /**
     * @param Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed $items
     * @param Warehouse|null                                                                                                     $warehouse
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function TransformToModal($items, Warehouse $warehouse = null)
    {
        /** @var Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed $items */
        return $items->transform(function ($row) use ($warehouse) {
            /** @var Item $row */
            return $row->getDataToItemModal($warehouse);
        });
    }
    /**
     * @param Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed $items
     * @param Warehouse|null                                                                                                     $warehouse
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function TransformToModalAndSupply($items, Warehouse $warehouse = null)
    {
        /** @var Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed $items */
        return $items
            ->transform(function (Item $row) use ($warehouse) {
                $data = $row->getDataToItemModal($warehouse);
                $suppl = $row->supplies;


                $data['supplies'] = $row->supplies;
                return  $data;
            });
    }

    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getItemsToSaleNote(Request $request = null, $id = 0)
    {

        /*

            $items_u = Item::whereWarehouse()->whereIsActive()->whereNotIsSet()->orderBy('description')->take(20)->get();

            $items_s = Item::where('unit_type_id','ZZ')->whereIsActive()->orderBy('description')->take(10)->get();

            $items = $items_u->merge($items_s);
            */

        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);

        return self::TransformToModalSaleNote($items_not_services->merge($items_services));
    }

    /**
     * @param Item[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|Collection|mixed $items
     * @param Warehouse|null                                                                                                     $warehouse
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function TransformToModalSaleNote($items, Warehouse $warehouse = null)
    {
        $configuration = Configuration::first();
        $warehouse_id = ($warehouse) ? $warehouse->id : null;
        if ($warehouse_id == null) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            $warehouse_id = ($warehouse) ? $warehouse->id : null;
        }

        return $items->transform(function ($row) use ($warehouse_id, $warehouse, $configuration) {
            /** @var Item $row */
            $sale_unit_price = number_format(round($row->sale_unit_price, 6), $configuration->decimal_quantity, ".", "");
            if ($configuration->active_warehouse_prices) {
                $establishment_id = auth()->user()->establishment_id;
                $warehouse_id = Warehouse::where('establishment_id', $establishment_id)->first()->id;
                $item_warehouse_price = ItemWarehousePrice::where('item_id', $row->id)->where('warehouse_id', $warehouse_id)->first();
                if ($item_warehouse_price) {
                    $sale_unit_price = number_format(round($item_warehouse_price->price, 6), $configuration->decimal_quantity, ".", "");
                }
            }
            $temp =   [
                'id' => $row->id,
                // 'sale_unit_price' => number_format(round($row->sale_unit_price, 6), $configuration->decimal_quantity, ".", ""),
                'sale_unit_price' => $sale_unit_price,
                'purchase_unit_price' => number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ","),
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_igv' => (bool)$row->has_igv,
                'lots_enabled' => (bool)$row->lots_enabled,
                'series_enabled' => (bool)$row->series_enabled,
                'is_set' => (bool)$row->is_set,
                'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse_id) {
                    /** @var ItemWarehouse $row */
                    /** @var Warehouse $c_warehouse */
                    /** @var Ware $p_warehouse */
                    $c_warehouse = $row->warehouse;
                    $price = ItemWarehousePrice::where('item_id', $row->item_id)->where('warehouse_id', $c_warehouse->id)->first();
                    if ($price) {
                        $price = $price->price;
                    } else {
                        $price = 0;
                    }
                    return [
                        'price' => $price,
                        'warehouse_id' => $c_warehouse->id,
                        'warehouse_description' => $c_warehouse->description,
                        'stock' => $row->stock,
                        'checked' => ($c_warehouse->id == $warehouse_id) ? true : false,
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
                        'checked' => false,
                        'compromise_quantity' => 0,
                        'warehouse_id' => $row->warehouse_id,
                        'warehouse' => $row->warehouse_id ? $row->warehouse->description : null,

                    ];
                }),
                'lot_code' => $row->lot_code,
                'date_of_due' => $row->date_of_due,
            ];

            return  array_merge($row->getCollectionData(), $row->getDataToItemModal(), $temp);
        });
    }

    /**
     * Centralizado de busqueda para Cotizaciones
     *
     * @param Request|null $request
     * @param int          $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public static function getItemsToQuotation(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);

        $onlyService = false;
        if (
            ($request !== null  && $request->has('only_service') && (bool)$request->only_service == true) ||
            (isset($_GET['only_service']) && $_GET['only_service'] == 1)
        ) {
            // Si la busqueda tiene only_service DEBE BUSCAR SOLO SERVICIOS
            $onlyService = true;
        }

        if ($onlyService == true) {
            return self::TransformToModal($items_services);
        }
        return self::TransformToModal($items_not_services->merge($items_services));
    }

    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return mixed
     */
    public static function getItemsToOrderNote(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        // aqui
        return self::TransformModalToOrderNote($items_not_services->merge($items_services), $warehouse);
    }

    /**
     * @param                $items
     * @param Warehouse|null $warehouse
     *
     * @return mixed
     */
    public static function TransformModalToOrderNote($items, Warehouse $warehouse = null)
    {
        $warehouse_id = ($warehouse) ? $warehouse->id : null;
        $configuration = Configuration::getConfig();
        if ($warehouse_id == null) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            $warehouse_id = ($warehouse) ? $warehouse->id : null;
        }

        return $items->transform(function ($row) use ($warehouse_id, $warehouse, $configuration) {
            $observation_apportionment = null;
            if ($configuration->purchase_apportionment) {
                $apportionment_items_stock = DB::connection('tenant')->table('apportionment_items_stock')->where('item_id', $row->id)
                    ->where('stock_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->first();
                if (!empty($apportionment_items_stock)) {
                    $observation_apportionment = $apportionment_items_stock->observation;
                }
            }
            /** @var Item $row */
            $detail = self::getFullDescriptionToSaleNote($row, $warehouse);
            return [
                'observation_apportionment' => $observation_apportionment,
                'id' => $row->id,
                'internal_id' => $row->internal_id,
                'full_description' => $detail['full_description'],
                'brand' => $detail['brand'],
                'category' => $detail['category'],
                'stock' => $detail['stock'],
                'description' => $row->description,
                'calculate_quantity' => (bool)$row->calculate_quantity,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => round($row->sale_unit_price, 2),
                'purchase_unit_price' => number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ","),
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_igv' => (bool)$row->has_igv,
                'lots_enabled' => (bool)$row->lots_enabled,
                'series_enabled' => (bool)$row->series_enabled,
                'is_set' => (bool)$row->is_set,
                'warehouses' => collect($row->warehouses)->transform(function ($wh) use ($warehouse, $row) {
                    $price = self::getSaleUnitPriceByWarehouse($row, $warehouse->id);
                    return [
                        'price' => $price,
                        'warehouse_id' => $wh->warehouse->id,
                        'warehouse_description' => $wh->warehouse->description,
                        'stock' => $wh->stock,
                        'checked' => ($wh->warehouse_id == $warehouse->id) ? true : false,
                    ];
                }),
                'label_color' => $row->labelColor && $configuration->label_item_color ? [
                    'id' => $row->labelColor->id,
                    'description' => $row->labelColor->labelColor->description,
                    'color' => $row->labelColor->labelColor->color,
                ] : null,
                'item_unit_types' => $row->item_unit_types,
                'lots' => [],
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked' => false,
                        'compromise_quantity' => 0,
                        'warehouse_id' => $row->warehouse_id,
                        'warehouse' => $row->warehouse_id ? $row->warehouse->description : null,
                    ];
                }),
                'lot_code' => $row->lot_code,
                'item_attributes' => $row->getItemAttributes(),
                'restrict_sale_cpe' => $row->restrict_sale_cpe,
                'date_of_due' => $row->date_of_due,
                'payment_conditions' => $row->payment_conditions,
            ];
        });
    }

    /**
     * @param Item           $item
     * @param Warehouse|null $warehouse
     *
     * @return string[]
     */
    public static function getFullDescriptionToSaleNote(Item $item, Warehouse $warehouse = null)
    {

        $desc = ($item->internal_id) ? $item->internal_id . ' - ' . $item->description : $item->description;
        $category = ($item->category) ? "{$item->category->name}" : "";
        $brand = ($item->brand) ? "{$item->brand->name}" : "";

        if ($item->unit_type_id != 'ZZ') {
            $warehouse_stock = ($item->warehouses && $warehouse) ? number_format($item->warehouses->where('warehouse_id', $warehouse->id)->first() != null ? $item->warehouses->where('warehouse_id', $warehouse->id)->first()->stock : 0, 2) : 0;
            $stock = ($item->warehouses && $warehouse) ? "{$warehouse_stock}" : "";
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
    public static function getSaleUnitPriceByWarehouse(Item $item, int $warehouseId): string
    {
        $configuration = Configuration::first();
        if ($configuration->active_warehouse_prices) {

            $warehousePrice = $item->warehousePrices->where('item_id', $item->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $price = $warehousePrice->price ?? $item->sale_unit_price;
            return number_format($price,  $configuration->decimal_quantity, ".", "");
        }
        return number_format($item->sale_unit_price,  $configuration->decimal_quantity, ".", "");
    }
    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return mixed
     */
    public static function getItemToPurchaseOrder(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();

        return self::TransformModalToPurchaseOrder($items_not_services->merge($items_services), $warehouse);
        //
    }

    /**
     * @param                $items
     * @param Warehouse|null $warehouse
     *
     * @return mixed
     */
    public static function TransformModalToPurchaseOrder($items, Warehouse $warehouse = null)
    {
        $warehouse_id = ($warehouse) ? $warehouse->id : null;
        $configuration = Configuration::first();
        if ($warehouse_id == null) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            $warehouse_id = ($warehouse) ? $warehouse->id : null;
        }
        return $items->transform(function ($row) use ($warehouse_id, $warehouse, $configuration) {
            /** @var Item $row */
            $full_description = self::getFullDescriptionToPurchaseOrder($row);
            $stock = $row->warehouses->where('warehouse_id', $warehouse_id)->first() != null ? $row->warehouses->where('warehouse_id', $warehouse_id)->first()->stock : 0;
            if ($stock < 0) {
                $stock *= -1;
            }
            return [
                'id' => $row->id,
                'full_description' => $full_description,
                'description' => $row->description,
                'model' => $row->model,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ","),
                'purchase_unit_price' => number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ","),
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_perception' => (bool)$row->has_perception,
                'purchase_has_igv' => (bool)$row->purchase_has_igv,
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
                        'range_min' => $row->range_min,
                        'range_max' => $row->range_max,
                        'warehouse_id' => $row->warehouse_id,
                    ];
                }),
                'series_enabled' => (bool)$row->series_enabled,
                'lots_enabled' => (bool)$row->lots_enabled,
                'stock' => $stock,
            ];
        });
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    public static function getFullDescriptionToPurchaseOrder(Item $item)
    {

        $desc = ($item->internal_id) ? $item->internal_id . ' - ' . $item->description : $item->description;
        $category = ($item->category) ? " - {$item->category->name}" : "";
        $brand = ($item->brand) ? " - {$item->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }

    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return mixed
     */
    public static function getItemToPurchaseQuotation(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();

        return self::TransformModalToPurchaseQuotation($items_not_services->merge($items_services), $warehouse);
        //
    }

    /**
     * @param                $items
     * @param Warehouse|null $warehouse
     *
     * @return mixed
     */
    public static function TransformModalToPurchaseQuotation($items, Warehouse $warehouse = null)
    {
        $warehouse_id = ($warehouse) ? $warehouse->id : null;
        $configuration = Configuration::first();
        if ($warehouse_id == null) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            $warehouse_id = ($warehouse) ? $warehouse->id : null;
        }
        return $items->transform(function ($row) use ($warehouse_id, $warehouse, $configuration) {
            /** @var Item $row */
            $full_description = self::getFullDescriptionToPurchaseQuotation($row);
            return [
                'id' => $row->id,
                'full_description' => $full_description,
                'description' => $row->description,
                'unit_type_id' => $row->unit_type_id,
                'is_set' => (bool)$row->is_set,
                'model' => $row->model,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ","),
                'purchase_unit_price' => number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ","),
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_perception' => (bool)$row->has_perception,
                'purchase_has_igv' => (bool)$row->purchase_has_igv,
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
                        'range_min' => $row->range_min,
                        'range_max' => $row->range_max,
                        'warehouse_id' => $row->warehouse_id,
                    ];
                }),
                'series_enabled' => (bool)$row->series_enabled,
            ];
        });
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    public static function getFullDescriptionToPurchaseQuotation($item)
    {
        /** @var Item $item */

        $desc = ($item->internal_id) ? $item->internal_id . ' - ' . $item->description : $item->description;
        $category = ($item->category) ? " - {$item->category->name}" : "";
        $brand = ($item->brand) ? " - {$item->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }

    /**
     * @param Request|null $request
     * @param int          $id
     *
     * @return mixed
     */
    public static function getItemToPurchase(Request $request = null, $id = 0)
    {
        // $items_not_services = self::getNotServiceItemWithOutWarehouse($request, $id);
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        // if ($warehouse != null) {
        //     $items_not_services = $items_not_services->whereHas('warehouses', function ($query) use ($warehouse) {
        //         $query->where('warehouse_id', $warehouse->id);
        //     });
        //     $items_services = $items_services->whereHas('warehouses', function ($query) use ($warehouse) {
        //         $query->where('warehouse_id', $warehouse->id);
        //     });
        // }
        return self::TransformModalToPurchase($items_not_services->merge($items_services), $warehouse);
        //
    }

    public static function TransformModalToPurchase($items, Warehouse $warehouse = null)
    {
        $warehouse_id = ($warehouse) ? $warehouse->id : null;
        $configuration = Configuration::getConfig();
        if ($warehouse_id == null) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            $warehouse_id = ($warehouse) ? $warehouse->id : null;
        }

        return $items->transform(function ($row) use ($warehouse_id, $warehouse, $configuration) {
            /** @var Item $row */
            $temp = array_merge($row->getCollectionData(), $row->getDataToItemModal());

            if (isset($temp['name_product_pdf'])) $temp['name_product_pdf'] = null;

            $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
            $purchase_unit_price = number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ",");
            if ($configuration->purchase_apportionment) {
                $apportionment_items_stock = DB::connection('tenant')->table('apportionment_items_stock')->where('item_id', $row->id)
                    ->where('stock_remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->first();
                if ($apportionment_items_stock) {
                    $purchase_unit_price = number_format($apportionment_items_stock->unit_price_apportioned, $configuration->decimal_quantity, ".", ",");
                }
            }
            $data = [
                'id' => $row->id,
                'item_code' => $row->item_code,
                'full_description' => $full_description,
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ","),
                'purchase_unit_price' => $purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'purchase_has_igv' => (bool)$row->purchase_has_igv,
                'has_perception' => (bool)$row->has_perception,
                'lots_enabled' => (bool)$row->lots_enabled,
                'percentage_perception' => $row->percentage_perception,

                'item_unit_types' => $row->item_unit_types->transform(function ($row) {
                    if (is_array($row)) return $row;
                    if (is_object($row)) {
                        /**@var ItemUnitType $row */
                        return $row->getCollectionData();
                    }
                    return $row;
                }),
                'series_enabled' => (bool)$row->series_enabled,

                'purchase_has_isc' => $row->purchase_has_isc,
                'purchase_system_isc_type_id' => $row->purchase_system_isc_type_id,
                'purchase_percentage_isc' => $row->purchase_percentage_isc,

            ];
            foreach ($temp as $k => $v) {
                if (!isset($data[$k])) {
                    $data[$k] = $v;
                }
            }
            return $data;
        });
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @return mixed
     */
    public static function getItemToContract(Request $request = null, $id = 0)
    {
        // $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        /*
            $items = Item::orderBy('description')->whereIsActive()
                // ->with(['warehouses' => function($query) use($warehouse){
                //     return $query->where('warehouse_id', $warehouse->id);
                // }])
                ->get();
*/
        $items_not_services = self::getNotServiceItem($request, $id);
        $items_services = self::getServiceItem($request, $id);
        // $establishment_id = auth()->user()->establishment_id;
        $items = $items_not_services->merge($items_services);

        return self::TransformModalToContract($items);
    }

    /**
     * @param                $items
     * @param Warehouse|null $warehouse
     *
     * @return mixed
     */
    public static function TransformModalToContract($items, Warehouse $warehouse = null)
    {
        $configuration = Configuration::first();
        return $items->transform(function ($row) use ($warehouse, $configuration) {
            $full_description = self::getFullDescriptionToContract($row);
            // $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;
            return [
                'id' => $row->id,
                'full_description' => $full_description,
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ","),
                'purchase_unit_price' => number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ","),
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'is_set' => (bool)$row->is_set,
                'has_igv' => (bool)$row->has_igv,
                'calculate_quantity' => (bool)$row->calculate_quantity,
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
                'warehouses' => collect($row->warehouses)->transform(function ($row) {
                    return [
                        'warehouse_id' => $row->warehouse->id,
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                    ];
                })
            ];
        });
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    public static function getFullDescriptionToContract(Item $item)
    {

        $desc = ($item->internal_id) ? $item->internal_id . ' - ' . $item->description : $item->description;
        $category = ($item->category) ? " - {$item->category->name}" : "";
        $brand = ($item->brand) ? " - {$item->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }

    /**
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getItemToTrasferWithSearch(Request $request = null): \Illuminate\Database\Eloquent\Collection
    {
        $warehouse_id = 0;
        $whereItem = [];
        $whereExtra = [];
        if ($request !== null && $request->has('params')) {
            $params = $request->params;
            $warehouse_id = $params['warehouse_id'];
            $input = $params['input'];
            $search_by_barcode = $params['search_by_barcode'];
        }


        $data = self::getItemToTrasferCollection($warehouse_id);


        if (!empty($input)) {
            $whereItem[] = ['description', 'like', '%' . $input . '%'];
            $whereItem[] = ['internal_id', 'like', '%' . $input . '%'];
            $whereItem[] = ['barcode', '=', $input];
            $whereExtra[] = ['name', 'like', '%' . $input . '%'];
        }

        if (!empty($whereItem)) {
            $data = func_filter_items($data, $input);
            //                $data
            //                    ->selectRaw(
            //                            'match(text_filter) against(? in natural language mode) as score',
            //                            [$params['input']]
            //                        )
            //                        ->whereRaw(
            //                            'match(text_filter) against(? in natural language mode) > 0.0000001',
            //                            [$params['input']]
            //                        );
            //                        ->orderBy('score', 'desc');
            //                });

            //                $data->when($params['input'], function ($query, $search) {
            //                    $query->select('id', 'description', 'text_filter')
            //                        ->selectRaw(
            //                            'match(text_filter) against(? in natural language mode) as score',
            //                            [$search]
            //                        )
            //                        ->whereRaw(
            //                            'match(text_filter) against(? in natural language mode) > 0.0000001',
            //                            [$search]
            //                        )
            //                        ->orderBy('score', 'desc');
            //                });

            //                $items = Item::query()->when($params['input'], function ($query, $search) {
            //                    return $query->select('id', 'text_filter')
            //                        ->selectRaw('match(text_filter) against(? in natural language mode) as score', [$search])
            //                        ->whereRaw('match(text_filter) against(? in natural language mode) > 0.0000001', [$search]);
            //                })->get();
            //
            //                

            //                $data->whereRaw('match(text_filter) against(? in natural language mode) > 0.0000001', [$input]);
            //                });
            //                $items = Item::query()
            //                    ->select('id', 'text_filter')
            //                    ->selectRaw('match(text_filter) against(? in natural language mode) as score')
            //                    ->having('score', '>', 0)
            //
            //                    ->setBindings([$input])
            //                    ->orderBy('score', 'desc')
            //                ->get();

            //                $items = Item::query()
            //                    ->select('id', 'text_filter')
            //                    ->whereRaw('MATCH(text_filter) AGAINST(? IN BOOLEAN MODE)', ['radio%2080'])
            ////                    ->having('score', '>', 0)
            ////
            ////                    ->setBindings([$input])
            ////                    ->orderBy('score', 'desc')
            //                    ->get();
            //
            //                
            //                $data->whereRaw('match(text_filter)
            //                    against(? in natural language mode) > 0.0000001
            //                ', [$input]);

            //                foreach ($whereItem as $index => $wItem) {
            //                    if ($index < 1) {
            //                        $data->Where([$wItem]);
            //                    } else {
            //                        $data->orWhere([$wItem]);
            //                    }
            //                }
            //
            //                if ( !empty($whereExtra)) {
            //                    $data
            //                        ->orWhereHas('brand', function ($query) use ($whereExtra) {
            //                            $query->where($whereExtra);
            //                        })
            //                        ->orWhereHas('category', function ($query) use ($whereExtra) {
            //                            $query->where($whereExtra);
            //                        });
            //                }
            //                $data->OrWhereJsonContains('attributes', ['value' => $input]);
            // Limita la cantidad de productos en la busqueda a 250, puede modificarse en el .env con NUMBER_SEARCH_ITEMS
            $data->take(\Config('extra.number_items_in_search'));
        } else {
            // Inicia con 20 productos, puede añadirse en el env la variable NUMBER_ITEMS
            $data->take(\Config('extra.number_items_at_start'));
        }

        $data->whereIsActive();

        return self::getItemToTrasferModal($data, $warehouse_id);
    }

    /**
     * @param int $warehouse_id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getItemToTrasferWithoutSearch($warehouse_id = 0): \Illuminate\Database\Eloquent\Collection
    {
        $data = self::getItemToTrasferCollection($warehouse_id)->whereIsActive();

        // Inicia con 20 productos, puede añadirse en el env la variable NUMBER_ITEMS
        $data->take(\Config('extra.number_items_at_start'));
        return  self::getItemToTrasferModal($data, $warehouse_id);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|null $data
     * @param int $warehouse_id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getItemToTrasferModal(
        \Illuminate\Database\Eloquent\Builder $data = null,
        int $warehouse_id = 0
    ): \Illuminate\Database\Eloquent\Collection {
        return $data
            ->get()
            ->transform(function ($row) use ($warehouse_id) {
                /** @var \App\Models\Tenant\Item $row */
                $lots = $row->item_lots->where('has_sale', false)->where('warehouse_id', $warehouse_id)->transform(function ($row1) {
                    return [
                        'id' => $row1->id,
                        'series' => $row1->series,
                        'date' => $row1->date,
                        'item_id' => $row1->item_id,
                        'warehouse_id' => $row1->warehouse_id,
                        'has_sale' => (bool)$row1->has_sale,
                        'lot_code' => ($row1->item_loteable_type) ? (isset($row1->item_loteable->lot_code) ? $row1->item_loteable->lot_code : null) : null
                    ];
                })->values();
                $old = [
                    'lots' => $lots,
                ];
                $data = $row->getDataToItemModal(
                    Warehouse::find($warehouse_id),
                    false,
                    true

                );
                return array_merge($data, $old);
            });
    }

    /**
     * Realiza las busquedas para transferencia de items
     *
     * Extraido de modules/Inventory/Traits/InventoryTrait.php  optionsItemWareHousexId
     *
     * @param int $warehouse_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getItemToTrasferCollection(
        $warehouse_id = 0
    ): \Illuminate\Database\Eloquent\Builder {

        return Item::query()
            ->with('item_lots', 'warehouses')
            ->whereHas('warehouses', function ($query) use ($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            })
            ->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']])
            ->whereNotIsSet();
    }

    public static function getItemsToPackageZone(Request $request = null, $id = 0)
    {
        $items_not_services = self::getNotServiceItem($request, $id);
        // $items_services = self::getServiceItem($request, $id);
        // $data = self::TransformToModal($items_not_services->merge($items_services));
        $data = self::TransformToModal($items_not_services);
        return $data->transform(function ($row) {
            $data = $row;
            $data['color'] = CatColorsItem::wherein('id', $row['colors'])->get();
            return $data;
        });
    }
}
