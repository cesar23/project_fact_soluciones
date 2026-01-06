<?php

namespace App\Models\Tenant;

use Exception;
use Carbon\Carbon;
use Modules\Item\Models\Brand;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\Category;
use Illuminate\Support\Collection;
use Modules\Account\Models\Account;
use App\Models\Tenant\ItemWarehouse;
use Modules\Item\Models\WebPlatform;
use Modules\Digemid\Models\CatDigemid;
use Modules\Item\Models\ItemLotsGroup;
use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\Models\Warehouse;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\Tenant\Catalogs\UnitType;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\CatColorsItem;
use App\Models\Tenant\Catalogs\CatItemStatus;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\CatItemMoldCavity;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\CatItemMoldProperty;
use App\Models\Tenant\Catalogs\CatItemUnitBusiness;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Tenant\Catalogs\CatItemProductFamily;
use Modules\Inventory\Helpers\InventoryValuedKardex;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Tenant\Catalogs\CatItemPackageMeasurement;
use Illuminate\Support\Facades\DB;
use Modules\Ecommerce\Models\WoocommerceItem;
use Modules\Inventory\Models\GuideItem;
use Modules\Item\Models\ItemAttribute;
use Modules\Item\Models\ItemProperty;

/**
 * Class Item
 *
 * @package App\Models\Tenant
 * @mixin ModelTenant
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item pharmacy()
 * @method static Builder|Item query()
 * @method static Builder|Item whereFilterValuedKardex($params)
 * @method static Builder|Item whereHasInternalId()
 * @method static Builder|Item whereIsActive()
 * @method static Builder|Item whereIsNotActive()
 * @method static Builder|Item whereIsSet()
 * @method static Builder|Item whereNotIsSet()
 * @method static Builder|Item ForProduction()
 * @method static Builder|Item whereNotService()
 * @method static Builder|Item whereService()
 * @method static Builder|Item whereTypeUser()
 * @method static Builder|Item ProductSupply()
 * @method static Builder|Item ProductEnded()
 * @method static Builder|Item whereWarehouse()
 * @property \Illuminate\Database\Eloquent\Collection|ItemMovementRelExtra[] $item_movement_rel_extras
 * @property Account $account
 * @property Brand $brand
 * @property Bool $is_for_production
 * @property CatDigemid|null $cat_digemid
 * @property Category $category
 * @property CurrencyType $currency_type
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\DispatchItem[] $dispatch_items
 * @property int|null $dispatch_items_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\DocumentItem[] $document_items
 * @property int|null $document_items_count
 * @property mixed $attributes
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\ItemImage[] $images
 * @property int|null $images_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\InventoryKardex[] $inventory_kardex
 * @property int|null $inventory_kardex_count
 * @property \Illuminate\Database\Eloquent\Collection|ItemLot[] $item_lots
 * @property int|null $item_lots_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\ItemMovementRelExtra[] $item_movement_rel_extra
 * @property int|null $item_movement_rel_extra_count
 * @property \App\Models\Tenant\ItemType $item_type
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\ItemUnitType[] $item_unit_types
 * @property int|null $item_unit_types_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\Kardex[] $kardex
 * @property int|null $kardex_count
 * @property \Illuminate\Database\Eloquent\Collection|ItemLot[] $lots
 * @property int|null $lots_count
 * @property \Illuminate\Database\Eloquent\Collection|ItemLotsGroup[] $lots_group
 * @property int|null $lots_group_count
 * @property AffectationIgvType $purchase_affectation_igv_type
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\PurchaseItem[] $purchase_item
 * @property int|null $purchase_item_count
 * @property AffectationIgvType $sale_affectation_igv_type
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\SaleNoteItem[] $sale_note_items
 * @property int|null $sale_note_items_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\ItemSet[] $sets
 * @property int|null $sets_count
 * @property SystemIscType $system_isc_type
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\ItemTag[] $tags
 * @property int|null $tags_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\TechnicalServiceItem[] $technical_service_item
 * @property int|null $technical_service_item_count
 * @property UnitType $unit_type
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\ItemWarehousePrice[] $warehousePrices
 * @property int|null $warehouse_prices_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\ItemWarehouse[] $warehouses
 * @property int|null $warehouses_count
 * @property WebPlatform $web_platform
 * @method static Builder|Item whereFilterValuedKardexFormatSunat($params)
 * @property \Illuminate\Database\Eloquent\Collection|ItemSupply[] $supplies
 * @property \Illuminate\Database\Eloquent\Collection|ItemSupply[] supplies_items

 */
class Item extends ModelTenant
{
    protected $with = ['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags', 'item_lots'];

    public const SERVICE_UNIT_TYPE = 'ZZ';

    protected $fillable = [
        'attribute_enabled',
        'is_input',
        'can_edit_price',
        'location',
        'video_url',
        'is_food_dealer',
        'id_cupones',
        'genero',
        'meter',
        'has_sizes',
        'info_link',
        'warehouse_id',
        'name',
        'second_name',
        'description',
        'model',
        'technical_specifications',
        'item_type_id',
        'internal_id',
        'item_code',
        'item_code_gs1',
        'unit_type_id',
        'currency_type_id',
        'sale_unit_price',
        'purchase_unit_price',
        'has_isc',
        'system_isc_type_id',
        'percentage_isc',
        'suggested_price',

        'sale_affectation_igv_type_id',
        'purchase_affectation_igv_type_id',
        'calculate_quantity',
        'has_igv',

        'stock',
        'stock_min',
        'percentage_of_profit',

        'attributes',
        'has_perception',
        'percentage_perception',
        'image',
        'image_medium',
        'image_small',

        'account_id',
        'amount_plastic_bag_taxes',
        'date_of_due',
        'is_set',
        'sale_unit_price_set',
        'apply_store',
        'apply_restaurant',
        'brand_id',
        'category_id',
        'lot_code',
        'lots_enabled',
        'active',
        'line',
        'series_enabled',
        'purchase_has_igv',
        'web_platform_id',
        'has_plastic_bag_taxes',
        'barcode',
        'sanitary',
        'cod_digemid',
        'is_for_production',

        'purchase_percentage_isc',
        'purchase_system_isc_type_id',
        'purchase_has_isc',
        'subject_to_detraction',
        'exchange_points',
        'quantity_of_points',
        'factory_code',
        'restrict_sale_cpe',
        'frequent',
        'descuento_store',
        'limit_sale_daily'

        // 'warehouse_id'
    ];

    protected $casts = [
        'attribute_enabled' => 'boolean',
        'is_input' => 'boolean',
        'is_food_dealer' => 'boolean',
        'can_edit_price' => 'boolean',
        'meter' => 'float',
        'date_of_due' => 'date',
        'is_for_production' => 'boolean',
        'purchase_has_isc' => 'boolean',
        'has_igv' => 'boolean',
        'purchase_has_igv' => 'boolean',
        'subject_to_detraction' => 'boolean',
        'sale_unit_price' => 'float',
        'purchase_unit_price' => 'float',
        'favorite' => 'boolean',
        'exchange_points' => 'boolean',
        'quantity_of_points' => 'float',
        'restrict_sale_cpe' => 'boolean',
    ];

    /**
     * @return string
     */
    public function getSanitary()
    {
        return $this->sanitary;
    }

    /**
     * @param string $sanitary
     *
     * @return Item
     */
    public function setSanitary($sanitary)
    {
        $this->sanitary = $sanitary;
        return $this;
    }

    public function attribute()
    {
        return $this->hasMany(ItemProperty::class);
    }

    /**
     * @return string
     */
    public function getCodDigemid()
    {
        return $this->cod_digemid;
    }

    /**
     * @param string $cod_digemid
     *
     * @return Item
     */
    public function setCodDigemid($cod_digemid)
    {
        $this->cod_digemid = $cod_digemid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return Item
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /*protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', 1);
        });
    }*/

    public function getAttributesAttribute($value)
    {
        return (is_null($value)) ? null : json_decode($value);
    }

    public function setAttributesAttribute($value)
    {
        $this->attributes['attributes'] = (is_null($value)) ? null : json_encode($value);
    }

    /**
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo
     */
    public function item_type()
    {
        return $this->belongsTo(ItemType::class);
    }

    /**
     * @return BelongsTo
     */
    public function unit_type()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }
    public function production_order_item()
    {
        return $this->hasMany(ProductionOrderItem::class, 'item_id');
    }
    
