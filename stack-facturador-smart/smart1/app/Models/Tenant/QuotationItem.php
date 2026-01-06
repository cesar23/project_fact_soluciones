<?php

    namespace App\Models\Tenant;

    use App\Models\Tenant\Catalogs\AffectationIgvType;
    use App\Models\Tenant\Catalogs\PriceType;
    use App\Models\Tenant\Catalogs\SystemIscType;
    use App\Traits\AttributePerItems;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Model;

    class QuotationItem extends ModelTenant
    {
        use AttributePerItems;
        public $timestamps = false;
        protected $with = ['affectation_igv_type', 'system_isc_type', 'price_type','projectItem'];
        protected $fillable = [
            'quotation_id',
            'item_id',
            'item',
            'quantity',
            'unit_value',

            'affectation_igv_type_id',
            'total_base_igv',
            'percentage_igv',
            'total_igv',

            'system_isc_type_id',
            'total_base_isc',
            'percentage_isc',
            'total_isc',

            'total_base_other_taxes',
            'percentage_other_taxes',
            'total_other_taxes',
            'total_taxes',

            'price_type_id',
            'unit_price',

            'total_value',
            'total_charge',
            'total_discount',
            'total',

            'attributes',
            'charges',
            'discounts',

            'extra_attr_name',
            'extra_attr_value',
            'additional_information',
            'warehouse_id',
            'name_product_pdf',
        ];

        public static function boot()
        {
            parent::boot();
            static::creating(function (self $item) {
                $document = $item->quotation;
                if ($document !== null && empty($item->warehouse_id)) {
                    $warehouse = Warehouse::find($document->establishment_id);
                    if ($warehouse !== null) {
                        $item->warehouse_id = $document->establishment_id;
                    }
                }
            });
        
        }

        public function projectItem(){
            return $this->hasOne(QuotationProjectItem::class);
        }
        public function getItemAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }
        public function sellers()
    {
        return $this->hasOne(ItemSeller::class)->withDefault();
    }
        public function setItemAttribute($value)
        {
            $this->attributes['item'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getAttributesAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function warehouse()
        {
            return $this->belongsTo(Warehouse::class,'warehouse_id');
        }
        public function setAttributesAttribute($value)
        {
            $this->attributes['attributes'] = (is_null($value)) ? null : json_encode($value);
        }
        public function getConvertUnitValueToPen()
        {
            return $this->generalConvertValueToPen($this->unit_value, $this->quotation->exchange_rate_sale);
        }
        public function getConvertUnitPriceToPen()
        {
            return $this->generalConvertValueToPen($this->unit_price, $this->quotation->exchange_rate_sale);
        }
        public function getConvertTotalIscToPen()
        {
            return $this->generalConvertValueToPen($this->total_isc, $this->quotation->exchange_rate_sale);
        }
        public function getConvertTotalIgvToPen()
        {
            return $this->generalConvertValueToPen($this->total_igv, $this->quotation->exchange_rate_sale);
        }
        public function getConvertTotalValueToPen()
        {
            return $this->generalConvertValueToPen($this->total_value, $this->quotation->exchange_rate_sale);
        }
        public function getChargesAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }
        public function isCurrencyTypeUsd()
        {
            return $this->quotation->currency_type_id === 'USD';
        }
        public function setChargesAttribute($value)
        {
            $this->attributes['charges'] = (is_null($value)) ? null : json_encode($value);
        }
        public function getExchangeRateSale()
        {
            return $this->quotation->exchange_rate_sale;
        }

        public function getDiscountsAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setDiscountsAttribute($value)
        {
            $this->attributes['discounts'] = (is_null($value)) ? null : json_encode($value);
        }

        public function affectation_igv_type()
        {
            return $this->belongsTo(AffectationIgvType::class, 'affectation_igv_type_id');
        }

        public function system_isc_type()
        {
            return $this->belongsTo(SystemIscType::class, 'system_isc_type_id');
        }

        public function price_type()
        {
            return $this->belongsTo(PriceType::class, 'price_type_id');
        }

        public function relation_item()
        {
            return $this->belongsTo(Item::class, 'item_id');
        }
        public function quotation()
        {
            return $this->belongsTo(Quotation::class, 'quotation_id');
        }

        /**
         * Devuelve una estructura en conjunto para datos extra al momento de generar un pdf
         *
         * @return array
         */
        public function getPrintExtraData()
        {
            $item = $this->item;
            $extra = (property_exists($item, 'extra')) ? $item->extra : null;
            $extra_string = ($extra != null && property_exists($extra, 'string')) ? $extra->string : null;
            $colors = ($extra_string != null && property_exists($extra_string, 'colors')) ? $extra_string->colors : null;
            $CatItemUnitsPerPackage = ($extra_string != null && property_exists($extra_string, 'CatItemUnitsPerPackage')) ? $extra_string->CatItemUnitsPerPackage : null;
            $CatItemMoldProperty = ($extra_string != null && property_exists($extra_string, 'CatItemMoldProperty')) ? $extra_string->CatItemMoldProperty : null;
            $CatItemProductFamily = ($extra_string != null && property_exists($extra_string, 'CatItemProductFamily')) ? $extra_string->CatItemProductFamily : null;
            $CatItemMoldCavity = ($extra_string != null && property_exists($extra_string, 'CatItemMoldCavity')) ? $extra_string->CatItemMoldCavity : null;
            $CatItemPackageMeasurement = ($extra_string != null && property_exists($extra_string, 'CatItemPackageMeasurement')) ? $extra_string->CatItemPackageMeasurement : null;
            $CatItemStatus = ($extra_string != null && property_exists($extra_string, 'CatItemStatus')) ? $extra_string->CatItemStatus : null;
            $CatItemUnitBusiness = ($extra_string != null && property_exists($extra_string, 'CatItemUnitBusiness')) ? $extra_string->CatItemUnitBusiness : null;
            $CatItemSize = ($extra_string != null && property_exists($extra_string, 'CatItemSize')) ? $extra_string->CatItemSize : null;
            $data = [
                'colors' => (!empty($colors)) ? $colors : null,
                'CatItemUnitsPerPackage' => (!empty($CatItemUnitsPerPackage)) ? $CatItemUnitsPerPackage : null,
                'CatItemMoldProperty' => (!empty($CatItemMoldProperty)) ? $CatItemMoldProperty : null,
                'CatItemProductFamily' => (!empty($CatItemProductFamily)) ? $CatItemProductFamily : null,
                'CatItemMoldCavity' => (!empty($CatItemMoldCavity)) ? $CatItemMoldCavity : null,
                'CatItemPackageMeasurement' => (!empty($CatItemPackageMeasurement)) ? $CatItemPackageMeasurement : null,
                'CatItemStatus' => (!empty($CatItemStatus)) ? $CatItemStatus : null,
                'CatItemUnitBusiness' => (!empty($CatItemUnitBusiness)) ? $CatItemUnitBusiness : null,
                'CatItemSize' => (!empty($CatItemSize)) ? $CatItemSize : null,
            ];
            // Se añaden campos extra desde el item
            $itemModel = $this->getModelItem();
            $itemModel->getExtraDataToPrint($data);
            return $data;
        }
        public function getConvertTotalToPen()
        {
            
            return $this->generalConvertValueToPen($this->total, $this->quotation->exchange_rate_sale);
        }
        /**
         * @return Item|Item[]|Collection|Model|mixed|null
         */
        public function getModelItem()
        {
            return Item::find($this->item_id);
        }

        /**
         * @return int|null
         */
        public function getWarehouseId(): ?int
        {
            return (int)$this->warehouse_id;
        }

        /**
         * @param int|null $warehouse_id
         *
         * @return QuotationItem
         */
        public function setWarehouseId(?int $warehouse_id): QuotationItem
        {
            $this->warehouse_id = (int)$warehouse_id;
            return $this;
        }


    }
