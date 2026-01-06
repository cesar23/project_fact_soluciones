<?php

namespace Modules\Optometry\Models;

use App\Models\Tenant\CashDocument;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Company;
use App\Models\Tenant\User;
use App\Models\Tenant\SoapType;
use App\Models\Tenant\Person;
use App\Models\Tenant\ModelTenant;
// use App\Traits\SellerIdTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Tenant\{
    Document,
    SaleNote
};


class OptometryService extends ModelTenant
{
    use UsesTenantConnection;
    // use SellerIdTrait;


    protected $perPage = 25;


    protected $fillable = [
        'description',
        'user_id',
        'soap_type_id',
        'establishment_id',
        'establishment',
        'customer_id',
        'customer',
        'currency_type_id',
        'payment_condition_id',
        'payment_method_type_id',
        'seller_id',
        'exchange_rate_sale',
        'total_prepayment',
        'total_charge',
        'total_discount',
        'total_exportation',
        'total_free',
        'total_taxed',
        'total_unaffected',
        'total_exonerated',
        'total_igv',
        'total_igv_free',
        'total_base_isc',
        'total_isc',
        'total_base_other_taxes',
        'total_other_taxes',
        'total_plastic_bag_taxes',
        'total_taxes',
        'total_value',
        'subtotal',
        'total',
        'is_editable',
        'cellphone',
        'date_of_issue',
        'time_of_issue',
        'filename',
        'cost',
        'prepayment',
        'important_note',
    ];

    protected $casts = [
        'user_id' => 'int',
        'establishment_id' => 'int',
        'customer_id' => 'int',
        'seller_id' => 'int',
        'exchange_rate_sale' => 'float',
        'total_prepayment' => 'float',
        'total_charge' => 'float',
        'total_discount' => 'float',
        'total_exportation' => 'float',
        'total_free' => 'float',
        'total_taxed' => 'float',
        'total_unaffected' => 'float',
        'total_exonerated' => 'float',
        'total_igv' => 'float',
        'total_igv_free' => 'float',
        'total_base_isc' => 'float',
        'total_isc' => 'float',
        'total_base_other_taxes' => 'float',
        'total_other_taxes' => 'float',
        'total_plastic_bag_taxes' => 'float',
        'total_taxes' => 'float',
        'total_value' => 'float',
        'subtotal' => 'float',
        'total' => 'float',
        'is_editable' => 'int',
        'cost' => 'float',
        'prepayment' => 'float',

        'date_of_issue' => 'date',
    ];