    /**
     * @return BelongsTo
     */
    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function system_isc_type()
    {
        return $this->belongsTo(SystemIscType::class, 'system_isc_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function purchase_system_isc_type()
    {
        return $this->belongsTo(SystemIscType::class, 'purchase_system_isc_type_id');
    }

    /**
     * @return HasMany
     */
    public function kardex()
    {
        return $this->hasMany(Kardex::class);
    }

    public function inventory_kardex()
    {
        return $this->hasMany(InventoryKardex::class);
    }

    public function cat_digemid()
    {
        return $this->hasOne(CatDigemid::class);
    }

    public function purchase_item()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function dispatch_items()
    {
        return $this->hasMany(DispatchItem::class);
    }

    public function item_attributes()
    {
        return $this->hasMany(ItemAttribute::class, 'item_id','id');
    }

    /**
     * @return BelongsTo
     */
    public function sale_affectation_igv_type()
    {
        return $this->belongsTo(AffectationIgvType::class, 'sale_affectation_igv_type_id');
    }

    public function purchase_affectation_igv_type()
    {
        return $this->belongsTo(AffectationIgvType::class, 'purchase_affectation_igv_type_id');
    }

    public function scopeWhereWarehouse($query)
    {
        $configuration = Configuration::getConfig();

        if ($configuration->list_items_by_warehouse == true) {
            // $user = auth()->user() ?? auth('api')->user();
            $establishment_id = auth()->user()->establishment_id;
        } else {

            $establishment = $configuration->getMainWarehouse();
            $establishment_id = $establishment->id;
        }

        $warehouse = DB::connection('tenant')->table('warehouses')->select('id')->where('establishment_id', $establishment_id)->first();
        if ($warehouse) {
            return $query->whereHas('warehouses', function ($query) use ($warehouse) {
                $query->where('warehouse_id', $warehouse->id)
                    ->orWhere('shared', 1);
            })->orWhere('unit_type_id', 'ZZ');
        }
        return $query;
    }

    public function scopeWhereTypeUser($query)
    {
        $user = auth()->user();
        return ($user->type == 'seller') ? $this->scopeWhereWarehouse($query) : null;
    }

    public function scopeWhereNotIsSet($query)
    {
        return $query->where('is_set', false);
    }
    public function scopeWhereIsNotService($query)
    {
        return $query->where('unit_type_id', '!=', 'ZZ');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereIsActive($query)
    {
        return $query->where('items.active', true);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereIsSet($query)
    {
        return $query->where('is_set', true);
    }


    /**
     * @param Builder $query
     *
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopePharmacy($query)
    {
        return $query
            ->whereNotNull('items.cod_digemid')
            ->select('items.*')
            ->join('cat_digemid', 'cat_digemid.item_id', '=', 'items.id');
    }

    /**
     * @return int
     */
    public function getStockByWarehouse()
    {
        $configuration = Configuration::getConfig();
        $list_items_by_warehouse = $configuration->list_items_by_warehouse;
        $establishment_id = null;
        if ($list_items_by_warehouse) {
            $user = auth('api')->user() ?? auth()->user();
            if ($user) {
                $establishment_id = $user->establishment_id;
            }
        } else {
            $establishment = $configuration->getMainWarehouse();
            $establishment_id = $establishment->id;
        }
        if ($establishment_id) {
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            if ($warehouse) {
                $item_warehouse = $this->warehouses->where('warehouse_id', $warehouse->id)->first();
                return ($item_warehouse) ? $item_warehouse->stock : 0;
            }
            return 0;
        }
        return 0;
    }

    /**
     * @return HasMany
     */
    public function warehouses()
    {
        return $this->hasMany(ItemWarehouse::class)->with('warehouse');
    }

    public function item_warehouse()
    {
        return $this->hasMany(ItemWarehouse::class);
    }
    public function item_warehouse_without_warehouse()
    {
        return $this->hasMany(ItemWarehouse::class)->without('warehouse');
    }

    /**
     * @return HasMany
     */
    public function item_unit_types()
    {
        return $this->hasMany(ItemUnitType::class);
    }

    /**
     * @return HasMany
     */
    public function tags()
    {
        return $this->hasMany(ItemTag::class);
    }

    /**
     * @return BelongsToMany
     */
    public function labelColor()
    {
        return $this->hasOne(ItemLabelColor::class);
    }

    /**
     * @return HasMany
     */
    public function sets()
    {
        return $this->hasMany(ItemSet::class);
    }

    public function sets_individual()
    {
        return $this->hasMany(ItemSet::class, 'individual_item_id');
    }

    /**
     * @return BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class)->withDefault([
            'id' => '',
            'name' => ''
        ]);
    }

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault([
            'id' => '',
            'name' => ''
        ]);
    }

    /**
     * @return HasMany
     */
    public function item_lots()
    {
        return $this->hasMany(ItemLot::class, 'item_id');
    }

    /**
     * @return MorphMany
     */
    public function lots()
    {
        return $this->morphMany(ItemLot::class, 'item_loteable');
    }

    /**
     * @return HasMany
     */
    public  function images()
    {
        return $this->hasMany(ItemImage::class, 'item_id');
    }
    public function scopeHasStateInLotGroup($query)
    {

        return $query->where(function ($query) {
            $query->whereHas('lots_group', function ($q) {
                $q->where('state_id', 1)
                    ->orWhereNull('state_id');
            })->orWhereDoesntHave('lots_group');
        });
    }
    /**
     * @return HasMany
     */
    public function lots_group()
    {
        return $this->hasMany(ItemLotsGroup::class, 'item_id');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereNotService($query)
    {
        return $query->where('unit_type_id', '!=', 'ZZ');
    }
    public function scopeLoteApprove($query)
    {
        // return $query->where('unit_type_id', '!=', 'ZZ');
    }
    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereService($query)
    {
        return $query->where('unit_type_id', 'ZZ');
    }

    /**
     * @return HasMany
     */
    public  function document_items()
    {
        return $this->hasMany(DocumentItem::class, 'item_id');
    }

    /**
     * @return HasMany
     */
    public  function sale_note_items()
    {
        return $this->hasMany(SaleNoteItem::class, 'item_id');
    }

    /**
     * @param Builder                               $query
     * @param                                       $params
     *
     * @return Builder
     */
    public function scopeWhereFilterValuedKardex(Builder $query, $params)
    {

        /*
        $query->OrWhereHas('document_items', function ($q) use ($params) {
            $q->whereHas('document', function ($q1) use ($params) {
                $q1->whereStateTypeAccepted()
                    ->whereTypeUser()
                    ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                if ($params->establishment_id) {
                    $q1->where('establishment_id', $params->establishment_id);
                }
            });
        });
        $query->OrWhereHas('sale_note_items', function ($q) use ($params) {
            $q->whereHas('sale_note', function ($q1) use ($params) {
                $q1->whereStateTypeAccepted()
                    ->whereNotChanged()
                    ->whereTypeUser()
                    ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                if ($params->establishment_id) {
                    $q1->where('establishment_id', $params->establishment_id);
                }
            });

        });

        return $query;
        */
        // No selecciona corectamente los establecimeintos.
        if (property_exists($params, 'establishment_id') && $params->establishment_id) {

            // if ($params->establishment_id) {
            return $query->with([
                'document_items' => function ($q) use ($params) {
                    $q->whereHas('document', function ($q) use ($params) {
                        $q->whereStateTypeAccepted()
                            ->whereTypeUser()
                            ->whereBetween('date_of_issue', [$params->date_start, $params->date_end])
                            ->where('establishment_id', $params->establishment_id);
                    });
                },
                'sale_note_items' => function ($q) use ($params) {
                    $q->whereHas('sale_note', function ($q) use ($params) {
                        $q->whereStateTypeAccepted()
                            ->whereNotChanged()
                            ->whereTypeUser()
                            ->whereBetween('date_of_issue', [$params->date_start, $params->date_end])
                            ->where('establishment_id', $params->establishment_id);
                    });
                }
            ]);
        }

        return $query->with([
            'document_items' => function ($q) use ($params) {
                $q->whereHas('document', function ($q) use ($params) {
                    $q->whereStateTypeAccepted()
                        ->whereTypeUser()
                        ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                });
            },
            'sale_note_items' => function ($q) use ($params) {
                $q->whereHas('sale_note', function ($q) use ($params) {
                    $q->whereStateTypeAccepted()
                        ->whereNotChanged()
                        ->whereTypeUser()
                        ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                });
            }
        ]);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereIsNotActive($query)
    {
        return $query->where('items.active', false);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereHasInternalId($query)
    {
        return $query->where('internal_id', '!=', null);
    }

    /**
     * @return BelongsTo
     */
    public function web_platform()
    {
        return $this->belongsTo(WebPlatform::class);
    }

    /**
     * @return HasMany
     */
    public function warehousePrices()
    {
        return $this->hasMany(ItemWarehousePrice::class, 'item_id')->select('id', 'item_id', 'price', 'warehouse_id');
    }

    public function clientTypePrices()
    {
        return $this->hasMany(PersonTypePrice::class, 'item_id')->select('id', 'item_id', 'price', 'person_type_id');
    }

    public static function getSaleUnitPriceByWarehouse(Item $item, int $warehouseId): string
    {
        $configuration = Configuration::getConfig();
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
     * Devuelve la esuctura de item para los select correspondientes.
     *
     * @param \App\Models\Tenant\Warehouse|Warehouse $warehouse
     * @param false                                  $extended
     *
     * @return array
     */
    public function getFullDescription($warehouse, $extended = false, $configuration = null)
    {
        if ($configuration == null) {
            $configuration = Configuration::getConfig();
        }
        $description = $this->description;

        if ($configuration->show_internal_id_list) {
            $desc = ($this->internal_id) ? $this->internal_id . ' - ' . $description : $description;
        } else {
            $desc = $description;
        }
        $category = ($this->category) ? "{$this->category->name}" : '';
        $brand = ($this->brand) ? "{$this->brand->name}" : '';

        if ($this->unit_type_id != 'ZZ') {
            if (isset($this['stock'])) {
                $warehouse_stock = number_format($this['stock'], 2);
            } else {
                $warehouse_stock = ($this->warehouses && $warehouse)
                    ?
                    number_format($this->warehouses->where('warehouse_id', $warehouse->id)->first()->stock, 2)
                    :
                    0;
            }
            $stock = ($this->warehouses && $warehouse) ? "{$warehouse_stock}" : '';
        } else {
            $stock = '';
        }


        if ($configuration->show_models && $this->model) {
            $desc .= " - {$this->model}";
        }
        if ($configuration->show_brands && $brand) {
            $desc .= " - {$brand}";
        }
        if ($configuration->show_categories && $category) {
            $desc .= " - {$category}";
        }
        if ($this->location) {
            $desc .= " - {$this->location}";
        }

        return [
            'full_description'      => $desc,
            'brand'                 => $brand,
            'category'              => $category,
            'stock'                 => $stock,
            'warehouse_description' => $warehouse->description,
        ];
    }

    /**
     * Devuelve la relacion con el almacen dado
     * @param int $warehouse_id
     *
     * @return HasMany
     */
    public function getCurrentItemWarehouse($warehouse_id)
    {
        return $this->warehouses()->where('warehouse_id', $warehouse_id);
    }
    public function guide_item()
    {
        return $this->hasMany(GuideItem::class);
    }
    private function getLotsBySerie($warehouse, $series,  $search_item_by_series)
    {
        $lots = [];


        if ($search_item_by_series) {
            $lots = $this->item_lots()->where('has_sale', false)
                ->where('warehouse_id', $warehouse->id)
                ->where('series', $series)
                ->take(1)
                ->get();

            // 
        }

        return $lots;
    }

    /**
     * Devuelve un estandar de estructura para items.
     * Es utilizado en :
     * app/Http/Controllers/Tenant/DocumentController.php
     * modules/Order/Http/Controllers/OrderNoteController.php
     *
     * @param \App\Models\Tenant\Warehouse|Warehouse|null $warehouse
     * @param false                                       $with_lots_has_sale
     * @param false                                       $extended_description
     *
     * @return array
     */
    public function getDataToItemModal($warehouse = null, $with_lots_has_sale = false, $extended_description = false, $series = null, $search_item_by_series = false, $aditional_data = true, $type_request = null)
    {
        //obtener el request 
        $user = auth()->user() ?? auth('api')->user();
        $configuration = Configuration::getConfig();
        $search_item_by_series = $configuration->isSearchItemBySeries();
        $search_by_series_and_all = $configuration->search_by_series_and_all;
        $establishment_id = null;
        if ($warehouse == null) {
            $establishment_id = $user->establishment_id;
            $list_items_by_warehouse = $configuration->list_items_by_warehouse;
            if ($list_items_by_warehouse) {

                $establishment_id = $user->establishment_id;
            } else {
                $establishment = $configuration->getMainWarehouse();
                $establishment_id = $establishment->id;
            }
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        }
        $detail = $this->getFullDescription($warehouse, $extended_description);
        $realtion_item_unit_types = $this->item_unit_types;
        $lots_grp = $this->lots_group;
        // $this->restoreStockSizes();
        $lots = $this->getLotsBySerie($warehouse, $series, $search_item_by_series);
        $blank = [];
        $currentColors = collect($blank);
        $ItemUnitsPerPackage = collect($blank);
        $ItemMoldProperty = collect($blank);
        $ItemProductFamily = collect($blank);
        $ItemMoldCavity = collect($blank);
        $ItemPackageMeasurement = collect($blank);
        $ItemStatus = collect($blank);
        $ItemSize = collect($blank);
        $ItemUnitBusiness = collect($blank);
        if ($aditional_data === true) {
            $currentColors = $this->getItemColor()->transform(function ($row) {
                return $row->cat_colors_item_id;
            });
            $ItemUnitsPerPackage = $this->getItemUnitsPerPackage()->transform(function ($row) {
                return $row->cat_item_units_per_package_id;
            });
            $ItemMoldProperty = $this->getItemMoldProperty()->transform(function ($row) {
                return $row->cat_item_mold_properties_id;
            });


            $ItemProductFamily = $this->getItemProductFamily()->transform(function ($row) {
                return $row->cat_item_product_family_id;
            });
            $ItemMoldCavity = $this->getItemMoldCavity()->transform(function ($row) {
                return $row->cat_item_mold_cavities_id;
            });
            $ItemPackageMeasurement = $this->getItemPackageMeasurement()->transform(function ($row) {
                return $row->cat_item_package_measurements_id;
            });
            $ItemStatus = $this->getItemStatus()->transform(function ($row) {
                return $row->cat_item_status_id;
            });
            $ItemUnitBusiness = $this->getItemUnitBusiness()->transform(function ($row) {
                return $row->cat_item_unit_business_id;
            });

            $ItemSize = $this->getItemSize()->transform(function ($row) {
                return $row->cat_item_size_id;
            });
        }

        $put_lots = $search_item_by_series && count($lots) == 0;
        if ($with_lots_has_sale == true || $put_lots || $search_by_series_and_all) {
            $lots = $this->item_lots->where('has_sale', false)->transform(function ($row) {
                return [
                    'id'           => $row->id,
                    'series'       => $row->series,
                    'date'         => $row->date,
                    'item_id'      => $row->item_id,
                    'warehouse_id' => $row->warehouse_id,
                    'has_sale'     => (bool)$row->has_sale,
                    'lot_code'     => ($row->item_loteable_type)
                        ?
                        (isset($row->item_loteable->lot_code)
                            ?
                            $row->item_loteable->lot_code
                            :
                            null)
                        :
                        null,
                ];
            })->values();
        }
        $stock = $detail['stock'];

        // Obtiene el stock basado el en almacen itemwarehouse
        $stockItemWarehouse = $this->getCurrentItemWarehouse($warehouse->id)->first();
        if (is_object($stockItemWarehouse)) {
            $stock = $stockItemWarehouse->stock;
        }
        // $stockPerCategory = ItemMovement::getStockByCategory($this->id, $establishment_id);
        $stockPerCategory = [];
        $currency = $this->currency_type;
        if (empty($currency)) {
            $currency = new CurrencyType();
        }

        $purchase_unit_price = $this->purchase_unit_price;
        $purchase_unit_value = $this->purchase_unit_price;
        $observation_apportionment = null;
        if($configuration->purchase_apportionment){
            $apportionment_items_stock = DB::connection('tenant')->table('apportionment_items_stock')->where('item_id', $this->id)
            ->where('stock_remaining', '>', 0)
            ->orderBy('created_at', 'asc')
            ->first();
            if(!empty($apportionment_items_stock)){
                $purchase_unit_price = $apportionment_items_stock->unit_price_apportioned;
                $observation_apportionment = $apportionment_items_stock->observation;
            }
        }
        if ($this->purchase_has_igv) {
            $purchase_unit_value = round($purchase_unit_price / 1.18, 8);
        } else {
            $purchase_unit_price = $purchase_unit_value * 1.18;
        }
        $disponibilidad = null;
        $header = null;
        $projectItem = QuotationProjectItem::where('quotation_item_id', $this->id)->first();
        if ($projectItem) {
            $disponibilidad = $projectItem->disponibility;
            $header = $projectItem->header;
        }
        $itemBonus =  $this->bonusses;
        if (!empty($itemBonus)) {
            $itemBonus = $itemBonus->transform(function (ItemBonus $row) {
                return $row->getCollectionData();
            });
        }


        $sale_unit_price = self::getSaleUnitPriceByWarehouse($this, $warehouse->id);
        $data = [
            'attribute_enabled' => (bool)$this->attribute_enabled,
            'observation_apportionment' => $observation_apportionment,
            'has_perception' => (bool)$this->has_perception,
            'percentage_perception' => $this->percentage_perception,
            'can_edit_price' => (bool)$this->can_edit_price,
            'video_url' => $this->video_url,
            'meter' => $this->meter,
            'bonus_items' => $itemBonus,
            'disponibilidad' => $disponibilidad,
            'header' => $header,
            'id'                               => $this->id,
            'item_code'                    => $this->item_code,
            'full_description'                 => $detail['full_description'],
            'model'                            => $this->model,
            'brand'                            => $detail['brand'],
            'brand_id'                         => $this->brand_id,
            'category_id'                      => $this->category_id,
            'text_filter' => $this->text_filter,
            'stock_by_extra'                            =>  $stockPerCategory,
            'warehouse_description'            => $detail['warehouse_description'],
            'extra'                         => collect([
                'colors' => null,
                'CatItemUnitsPerPackage' => null,
                'CatItemMoldProperty' => null,
                'CatItemProductFamily' => null,
                'CatItemMoldCavity' => null,
                'CatItemPackageMeasurement' => null,
                'CatItemStatus' => null,
                'CatItemSize' => null,
                'CatItemUnitBusiness' => null,
            ]),
            'category'                         => $detail['category'],
            'stock'                            => $stock,
            'has_bonus_item' => $this->hasBonus(),
            'internal_id'                      => $this->internal_id,
            'description'                      => $this->description,
            'info_link'                       => $this->info_link,
            'id_cupones'                        => $this->id_cupones,
            // 'genero'                            => $this->genero
            'currency_type_id'                 => $this->currency_type_id,
            'currency_type_symbol'             => $currency->symbol,
            'has_igv'                          => (bool)$this->has_igv,
            //number_format($row->total, $configuration->decimal_quantity, ".", ""),
            'sale_unit_price'                  => $sale_unit_price,
            'purchase_has_igv'                 => $this->purchase_has_igv,
            'purchase_unit_value'              => $purchase_unit_value,
            'purchase_unit_price'              => $purchase_unit_price,
            'unit_type_id'                     => $this->unit_type_id,
            'unit_type_symbol'                 => optional($this->unit_type)->symbol ?? null,
            'original_unit_type_id'            => $this->unit_type_id,
            'sale_affectation_igv_type'     => $this->sale_affectation_igv_type,
            'sale_affectation_igv_type_id'     => $this->sale_affectation_igv_type_id,
            'purchase_affectation_igv_type_id' => $this->purchase_affectation_igv_type_id,
            'calculate_quantity'               => (bool)$this->calculate_quantity,
            'has_plastic_bag_taxes'            => (bool)$this->has_plastic_bag_taxes,
            'amount_plastic_bag_taxes'         => $this->amount_plastic_bag_taxes,
            'colors' => $currentColors,
            'label_color' => $this->labelColor && $configuration->label_item_color ? [
                'id' => $this->labelColor->id,
                'description' => $this->labelColor->labelColor->description,
                'color' => $this->labelColor->labelColor->color,
            ] : null,
            'CatItemUnitsPerPackage' => $ItemUnitsPerPackage,
            'CatItemMoldProperty' => $ItemMoldProperty,
            'CatItemProductFamily' => $ItemProductFamily,
            'CatItemMoldCavity' => $ItemMoldCavity,
            'CatItemPackageMeasurement' => $ItemPackageMeasurement,
            'CatItemStatus' => $ItemStatus,
            'CatItemSize' => $ItemSize,
            'CatItemUnitBusiness' => $ItemUnitBusiness,
            'item_unit_types'                  => $this->item_unit_types->transform(function ($item_unit_types) use ($configuration) {
                if (is_array($item_unit_types)) {
                    return $item_unit_types;
                }
                return [
                    'id'            => $item_unit_types->id,
                    'description'   => "{$item_unit_types->description}",
                    'item_id'       => $item_unit_types->item_id,
                    'unit_type_id'  => $item_unit_types->unit_type_id,
                    'quantity_unit' => $item_unit_types->quantity_unit,
                    'price1'        => number_format($item_unit_types->price1, $configuration->decimal_quantity, '.', ''),
                    'price2'        => number_format($item_unit_types->price2, $configuration->decimal_quantity, '.', ''),
                    'price3'        => number_format($item_unit_types->price3, $configuration->decimal_quantity, '.', ''),
                    'price_default' => $item_unit_types->price_default,
                    'factor_default' => (bool) $item_unit_types->factor_default,
                    'barcode' => $item_unit_types->barcode,
                    'range_min' => $item_unit_types->range_min,
                    'range_max' => $item_unit_types->range_max,
                    'warehouse_id' => $item_unit_types->warehouse_id,
                ];
            }),
            'warehouses' => collect($this->warehouses)->transform(function ($warehouses) use ($warehouse) {
                $price = self::getSaleUnitPriceByWarehouse($this, $warehouses->warehouse_id);
                return [
                    'price' => $price,
                    'warehouse_description' => $warehouses->warehouse->description,
                    'stock'                 => (!empty($warehouses->stock)) ? $warehouses->stock : 0,
                    'warehouse_id'          => $warehouses->warehouse_id,
                    'checked'               => ($warehouses->warehouse_id == $warehouse->id) ? true : false,
                ];
            }),
            // se listaran atributos necesarios en pdf de otra forma
            'attributes'     => $this->getAttributesAttribute($this->attributes['attributes']),
            'lots_group'     => collect($lots_grp)->transform(function ($lots_group) {
                return [
                    'id'          => $lots_group->id,
                    'code'        => $lots_group->code,
                    'quantity'    => $lots_group->quantity,
                    'date_of_due' => $lots_group->date_of_due,
                    'checked'     => false,
                    'compromise_quantity' => 0,
                    'warehouse_id' => $lots_group->warehouse_id,
                    'warehouse' => $lots_group->warehouse_id ? $lots_group->warehouse->description : '',
                ];
            }),
            'lots'           => $lots,
            'lots_enabled'   => (bool)$this->lots_enabled,
            'series_enabled' => (bool)$this->series_enabled,
            'is_set'         => (bool)$this->is_set,
            'item_customer_prices' => $this->clientTypePrices->transform(function ($row) {
                //si row es un array transformalo a objeto
                if (is_array($row)) {
                    return $row;
                }

                return [
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'person_type_id' => $row->person_type_id,
                    'price' => $row->price,
                    'description' => $row->person_type->description,
                ];
            }),
            'lot_code'    => $this->lot_code,
            'date_of_due' => $this->date_of_due,
            'barcode'     => $this->barcode,
            'change_free_affectation_igv'     => false,
            'original_affectation_igv_type_id'     => $this->sale_affectation_igv_type_id,
            'has_sizes' => (bool)$this->has_sizes,
            'sizes' => $this->sizes,
            'image_url' => ($this->image !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image)
                : asset("/logo/{$this->image}"),
            'image_url_medium' => ($this->image_medium !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image_medium)
                : asset("/logo/{$this->image_medium}"),
            'has_isc' => (bool)$this->has_isc,
            'system_isc_type_id' => $this->system_isc_type_id,
            'percentage_isc' => $this->percentage_isc,
            'is_for_production' => $this->isIsForProduction(),
            'subject_to_detraction' => $this->subject_to_detraction,
            'exchange_points' => $this->exchange_points,
            'quantity_of_points' => $this->quantity_of_points,
            'exchanged_for_points' => false, //para determinar si desea canjear el producto
            'used_points_for_exchange' => null, //total de puntos
            'factory_code' => $this->factory_code,
            'restrict_sale_cpe' => $this->restrict_sale_cpe,
            'payment_conditions' => $this->payment_conditions,
            'set_items' => $this->getSetItems() ? $this->getSetItems()->transform(function ($row) {
                return [
                    'item_id' => $row->individual_item_id,
                    'internal_id' => $row->individual_item->internal_id ?? '-',
                    'quantity' => $row->quantity,
                ];
            }) : [],

        ];
        // El nombre de producto, por defecto, sera la misma descripcion.
        $data['name_product_pdf'] = "<p>" . $data['description'] . "</p>";

        return $data;
    }
    public function getDataToItemModalLite($warehouse = null, $with_lots_has_sale = false, $extended_description = false, $series = null, $search_item_by_series = false, $aditional_data = true, $type_request = null)
    {
        //obtener el request 
        $user = auth()->user() ?? auth('api')->user();

        $establishment_id = null;
        if ($warehouse == null) {
            $establishment_id = auth()->user()->establishment_id;
            $configuration = Configuration::first();
            $list_items_by_warehouse = $configuration->list_items_by_warehouse;
            if ($list_items_by_warehouse) {

                $establishment_id = $user->establishment_id;
            } else {
                $establishment = $configuration->getMainWarehouse();
                $establishment_id = $establishment->id;
            }
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        }
        $detail = $this->getFullDescription($warehouse, $extended_description);




        $stock = $detail['stock'];


        $stockPerCategory = [];
        $currency = $this->currency_type;
        if (empty($currency)) {
            $currency = new CurrencyType();
        }

        $purchase_unit_price = $this->purchase_unit_price;
        $purchase_unit_value = $this->purchase_unit_price;
        if ($this->purchase_has_igv) {
            $purchase_unit_value = round($purchase_unit_price / 1.18, 8);
        } else {
            $purchase_unit_price = $purchase_unit_value * 1.18;
        }
        $disponibilidad = null;
        $header = null;




        $data = [
            'can_edit_price' => (bool)$this->can_edit_price,
            'video_url' => $this->video_url,
            'meter' => $this->meter,
            'disponibilidad' => $disponibilidad,
            'header' => $header,
            'id'                               => $this->id,
            'item_code'                    => $this->item_code,
            'full_description'                 => $detail['full_description'],
            'model'                            => $this->model,
            'brand'                            => $detail['brand'],
            'brand_id'                         => $this->brand_id,
            'category_id'                      => $this->category_id,
            'text_filter' => $this->text_filter,
            'stock_by_extra'                            =>  $stockPerCategory,
            'warehouse_description'            => $detail['warehouse_description'],

            'category'                         => $detail['category'],
            'stock'                            => $stock,
            'has_bonus_item' => $this->hasBonus(),
            'internal_id'                      => $this->internal_id,
            'description'                      => $this->description,
            'info_link'                       => $this->info_link,
            'id_cupones'                        => $this->id_cupones,
            // 'genero'                            => $this->genero
            'currency_type_id'                 => $this->currency_type_id,
            'currency_type_symbol'             => $currency->symbol,
            'has_igv'                          => (bool)$this->has_igv,
            'sale_unit_price'                  => $this->sale_unit_price,
            'purchase_has_igv'                 => $this->purchase_has_igv,
            'purchase_unit_value'              => $purchase_unit_value,
            'purchase_unit_price'              => $purchase_unit_price,
            'unit_type_id'                     => $this->unit_type_id,
            'unit_type_symbol'                 => optional($this->unit_type)->symbol ?? null,
            'original_unit_type_id'            => $this->unit_type_id,
            'sale_affectation_igv_type'     => $this->sale_affectation_igv_type,
            'sale_affectation_igv_type_id'     => $this->sale_affectation_igv_type_id,
            'purchase_affectation_igv_type_id' => $this->purchase_affectation_igv_type_id,
            'calculate_quantity'               => (bool)$this->calculate_quantity,
            'has_plastic_bag_taxes'            => (bool)$this->has_plastic_bag_taxes,
            'amount_plastic_bag_taxes'         => $this->amount_plastic_bag_taxes,


            'attributes'     => $this->getAttributesAttribute($this->attributes['attributes']),

            'lots_enabled'   => (bool)$this->lots_enabled,
            'series_enabled' => (bool)$this->series_enabled,
            'is_set'         => (bool)$this->is_set,

            'lot_code'    => $this->lot_code,
            'date_of_due' => $this->date_of_due,
            'barcode'     => $this->barcode,
            'change_free_affectation_igv'     => false,
            'original_affectation_igv_type_id'     => $this->sale_affectation_igv_type_id,
            'has_sizes' => (bool)$this->has_sizes,
            'sizes' => $this->sizes,
            'image_url' => ($this->image !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image)
                : asset("/logo/{$this->image}"),
            'image_url_medium' => ($this->image_medium !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image_medium)
                : asset("/logo/{$this->image_medium}"),
            'has_isc' => (bool)$this->has_isc,
            'system_isc_type_id' => $this->system_isc_type_id,
            'percentage_isc' => $this->percentage_isc,
            'is_for_production' => $this->isIsForProduction(),
            'subject_to_detraction' => $this->subject_to_detraction,
            'exchange_points' => $this->exchange_points,
            'quantity_of_points' => $this->quantity_of_points,
            'exchanged_for_points' => false, //para determinar si desea canjear el producto
            'used_points_for_exchange' => null, //total de puntos
            'factory_code' => $this->factory_code,
            'restrict_sale_cpe' => $this->restrict_sale_cpe,
            'payment_conditions' => $this->payment_conditions,


        ];
        // El nombre de producto, por defecto, sera la misma descripcion.
        $data['name_product_pdf'] = "<p>" . $data['description'] . "</p>";

        return $data;
    }
    public function payment_conditions()
    {
        return $this->hasMany(ItemPricePaymentCondition::class, 'item_id');
    }

    public function sizes()
    {
        return $this->hasMany(ItemSizeStock::class);
    }

    public function getItemAttributes()
    {
        $attributes = $this->getAttributesAttribute($this->attributes['attributes']);

        return $attributes ?? [];
    }
    public function getCatDigemid()
    {
        return CatDigemid::where('item_id', $this->id)->first();
    }
    public function woocommerce_item()
    {
        return $this->hasMany(WoocommerceItem::class, 'item_id', 'id');
    }
    public static function AffectationIgvTypesExoneratedUnaffected()
    {
        return ['20', '21', '30', '31', '32', '33', '34', '35', '36', '37'];
    }
    public function food_dealer()
    {
        return $this->belongsTo(ItemFoodDealer::class, 'id', 'item_id');
    }
    public function getCollectionData(Configuration $configuration = null)
    {
        if (empty($configuration)) {
            $configuration =  Configuration::getConfig();
        }
        $is_input = (bool)$this->is_input;
        $brand = null;
        $observation_apportionment = null;
        $purchase_unit_price = $this->purchase_unit_price;

        if($configuration->purchase_apportionment){
            $apportionment_items_stock = DB::connection('tenant')->table('apportionment_items_stock')->where('item_id', $this->id)
            ->where('stock_remaining', '>', 0)
            ->orderBy('created_at', 'asc')
            ->first();
            if(!empty($apportionment_items_stock)){
                $observation_apportionment = $apportionment_items_stock->observation;
                $purchase_unit_price = $apportionment_items_stock->unit_price_apportioned;
            }
        }
        if (!empty($this->brand_id)) {
            $brand = $this->brand()->first()->name;
        }
        $has_igv_description = null;
        $purchase_has_igv_description = null;

        $affectation_igv_types_exonerated_unaffected = self::AffectationIgvTypesExoneratedUnaffected();

        if (in_array($this->sale_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
            $has_igv_description = 'No';
        } else {
            $has_igv_description = ((bool)$this->has_igv) ? 'Si' : 'No';
        }

        if (in_array($this->purchase_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
            $purchase_has_igv_description = 'No';
        } else {
            $purchase_has_igv_description = ((bool)$this->purchase_has_igv) ? 'Si' : 'No';
        }
        $digemid_exportable = false;
        $name_disa = '';
        $laboratory = '';
        $reg_san = '';
        $date_due = null;
        $currentColors = ItemColor::where('item_id', $this->id)->get()->transform(function ($row) {
            return $row->TransformDatatoEdit();
        });

        if ($configuration->isPharmacy()) {
            $digemid = $this->getCatDigemid();
            if (!empty($digemid)) {
                $digemid_exportable = (bool)$digemid->active;
                $name_disa = $digemid->getNomProd();
                $laboratory = $digemid->getNomTitular();
                $reg_san = $digemid->getNumRegSan();
                $date_due = $digemid->getFechaVencimiento();
            }
        }
        $label_color = null;
        $decimal_units = (int)$configuration->decimal_quantity;
        // $stockPerCategory = ItemMovement::getStockByCategory($this->id, auth()->user()->establishment_id);
        $stockPerCategory = [];
        $has_igv = (bool)$this->has_igv;
        $igv = 1.18; // El igv es de 18%
        $affectation_igv_types_exonerated_unaffected = self::AffectationIgvTypesExoneratedUnaffected();
        if (in_array($this->sale_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
            // Exonerado, solo se multiplica por la unidad para que no haga cambio.
            $igv = 1;
        }
        $itemSupply = $this->supplies;
        if (!empty($itemSupply)) {
            $itemSupply = $itemSupply->transform(function (ItemSupply $row) {
                return $row->getCollectionData();
            });
        }
        $salePriceWithIgv = ($has_igv == true) ? $this->sale_unit_price : ($this->sale_unit_price * $igv);
        $salePriceWithIgv = number_format($salePriceWithIgv, $configuration->decimal_quantity, '.', '');
        $currency = $this->currency_type;
        if (empty($currency)) {
            $currency = new CurrencyType();
        }
    
        $number_format = number_format($this->getFormatSaleUnitPrice(), $configuration->decimal_quantity, '.', '');
        $show_sale_unit_price = "{$currency->symbol} {$number_format}";
        $itemwarehouse = ItemWarehouse::where('warehouse_id', $this->warehouse_id)->where('item_id', $this->id)->first();
        $label_color = ItemLabelColor::where('item_id', $this->id)->where('warehouse_id', $this->warehouse_id ?? 1)->first();
        return [
            'is_input' => $is_input,
            'observation_apportionment' => $observation_apportionment,
            'location' => $this->location,
            'platforms' => optional($this->web_platform)->name,
            'is_food_dealer' => (bool)$this->is_food_dealer,
            'meter' => $this->meter,
            'has_sizes' => (bool)$this->has_sizes,
            'info_link' => $this->info_link,
            'name_disa' => $name_disa,
            'is_set' => false,
            'favorite'     => $this->favorite,
            'laboratory' => $laboratory,
            'date_due' => $date_due,
            'exportable_pharmacy' => $digemid_exportable,
            'colors' => $currentColors,
            'shared' =>  $itemwarehouse != null ? (bool) $itemwarehouse->shared : false,
            'stock_by_extra' => $stockPerCategory,
            'id' => $this->id,
            'sanitary' => $reg_san,
            'cod_digemid' => $this->cod_digemid,
            'unit_type_id' => $this->unit_type_id,
            'unit_type_text' => $this->getUnitTypeText(),
            'description' => $this->description,
            'label_color' => $label_color ? [
                'description' => $label_color->labelColor->description,
                'color' => $label_color->labelColor->color,
            ] : null,
            'name' => $this->name,
            'second_name' => $this->second_name,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'brand' => $brand,
            'category_description' => optional($this->category)->name,
            'warehouse_id' => $this->warehouse_id,
            'internal_id' => $this->internal_id,
            'item_code' => $this->item_code,
            'item_code_gs1' => $this->item_code_gs1,
            'stock' => $this->getStockByWarehouse(),
            'stock_in_more_warehouses' => $this->item_warehouse->count() > 1,
            'stock_min' => $this->stock_min,
            'currency_type_id' => $this->currency_type_id,
            'currency_type_symbol' => $currency->symbol,
            'sale_affectation_igv_type_id' => $this->sale_affectation_igv_type_id,
            'purchase_affectation_igv_type_id' => $this->purchase_affectation_igv_type_id,
            'amount_sale_unit_price' => number_format($this->sale_unit_price, $configuration->decimal_quantity, ".", ""),
            'calculate_quantity' => (bool)$this->calculate_quantity,
            'has_igv' => (bool)$this->has_igv,
            'active' => (bool)$this->active,
            'has_igv_description' => $has_igv_description,
            'purchase_has_igv_description' => $purchase_has_igv_description,
            'sale_unit_price' =>  $show_sale_unit_price,

            // 'sale_unit_price' => "{$currency->symbol} {$this->sale_unit_price}",
            'sale_unit_price_with_igv' => "{$currency->symbol} $salePriceWithIgv",
            'purchase_unit_price' => "{$currency->symbol} {$purchase_unit_price}",
            'created_at' => ($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : '',
            'updated_at' => ($this->created_at) ? $this->updated_at->format('Y-m-d H:i:s') : '',
            'warehouses' => collect($this->warehouses)->transform(function ($row) use ($salePriceWithIgv) {
                $price = $salePriceWithIgv;
                $item_id = $row->item_id;
                $warehouse_id = $row->warehouse_id;
                $itemwarehouse = ItemWarehousePrice::where('warehouse_id', $warehouse_id)->where('item_id', $item_id)->first();
                if ($itemwarehouse) {
                    $price = $itemwarehouse->price;
                }
                return [
                    'warehouse_id' => $row->warehouse_id,
                    'price' => $price,
                    'warehouse_description' => $row->warehouse->description,
                    'stock' => $row->stock,
                ];
            }),
            'apply_store' => (bool)$this->apply_store,
            'apply_restaurant' => (bool)$this->apply_restaurant,
            'image_url' => ($this->image !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image)
                : asset("/logo/{$this->image}"),
            'image_url_medium' => ($this->image_medium !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image_medium)
                : asset("/logo/{$this->image_medium}"),
            'image_url_small' => ($this->image_small !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image_small)
                : asset("/logo/{$this->image_small}"),
            'tags' => $this->tags,
            'tags_id' => $this->tags->pluck('tag_id'),
            'item_unit_types' => $this->item_unit_types->transform(function ($row) use ($decimal_units, $configuration) {
                /** @var ItemUnitType $row */
                return $row->getCollectionData($decimal_units);
                /** Movido al modelo */
                return [
                    'id' => $row->id,
                    'description' => "{$row->description}",
                    'item_id' => $row->item_id,
                    'unit_type_id' => $row->unit_type_id,
                    'quantity_unit' => number_format($this->quantity_unit, $configuration->decimal_quantity, '.', ''),
                    'price1' => number_format($this->price1, $configuration->decimal_quantity, '.', ''),
                    'price2' => number_format($this->price2, $configuration->decimal_quantity, '.', ''),
                    'price3' => number_format($this->price3, $configuration->decimal_quantity, '.', ''),
                    'price_default' => $this->price_default,
                    'warehouse_id' => $row->warehouse_id,
                ];
            }),
            'item_warehouse_prices' => $this->warehousePrices->transform(function ($row) {
                if (is_array($row)) {
                    return $row;
                }
                return [
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'warehouse_id' => $row->warehouse_id,
                    'price' => $row->price,
                    'description' => $row->warehouse->description,
                ];
            }),
            'item_customer_prices' => $this->clientTypePrices->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'person_type_id' => $row->person_type_id,
                    'price' => $row->price,
                    'description' => $row->person_type->description,
                ];
            }),
            'is_for_production' => $this->isIsForProduction(),
            'supplies' => $itemSupply,
            'frequent' => (bool) $this->frequent,

        ];
    }

    public function getFormatSaleUnitPrice()
    {
        return ((int)$this->sale_unit_price != $this->sale_unit_price) ? $this->sale_unit_price : round($this->sale_unit_price);
    }


    /**
     * Establece un standar para insersion por catalogo DIGEMID
     *
     * Este proviene del excel, debe tener la estructura :
     *
     * $Cod_Prod = $row[0];
     * $Nom_Prod = $row[1];
     * $Concent = $row[2];
     * $Nom_Form_Farm = $row[3];
     * $Nom_Form_Farm_Simplif = $row[4];
     * $Presentac = $row[5];
     * $Fracciones = $row[6];
     * $Fec_Vcto_Reg_Sanitario = $row[7];
     * $Num_RegSan = $row[8];
     * $Nom_Titular = $row[9];
     * $Situacion = $row[10];
     *
     * @param array $data
     *
     * @return $this
     */
    public function fillFormDigemid($data)
    {

        $model = substr($data[5], 0, 100);
        $line = substr($data[4], 0, 255);

        $this->cod_digemid = $data[0];

        $active = 1;
        if (strtolower(trim($data[10])) !== 'act') {
            $active = 0;
        }
        $warehouse = auth()->user()->establishment_id;
        $today =  Carbon::now()->format('Y-m-d');
        $this
            ->setInArray('sanitary', $data[8])
            ->setInArray('internal_id', $data[0])
            ->setInArray('description', $data[1])
            ->setInArray('second_name', $data[1] . " " . $data[2])
            ->setInArray('name', $data[3] . ' ' . $data[1] . " " . $data[2])
            ->setInArray('sale_affectation_igv_type_id', 10)
            ->setInArray('purchase_affectation_igv_type_id', $this->sale_affectation_igv_type_id)
            ->setInArray('item_type_id', '01')
            ->setInArray('barcode', $this->internal_id)
            ->setInArray('lot_code', $this->internal_id)
            ->setInArray('model', $model)
            ->setInArray('line', $line)
            ->setInArray('lots_enabled', true)
            ->setInArray('stock', 0)
            ->setInArray('stock_min', 0)
            ->setInArray('currency_type_id', 'PEN')
            ->setInArray('unit_type_id', 'NIU')
            ->setInArray('active', $active)
            ->setInArray('sale_unit_price', 1)
            ->setInArray('sale_unit_price_set', null)
            ->setInArray('has_igv', true)
            ->setInArray('is_set', false)
            ->setInArray('purchase_has_igv', true)
            ->setInArray('amount_plastic_bag_taxes', 0.5)
            ->setInArray('purchase_unit_price', 0)
            ->setInArray('percentage_isc', 0)
            ->setInArray('suggested_price', 0)
            ->setInArray('has_plastic_bag_taxes', false)
            ->setInArray('has_isc', false)
            ->setInArray('has_plastic_bag_taxes', false)
            ->setInArray('warehouse_id', $warehouse)
            ->setInArray('image', 'imagen-no-disponible.jpg')
            ->setInArray('image_medium', 'imagen-no-disponible.jpg')
            ->setInArray('image_small', 'imagen-no-disponible.jpg')
            ->setInArray('date_of_due', $today)
            ->setInArray('item_code', $this->cod_digemid)
            ->setInArray('brand_id', null)
            ->setInArray('category_id', null)

            /*
        'technical_specifications',
        'item_code_gs1',
        'system_isc_type_id',
        'sale_affectation_igv_type_id',
        'purchase_affectation_igv_type_id',
        'calculate_quantity',
        'percentage_of_profit',
        'attributes',
        'has_perception',
        'percentage_perception',
        'account_id',
        'apply_store',
        'apply_restaurant',
        'series_enabled',
        'web_platform_id',
        */;
        $this->description = substr($this->description, 0, 600);
        $this->second_name = substr($this->second_name, 0, 600);
        $this->name = substr($this->name, 0, 600);
        return $this;
    }

    /**
     * Si la propiedad es nula, establece el valor value
     * @param $property
     * @param $value
     *
     * @return $this
     */
    protected function setInArray($property, $value)
    {

        if ($this->{$property} == null) {
            $this->{$property} = $value;
        }

        return $this;
    }

    /**
     * @param $cod
     *
     * @return Item|Model|\Illuminate\Database\Query\Builder|mixed|object|null
     */
    public static function FindByCodDigemid($cod)
    {
        return self::where('cod_digemid', $cod)->first();
    }

    /**
     * Devuelve el modelo de WebPlatform Asociado
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model|mixed|WebPlatform|WebPlatform[]|null
     */
    public function getWebPlatformModel()
    {
        return WebPlatform::find($this->web_platform_id);
    }

    /**
     * @return ItemSet[]|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|Collection|mixed|null
     */
    public function getSetItems()
    {

        $is_set = (bool) $this->is_set;
        if ($is_set) {
            return  ItemSet::where('item_id', $this->id)->get();
        }
        return null;
    }

    /**
     * Devuelve una estructura en comun para el reporte Kardex
     * @return array
     */
    public function getReportValuedKardexCollection()
    {

        $values_records = InventoryValuedKardex::getValuesRecords($this->document_items, $this->sale_note_items);
        $quantity_sale = $values_records['quantity_sale'];
        $total_sales = $values_records['total_sales'];
        $item_cost = $quantity_sale * $this->purchase_unit_price;
        $valued_unit = $total_sales - $item_cost;
        $item = $this;
        return [
            'id' => $this->id,
            'item_description' => $this->description,
            'category_description' => optional($this->category)->name,
            'brand_description' => optional($this->brand)->name,
            'unit_type_id' => $this->unit_type_id,
            'quantity_sale' => number_format($quantity_sale, 2, ".", ""),
            'purchase_unit_price' => number_format($this->purchase_unit_price, 2, ".", ""),
            'total_sales' => number_format($total_sales, 2, ".", ""),
            'item_cost' => number_format($item_cost, 2, ".", ""),
            'valued_unit' => number_format($valued_unit, 2, ".", ""),
            'warehouses' => $this->warehouses->transform(function ($row, $key) use ($item) {
                return [
                    'id' => $row->id,
                    'stock' => $row->stock,
                    'warehouse_description' => $row->warehouse->description,
                    'description' => "{$row->warehouse->description} - {$row->stock}",
                    'sale_unit_price' => self::getSaleUnitPriceByWarehouse($item, $row->id),
                ];
            }),

        ];
    }


    /**
     * Almacena las unidades por almacen para poder guardarlos en la tabla.
     *
     * @param array $ItemUnitsPerPackage
     *
     * @return $this
     * @throws Exception
     */
    public function setItemUnitsPerPackage(array $ItemUnitsPerPackage = [])
    {

        return $this->setExtraData(ItemUnitsPerPackage::class, 'cat_item_units_per_package_id', $ItemUnitsPerPackage);
    }

    public function getItemUnitsPerPackage()
    {
        return ItemUnitsPerPackage::where('item_id', $this->id)->where('active', 1)->get();
    }
    /**
     * Almacena los colores para poder guardarlos en la tabla.
     * @param array $colors
     *
     * @return $this
     * @throws Exception
     */
    public function setItemColor($colors = [])
    {
        return $this->setExtraData(ItemColor::class, 'cat_colors_item_id', $colors);
    }

    public function getItemColor()
    {
        return ItemColor::where('item_id', $this->id)->where('active', 1)->get();
    }

    public function setItemMoldProperty($data = [])
    {
        return $this->setExtraData(ItemMoldProperty::class, 'cat_item_mold_properties_id', $data);
    }
    public function getItemMoldProperty()
    {
        return ItemMoldProperty::where('item_id', $this->id)->where('active', 1)->get();
    }
    public function setItemUnitBusiness($data = [])
    {
        return $this->setExtraData(ItemUnitBusiness::class, 'cat_item_unit_business_id', $data);
    }

    public function getItemUnitBusiness()
    {
        return ItemUnitBusiness::where('item_id', $this->id)->where('active', 1)->get();
    }
    public function setItemStatus($data = [])
    {
        return $this->setExtraData(ItemStatus::class, 'cat_item_status_id', $data);
    }

    public function getItemStatus()
    {
        return ItemStatus::where('item_id', $this->id)->where('active', 1)->get();
    }

    public function setItemPackageMeasurement($data = [])
    {
        return $this->setExtraData(ItemPackageMeasurement::class, 'cat_item_package_measurements_id', $data);
    }

    public function getItemPackageMeasurement()
    {
        return ItemPackageMeasurement::where('item_id', $this->id)->where('active', 1)->get();
    }
    public function setItemMoldCavity($data = [])
    {
        return $this->setExtraData(ItemMoldCavity::class, 'cat_item_mold_cavities_id', $data);
    }

    public function scopeItemIsInput($query)
    {
        return $this->where('is_input', true);
    }

    public function scopeItemIsNotInput($query)
    {
        return $this->where('is_input', false);
    }

    public function getItemMoldCavity()
    {
        return ItemMoldCavity::where('item_id', $this->id)->where('active', 1)->get();
    }
    public function setItemProductFamily($data = [])
    {
        return $this->setExtraData(ItemProductFamily::class, 'cat_item_product_family_id', $data);
    }
    public function setItemSize($data = [])
    {
        return $this->setExtraData(ItemSize::class, 'cat_item_size_id', $data);
    }

    public function getItemProductFamily()
    {
        return ItemProductFamily::where('item_id', $this->id)->where('active', 1)->get();
    }
    public function getItemSize()
    {
        return ItemSize::where('item_id', $this->id)->where('active', 1)->get();
    }

    /**
     * @param  ItemMoldProperty|ItemUnitBusiness|ItemStatus|ItemColor|ItemUnitsPerPackage|ItemProductFamily|ItemMoldCavity|ItemPackageMeasurement|null      $class
     * @param string $field_id
     * @param array  $data
     *
     * @return $this
     */
    protected function setExtraData($class, $field_id = '', $data = []): Item
    {
        $dataCollection = collect($data);
        $currentRow = $class::where('item_id', $this->id)
            ->whereNotIn($field_id, $dataCollection)
            ->get();

        foreach ($currentRow as $row) {
            $row->setActive(false)->push();
            $row->delete();
        }



        foreach ($dataCollection as $item) {

            if ($field_id == 'cat_item_product_family_id' && get_class($item) == CatItemProductFamily::class) {
                $item = $item->id;
            }
            $ck = [
                'item_id' => $this->id,
                $field_id => $item
            ];
            $newRow = $class::where($ck)->first();

            if (empty($newRow)) {
                $newRow = new $class($ck);
            }

            $newRow->setActive(true)->push();
        }
        return $this;
    }
    /**
     * @param  ItemMoldProperty|ItemUnitBusiness|ItemStatus|ItemColor|ItemUnitsPerPackage|ItemProductFamily|ItemMoldCavity|ItemPackageMeasurement|null      $class
     * @param string $field_id
     * @param array  $data
     *
     * @return $this
     */
    public function setExtraDataByCatalogCategory($class, $field_id = '', $data = [])
    {
        return $this->setExtraData($class, $field_id, $data);
    }
    /**
     * Genera una estructura unica para los datos extra.
     *
     * @param       $array
     * @param array $row
     */
    public static function SaveExtraDataToRequest(&$array, $row = [])
    {

        if (isset($row['item'], $row['item']['extra'])) {
            $extra = $row['item']['extra'];


            $colors_id = isset($extra['colors']) ? (int)$extra['colors'] : null;
            $CatItemUnitsPerPackage_id = isset($extra['CatItemUnitsPerPackage']) ? (int)$extra['CatItemUnitsPerPackage'] : null;
            $CatItemMoldProperty_id = isset($extra['CatItemMoldProperty']) ? (int)$extra['CatItemMoldProperty'] : null;
            $CatItemProductFamily_id = isset($extra['CatItemProductFamily']) ? (int)$extra['CatItemProductFamily'] : null;
            $CatItemMoldCavity_id = isset($extra['CatItemMoldCavity']) ? (int)$extra['CatItemMoldCavity'] : null;
            $CatItemPackageMeasurement_id = isset($extra['CatItemPackageMeasurement']) ? (int)$extra['CatItemPackageMeasurement'] : null;
            $CatItemStatus_id = isset($extra['CatItemStatus']) ? (int)$extra['CatItemStatus'] : null;
            $CatItemUnitBusiness_id = isset($extra['CatItemUnitBusiness']) ? (int)$extra['CatItemUnitBusiness'] : null;
            $CatItemSize_id = isset($extra['CatItemSize']) ? (int)$extra['CatItemSize'] : null;


            $array['item']['extra']["colors"] = $colors_id;
            $array['item']['extra']["CatItemUnitsPerPackage"] = $CatItemUnitsPerPackage_id;
            $array['item']['extra']["CatItemMoldProperty"] = $CatItemMoldProperty_id;
            $array['item']['extra']["CatItemProductFamily"] = $CatItemProductFamily_id;
            $array['item']['extra']["CatItemMoldCavity"] = $CatItemMoldCavity_id;
            $array['item']['extra']["CatItemPackageMeasurement"] = $CatItemPackageMeasurement_id;
            $array['item']['extra']["CatItemStatus"] = $CatItemStatus_id;
            $array['item']['extra']["CatItemUnitBusiness"] = $CatItemUnitBusiness_id;
            $array['item']['extra']["CatItemSize"] = $CatItemSize_id;

            // Guarda el nombre del campo si existe

            $colors_string = '';
            if (!empty($colors_id)) {
                $temp = CatColorsItem::find($colors_id);
                if ($temp !== null) {
                    $colors_string = $temp->getName();
                }
            }
            $CatItemUnitsPerPackage_string = '';

            if (!empty($CatItemUnitsPerPackage_id)) {
                $temp = CatItemUnitBusiness::find($CatItemUnitsPerPackage_id);
                if ($temp !== null) {
                    $CatItemUnitsPerPackage_string = $temp->getName();
                }
            }
            $CatItemMoldProperty_string = '';

            if (!empty($CatItemMoldProperty_id)) {
                $temp = CatItemMoldProperty::find($CatItemMoldProperty_id);
                if ($temp !== null) {
                    $CatItemMoldProperty_string = $temp->getName();
                }
            }
            $CatItemProductFamily_string = '';

            if (!empty($CatItemProductFamily_id)) {
                $temp = CatItemProductFamily::find($CatItemProductFamily_id);
                if ($temp !== null) {
                    $CatItemProductFamily_string = $temp->getName();
                }
            }
            $CatItemMoldCavity_string = '';

            if (!empty($CatItemMoldCavity_id)) {
                $temp = CatItemMoldCavity::find($CatItemMoldCavity_id);
                if ($temp !== null) {
                    $CatItemMoldCavity_string = $temp->getName();
                }
            }
            $CatItemPackageMeasurement_string = '';

            if (!empty($CatItemPackageMeasurement_id)) {
                $temp = CatItemPackageMeasurement::find($CatItemPackageMeasurement_id);
                if ($temp !== null) {
                    $CatItemPackageMeasurement_string = $temp->getName();
                }
            }
            $CatItemStatus_string = '';

            if (!empty($CatItemStatus_id)) {
                $temp = CatItemStatus::find($CatItemStatus_id);
                if ($temp !== null) {
                    $CatItemStatus_string = $temp->getName();
                }
            }
            $CatItemUnitBusiness_string = '';

            if (!empty($CatItemUnitBusiness_id)) {
                $temp = CatItemUnitBusiness::find($CatItemUnitBusiness_id);
                if ($temp !== null) {
                    $CatItemUnitBusiness_string = $temp->getName();
                }
            }

            $CatItemSize_string = '';

            if (!empty($CatItemSize_id)) {
                $temp = CatItemSize::find($CatItemSize_id);
                if ($temp !== null) {
                    $CatItemSize_string = $temp->getName();
                }
            }


            $array['item']['extra']["string"]["colors"] = $colors_string;
            $array['item']['extra']["string"]["CatItemUnitsPerPackage"] = $CatItemUnitsPerPackage_string;
            $array['item']['extra']["string"]["CatItemMoldProperty"] = $CatItemMoldProperty_string;
            $array['item']['extra']["string"]["CatItemProductFamily"] = $CatItemProductFamily_string;
            $array['item']['extra']["string"]["CatItemMoldCavity"] = $CatItemMoldCavity_string;
            $array['item']['extra']["string"]["CatItemPackageMeasurement"] = $CatItemPackageMeasurement_string;
            $array['item']['extra']["string"]["CatItemStatus"] = $CatItemStatus_string;
            $array['item']['extra']["string"]["CatItemUnitBusiness"] = $CatItemUnitBusiness_string;
            $array['item']['extra']["string"]["CatItemSize"] = $CatItemSize_string;
        }
    }

    /**
     *
     * Añade elementos extra al momento de generar el pdf
     *
     * @param $array
     */
    public function getExtraDataToPrint(&$array)
    {

        $array['image_url'] = ($this->image !== 'imagen-no-disponible.jpg')
            ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image)
            : asset("/logo/{$this->image}");
        $array['image_url_medium'] = ($this->image_medium !== 'imagen-no-disponible.jpg')
            ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image_medium)
            : asset("/logo/{$this->image_medium}");
        $array['image_url_small'] = ($this->image_small !== 'imagen-no-disponible.jpg')
            ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image_small)
            : asset("/logo/{$this->image_small}");
    }

    /**
     * Crea o actualiza el precio de inventario.
     *
     * @param int    $item_warehouse_id
     * @param int    $warehouse_id
     * @param string $price
     *
     * @return $this
     * @throws Exception
     */
    public function setItemWarehousePrice(
        $item_warehouse_id = 0,
        $warehouse_id = 0,
        $price = ''
    ) {
        if ($price != '') {
            ItemWarehousePrice::updateOrCreate([
                'item_id' => $this->id,
                'warehouse_id' => $warehouse_id,
            ], [
                'price' => $price,
            ]);
        } else if ($item_warehouse_id != 0) {
            ItemWarehousePrice::findOrFail($item_warehouse_id)->delete();
        }
        return $this;
    }

    /**
     * Llamado estatico de setItemWarehousePrice
     *
     * @param int    $item_id
     * @param int    $item_warehouse_id
     * @param int    $warehouse_id
     * @param string $price
     *
     * @return bool
     * @throws Exception
     */
    public static function setStaticItemWarehousePrice(
        $item_id = 0,
        $item_warehouse_id = 0,
        $warehouse_id = 0,
        $price = ''
    ) {
        if ($item_id != 0) {
            $item = self::find($item_id);
            if ($item !== null) {
                $item->setItemWarehousePrice(
                    $item_warehouse_id,
                    $warehouse_id,
                    $price
                );
                return true;
            }
        }
        return false;
    }

    /**
     * Deuelve codificacion 64 para el codigo de barras
     *
     * @param int   $height
     * @param int[] $colour
     *
     * @return string
     */
    public function getBarCode(int $height = 60, $colour = [0, 0, 0])
    {

        $code = $this->getIdentificacionCode();
        if (empty($code)) {
            return null;
        }
        $generator = new BarcodeGeneratorPNG();
        return  "data:image/png;base64," . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128, 1, $height, $colour));
    }

    /**
     * Obtiene el codigo de barras o el codigo interno para devolverlo
     * @return string|null
     */
    public function getIdentificacionCode()
    {
        $code = $this->barcode;
        if (empty($code)) {
            $code = $this->internal_id;
        }
        return $code;
    }


    /**
     * Obtiene una estructura estandar para generar los codigos de barra
     * Normalmente, el alto ($barCodeHeight) de codigo de barradas es 60 px.
     *
     * @param int $barCodeHeight
     *
     * @return array
     */
    public function getBarCodeData($barCodeHeight = 60)
    {

        $currency = $this->currency_type;
        if (empty($currency)) {
            $currency = new CurrencyType();
        }
        $data = [];
        $data['internal_id'] = $this->internal_id;
        $data['name'] = $this->getDescription();
        $data['model'] = $this->model;
        $data['code'] = $this->getIdentificacionCode();
        $data['short_name'] = substr($data['name'], 0, 30);
        $data['short_model'] = substr($data['model'], 0, 10);
        $data['bar_code'] = $this->getBarCode($barCodeHeight);
        $data['price'] = $currency->symbol . " " . $this->withoutRounding();
        $data['second_name'] = $this->second_name;
        $data['short_second_name'] = substr($data['second_name'], 0, 4);
        $data['talla'] = $data['short_second_name'];
        return $data;
    }


    /**
     * Genera los numeros para el codigo de barras sin redondeo.
     *
     * @param int $total_decimals
     *
     * @return mixed|string
     */
    public  function withoutRounding($total_decimals = 2)
    {
        $number = (string)$this->sale_unit_price;
        if ($number === '') {
            $number = '0';
        }
        if (strpos($number, '.') === false) {
            $number .= '.';
        }
        $number_arr = explode('.', $number);

        $decimals = substr($number_arr[1], 0, $total_decimals);
        if ($decimals === false) {
            $decimals = '0';
        }

        $return = '';
        if ($total_decimals == 0) {
            $return = $number_arr[0];
        } else {
            if (strlen($decimals) < $total_decimals) {
                $decimals = str_pad($decimals, $total_decimals, '0', STR_PAD_RIGHT);
            }
            $return = $number_arr[0] . '.' . $decimals;
        }
        return $return;
    }

    public function getDataToPurchase()
    {

        $full_description = ($this->internal_id) ? $this->internal_id . ' - ' . $this->description : $this->description;
        return [
            'id' => $this->id,
            'item_code'  => $this->item_code,
            'full_description' => $full_description,
            'description' => $this->description,
            'currency_type_id' => $this->currency_type_id,
            'currency_type_symbol' => $this->currency_type->symbol,
            'sale_unit_price' => $this->sale_unit_price,
            'purchase_unit_price' => $this->purchase_unit_price,
            'unit_type_id' => $this->unit_type_id,
            'sale_affectation_igv_type_id' => $this->sale_affectation_igv_type_id,
            'purchase_affectation_igv_type_id' => $this->purchase_affectation_igv_type_id,
            'purchase_has_igv' => (bool) $this->purchase_has_igv,
            'has_perception' => (bool) $this->has_perception,
            'lots_enabled' => (bool) $this->lots_enabled,
            'percentage_perception' => $this->percentage_perception,
            'item_unit_types' => collect($this->item_unit_types)->transform(function ($row) {
                // validar este
                /*
            
                */
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
            'series_enabled' => (bool) $this->series_enabled,

            // 'warehouses' => collect($row->warehouses)->transform(function($row) {
            //     return [
            //         'warehouse_id' => $row->warehouse->id,
            //         'warehouse_description' => $row->warehouse->description,
            //         'stock' => $row->stock,
            //     ];
            // })
        ];
    }


    /**
     * @param Builder                               $query
     * @param                                       $params
     *
     * @return Builder
     */
    public function scopeWhereFilterValuedKardexFormatSunat(Builder $query, $params)
    {

        if ($params->establishment_id) {

            return $query->with([
                'document_items' => function ($q) use ($params) {
                    $q->whereHas('document', function ($q) use ($params) {
                        $q->whereValuedKardexFormatSunat($params)
                            ->where('establishment_id', $params->establishment_id);
                    });
                },
                'purchase_item' => function ($q) use ($params) {
                    $q->whereHas('purchase', function ($q) use ($params) {
                        $q->whereValuedKardexFormatSunat($params)
                            ->where('establishment_id', $params->establishment_id);
                    });
                },
                'dispatch_items' => function ($q) use ($params) {
                    $q->whereHas('dispatch', function ($q) use ($params) {
                        $q->whereValuedKardexFormatSunat($params)
                            ->where('establishment_id', $params->establishment_id);
                    });
                }
            ])
                ->without([
                    'currency_type', 'item_type', 'warehouses', 'item_unit_types', 'tags'
                ]);
        }


        return $query->with([
            'document_items' => function ($q) use ($params) {
                $q->whereHas('document', function ($q) use ($params) {
                    $q->whereValuedKardexFormatSunat($params);
                });
            },
            'purchase_item' => function ($q) use ($params) {
                $q->whereHas('purchase', function ($q) use ($params) {
                    $q->whereValuedKardexFormatSunat($params);
                });
            },
            'dispatch_items' => function ($q) use ($params) {
                $q->whereHas('dispatch', function ($q) use ($params) {
                    $q->whereValuedKardexFormatSunat($params);
                });
            }
        ])
            ->without([
                'currency_type', 'item_type', 'warehouses', 'item_unit_types', 'tags'
            ]);
    }


    /**
     * Buscar codigo de la tabla 6
     */
    public function findUnitTypeCodeTableSix()
    {

        $record = $this->findUnitTypeCodeTableSixByDescription(strtoupper($this->unit_type->description ?? ""));
        if ($record) return $record;

        //buscar otros
        $others = $this->findUnitTypeCodeTableSixByDescription('OTROS');
        $others['description'] = $others['description'] . ' - ' . strtoupper($this->unit_type->description ?? "");

        return $others;
    }


    /**
     * Buscar codigo de la tabla 6 por descripcion de unidad
     */
    public function findUnitTypeCodeTableSixByDescription($description)
    {

        return $this->getDataTableSixKardexSunat()->first(function ($row) use ($description) {
            return $row['description'] == strtoupper($description);
        });
    }

    /**
     * Datos de la tabla 6 para reporte kardex valorizado formato sunat 13.1
     */
    public function getDataTableSixKardexSunat()
    {
        return collect([
            ['code' => '01', 'description' => 'KILOGRAMOS'],
            ['code' => '02', 'description' => 'LIBRAS'],
            ['code' => '03', 'description' => 'TONELADAS LARGAS'],
            ['code' => '04', 'description' => 'TONELADAS MÉTRICAS'],
            ['code' => '05', 'description' => 'TONELADAS CORTAS'],
            ['code' => '06', 'description' => 'GRAMOS'],
            ['code' => '07', 'description' => 'UNIDADES'],
            ['code' => '08', 'description' => 'LITROS'],
            ['code' => '09', 'description' => 'GALONES'],
            ['code' => '10', 'description' => 'BARRILES'],
            ['code' => '11', 'description' => 'LATAS'],
            ['code' => '12', 'description' => 'CAJAS'],
            ['code' => '13', 'description' => 'MILLARES'],
            ['code' => '14', 'description' => 'METROS CÚBICOS'],
            ['code' => '15', 'description' => 'METROS'],
            ['code' => '99', 'description' => 'OTROS'],
        ]);
    }


    /**
     * @return HasMany
     */
    public  function technical_service_item()
    {
        return $this->hasMany(TechnicalServiceItem::class, 'item_id');
    }

    /**
     * Devuelve el total de stock basado en la tabla item_movement
     *
     * Si $establisnment_id es diferente a 0, se sumaria basado en el establecimiento. Solo se suma los elementos countable
     *
     * @param int $establisnment_id
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getNewStock($establisnment_id = 0)
    {
        $query = DB::connection('tenant')
            ->table('item_movement')
            ->where('countable', 1)
            ->where('item_id', $this->id);
        if ($establisnment_id != 0) {
            $query->where('establishment_id', $establisnment_id);
        }
        // Validacion para almacen?
        $query = $query->select(DB::raw(' sum(quantity) as total'))->first();

        return $query->total;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function item_movement_rel_extra()
    {
        return $this->hasMany(ItemMovementRelExtra::class);
    }

    /**
     * Devuelve una estructura comun para los datos extra
     *
     * @return array
     */
    public function getExtraDataFields()
    {
        $data = [
            'colors' => ItemColor::ByItem($this->id)->get(),
            'CatItemUnitsPerPackage' => ItemUnitsPerPackage::ByItem($this->id)->get(),
            'CatItemMoldProperty' => ItemMoldProperty::ByItem($this->id)->get(),
            'CatItemUnitBusiness' => ItemUnitBusiness::ByItem($this->id)->get(),
            'CatItemStatus' => ItemStatus::ByItem($this->id)->get(),
            'CatItemPackageMeasurement' => ItemPackageMeasurement::ByItem($this->id)->get(),
            'CatItemMoldCavity' => ItemMoldCavity::ByItem($this->id)->get(),
            'CatItemProductFamily' => ItemProductFamily::ByItem($this->id)->get(),
            'CatItemSize' => ItemSize::ByItem($this->id)->get(),

        ];
        return $data;
    }

    /**
     * @return string|null
     */
    public function getUnitTypeText()
    {
        if (!empty($this->unit_type)) {
            return $this->unit_type->description;
        }

        return null;
    }

    public function scopeForProduction($query)
    {
        return $query->where([
            ['item_type_id', '01'],
            ['unit_type_id', '!=', 'ZZ'],
            ['is_for_production', 1],
            ['is_set', 0]
        ]);
    }

    public function scopeForProductionSupply($query)
    {
        return $query->where([
            ['item_type_id', '01'],
            ['unit_type_id', '!=', 'ZZ'],
            ['is_for_production', 0],
            ['is_set', 0]
        ]);
    }

    /**
     * Devuelve codigo interno - descripcion producto
     *
     * @return array
     */
    public function getInternalIdDescription()
    {
        return $this->internal_id ? "{$this->internal_id} - {$this->description}" : $this->description;
    }

    /**
     * @return HasMany
     */
    public function supplies()
    {
        return $this->hasMany(ItemSupply::class);
    }

    public function bonusses()
    {
        return $this->hasMany(ItemBonus::class);
    }
    /**
     * @return HasMany
     */
    public function supplies_items()
    {
        return $this->hasMany(ItemSupply::class, 'individual_item_id');
    }

    /**
     * @return bool
     */
    public function isIsForProduction(): bool
    {
        return (bool) $this->is_for_production;
    }
    public function hasBonus(): bool
    {
        return (bool) $this->bonusses->count() > 0;
    }
    /**
     * @param Builder $query
     *
     * @return mixed
     */
    public function scopeProductEnded(Builder $query)
    {
        return $query->where([
            ['item_type_id', '01'],
            ['unit_type_id', '!=', 'ZZ'],
            ['is_for_production', 1]
        ])
            ->with('supplies')
            ->whereNotIsSet();
    }

    /**
     * @param Builder $query
     *
     * @return mixed
     */
    public function scopeProductSupply(Builder $query)
    {
        $sup = ItemSupply::select('individual_item_id')->distinct()->pluck('individual_item_id');
        return $query->where([
            ['unit_type_id', '!=', 'ZZ'],
        ])
            ->wherein('id', $sup)
            ->with('supplies_items')
            ->whereNotIsSet()
            // ->join('items', 'item_supplies.individual_item_id', '=', 'items.id')
            ->distinct();
    }


    /**
     * Almacenes asociados al producto
     *
     * @return array
     */
    public function getDataWarehouses()
    {
        return collect($this->warehouses)->transform(function ($row) {
            return [
                'warehouse_description' => $row->warehouse->description,
                'stock' => $row->stock,
                'warehouse_id' => $row->warehouse_id,
            ];
        });
    }


    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeCategory($query, $id = null)
    {
        if ($id) {
            return $query->where('category_id', $id);
        }
    }

    public function scopeWhereStockMin($query)
    {
        $stockmin = (int)$this->stock_min;
        return $query->whereHas('warehouses', function ($query) use ($stockmin) {
            $query->where('stock', '<=', $stockmin);
        });
    }

    public function scopeWhereStockMinValidate($query)
    {
        $stockmin = (int)$this->stock_min;
        return $query->whereHas('warehouses', function ($query) use ($stockmin) {
            $query->where('stock', '>', $stockmin);
        });
    }


    /**
     *
     * Obtener presentaciones
     *
     * Usado en:
     * PosController
     *
     * @param  bool $search_item_by_barcode_presentation
     * @param  string $barcode_presentation
     * @return array
     */
    public function getItemUnitTypesBarcode($search_item_by_barcode_presentation = false, $barcode_presentation)
    {
        return $search_item_by_barcode_presentation ? $this->item_unit_types()->where('barcode', $barcode_presentation)->get() : $this->item_unit_types;
    }

    /**
     *
     * Filtrar por codigo de barra de presentacion
     *
     * Usado en:
     * PosController
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrFilterItemUnitTypeBarcode($query, $barcode)
    {
        return $query->orWhereHas('item_unit_types', function ($query) use ($barcode) {
            $query->where('barcode', $barcode);
        });
    }

    /**
     *
     * Filtrar por codigo de barra de presentacion
     *
     * Usado en:
     * SearchItemController
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFilterItemUnitTypeBarcode($query, $barcode)
    {
        return $query->whereHas('item_unit_types', function ($query) use ($barcode) {
            $query->where('barcode', $barcode);
        });
    }


    /**
     *
     * Filtro para no incluir relaciones en consulta
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWhereFilterWithOutRelations($query)
    {
        return $query->withOut(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags', 'item_lots']);
    }


    /**
     *
     * Filtro para consulta al actualizar precios
     *
     * Usado en:
     * ItemUpdatePriceImport
     *
     * @param Builder $query
     * @param  string $internal_id
     * @return Builder
     */
    public function scopeWhereFilterUpdatePrices($query, $internal_id)
    {
        return $query->whereFilterWithOutRelations()->where('internal_id', $internal_id)->select('id', 'internal_id', 'sale_unit_price', 'purchase_unit_price');
    }


    /**
     *
     * Filtro avanzado para busqueda
     * Usado en:
     * ItemController - records
     * Modules\Inventory\Http\Controllers\ItemController - advancedItemsSearch
     * Modules\Inventory\Http\Controllers\InventoryController - records
     *
     * @param Builder $query
     * @param  string $column
     * @param  string $value
     * @return Builder
     *
     */
    public function scopeWhereAdvancedRecordsSearch($query, $column, $value)
    {
        $search_values = $this->getSearchValues($value);

        return $query->where(function ($q) use ($search_values, $column) {

            foreach ($search_values as $search_value) {
                $q->where($column, 'like', "%{$search_value}%");
            }
        });
    }


    /**
     *
     * Filtro para busqueda avanzada de items en reporte kardex
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeWhereFilterReportKardex($query)
    {
        return $query->whereNotIsSet()->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);
    }


    /**
     *
     * Datos del item para busqueda avanzada
     *
     * Usado en:
     * Modules\Inventory\Http\Controllers\ItemController
     *
     * @return array
     */
    public function getRowResourceAdvancedSearch()
    {

        $full_description = $this->getFullDescriptionAdvancedSearch();

        return [
            'id' => $this->id,
            'full_description' => $full_description,
            'internal_id' => $this->internal_id,
            'description' => $this->description,
        ];
    }


    /**
     *
     * Descripcion del item para busqueda avanzada
     *
     * @return string
     */
    public function getFullDescriptionAdvancedSearch()
    {
        $description = ($this->internal_id) ? $this->internal_id . ' - ' . $this->description : $this->description;

        $category = "";
        if ($this->category) $category = ($this->category->id) ? " - {$this->category->name}" : "";

        $brand = "";
        if ($this->brand) $brand = ($this->brand->id) ? " - {$this->brand->name}" : "";

        return "{$description}{$category}{$brand}";
    }


    /**
     *
     * Obtener datos para lista de items en app
     *
     * @return array
     */
    public function getApiRowResource()
    {

        $currency = $this->currency_type;
        $show_sale_unit_price = "{$currency->symbol} {$this->getFormatSaleUnitPrice()}";

        return [
            'id' => $this->id,
            'full_description' => $this->getFullDescriptionAdvancedSearch(),
            'unit_type_id' => $this->unit_type_id,
            'unit_type_description' => $this->getUnitTypeText(),
            'description' => $this->description,
            'name' => $this->name,
            'second_name' => $this->second_name,
            'barcode' => $this->barcode,
            'internal_id' => $this->internal_id,
            'item_code' => $this->item_code,
            'currency_type_id' => $this->currency_type_id,
            'currency_type_symbol' => $currency->symbol,
            'sale_affectation_igv_type_id' => $this->sale_affectation_igv_type_id,
            'has_igv' => (bool)$this->has_igv,
            'sale_unit_price' => (float) $this->sale_unit_price,
            'show_sale_unit_price' => $show_sale_unit_price,
            'image_url' => $this->getImageUrl(),
            'purchase_affectation_igv_type_id' => $this->purchase_affectation_igv_type_id,
            'purchase_unit_price' => $this->purchase_unit_price,
            'category_id' => $this->category_id,
            'active' => (bool) $this->active,
            'stock' => $this->getWarehouseCurrentStock(),
            'favorite' => $this->favorite,
            'has_isc' => (bool)$this->has_isc,
            'system_isc_type_id' => $this->system_isc_type_id,
            'percentage_isc' => $this->percentage_isc,

            'warehouses' => $this->getApiDataWarehouses(),
            'item_unit_types' => $this->getApiDataItemUnitTypes(),
        ];
    }


    /**
     *
     * Datos de almacenes asociados al item
     *
     * @return array
     */
    public function getApiDataWarehouses()
    {
        return $this->warehouses->transform(function ($row) {
            return [
                'warehouse_description' => $row->warehouse->description,
                'stock' => $row->stock,
                'warehouse_id' => $row->warehouse_id,
            ];
        });
    }


    /**
     *
     * Datos de lista de precios asociados al item
     *
     * @return array
     */
    public function getApiDataItemUnitTypes()
    {
        return $this->item_unit_types->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                'unit_type_id' => $row->unit_type_id,
                'quantity_unit' => $row->quantity_unit,
                'price1' => $row->price1,
                'price2' => $row->price2,
                'price3' => $row->price3,
                'price_default' => $row->price_default,
                'range_min' => $row->range_min,
                'range_max' => $row->range_max,
            ];
        });
    }


    /**
     *
     * Obtener url de la imagen del producto
     *
     * @return string
     */
    public function getImageUrl()
    {
        return ($this->image !== 'imagen-no-disponible.jpg') ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $this->image) : asset("/logo/{$this->image}");
    }


    /**
     *
     * Filtro para búsqueda de items desde el listado de la app
     *
     * @param  Builder $query
     * @param  string $input
     * @param  int $search_by_barcode
     * @return Builder
     */
    public function scopeWhereFilterRecordsApi($query, $input, $search_by_barcode)
    {

        if ((bool) $search_by_barcode) {
            $query->where('barcode', $input)->limit(1);
        } else {
            $query->where('description', 'like', "%{$input}%")->orWhere('internal_id', 'like', "%{$input}%");
        }

        return $query->whereHasInternalId()
            ->whereWarehouse()
            ->orderBy('description');
    }


    /**
     *
     * Redimensionar imagen
     *
     * @param  string $temp_path
     * @param  float $size
     * @return \Image
     */
    public function getImageResize($temp_path, $size)
    {

        $image = \Image::make($temp_path);

        return $image->resize($size, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }


    /**
     *
     * Filtrar por categoria
     *
     * @param  Builder $query
     * @param  int $category_id
     * @return Builder
     */
    public function scopeFilterByCategory($query, $category_id)
    {
        if ($category_id)  $query->where('category_id', $category_id);

        return $query;
    }


    /**
     *
     * Obtener stock del almacen asociado al usuario
     *
     * @param  Warehouse $warehouse
     * @return float
     */
    public function getWarehouseCurrentStock($warehouse = null)
    {
        $stock = 0;

        if ($this->unit_type_id !== self::SERVICE_UNIT_TYPE) {
            $warehouse = $warehouse ?? Warehouse::select('id')->where('establishment_id', auth()->user()->establishment_id)->first();

            if ($warehouse) {
                $item_warehouse =  ItemWarehouse::select('stock')->where([['item_id', $this->id], ['warehouse_id', $warehouse->id]])->first();

                if ($item_warehouse) $stock = $item_warehouse->stock;
            }
        }

        return (float) $stock;
    }


    /**
     *
     * Filtro para no incluir todas las relaciones en consulta
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFilterRecordsSaleApi($query, $request)
    {

        $category_id = $request->category_id ??  null;
        $favorite = $request->has('favorite') && (bool) $request->favorite;

        return $query->whereFilterWithOutRelations()

            ->with(['category', 'brand', 'currency_type'])
            ->whereFilterRecordsApi($request->input, $request->search_by_barcode)
            ->filterByCategory($category_id)
            ->filterFavorite($favorite)
            ->whereIsActive();
    }


    /**
     *
     * Filtrar favoritos
     *
     * @param  Builder $query
     * @param  bool $favorite
     * @return Builder
     */
    public function scopeFilterFavorite($query, $favorite)
    {
        if ($favorite)  $query->where('favorite', $favorite);

        return $query;
    }


    /**
     *
     * Obtener datos para lista de productos en ventas - Modo Pos
     *
     * @return array
     */
    public function getSaleApiRowResource($warehouse)
    {
        $currency = $this->currency_type;

        return [
            'id' => $this->id,
            'item_id' => $this->id,
            'name' => $this->name,
            'full_description' => $this->getInternalIdDescription(),
            'description' => $this->description,
            'currency_type_id' => $this->currency_type_id,
            'internal_id' => $this->internal_id,
            'item_code' => $this->item_code,
            'barcode' => $this->barcode,
            'currency_type_symbol' => $currency->symbol,
            'sale_unit_price' => $this->generalApplyNumberFormat($this->sale_unit_price),
            'unit_type_id' => $this->unit_type_id,
            'sale_affectation_igv_type_id' => $this->sale_affectation_igv_type_id,
            'has_igv' => (bool) $this->has_igv,
            'quantity' => 0,
            'stock' => $this->getWarehouseCurrentStock($warehouse),
            'image_url' => $this->getImageUrl(),
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'is_set' => $this->is_set,
        ];
    }


    /**
     * Stock de variantes para revision inventario
     *
     * @param  int $establishment_id
     * @return array
     */
    public function getStockByVariantsInventoryReview($establishment_id)
    {
        return ItemMovement::getStockByVariantSizeColor($this->id, $establishment_id);
    }


    /**
     *
     * Filtrar por almacen
     *
     * @param  Builder $query
     * @param  int $warehouse_id
     * @return Builder
     */
    public function scopeFilterByWarehouseId($query, $warehouse_id)
    {
        if (!is_null($warehouse_id) && $warehouse_id != 'all') {
            $query->whereHas('warehouses', function ($query) use ($warehouse_id) {
                return $query->where('warehouse_id', $warehouse_id);
            });
        }

        return $query;
    }


    /**
     *
     * Filtro por coincidencia para X campo
     *
     * @param  Builder $query
     * @param  string $column
     * @param  string $input
     * @return Builder
     */
    public function scopeWhereColumnFilterLike($query, $column, $input)
    {
        return $query->where($column, 'like', "%{$input}%");
    }


    /**
     *
     * Filtro por coincidencia para X campo de una tabla relacionada
     *
     * @param  Builder $query
     * @param  string $relation
     * @param  string $column
     * @param  string $input
     * @return Builder
     */
    public function scopeFilterLikeCustomRelation($query, $relation, $column, $input)
    {
        return $query->whereHas($relation, function ($q) use ($column, $input) {
            return $q->where($column, 'like', "%{$input}%");
        });
    }


    /**
     *
     * Filtro para reporte ajuste stock - inventario
     *
     * @param  Builder $query
     * @param  string $column
     * @param  string $input
     * @return Builder
     */
    public function scopeFilterRecordsStockReport($query, $column, $input)
    {
        switch ($column) {
            case 'description':
            case 'internal_id':
            case 'model':
                $query->whereColumnFilterLike($column, $input);
                break;

            case 'category':
            case 'brand':
                $query->filterLikeCustomRelation($column, 'name', $input);
                break;
        }

        return $query;
    }
    public static function createItemService()
    {

        $establishment_id = auth()->user()->establishment_id;
        $item = Item::where(
            'internal_id',
            'ZZ001'
        )->where('unit_type_id', 'ZZ')->first();
        if ($item) {
            $warehouse_id = Warehouse::where('establishment_id', $establishment_id)->first()->id;
            $item_warehouse = ItemWarehouse::where([['item_id', $item->id], ['warehouse_id', $warehouse_id]])->first();
            if (!$item_warehouse) {
                $item_warehouse = new ItemWarehouse();
                $item_warehouse->item_id = $item->id;
                $item_warehouse->warehouse_id = $warehouse_id;
                $item_warehouse->stock = 0;
                $item_warehouse->save();
            }

            return;
        }


        $item = new Item();
        $item->name = 'Servicio';
        $item->internal_id = 'ZZ001';
        $item->description = 'Servicio';
        $item->unit_type_id = 'ZZ';
        $item->currency_type_id = 'PEN';
        $item->item_type_id = "01";
        $item->sale_unit_price = 0;
        $item->purchase_unit_price = 0;
        $item->sale_affectation_igv_type_id = '10';
        $item->purchase_affectation_igv_type_id = '10';
        $item->has_igv = false;
        $item->has_perception = false;
        $item->has_isc = false;
        $item->percentage_perception = 0;
        $item->percentage_isc = 0;
        $item->stock = 0;
        $item->stock_min = 0;
        $item->is_set = false;
        $item->save();
        // return $item;
    }

    /**
     * @return bool
     */
    public function checkIsNotService()
    {
        return $this->unit_type_id !== self::SERVICE_UNIT_TYPE;
    }


    /**
     * @return bool
     */
    public function checkIsSet()
    {
        return $this->is_set;
    }


    /**
     *
     * Obtener item por codigo interno
     *
     * Usado para importacion lotes/series en movimientos
     *
     * @return Item
     */
    public static function getItemByInternalId($internal_id)
    {
        return self::whereFilterWithOutRelations()
            ->where('internal_id', $internal_id)
            ->select([
                'id',
                'internal_id',
                'series_enabled',
                'lots_enabled'
            ])
            ->first();
    }


    /**
     *
     * Obtener lotes para gestionar compra/venta/movimiento
     *
     * @return array
     */
    public function getLotsGroupForCompromise()
    {
        return $this->lots_group->transform(function ($lots_group) {
            return [
                'id'          => $lots_group->id,
                'code'        => $lots_group->code,
                'quantity'    => $lots_group->quantity,
                'date_of_due' => $lots_group->date_of_due,
                'checked'     => false,
                'compromise_quantity' => 0
            ];
        });
    }


    public function restoreStockSizes()
    {

        $has_sizes = (bool) $this->has_sizes;
        if ($has_sizes) {
            $item_id = $this->id;
            $warehouses_id = Warehouse::where('active', true)->get()->pluck('id');
            foreach ($warehouses_id as $w_id) {
                $stock = ItemSizeStock::where([['item_id', $item_id], ['warehouse_id', $w_id]])->sum('stock');
                $item_warehouse = ItemWarehouse::where([['item_id', $item_id], ['warehouse_id', $w_id]])->first();
                if ($item_warehouse) {
                    $item_warehouse->stock = $stock;
                    $item_warehouse->save();
                }
            }
        }
    }

    public function cupones()
    {
        return $this->belongsTo(Coupon::class, 'id_cupones', 'id');
    }
    public static function checkIfExistBaseItemService()
    {
        $item = DB::connection('tenant')->table('items')->where('internal_id', 'ZZ001')->where('unit_type_id', 'ZZ')->first();
        if (!$item) {
            $item = self::createBaseItemService();
        }
        return $item;
    }

    public static function createBaseItemService()
    {
        $item = new Item();
        $item->internal_id = "ZZ001";
        $item->description = "Servicio";
        $item->name = "Servicio";
        $item->second_name = "Servicio";
        $item->item_type_id = "01";
        $item->item_code = null;
        $item->item_code_gs1 = null;
        $item->unit_type_id = "ZZ";
        $item->currency_type_id = "PEN";
        $item->sale_unit_price =  1;
        $item->sale_affectation_igv_type_id = "10";
        $item->purchase_affectation_igv_type_id = "10";
        $item->stock = 0;
        $item->amount_plastic_bag_taxes = 0.0;
        $item->save();
        return $item;
    }

    public static function checkIfExistBaseItem()
    {
        $item = DB::connection('tenant')->table('items')->where('internal_id', 'B4S3')->where('unit_type_id', 'NIU')->first();
        if (!$item) {
            $item = self::createBaseItem();
        }
        return $item;
    }

    public static function createBaseItem()
    {
        $item = new Item();
        $item->internal_id = "B4S3";
        $item->description = "Base";
        $item->name = "Base";
        $item->second_name = "Base";
        $item->item_type_id = "01";
        $item->item_code = "B";
        $item->item_code_gs1 = null;
        $item->unit_type_id = "NIU";
        $item->currency_type_id = "PEN";
        $item->sale_unit_price =  1;
        $item->sale_affectation_igv_type_id = "10";
        $item->purchase_affectation_igv_type_id = "10";
        $item->stock = 0;
        $item->amount_plastic_bag_taxes = 0.0;
        $item->save();
        return $item;
    }

    public static function getItemDataToDocuments($records, $search_item_by_series = null, $series = null)
    {

        $configuration = Configuration::getConfig();
        $decimal_quantity = $configuration->decimal_quantity;
        $connection = DB::connection('tenant');
        $personTypesData = $connection->table('person_types')->get()->keyBy('id');
        $currenciesData = DB::connection('tenant')->table('cat_currency_types')->get();
        $item_ids = $records->pluck('id');
        $user = auth()->user() ?? auth('api')->user();
        $establishment_id = null;
        $list_items_by_warehouse = $configuration->list_items_by_warehouse;
        if ($list_items_by_warehouse) {
            $establishment_id = $user->establishment_id;
        } else {
            $establishment_id = $configuration->getMainWarehouseId();
            // $establishment_id = $establishment->id;
        }
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();

        $lotsData = collect([]);
        if ($search_item_by_series) {
            $lotsData = ItemLot::whereIn('item_id', $item_ids)->where('series', $series)
                ->where('warehouse_id', $warehouse->id)
                ->where('has_sale', false)
                ->take(1)
                ->get()
                ->groupBy('item_id');
        }
        $itemPricePaymentConditionsData = $connection->table('item_price_payment_condition')
            ->whereIn('item_id', $item_ids)
            ->get()->groupBy('item_id');
        $warehousePricesData = collect([]);
        $warehousePricesData = ItemWarehousePrice::where('warehouse_id', $warehouse->id)->whereIn('item_id', $item_ids)->get()->keyBy('item_id');
        $affectationIgvTypesData = AffectationIgvType::get()->keyBy('id');
        $itemLotsGroupData = ItemLotsGroup::whereIn('item_id', $item_ids)->get()->groupBy('item_id');
        $itemBonusData = $connection->table('item_bonus')
            ->whereIn('item_id', $item_ids)
            ->get();
        $itemBonuesItemIds = $itemBonusData->pluck('item_id');
        $itemBonuesItemBounsIds = $itemBonusData->pluck('item_bonus_id');
        $itemsBonusData = $connection->table('items')->whereIn('id', $itemBonuesItemBounsIds)->get()->keyBy('id');
        $itemBonusDataBonus = self::allGetDataToItemModalLite($itemBonuesItemIds);


        $itemBonusData = $itemBonusData->transform(function ($row) use ($itemsBonusData,$itemBonusDataBonus) {
                $data = (array)$row;
                unset($data['item_bonus']["bonus"]['bonus_items']);
                $data['item'] = $itemsBonusData->where('id', $row->item_id)->first();
                $data['item_bonus'] = $itemBonusDataBonus->where('id', $row->item_bonus_id)->first();
                $data['item_bonus']["bonus"] = true;
                $data['item_bonus']["sale_affectation_igv_type_id"] = "15";
                $data['item_bonus']["sale_affectation_igv_type"] = AffectationIgvType::find("15");
                return $data;
            })->groupBy('item_id');
        $quotationProjectItemData = $connection->table('quotation_project_items')
            ->whereIn('quotation_item_id', $item_ids)
            ->get()->groupBy('item_id');

        $categoriesData = $connection->table('categories')->get()->keyBy('id');
        $brandsData = $connection->table('brands')->get()->keyBy('id');
        $sizesData = $connection->table('item_sizes')
            ->whereIn('item_id', $item_ids)
            ->get()->groupBy('item_id');
        $clientTypePricesData = $connection->table('client_type_prices')
            ->select('id', 'item_id', 'price', 'person_type_id')
            ->whereIn('item_id', $item_ids)
            ->get()->groupBy('item_id');
        try {

            return $records->transform(function ($row) use (
                $itemBonusData,
                $quotationProjectItemData,
                $configuration,
                $currenciesData,
                $warehousePricesData,
                $decimal_quantity,
                $warehouse,
                $itemLotsGroupData,
                $lotsData,
                $categoriesData,
                $brandsData,
                $affectationIgvTypesData,
                $sizesData,
                $itemPricePaymentConditionsData,
                $clientTypePricesData
            ) {
                /** @var Item $row */
                $blank = [];
                $currentColors = collect($blank);
                $ItemUnitsPerPackage = collect($blank);
                $ItemMoldProperty = collect($blank);
                $ItemProductFamily = collect($blank);
                $ItemMoldCavity = collect($blank);
                $ItemPackageMeasurement = collect($blank);
                $ItemStatus = collect($blank);
                $ItemSize = collect($blank);
                $ItemUnitBusiness = collect($blank);
                $quotationProjectItem = $quotationProjectItemData->where('item_id', $row->id)->first();
                $warehouse_price = $warehousePricesData->where('item_id', $row->id)->first();
                $sale_unit_price = number_format($row->sale_unit_price, $decimal_quantity, ".", "");
                if ($warehouse_price) {
                    $sale_unit_price = number_format($warehouse_price->price, $decimal_quantity, ".", "");
                }
                $lots = [];
            
                $existLots = $lotsData->where('item_id', $row->id)->first();
                if ($existLots) {
                    $lots = $existLots;
                }
                $disponibility = null;
                $header = null;
                if ($quotationProjectItem) {
                    $disponibility = $quotationProjectItem->disponibility;
                    $header = $quotationProjectItem->header;
                }
                $stockPerCategory = [];
                $purchase_unit_price = $row->purchase_unit_price;
                $purchase_unit_value = $row->purchase_unit_price;

                $description = $row->description;

                if ($configuration->show_internal_id_list) {
                    $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $description : $description;
                } else {
                    $desc = $description;
                }
                $category = '';
                $categoryFound = $categoriesData->where('id', $row->category_id)->first();
                if ($categoryFound) {
                    $category = $categoryFound->name;
                }
                //  ($this->category) ? "{$this->category->name}" : '';
                $brand = '';
                $brandFound = $brandsData->where('id', $row->brand_id)->first();
                if ($brandFound) {
                    $brand = $brandFound->name;
                }
                // ($this->brand) ? "{$this->brand->name}" : '';

                if ($row->unit_type_id != 'ZZ') {
                    $warehouseFound = $warehousePricesData->where('item_id', $row->id)->where('warehouse_id', $warehouse->id)->first();
                    if (isset($row['stock'])) {
                        $warehouse_stock = number_format($row['stock'], 2);
                    } else {
                        $warehouse_stock = $warehouseFound ? number_format($warehouseFound->stock, 2) : 0;
                    }
                    $stock = ($warehouseFound) ? "{$warehouse_stock}" : '';
                } else {
                    $stock = '';
                }


                if ($configuration->show_models && $row->model) {
                    $desc .= " - {$row->model}";
                }
                if ($configuration->show_brands && $brand) {
                    $desc .= " - {$brand}";
                }
                if ($configuration->show_categories && $category) {
                    $desc .= " - {$category}";
                }
                if ($row->location) {
                    $desc .= " - {$row->location}";
                }

                $detail = [
                    'full_description'      => $desc,
                    'brand'                 => $brand,
                    'category'              => $category,
                    'stock'                 => $stock,
                    'warehouse_description' => $warehouse->description,
                ];
                $stock = $detail['stock'];
                $clientTypePrices = $clientTypePricesData->where('item_id', $row->id)->first() ?? collect([]);
                $existLotsGroup = $itemLotsGroupData->get($row->id);
                $lotsGroup = collect([]);
                if ($existLotsGroup) {
                    $lotsGroup = $existLotsGroup;
                }
                $data = [
                    'has_perception' => (bool)$row->has_perception,
                    'percentage_perception' => $row->percentage_perception,
                    'can_edit_price' => (bool)$row->can_edit_price,
                    'video_url' => $row->video_url,
                    'meter' => $row->meter,
                    'bonus_items' => $itemBonusData->where('item_id', $row->id),
                    'disponibilidad' => $disponibility,
                    'header' => $header,
                    'id'                               => $row->id,
                    'item_code'                    => $row->item_code,
                    'full_description'                 => $detail['full_description'],
                    'model'                            => $row->model,
                    'brand'                            => $detail['brand'],
                    'brand_id'                         => $row->brand_id,
                    'category_id'                      => $row->category_id,
                    'text_filter' => $row->text_filter,
                    'stock_by_extra'                            =>  $stockPerCategory,
                    'warehouse_description'            => $detail['warehouse_description'],
                    'extra'                         => collect([
                        'colors' => null,
                        'CatItemUnitsPerPackage' => null,
                        'CatItemMoldProperty' => null,
                        'CatItemProductFamily' => null,
                        'CatItemMoldCavity' => null,
                        'CatItemPackageMeasurement' => null,
                        'CatItemStatus' => null,
                        'CatItemSize' => null,
                        'CatItemUnitBusiness' => null,
                    ]),
                    'category'                         => $detail['category'],
                    'stock'                            => $stock,
                    'has_bonus_item' => $row->hasBonus(),
                    'internal_id'                      => $row->internal_id,
                    'description'                      => $row->description,
                    'info_link'                       => $row->info_link,
                    'id_cupones'                        => $row->id_cupones,
                    // 'genero'                            => $this->genero
                    'currency_type_id'                 => $row->currency_type_id,
                    'currency_type_symbol'             => $currenciesData->where('id', $row->currency_type_id)->first()->symbol,
                    'has_igv'                          => (bool)$row->has_igv,
                    'sale_unit_price'                  => $sale_unit_price,
                    'purchase_has_igv'                 => $row->purchase_has_igv,
                    'purchase_unit_value'              => $purchase_unit_value,
                    'purchase_unit_price'              => $purchase_unit_price,
                    'unit_type_id'                     => $row->unit_type_id,
                    'unit_type_symbol'                 => optional($row->unit_type)->symbol ?? null,
                    'original_unit_type_id'            => $row->unit_type_id,
                    'sale_affectation_igv_type'     => $affectationIgvTypesData->where('id', $row->sale_affectation_igv_type_id)->first(),
                    'sale_affectation_igv_type_id'     => $row->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $affectationIgvTypesData->where('id', $row->purchase_affectation_igv_type_id)->first(),
                    'calculate_quantity'               => (bool)$row->calculate_quantity,
                    'has_plastic_bag_taxes'            => (bool)$row->has_plastic_bag_taxes,
                    'amount_plastic_bag_taxes'         => $row->amount_plastic_bag_taxes,
                    'colors' => $currentColors,
                    'CatItemUnitsPerPackage' => $ItemUnitsPerPackage,
                    'CatItemMoldProperty' => $ItemMoldProperty,
                    'CatItemProductFamily' => $ItemProductFamily,
                    'CatItemMoldCavity' => $ItemMoldCavity,
                    'CatItemPackageMeasurement' => $ItemPackageMeasurement,
                    'CatItemStatus' => $ItemStatus,
                    'CatItemSize' => $ItemSize,
                    'CatItemUnitBusiness' => $ItemUnitBusiness,
                    'item_unit_types'                  => $row->item_unit_types->transform(function ($item_unit_types) {
                        if (is_array($item_unit_types)) {
                            return $item_unit_types;
                        }
                        return [
                            'id'            => $item_unit_types->id,
                            'description'   => "{$item_unit_types->description}",
                            'item_id'       => $item_unit_types->item_id,
                            'unit_type_id'  => $item_unit_types->unit_type_id,
                            'quantity_unit' => $item_unit_types->quantity_unit,
                            'price1'        => $item_unit_types->price1,
                            'price2'        => $item_unit_types->price2,
                            'price3'        => $item_unit_types->price3,
                            'price_default' => $item_unit_types->price_default,
                            'factor_default' => (bool) $item_unit_types->factor_default,
                            'barcode' => $item_unit_types->barcode,
                            'range_min' => $item_unit_types->range_min,
                            'range_max' => $item_unit_types->range_max,
                            'warehouse_id' => $item_unit_types->warehouse_id,
                        ];
                    }),
                    'warehouses' => collect($row->warehouses)->transform(function ($warehouses) use ($warehouse, $row) {
                        $price = self::getSaleUnitPriceByWarehouse($row, $warehouses->warehouse_id);
                        return [
                            'price' => $price,
                            'warehouse_description' => $warehouses->warehouse->description,
                            'stock'                 => (!empty($warehouses->stock)) ? $warehouses->stock : 0,
                            'warehouse_id'          => $warehouses->warehouse_id,
                            'checked'               => ($warehouses->warehouse_id == $warehouse->id) ? true : false,
                        ];
                    }),
                    // se listaran atributos necesarios en pdf de otra forma
                    'attributes'     => $row->getAttributesAttribute($row->attributes['attributes']),
                    'lots_group'     => $lotsGroup->transform(function ($lots_group) {
                        return [
                            'id'          => $lots_group->id,
                            'code'        => $lots_group->code,
                            'quantity'    => $lots_group->quantity,
                            'date_of_due' => $lots_group->date_of_due,
                            'checked'     => false,
                            'compromise_quantity' => 0,
                            'warehouse_id' => $lots_group->warehouse_id,
                            'warehouse' => $lots_group->warehouse_id ? $lots_group->warehouse->description : '',
                        ];
                    }),
                    'lots'           => $lots,
                    'lots_enabled'   => (bool)$row->lots_enabled,
                    'series_enabled' => (bool)$row->series_enabled,
                    'is_set'         => (bool)$row->is_set,
                    'item_customer_prices' => $clientTypePrices->transform(function ($row) {
                        //si row es un array transformalo a objeto
                        if (is_array($row)) {
                            return $row;
                        }

                        return [
                            'id' => $row->id,
                            'item_id' => $row->item_id,
                            'person_type_id' => $row->person_type_id,
                            'price' => $row->price,
                            'description' => $row->person_type->description,
                        ];
                    }),
                    'lot_code'    => $row->lot_code,
                    'date_of_due' => $row->date_of_due,
                    'barcode'     => $row->barcode,
                    'change_free_affectation_igv'     => false,
                    'original_affectation_igv_type_id'     => $row->sale_affectation_igv_type_id,
                    'has_sizes' => (bool)$row->has_sizes,
                    'sizes' => $sizesData->get($row->id),
                    'image_url' => ($row->image !== 'imagen-no-disponible.jpg')
                        ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image)
                        : asset("/logo/{$row->image}"),
                    'image_url_medium' => ($row->image_medium !== 'imagen-no-disponible.jpg')
                        ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image_medium)
                        : asset("/logo/{$row->image_medium}"),
                    'has_isc' => (bool)$row->has_isc,
                    'system_isc_type_id' => $row->system_isc_type_id,
                    'percentage_isc' => $row->percentage_isc,
                    'is_for_production' => $row->isIsForProduction(),
                    'subject_to_detraction' => $row->subject_to_detraction,
                    'exchange_points' => $row->exchange_points,
                    'quantity_of_points' => $row->quantity_of_points,
                    'exchanged_for_points' => false, //para determinar si desea canjear el producto
                    'used_points_for_exchange' => null, //total de puntos
                    'factory_code' => $row->factory_code,
                    'restrict_sale_cpe' => $row->restrict_sale_cpe,
                    'payment_conditions' => $itemPricePaymentConditionsData->where('item_id', $row->id)->first() ?? [],
                    'set_items' => $row->getSetItems() ? $row->getSetItems()->transform(function ($row) {
                        return [
                            'item_id' => $row->individual_item_id,
                            'internal_id' => $row->individual_item->internal_id ?? '-',
                            'quantity' => $row->quantity,
                        ];
                    }) : [],

                ];
                // El nombre de producto, por defecto, sera la misma descripcion.
                $data['name_product_pdf'] = "<p>" . $data['description'] . "</p>";

                return $data;
            });
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
        }
    }

    private  function allGetDataToItemModalLite($ids){
        $items = Item::whereIn('id', $ids)->get();
        $itemBonusData = $items->transform(function ($row) {
            return $row->getDataToItemModalLite();
        });
        return $itemBonusData->groupBy('id');
    }
}
