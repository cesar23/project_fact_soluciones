<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\AddressType;
use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Province;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\DocumentaryProcedure\Models\DocumentaryFile;
use Modules\Expense\Models\Expense;
use Modules\Order\Models\OrderForm;
use Modules\Order\Models\OrderNote;
use Modules\Purchase\Models\FixedAssetPurchase;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Sale\Models\Contract;
use Modules\Sale\Models\SaleOpportunity;
use Modules\Sale\Models\TechnicalService;
use App\Models\Tenant\Configuration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Dashboard\Helpers\DashboardView;
use Modules\Dispatch\Models\DispatchAddress;
use Modules\Suscription\Models\Tenant\SuscriptionPayment;
use Modules\Suscription\Models\Tenant\UserRelSuscriptionPlan;
use App\Models\Tenant\Zone;
use Modules\FullSuscription\Models\Tenant\FullSuscriptionCreditPerson;
use Modules\FullSuscription\Models\Tenant\FullSuscriptionServerDatum;
use Modules\FullSuscription\Models\Tenant\FullSuscriptionUserDatum;

/**
 * App\Models\Tenant\Person
 *
 * @property int|null                             $seller_id
 * @property User                                 $seller
 * @property int|null                             $zone_id
 * @property Zone                                 $zone
 * @property-read AddressType                     $address_type
 * @property-read Collection|PersonAddress[]      $addresses
 * @property-read int|null                        $addresses_count
 * @property-read Collection|Contract[]           $contracts_where_customer
 * @property-read int|null                        $contracts_where_customer_count
 * @property-read Country                         $country
 * @property-read Department                      $department
 * @property-read Collection|Dispatch[]           $dispatches_where_customer
 * @property-read int|null                        $dispatches_where_customer_count
 * @property-read District                        $district
 * @property-read Collection|DocumentaryFile[]    $documentary_files
 * @property-read int|null                        $documentary_files_count
 * @property-read Collection|Document[]           $documents
 * @property-read int|null                        $documents_count
 * @property-read Collection|Document[]           $documents_where_customer
 * @property-read int|null                        $documents_where_customer_count
 * @property-read Collection|Expense[]            $expenses_where_supplier
 * @property-read int|null                        $expenses_where_supplier_count
 * @property-read Collection|FixedAssetPurchase[] $fixed_asset_purchases_where_customer
 * @property-read int|null                        $fixed_asset_purchases_where_customer_count
 * @property-read Collection|FixedAssetPurchase[] $fixed_asset_purchases_where_supplier
 * @property-read int|null                        $fixed_asset_purchases_where_supplier_count
 * @property-read mixed                           $address_full
 * @property mixed                                $contact
 * @property-read IdentityDocumentType            $identity_document_type
 * @property-read Collection|PersonAddress[]      $more_address
 * @property-read int|null                        $more_address_count
 * @property-read Collection|OrderForm[]          $order_forms_where_customer
 * @property-read int|null                        $order_forms_where_customer_count
 * @property-read Collection|OrderNote[]          $order_notes_where_customer
 * @property-read int|null                        $order_notes_where_customer_count
 * @property-read Collection|Perception[]         $perceptions_where_customer
 * @property-read int|null                        $perceptions_where_customer_count
 * @property-read Collection|PersonAddress[]      $person_addresses
 * @property int|null                             $parent_id
 * @property-read \App\Models\Tenant\Person       $parent_person
 * @property-read \App\Models\Tenant\Person       $children_person
 * @property-read int|null                        $person_addresses_count
 * @property-read PersonType                      $person_type
 * @property-read Province                        $province
 * @property-read Collection|PurchaseOrder[]      $purchase_orders_where_supplier
 * @property-read int|null                        $purchase_orders_where_supplier_count
 * @property-read Collection|PurchaseSettlement[] $purchase_settlements_where_supplier
 * @property-read int|null                        $purchase_settlements_where_supplier_count
 * @property-read Collection|Purchase[]           $purchases_where_customer
 * @property-read int|null                        $purchases_where_customer_count
 * @property-read Collection|Purchase[]           $purchases_where_supplier
 * @property-read int|null                        $purchases_where_supplier_count
 * @property-read Collection|Quotation[]          $quotations_where_customer
 * @property-read int|null                        $quotations_where_customer_count
 * @property-read Collection|Retention[]          $retentions_where_supplier
 * @property-read int|null                        $retentions_where_supplier_count
 * @property-read Collection|SaleNote[]           $sale_notes_where_customer
 * @property-read int|null                        $sale_notes_where_customer_count
 * @property-read Collection|SaleOpportunity[]    $sale_opportunities_where_customer
 * @property-read int|null                        $sale_opportunities_where_customer_count
 * @property-read Collection|TechnicalService[]   $technical_services_where_customer
 * @property-read int|null                        $technical_services_where_customer_count
 * @method static Builder|Person newModelQuery()
 * @method static Builder|Person newQuery()
 * @method static Builder|Person query()
 * @method static Builder|Person whereIsEnabled()
 * @method static Builder|Person whereType($type)
 * @mixin ModelTenant
 * @mixin Eloquent
 */