    protected $dates = [
        'date_of_issue',
        // 'time_of_issue'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $item) {

            if (empty($item->establishment_id) && !empty($item->user_id)) {
                $item->establishment_id = $item->user->establishment_id;
            }
            if (empty($item->currency_type_id)) $item->currency_type_id = 'PEN';
            //self::adjustSellerIdField($model);
        });
        static::retrieved(function (self $item) {

            if (empty($item->establishment_id) && !empty($item->user_id)) {
                $item->establishment_id = $item->user->establishment_id;
            }
            if (empty($item->currency_type_id)) $item->currency_type_id = 'PEN';
        });
    }




    /**
     * @param $value
     *
     * @return object|null
     */
    public function getEstablishmentAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    /**
     * @param $value
     */
    public function setEstablishmentAttribute($value)
    {
        $this->attributes['establishment'] = (is_null($value)) ? null : json_encode($value);
    }

    /**
     * @return BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    /**
     * @return BelongsTo
     */
    public function soap_type()
    {
        return $this->belongsTo(SoapType::class, 'soap_type_id', 'id');
    }

    /**
     * @param $value
     *
     * @return object|null
     */
    public function getCustomerAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    /**
     * @param $value
     */
    public function setCustomerAttribute($value)
    {
        $this->attributes['customer'] = (is_null($value)) ? null : json_encode($value);
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function person()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    /**
     * @param $query
     *
     * @return null
     */
    public function scopeWhereTypeUser($query, $params = [])
    {
        if (isset($params['user_id'])) {
            $user_id = (int)$params['user_id'];
            $user = User::find($user_id);
            if (!$user) {
                $user = new User();
            }
        } else {
            $user = auth()->user();
        }
        return ($user->type == 'seller') ? $query->where('user_id', $user->id) : null;
    }

    /**
     * @param $value
     */
    public function setImportantNoteAttribute($value)
    {
        $this->attributes['important_note'] = (is_null($value)) ? null : json_encode($value);
    }

    /**
     * @return HasMany
     */
    public function cash_documents()
    {
        return $this->hasMany(CashDocument::class);
    }

    /**
     * @return HasOne
     */
    public function document()
    {
        return $this->hasOne(Document::class);
    }

    /**
     * @return HasOne
     */
    public function sale_note()
    {
        return $this->hasOne(SaleNote::class);
    }

    /**
     * @param $value
     *
     * @return array|null
     */
    public function getImportantNoteAttribute($value)
    {
        return (is_null($value)) ? null : (array)json_decode($value);
    }

    /**
     * @return HasMany
     */
    public function optometry_service_payments()
    {
        return $this->hasMany(OptometryServicePayment::class);
    }

    /**
     * @return HasMany
     */
    public function payments()
    {
        return $this->optometry_service_payments();
    }

    /**
     * @return HasMany
     */
    public function prepayments()
    {
        return $this->optometry_service_payments();
    }

    /**
     * @return string
     */
    public function getNumberFullAttribute()
    {
        return "TS-{$this->id}";
    }

    /**
     * @return string
     */
    public function getCurrencyTypeIdAttribute()
    {
        return 'PEN';
    }

    /**
     * @return HasMany
     */
    public function optometry_service_item()
    {
        return $this->hasMany(OptometryServiceItem::class, 'optometry_services_id');
    }

    /**
     * @return array
     */
    public function getCollectionData()
    {
        $items = $this->items->transform(function ($row) {
            /** @var OptometryServiceItem $row */
            return $row->getCollectionData();
        });
        $total = $this->cost + $this->total;

        //docs asociados
        $has_document_sale_note = false;
        $number_document_sale_note = null;

        if ($this->sale_note || $this->document) {
            $has_document_sale_note = true;
            $number_document_sale_note = ($this->sale_note) ? $this->sale_note->number_full : $this->document->number_full;
        }
        $data = array_merge($this->toArray(), [
            'id' => $this->id,
            'soap_type_id' => $this->soap_type_id,
            'cellphone' => $this->cellphone,
            'serial_number' => $this->serial_number,
            'cost' => $this->cost,
            'total' => $this->total,
            'sum_total' => $total,
            'prepayment' => $this->prepayment,
            'general' => $this->optometry_service_data,
            // 'balance' => $this->cost - $this->prepayment,
            'balance' => ($total) - collect($this->payments)->sum('payment'),
            'date_of_issue' => $this->date_of_issue->format('Y-m-d'),
            'customer_name' => $this->customer->name,
            'customer_number' => $this->customer->number,

            'customer_id' => $this->customer_id,
            'time_of_issue' => $this->time_of_issue,
            'state' => $this->state,

            'important_note' => $this->important_note ?: [],

            'items' => $items,
            'payments' => $this->payments,

            'has_document_sale_note' => $has_document_sale_note,
            'number_document_sale_note' => $number_document_sale_note,

        ]);

        return $data;
    }

    public function optometry_service_data()
    {
        return $this->hasOne(OptometryServiceData::class, 'optometry_service_id');
    }

    /**
     * @param Company|null $company
     * @param Person|null  $customer
     *
     * @return array
     */
    public function getPdfData(Company $company = null, Person $customer = null)
    {

        $stablishment = $this->user->establishment;
        if ($company == null) {
            $company = Company::first();
        }
        if ($customer == null) {
            $customer = $this->person;
        }
        $data = [];
        //$data['company'] = $company->toArray();
        //$data['company']['logo'] = (!empty($company->getLogo())) ? $company->getLogo() : null;
        //$data['stablishment'] = $stablishment->toArray();
        //$data['customer'] = $customer->toArray();
        // $data['document'] = $this->toArray();
        $data['items'] = $this->items()->get()->transform(function ($row) {
            return $row->getCollectionData();
        });

        return $data;
    }

    /**
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(OptometryServiceItem::class, 'optometry_services_id');
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return OptometryService
     */
    public function setUserId(int $user_id): OptometryService
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSoapTypeId(): string
    {
        return $this->soap_type_id;
    }

    /**
     * @param string $soap_type_id
     *
     * @return OptometryService
     */
    public function setSoapTypeId(string $soap_type_id): OptometryService
    {
        $this->soap_type_id = $soap_type_id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEstablishmentId(): ?int
    {
        return $this->establishment_id;
    }

    /**
     * @param int|null $establishment_id
     *
     * @return OptometryService
     */
    public function setEstablishmentId(?int $establishment_id): OptometryService
    {
        $this->establishment_id = $establishment_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEstablishment(): ?string
    {
        return $this->establishment;
    }

    /**
     * @param string|null $establishment
     *
     * @return OptometryService
     */
    public function setEstablishment(?string $establishment): OptometryService
    {
        $this->establishment = $establishment;
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customer_id;
    }

    /**
     * @param int $customer_id
     *
     * @return OptometryService
     */
    public function setCustomerId(int $customer_id): OptometryService
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomer(): string
    {
        return $this->customer;
    }

    /**
     * @param string $customer
     *
     * @return OptometryService
     */
    public function setCustomer(string $customer): OptometryService
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrencyTypeId(): ?string
    {
        return $this->currency_type_id;
    }

    /**
     * @return BelongsTo
     */
    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    /**
     * @param string|null $currency_type_id
     *
     * @return OptometryService
     */
    public function setCurrencyTypeId(?string $currency_type_id): OptometryService
    {
        $this->currency_type_id = $currency_type_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentConditionId(): ?string
    {
        return $this->payment_condition_id;
    }

    /**
     * @param string|null $payment_condition_id
     *
     * @return OptometryService
     */
    public function setPaymentConditionId(?string $payment_condition_id): OptometryService
    {
        $this->payment_condition_id = $payment_condition_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethodTypeId(): ?string
    {
        return $this->payment_method_type_id;
    }

    /**
     * @param string|null $payment_method_type_id
     *
     * @return OptometryService
     */
    public function setPaymentMethodTypeId(?string $payment_method_type_id): OptometryService
    {
        $this->payment_method_type_id = $payment_method_type_id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSellerId(): ?int
    {
        return $this->seller_id;
    }

    /**
     * @param int|null $seller_id
     *
     * @return OptometryService
     */
    public function setSellerId(?int $seller_id): OptometryService
    {
        $this->seller_id = $seller_id;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getExchangeRateSale(): ?float
    {
        return $this->exchange_rate_sale;
    }

    /**
     * @param float|null $exchange_rate_sale
     *
     * @return OptometryService
     */
    public function setExchangeRateSale(?float $exchange_rate_sale): OptometryService
    {
        $this->exchange_rate_sale = $exchange_rate_sale;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalPrepayment(): ?float
    {
        return $this->total_prepayment;
    }

    /**
     * @param float|null $total_prepayment
     *
     * @return OptometryService
     */
    public function setTotalPrepayment(?float $total_prepayment): OptometryService
    {
        $this->total_prepayment = $total_prepayment;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalCharge(): ?float
    {
        return $this->total_charge;
    }

    /**
     * @param float|null $total_charge
     *
     * @return OptometryService
     */
    public function setTotalCharge(?float $total_charge): OptometryService
    {
        $this->total_charge = $total_charge;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalDiscount(): ?float
    {
        return $this->total_discount;
    }

    /**
     * @param float|null $total_discount
     *
     * @return OptometryService
     */
    public function setTotalDiscount(?float $total_discount): OptometryService
    {
        $this->total_discount = $total_discount;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalExportation(): ?float
    {
        return $this->total_exportation;
    }

    /**
     * @param float|null $total_exportation
     *
     * @return OptometryService
     */
    public function setTotalExportation(?float $total_exportation): OptometryService
    {
        $this->total_exportation = $total_exportation;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalFree(): ?float
    {
        return $this->total_free;
    }

    /**
     * @param float|null $total_free
     *
     * @return OptometryService
     */
    public function setTotalFree(?float $total_free): OptometryService
    {
        $this->total_free = $total_free;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalTaxed(): ?float
    {
        return $this->total_taxed;
    }

    /**
     * @param float|null $total_taxed
     *
     * @return OptometryService
     */
    public function setTotalTaxed(?float $total_taxed): OptometryService
    {
        $this->total_taxed = $total_taxed;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalUnaffected(): ?float
    {
        return $this->total_unaffected;
    }

    /**
     * @param float|null $total_unaffected
     *
     * @return OptometryService
     */
    public function setTotalUnaffected(?float $total_unaffected): OptometryService
    {
        $this->total_unaffected = $total_unaffected;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalExonerated(): ?float
    {
        return $this->total_exonerated;
    }

    /**
     * @param float|null $total_exonerated
     *
     * @return OptometryService
     */
    public function setTotalExonerated(?float $total_exonerated): OptometryService
    {
        $this->total_exonerated = $total_exonerated;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalIgv(): ?float
    {
        return $this->total_igv;
    }

    /**
     * @param float|null $total_igv
     *
     * @return OptometryService
     */
    public function setTotalIgv(?float $total_igv): OptometryService
    {
        $this->total_igv = $total_igv;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalIgvFree(): ?float
    {
        return $this->total_igv_free;
    }

    /**
     * @param float|null $total_igv_free
     *
     * @return OptometryService
     */
    public function setTotalIgvFree(?float $total_igv_free): OptometryService
    {
        $this->total_igv_free = $total_igv_free;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalBaseIsc(): ?float
    {
        return $this->total_base_isc;
    }

    /**
     * @param float|null $total_base_isc
     *
     * @return OptometryService
     */
    public function setTotalBaseIsc(?float $total_base_isc): OptometryService
    {
        $this->total_base_isc = $total_base_isc;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalIsc(): ?float
    {
        return $this->total_isc;
    }

    /**
     * @param float|null $total_isc
     *
     * @return OptometryService
     */
    public function setTotalIsc(?float $total_isc): OptometryService
    {
        $this->total_isc = $total_isc;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalBaseOtherTaxes(): ?float
    {
        return $this->total_base_other_taxes;
    }

    /**
     * @param float|null $total_base_other_taxes
     *
     * @return OptometryService
     */
    public function setTotalBaseOtherTaxes(?float $total_base_other_taxes): OptometryService
    {
        $this->total_base_other_taxes = $total_base_other_taxes;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalOtherTaxes(): ?float
    {
        return $this->total_other_taxes;
    }

    /**
     * @param float|null $total_other_taxes
     *
     * @return OptometryService
     */
    public function setTotalOtherTaxes(?float $total_other_taxes): OptometryService
    {
        $this->total_other_taxes = $total_other_taxes;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalPlasticBagTaxes(): ?float
    {
        return $this->total_plastic_bag_taxes;
    }

    /**
     * @param float|null $total_plastic_bag_taxes
     *
     * @return OptometryService
     */
    public function setTotalPlasticBagTaxes(?float $total_plastic_bag_taxes): OptometryService
    {
        $this->total_plastic_bag_taxes = $total_plastic_bag_taxes;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalTaxes(): ?float
    {
        return $this->total_taxes;
    }

    /**
     * @param float|null $total_taxes
     *
     * @return OptometryService
     */
    public function setTotalTaxes(?float $total_taxes): OptometryService
    {
        $this->total_taxes = $total_taxes;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalValue(): ?float
    {
        return $this->total_value;
    }

    /**
     * @param float|null $total_value
     *
     * @return OptometryService
     */
    public function setTotalValue(?float $total_value): OptometryService
    {
        $this->total_value = $total_value;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    /**
     * @param float|null $subtotal
     *
     * @return OptometryService
     */
    public function setSubtotal(?float $subtotal): OptometryService
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * @param float|null $total
     *
     * @return OptometryService
     */
    public function setTotal(?float $total): OptometryService
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getIsEditable(): ?int
    {
        return $this->is_editable;
    }

    /**
     * @param int|null $is_editable
     *
     * @return OptometryService
     */
    public function setIsEditable(?int $is_editable): OptometryService
    {
        $this->is_editable = $is_editable;
        return $this;
    }

    /**
     * @return string
     */
    public function getCellphone(): string
    {
        return $this->cellphone;
    }

    /**
     * @param string $cellphone
     *
     * @return OptometryService
     */
    public function setCellphone(string $cellphone): OptometryService
    {
        $this->cellphone = $cellphone;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getDateOfIssue(): Carbon
    {
        return $this->date_of_issue;
    }

    /**
     * @param Carbon $date_of_issue
     *
     * @return OptometryService
     */
    public function setDateOfIssue(Carbon $date_of_issue): OptometryService
    {
        $this->date_of_issue = $date_of_issue;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getTimeOfIssue(): Carbon
    {
        return $this->time_of_issue;
    }

    /**
     * @param Carbon $time_of_issue
     *
     * @return OptometryService
     */
    public function setTimeOfIssue(Carbon $time_of_issue): OptometryService
    {
        $this->time_of_issue = $time_of_issue;
        return $this;
    }



    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return OptometryService
     */
    public function setState(string $state): OptometryService
    {
        $this->state = $state;
        return $this;
    }





    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     *
     * @return OptometryService
     */
    public function setFilename(?string $filename): OptometryService
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     *
     * @return OptometryService
     */
    public function setCost(float $cost): OptometryService
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrepayment(): float
    {
        return $this->prepayment;
    }

    /**
     * @param float $prepayment
     *
     * @return OptometryService
     */
    public function setPrepayment(float $prepayment): OptometryService
    {
        $this->prepayment = $prepayment;
        return $this;
    }



    /**
     * @return SoapType
     */
    public function getSoapType(): SoapType
    {
        return $this->soap_type;
    }

    /**
     * @param SoapType $soap_type
     *
     * @return OptometryService
     */
    public function setSoapType(SoapType $soap_type): OptometryService
    {
        $this->soap_type = $soap_type;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return OptometryService
     */
    public function setUser(User $user): OptometryService
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return CashDocument[]|Collection
     */
    public function getCashDocuments()
    {
        return $this->cash_documents;
    }

    /**
     * @param CashDocument[]|Collection $cash_documents
     *
     * @return OptometryService
     */
    public function setCashDocuments($cash_documents)
    {
        $this->cash_documents = $cash_documents;
        return $this;
    }

    /**
     * @return Collection|OptometryServicePayment[]
     */
    public function getOptometryServicePayments()
    {
        return $this->optometry_service_payments;
    }

    /**
     * @param Collection|OptometryServicePayment[] $optometry_service_payments
     *
     * @return OptometryService
     */
    public function setOptometryServicePayments($optometry_service_payments)
    {
        $this->optometry_service_payments = $optometry_service_payments;
        return $this;
    }


    /**
     * 
     * Obtener descripción del tipo de documento
     *
     * @return string
     */
    public function getDocumentTypeDescription()
    {
        return 'SERVICIO TÉCNICO';
    }


    /**
     * 
     * Obtener pagos en efectivo
     *
     * @return Collection
     */
    public function getCashPayments()
    {
        return $this->payments()->whereFilterCashPayment()->get()->transform(function ($row) { {
                return $row->getRowResourceCashPayment();
            }
        });
    }


    /**
     * Total del servicio tecnico
     *
     * @return float
     */
    public function getTotalRecordAttribute()
    {
        return $this->cost + $this->total;
    }


    /**
     * 
     * Total pagado
     *
     * @return float
     */
    public function getTotalPaidAttribute()
    {
        return (float) $this->payments()->sum('payment');
    }


    /**
     *
     * Validar si esta pagado a la totalidad
     *
     * @return bool
     */
    public function hasFullPayment()
    {
        return $this->total_record == $this->total_paid;
    }


    /**
     *
     * Validar si cumple las condiciones para sumar a los ingresos en reporte (pos)
     *
     * @return bool
     */
    public function applyToCash()
    {
        return $this->hasFullPayment();
    }


    /**
     * 
     * Tipo de transaccion para caja
     *
     * @return string
     */
    public function getTransactionTypeCash()
    {
        return 'income';
    }


    /**
     * 
     * Tipo de documento para caja
     *
     * @return string
     */
    public function getDocumentTypeCash()
    {
        return $this->getTable();
    }


    /**
     * 
     * Datos para resumen diario de operaciones
     *
     * @return array
     */
    public function applySummaryDailyOperations()
    {
        return [
            'transaction_type' => $this->getTransactionTypeCash(),
            'document_type' => $this->getDocumentTypeCash(),
            'apply' => true,
        ];
    }


    /**
     *
     * Obtener total de pagos en efectivo sin considerar destino
     *
     * @return float
     */
    public function totalCashPaymentsWithoutDestination()
    {
        return $this->payments()->filterCashPaymentWithoutDestination()->sum('payment');
    }


    /**
     *
     * Obtener total de pagos en transferencia
     *
     * @return float
     */
    public function totalTransferPayments()
    {
        return $this->payments()->filterTransferPayment()->sum('payment');
    }


    /**
     * 
     * Validar que no tenga comprobantes asociados
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeWhereNotHasDocuments($query)
    {
        return $query->whereDoesntHave('document');
    }
}
