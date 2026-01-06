<?php

namespace Modules\Purchase\Models;

use App\Models\Tenant\User;
use App\Models\Tenant\SoapType;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\StateType;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Person;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Series;
use Modules\Sale\Models\SaleOpportunity;

class PurchaseOrder extends ModelTenant
{

    protected $fillable = [
        'series',
        'number',
        'bank_name',
        'bank_account_number',
        'created_by_id',
        'approved_by_id',
        'client_internal_id',
        'type',
        'quotation_id',
        'observation',
        'user_id',
        'external_id',
        'prefix',
        'establishment_id',
        'soap_type_id',
        'state_type_id',
        'date_of_issue',
        'time_of_issue',
        'date_of_due',
        'supplier_id',
        'supplier',
        'currency_type_id',
        'exchange_rate_sale',
        'total_prepayment',
        'total_discount',
        'total_charge',
        'total_exportation',
        'total_free',
        'total_taxed',
        'total_unaffected',
        'total_exonerated',
        'total_igv',
        'total_base_isc',
        'total_isc',
        'total_base_other_taxes',
        'total_other_taxes',
        'total_taxes',
        'total_value',
        'total',
        'filename',
        'upload_filename',
        'purchase_quotation_id',
        'payment_method_type_id',
        'sale_opportunity_id',
        'sale_opportunity_number',
        'shipping_address', 
        'limit_date',
        'purchase_quotation',
        'mail_purchase_quotation',
        'type_purchase_order',
        'work_description',

    ];

    protected $casts = [
        'date_of_issue' => 'date',
        'date_of_due' => 'date',
    ];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
    public function getSupplierAttribute($value)
    {
        return (is_null($value)) ? null : (object) json_decode($value);
    }

    public function setSupplierAttribute($value)
    {
        $this->attributes['supplier'] = (is_null($value)) ? null : json_encode($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment_method_type()
    {
        return $this->belongsTo(PaymentMethodType::class);
    }

    public function soap_type()
    {
        return $this->belongsTo(SoapType::class);
    }

    public function state_type()
    {
        return $this->belongsTo(StateType::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    public function supplier_relation()
    {
        return $this->belongsTo(Person::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getNumberFullAttribute()
    {
        $configuration = Configuration::getConfig();
        if (!empty($this->series) && !empty($this->number) && !$configuration->restore_num_order_purchase) {
            return $this->series . '-' . $this->number;
        }
        
        // Registros antiguos sin serie (compatibilidad hacia atrás)
        if (!empty($this->prefix) && !empty($this->id)) {
            return $this->prefix . '-' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
        }
        
        // Fallback por si no hay prefix
        return 'OC-' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }

    public function scopeWhereTypeUser($query)
    {
        $user = auth()->user();
        return ($user->type == 'seller') ? $query->where('user_id', $user->id) : null;
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchase_quotation()
    {
        return $this->belongsTo(PurchaseQuotation::class);
    }
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function sale_opportunity()
    {
        return $this->belongsTo(SaleOpportunity::class);
    }

    public function series_relation()
    {
        return $this->belongsTo(Series::class, 'series', 'number');
    }

    /**
     * 
     * Validar si el registro esta rechazado o anulado
     * 
     * @return bool
     */
    public function isVoidedOrRejected()
    {
        return in_array($this->state_type_id, self::VOIDED_REJECTED_IDS);
    }


    /**
     * 
     * Mostrar botones de acciones si no esta anulado o no tiene compras aceptadas
     *
     * @return bool
     */
    public function getShowActionsRow()
    {
        $show_actions_row = true;

        $has_accepted_purchases = $this->purchases()->whereStateTypeAccepted()->count();

        if ($has_accepted_purchases > 0 || $this->isVoidedOrRejected()) {
            $show_actions_row = false;
        }

        return $show_actions_row;
    }
}