class Person extends ModelTenant
{
    use UsesTenantConnection;

    protected $table = 'persons';

    protected $with = [
        // 'identity_document_type',
        // 'country',
        // 'department',
        // 'province',
        // 'district',
    ];

    protected $fillable = [
        'line_credit',
        'bank_name',
        'bank_account_number',
        'auto_retention',
        'qualification_client',
        'location',
        'is_driver',
        'color',
        'type',
        'identity_document_type_id',
        'number',
        'name',
        'trade_name',
        'internal_code',
        'country_id',
        'nationality_id',
        'department_id',
        'province_id',
        'district_id',
        'address_type_id',
        'address',
        'condition',
        'state',
        'email',
        'telephone',
        'perception_agent',
        'person_type_id',
        'contact',
        'comment',
        'percentage_perception',
        'enabled',
        'website',
        'barcode',
        // 'zone',
        'observation',
        'credit_days',
        'optional_email',
        'seller_id',
        'zone_id',
        'status',
        'parent_id',
        'accumulated_points',
        'has_discount',
        'discount_type',
        'discount_amount',
        'photo_filename',
        'person_reg_id',
    ];

    protected $casts = [
        'auto_retention' => 'boolean',
        'is_driver' => 'bool',
        'perception_agent' => 'bool',
        'person_type_id' => 'int',
        'percentage_perception' => 'float',
        'enabled' => 'bool',
        'status' => 'int',
        'credit_days' => 'int',
        'seller_id' => 'int',
        'zone_id' => 'int',
        'parent_id' => 'int',
        'accumulated_points' => 'float',
        'has_discount' => 'bool',
        'discount_amount' => 'float',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope('active', function (Builder $builder) {
    //         $builder->where('status', 1);
    //     });
    // }

    /**
     * Devuelve un conjunto de hijos basado en parent_id
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children_person()
    {
        return $this->hasMany(Person::class, 'parent_id');
    }

    /**
     * Devuelve el padre basado en parent_id
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent_person()
    {
        return $this->belongsTo(Person::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function person_addresses()
    {
        return $this->hasMany(PersonAddress::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(PersonAddress::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function identity_document_type()
    {
        return $this->belongsTo(IdentityDocumentType::class, 'identity_document_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents_where_customer()
    {
        return $this->hasMany(Document::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address_type()
    {
        return $this->belongsTo(AddressType::class);
    }

    /**
     * @param $query
     * @param $type
     *
     * @return mixed
     */
    public function scopeWhereType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getUbigeoFullAttribute()
    {
        $department = $this->department ? $this->department->description : '';
        $province = $this->province ? $this->province->description : '';
        $district = $this->district ? $this->district->description : '';

        if (!$department && !$province && !$district) {
            return '';
        }

        return "{$department} - {$province} - {$district}";
    }
    public function getAddressFullAttribute()
    {
        $address = trim($this->address);
        $address = ($address === '-' || $address === '') ? '' : $address . ' ,';
        if ($address === '') {
            return '';
        }
        return "{$address} {$this->department->description} - {$this->province->description} - {$this->district->description}";
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function more_address()
    {
        return $this->hasMany(PersonAddress::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function person_type()
    {
        return $this->belongsTo(PersonType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts_where_customer()
    {
        return $this->hasMany(Contract::class, 'customer_id');
    }

    public function dispatch_addresses()
    {
        return $this->hasMany(DispatchAddress::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dispatches_where_customer()
    {
        return $this->hasMany(Dispatch::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentary_files()
    {
        return $this->hasMany(DocumentaryFile::class);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeWhereIsEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses_where_supplier()
    {
        return $this->hasMany(Expense::class, 'supplier_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fixed_asset_purchases_where_customer()
    {
        return $this->hasMany(FixedAssetPurchase::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fixed_asset_purchases_where_supplier()
    {
        return $this->hasMany(FixedAssetPurchase::class, 'supplier_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order_forms_where_customer()
    {
        return $this->hasMany(OrderForm::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order_notes_where_customer()
    {
        return $this->hasMany(OrderNote::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perceptions_where_customer()
    {
        return $this->hasMany(Perception::class, 'customer_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_orders_where_supplier()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_settlements_where_supplier()
    {
        return $this->hasMany(PurchaseSettlement::class, 'supplier_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases_where_customer()
    {
        return $this->hasMany(Purchase::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases_where_supplier()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quotations_where_customer()
    {
        return $this->hasMany(Quotation::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function retentions_where_supplier()
    {
        return $this->hasMany(Retention::class, 'supplier_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sale_notes_where_customer()
    {
        return $this->hasMany(SaleNote::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sale_opportunities_where_customer()
    {
        return $this->hasMany(SaleOpportunity::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function technical_services_where_customer()
    {
        return $this->hasMany(TechnicalService::class, 'customer_id');
    }

    public function getContactAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setContactAttribute($value)
    {
        $this->attributes['contact'] = (is_null($value)) ? null : json_encode($value);
    }

    /**
     * Retorna un standar de nomenclatura para el modelo
     *
     * @param bool $withFullAddress
     * @param bool $childrens
     *
     * @return array
     */
    public function getCollectionData($withFullAddress = false, $childrens = false, $servers = false, $year = null, $grade = null)
    {

        $configuration = Configuration::getConfig();
        if ($year == null) {
            $year = Carbon::now()->year;
        }
        $addresses = $this->addresses;
        if ($withFullAddress == true) {
            $addresses = collect($addresses)->transform(function ($row) {
                return $row->getCollectionData();
            });
        }
        $person_type_descripton = '';
        if ($this->person_type !== null) {
            $person_type_descripton = $this->person_type->description;
        }
        $optional_mail = $this->getOptionalEmailArray();
        $optional_mail_send = [];
        if (!empty($this->email)) {
            $optional_mail_send[] = $this->email;
        }
        $total_optional_mail = count($optional_mail);
        for ($i = 0; $i < $total_optional_mail; $i++) {
            $temp = trim($optional_mail[$i]['email']);
            if (!empty($temp) && $temp != $this->email) {
                $optional_mail_send[] = $temp;
            }
        }
        /** @var \App\Models\Tenant\Catalogs\Department  $department */
        $department = \App\Models\Tenant\Catalogs\Department::find($this->department_id);
        if (!empty($department)) {
            $department = [
                "id" => $department->id,
                "description" => $department->description,
                "active" => $department->active,
            ];
        }

        $location_id = [];
        /** @var \App\Models\Tenant\Catalogs\Department  $department */
        $department = \App\Models\Tenant\Catalogs\Department::find($this->department_id);
        if (!empty($department)) {
            $department = [
                "id" => $department->id,
                "description" => $department->description,
                "active" => $department->active,
            ];
            array_push($location_id, $department['id']);
        }
        $province = \App\Models\Tenant\Catalogs\Province::find($this->province_id);

        if (!empty($province)) {
            $province = [
                "id" => $province->id,
                "description" => $province->description,
                "active" => $province->active,
            ];
            array_push($location_id, $province['id']);
        }
        $district = \App\Models\Tenant\Catalogs\District::find($this->district_id);

        if (!empty($district)) {
            $district = [
                "id" => $district->id,
                "description" => $district->description,
                "active" => $district->active,
            ];
            array_push($location_id, $district['id']);
        }
        $seller = User::find($this->seller_id);
        if (!empty($seller)) {
            $seller = $seller->getCollectionData();
        }

        $data_credit = null;
        
        if ($configuration->bill_of_exchange_special) {
            $data_credit = $this->getCreditData();
        }
        $total_used = 0;
        $amount_available = 0;
        if ($data_credit) {
            $line_credit = floatval($this->line_credit);
            $total_used = floatval($data_credit->sum('total'));
            $amount_available = $line_credit - $total_used;
        }
        $data = [
            'line_credit' => number_format($this->line_credit, 2),
            'total_used' => number_format($total_used, 2),
            'amount_available' => number_format($amount_available, 2),
            'auto_retention' => (bool)$this->auto_retention,
            'qualification_client' => $this->qualification_client,
            'telephones' => $this->telephones->pluck('telephone'),
            'dispatch_addresses' => $this->dispatch_addresses ?  $this->dispatch_addresses->transform(function ($row) {
                if ($row) {
                    //if row is not object cast it
                    if (!is_object($row)) {
                        $row = (object)$row;
                    }

                    $address = $row->address;
                    $location_id =  isset($row->location_id) ? $row->location_id : null;
                    $location = null;
                    if ($location_id) {
                        //if has index 2 
                        if (isset($location_id[2])) {
                            $location = func_get_location($location_id[2]);
                            $address = $address . ' - ' . $location;
                        }
                        //if has index 1
                    }
                    return [
                        'id' => $row->id,
                        'address' => $address,
                        'location_id' => $location_id,
                        'reason' => $row->reason,
                        'agency' => $row->agency,
                        'person' => $row->person,
                        'person_document' => $row->person_document,
                        'person_telephone' => $row->person_telephone,
                        'google_location' => $row->google_location,
                        'reference' => $row->reference,
                        'identity_document_type_id' => $row->identity_document_type_id,

                    ];
                } else {

                    return [
                        'id' => null,
                        'address' => '',
                        'location_id' => null,
                    ];
                }
            }) : [],
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'all_addresses' => $this->all_addresses(),
            'location' => $this->location,
            'is_driver' => (bool)$this->is_driver,
            'id' => $this->id,
            'color' => $this->color,
            'photo_filename' => $this->photo_filename,
            'photo_temp_image' => $this->getPhotoForView(),
            'photo_temp_path' => null,
            'description' => $this->number . ' - ' . $this->name,
            'name' => $this->name,
            'number' => $this->number,
            'identity_document_type_id' => $this->identity_document_type_id,
            'identity_document_type_code' => $this->identity_document_type->code,
            'address' => $this->address ? $this->address : '-',
            'internal_code' => $this->internal_code,
            'barcode' => $this->barcode,
            'observation' => $this->observation,
            'seller' => $seller,
            'zone' => $this->getZone(),
            'zone_id' => $this->zone_id,
            'seller_id' => $this->seller_id,
            'website' => $this->website,
            'document_type' => $this->identity_document_type->description,
            'enabled' => (bool)$this->enabled,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
            'type' => $this->type,
            'trade_name' => $this->trade_name,
            'country_id' => $this->country_id,
            'nationality_id' => $this->nationality_id,
            'department_id' => $department['id'] ?? null,
            'department' => $department,
            'province_id' => $province['id'] ?? null,
            'province' => $province,
            'person_reg_id' => $this->person_reg_id,
            'district_id' => $district['id'] ?? null,
            'district' => $district,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'perception_agent' => (bool)$this->perception_agent,
            'percentage_perception' => $this->percentage_perception,
            'state' => $this->state,
            'condition' => $this->condition,
            'person_type_id' => $this->person_type_id,
            'person_type' => $person_type_descripton,
            'contact' => $this->contact,
            'comment' => $this->comment,
            'addresses' => $addresses,
            'parent_id' => $this->parent_id,
            'credit_days' => (int)$this->credit_days,
            'optional_email' => $optional_mail,
            'optional_email_send' => implode(',', $optional_mail_send),
            'childrens' => [],
            'accumulated_points' => $this->accumulated_points,
            'has_discount' => $this->has_discount,
            'discount_type' => $this->discount_type,
            'discount_amount' => $this->discount_amount,
            'location_id' => $location_id,
            'person_date' => $this->person_date ? Carbon::parse($this->person_date)->format('Y-m-d') : null,
            'months' => $this->parent_id != 0 ? $this->getPayForMonths($year) : null,
            // 'student' => $this->student()->exists() ? $this->student()->latest()->first() : null,
            'student' => $this->check_student($grade),

        ];

        if ($childrens == true) {
            $child = $this->children_person->transform(function ($row) {
                return $row->getCollectionData();
            });
            $data['childrens'] = $child;
            $parent = null;
            if ($this->parent_person) {
                $parent = $this->parent_person->getCollectionData();
            }

            $data['parent'] = $parent;
        }

        if ($servers == true) {

            $serv = FullSuscriptionServerDatum::where('person_id', $this->id)->get();
            $extra_data = FullSuscriptionUserDatum::where('person_id', $this->id)->first();
            if (empty($extra_data)) {
                $extra_data = new FullSuscriptionUserDatum();
            }
            $data['servers'] = $serv;
            $data['person_id'] = $extra_data->getPersonId();
            $data['discord_user'] = $extra_data->getDiscordUser();
            $data['slack_channel'] = $extra_data->getSlackChannel();
            $data['discord_channel'] = $extra_data->getDiscordChannel();
            $data['gitlab_user'] = $extra_data->getGitlabUser();
        }
        $aval = [
            'telephone_aval' => null,
            'address_aval' => null,
            'location_id_aval' => null,
            'identity_document_type_id_aval' => '1',
            'number_aval' => null,
            'name_aval' => null,
            'trade_name_aval' => null,
            'country_id_aval' => 'PE',
        ];
        $person_aval = $this->person_aval;
        if ($person_aval) {
            $aval = $person_aval->getCollectionData();
        }

        $data = array_merge($data, $aval);
    

        return $data;
    }

    public function getCollectionDataDocument($withFullAddress = false, $childrens = false, $servers = false, $year = null, $grade = null)
    {

        $configuration = Configuration::getConfig();
        if ($year == null) {
            $year = Carbon::now()->year;
        }
        $addresses = $this->addresses;
        if ($withFullAddress == true) {
            $addresses = collect($addresses)->transform(function ($row) {
                return $row->getCollectionData();
            });
        }
        $person_type_descripton = '';
        if ($this->person_type !== null) {
            $person_type_descripton = $this->person_type->description;
        }
        $optional_mail = $this->getOptionalEmailArray();
        $optional_mail_send = [];
        if (!empty($this->email)) {
            $optional_mail_send[] = $this->email;
        }
        $total_optional_mail = count($optional_mail);
        for ($i = 0; $i < $total_optional_mail; $i++) {
            $temp = trim($optional_mail[$i]['email']);
            if (!empty($temp) && $temp != $this->email) {
                $optional_mail_send[] = $temp;
            }
        }
        /** @var \App\Models\Tenant\Catalogs\Department  $department */
        if (!empty($department)) {
            $department = [
                "id" => $department->id,
                "description" => $department->description,
                "active" => $department->active,
            ];
        }

        $location_id = [];
        /** @var \App\Models\Tenant\Catalogs\Department  $department */
        if (!empty($department)) {
            $department = [
                "id" => $department->id,
                "description" => $department->description,
                "active" => $department->active,
            ];
            array_push($location_id, $department['id']);
        }

        if (!empty($province)) {
            $province = [
                "id" => $province->id,
                "description" => $province->description,
                "active" => $province->active,
            ];
            array_push($location_id, $province['id']);
        }

        if (!empty($district)) {
            $district = [
                "id" => $district->id,
                "description" => $district->description,
                "active" => $district->active,
            ];
            array_push($location_id, $district['id']);
        }
        if (!empty($seller)) {
            $seller = $seller->getCollectionData();
        }

        $data_credit = null;
        
        if ($configuration->bill_of_exchange_special) {
            $data_credit = $this->getCreditData();
        }
        $total_used = 0;
        $amount_available = 0;
        if ($data_credit) {
            $line_credit = floatval($this->line_credit);
            $total_used = floatval($data_credit->sum('total'));
            $amount_available = $line_credit - $total_used;
        }
        $data = [
            'line_credit' => number_format($this->line_credit, 2),
            'total_used' => number_format($total_used, 2),
            'amount_available' => number_format($amount_available, 2),
            'auto_retention' => (bool)$this->auto_retention,
            'qualification_client' => $this->qualification_client,
            'telephones' => $this->telephones->pluck('telephone'),
            'dispatch_addresses' => $this->dispatch_addresses ?  $this->dispatch_addresses->transform(function ($row) {
                if ($row) {
                    //if row is not object cast it
                    if (!is_object($row)) {
                        $row = (object)$row;
                    }

                    $address = $row->address;
                    $location_id =  isset($row->location_id) ? $row->location_id : null;
                    $location = null;
                    if ($location_id) {
                        //if has index 2 
                        if (isset($location_id[2])) {
                            $location = func_get_location($location_id[2]);
                            $address = $address . ' - ' . $location;
                        }
                        //if has index 1
                    }
                    return [
                        'id' => $row->id,
                        'address' => $address,
                        'location_id' => $location_id,
                        'reason' => $row->reason,
                        'agency' => $row->agency,
                        'person' => $row->person,
                        'person_document' => $row->person_document,
                        'person_telephone' => $row->person_telephone,
                        'google_location' => $row->google_location,

                    ];
                } else {

                    return [
                        'id' => null,
                        'address' => '',
                        'location_id' => null,
                    ];
                }
            }) : [],
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'all_addresses' => $this->all_addresses(),
            'location' => $this->location,
            'is_driver' => (bool)$this->is_driver,
            'id' => $this->id,
            'color' => $this->color,
            'photo_filename' => $this->photo_filename,
            'photo_temp_image' => $this->getPhotoForView(),
            'photo_temp_path' => null,
            'description' => $this->number . ' - ' . $this->name,
            'name' => $this->name,
            'number' => $this->number,
            'identity_document_type_id' => $this->identity_document_type_id,
            'identity_document_type_code' => $this->identity_document_type->code,
            'address' => $this->address ? $this->address : '-',
            'internal_code' => $this->internal_code,
            'barcode' => $this->barcode,
            'observation' => $this->observation,
            'seller' => $this->seller,
            'zone' => $this->zoneRelation,
            'zone_id' => $this->zone_id,
            'seller_id' => $this->seller_id,
            'website' => $this->website,
            'document_type' => $this->identity_document_type->description,
            'enabled' => (bool)$this->enabled,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
            'type' => $this->type,
            'trade_name' => $this->trade_name,
            'country_id' => $this->country_id,
            'nationality_id' => $this->nationality_id,
            'department_id' => $this->department_id,
            'department' => $this->department,
            'province_id' => $this->province_id,
            'province' => $this->province,
            'person_reg_id' => $this->person_reg_id,
            'district_id' => $this->district_id,
            'district' => $this->district,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'perception_agent' => (bool)$this->perception_agent,
            'percentage_perception' => $this->percentage_perception,
            'state' => $this->state,
            'condition' => $this->condition,
            'person_type_id' => $this->person_type_id,
            'person_type' => $person_type_descripton,
            'contact' => $this->contact,
            'comment' => $this->comment,
            'addresses' => $addresses,
            'parent_id' => $this->parent_id,
            'credit_days' => (int)$this->credit_days,
            'optional_email' => $optional_mail,
            'optional_email_send' => implode(',', $optional_mail_send),
            'childrens' => [],
            'accumulated_points' => $this->accumulated_points,
            'has_discount' => $this->has_discount,
            'discount_type' => $this->discount_type,
            'discount_amount' => $this->discount_amount,
            'location_id' => $location_id,
            'person_date' => $this->person_date ? Carbon::parse($this->person_date)->format('Y-m-d') : null,
            'months' => $this->parent_id != 0 ? $this->getPayForMonths($year) : null,
            // 'student' => $this->student()->exists() ? $this->student()->latest()->first() : null,
            'student' => $this->check_student($grade),

        ];

        if ($childrens == true) {
            $child = $this->children_person->transform(function ($row) {
                return $row->getCollectionData();
            });
            $data['childrens'] = $child;
            $parent = null;
            if ($this->parent_person) {
                $parent = $this->parent_person->getCollectionData();
            }

            $data['parent'] = $parent;
        }

        if ($servers == true) {

            $serv = FullSuscriptionServerDatum::where('person_id', $this->id)->get();
            $extra_data = FullSuscriptionUserDatum::where('person_id', $this->id)->first();
            if (empty($extra_data)) {
                $extra_data = new FullSuscriptionUserDatum();
            }
            $data['servers'] = $serv;
            $data['person_id'] = $extra_data->getPersonId();
            $data['discord_user'] = $extra_data->getDiscordUser();
            $data['slack_channel'] = $extra_data->getSlackChannel();
            $data['discord_channel'] = $extra_data->getDiscordChannel();
            $data['gitlab_user'] = $extra_data->getGitlabUser();
        }
        $aval = [
            'telephone_aval' => null,
            'address_aval' => null,
            'location_id_aval' => null,
            'identity_document_type_id_aval' => '1',
            'number_aval' => null,
            'name_aval' => null,
            'trade_name_aval' => null,
            'country_id_aval' => 'PE',
        ];
        $person_aval = $this->person_aval;
        if ($person_aval) {
            $aval = $person_aval->getCollectionData();
        }

        $data = array_merge($data, $aval);
    

        return $data;
    }

    public function getCreditData()
    {
        $data = null;
        $data = DashboardView::getUnpaidByCustomerJustTotalAndTotalPayment($this->id)->get();
    
        return $data;
    }

    public function person_aval()
    {
        return $this->hasOne(PersonAval::class, 'person_id');
    }
    function check_student($grade = null)
    {

        $student = $this->student()->exists();
        if ($student) {
            if ($grade != null) {
                $student = $this->student()->where('grade', $grade)->latest()->first();
                if ($student) {
                    return $student;
                }
            }
            return $this->student()->latest()->first();
        }
        return null;
    }
    function getPayForMonths($currentYear)
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $i + 1;
            $date = Carbon::createFromDate($currentYear, $month, 1)->format('Y-m-d');

            $payment_suscription = SuscriptionPayment::where('child_id', $this->id)
                ->where('client_id', $this->parent_id)
                ->where('period', $date)->get();
            $total = 0;
            foreach ($payment_suscription as $key => $value) {
                if ($value->document) {
                    $periods = count($value->document->periods) != 0 ? count($value->document->periods) : 1;
                    $total += $value->document->total / $periods;
                } else {
                    $periods = count($value->sale_note->periods) != 0 ? count($value->sale_note->periods) : 1;
                    $total += $value->sale_note->total / $periods;
                }
            }

            $months[] = $total;
        }

        return $months;
    }
    public function person_reg()
    {
        return $this->belongsTo(PersonRegModel::class, 'person_reg_id');
    }
    public function getPhotoForView()
    {
        return $this->photo_filename ? (new ModelTenant)->getPathPublicUploads('users', $this->photo_filename) : null;
    }
    /**
     * @return array
     */
    public function getOptionalEmailArray(): array
    {
        $data = unserialize($this->optional_email);
        if ($data === false) {
            $data = [];
        }

        return $data;
    }

    public function telephones()
    {
        return $this->hasMany(TelephonePerson::class);
    }
    /**
     * @return string
     */
    public function getObservation(): string
    {
        return $this->observation;
    }

    public function sale_notes(){
        return $this->hasMany(SaleNote::class, 'customer_id', 'id');
    }

    public function full_suscription_credit(){
        return $this->hasOne(FullSuscriptionCreditPerson::class, 'person_id', 'id');
    }
    /**
     * @param string $observation
     *
     * @return Person
     */
    public function setObservation(string $observation): Person
    {
        $this->observation = $observation;
        return $this;
    }


    /**
     * @return string
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * @param string $website
     *
     * @return Person
     */
    public function setWebsite(string $website): Person
    {
        $this->website = $website;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOptionalEmail(): ?string
    {
        return $this->optional_email;
    }

    /**
     * @param string|null $optional_email
     *
     * @return Person
     */
    public function setOptionalEmail(?string $optional_email): Person
    {
        $this->optional_email = $optional_email;
        return $this;
    }

    /**
     * @param array $optional_email_array
     *
     * @return Person
     */
    public function setOptionalEmailArray(array $optional_email_array = []): Person
    {
        $this->optional_email = serialize($optional_email_array);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return (int)$this->parent_id;
    }

    /**
     * @param int|null $parent_id
     *
     * @return Person
     */
    public function setParentId(?int $parent_id): Person
    {
        $this->parent_id = (int)$parent_id;
        return $this;
    }

    /**
     * @return BelongsTo
     */
    public function zoneRelation()
    {
        
        // return $this->belongsTo(Zone::class, 'zone_id','id');
    
             return $this->belongsTo(Zone::class, 'zone_id', 'id');
    }
    /**
     * @return BelongsTo
     */

    public function getZone()
    {
        return Zone::find($this->zone_id);
    }


    /**
     * @return BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function scopeSearchCustomer(Builder $query, $dni_ruc, $name = null, $email = null)
    {
        $query->where('type', 'customers');
        $query->where('number', $dni_ruc);
        if (!empty($name)) {
            $query->where('name', 'like', "%$name%");
        }
        if (!empty($email)) {
            $query->where('email', 'like', "%$email%");
        }

        return $query;
    }


    /**
     *
     * Aplicar filtro por vendedor asignado al cliente
     *
     * Usado en:
     * PersonController - records
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function student()
    {
        return $this->hasMany(UserRelSuscriptionPlan::class, 'children_customer_id', 'id');
    }
    public function scopeWhereFilterCustomerBySeller($query, $type)
    {
        if ($type === 'customers') {
            $user = auth()->user() ?? auth('api')->user();

            if ($user->applyCustomerFilterBySeller()) {
                return $query->where('seller_id', $user->id);
            }
        }

        return $query;
    }


    /**
     *
     * Obtener datos para api (app)
     *
     * @return array
     */
    public function getApiRowResource()
    {
        return [
            'id' => $this->id,
            'description' => $this->getPersonDescription(),
            'name' => $this->name,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'number' => $this->number,
            'identity_document_type_id' => $this->identity_document_type_id,
            'identity_document_type_code' => $this->identity_document_type->code,
            'address' => $this->address,
            'telephone' => $this->telephone,
            'country_id' => $this->country_id,
            'district_id' => $this->district_id,
            'email' => $this->email,
            'enabled' => $this->enabled,
            'selected' => false,
            'identity_document_type_description' => $this->identity_document_type->description,
        ];
    }

    public function all_addresses()
    {
        $addresses = $this->person_addresses->transform(function ($row) {
            return [
                'id' => $row->id,
                'address' => trim($row->address .
                    ($row->department ? ' - ' . $row->department->description : '') .
                    ($row->province ? ' - ' . $row->province->description : '') .
                    ($row->district ? ' - ' . $row->district->description : '')),
                'is_principal' => false
            ];
        })->toArray();

        // Agregar dirección principal si existe
        if ($this->address) {
            array_unshift($addresses, [
                'id' => 'main',
                'address' => trim($this->address .
                    ($this->department ? ' - ' . $this->department->description : '') .
                    ($this->province ? ' - ' . $this->province->description : '') .
                    ($this->district ? ' - ' . $this->district->description : '')),
                'is_principal' => true
            ]);
        }

        return collect($addresses);
    }
    /**
     *
     * Descripción para mostrar en campos de búsqueda, etc
     *
     * @return string
     */
    public function getPersonDescription()
    {
        return "{$this->number} - {$this->name}";
    }


    /**
     *
     * Filtro para búsqueda de clientes/proveedores
     *
     * Usado en:
     * clientes - app
     *
     * @param  Builder $query
     * @param  string $input
     * @param  string $type
     * @return Builder
     */
    public function scopeWhereFilterRecordsApi($query, $input, $type)
    {
        return $query->where('name', 'like', "%{$input}%")
            ->orWhere('number', 'like', "%{$input}%")
            ->whereType($type)
            ->orderBy('name');
    }


    /**
     *
     * @return string
     */
    public function getTitlePersonDescription()
    {
        return $this->type === 'customers' ? 'Cliente' : 'Proveedor';
    }


    /**
     *
     * Filtro para no incluir relaciones en consulta
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereFilterWithOutRelations($query)
    {
        return $query->withOut([
            'identity_document_type',
            'country',
            'department',
            'province',
            'district'
        ]);
    }


    /**
     * Obtener datos iniciales para mostrar lista de clientes - App
     *
     * @param  int $take
     * @return array
     */
    public function scopeFilterApiInitialCustomers($query, $take = 10)
    {
        return $query->whereType('customers')
            ->whereFilterWithOutRelations()
            ->with(['identity_document_type'])
            ->orderBy('name')
            ->take($take);
    }

    /**
     *
     * Filtro para cliente varios por defecto
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWhereFilterVariousClients($query)
    {
        return $query->where([
            ['identity_document_type_id', '0'],
            ['number', '99999999'],
            ['type', 'customers'],
        ]);
    }


    /**
     *
     * Obtener puntos acumulados
     *
     * @param Builder $query
     * @param int $id
     * @return float
     */
    public function scopeGetOnlyAccumulatedPoints($query, $id)
    {
        return $query->whereFilterWithOutRelations()->select('accumulated_points')->findOrFail($id)->accumulated_points;
    }

    public static function getAccumulatedDue($person_id)
    {
        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select('document_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('document_id');
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

        $sale_note_payments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select('sale_note_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('sale_note_id');

        $sale_notes = DB::connection('tenant')
            ->table('sale_notes')
            ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
            ->join('users', 'users.id', '=', 'sale_notes.user_id')
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->select(DB::raw($sale_note_select))
            ->where('sale_notes.changed', false)
            ->where('sale_notes.total_canceled', false);

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

        $documents->where('documents.customer_id', $person_id);
        $sale_notes->where('sale_notes.customer_id', $person_id);
        $total = $documents->union($sale_notes)->sum('total_subtraction');
        return $total;
    }
}
