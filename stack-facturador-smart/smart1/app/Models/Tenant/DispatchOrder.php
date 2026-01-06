<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\District;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Dispatch\Models\DispatchAddress;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchOrder whereTypeUser()
 */


class DispatchOrder extends ModelTenant
{
    use UsesTenantConnection;

    protected $with = [];

    protected $fillable = [
        "user_id",
        "external_id",
        "establishment_id",
        "establishment",
        "soap_type_id",
        "state_type_id",
        "responsible_id",
        "production_order_id",
        "prefix",
        "number",
        "date_of_issue",
        "time_of_issue",
        "date_of_due",
        "delivery_date",
        "customer_id",
        "customer",
        "shipping_address",
        "currency_type_id",
        "payment_method_type_id",
        "exchange_rate_sale",
        "total_prepayment",
        "total_charge",
        "total_discount",
        "total_exportation",
        "total_free",
        "total_taxed",
        "total_unaffected",
        "total_exonerated",
        "total_igv",
        "total_igv_free",
        "total_base_isc",
        "total_isc",
        "total_base_other_taxes",
        "total_other_taxes",
        "total_taxes",
        "total_value",
        "total",
        "charges",
        "discounts",
        "prepayments",
        "guides",
        "related",
        "perception",
        "detraction",
        "legends",
        "additional_data",
        "filename",
        "observation",
        "seller_id",
        "dispatch_order_state_id",
        "package_number",


    ];

    protected $casts = [
        'user_id' => 'int',
        'establishment_id' => 'int',
        'responsible_id' => 'int',
        'number' => 'int',
        'customer_id' => 'int',
        'exchange_rate_sale' => 'float',
        'apply_concurrency' => 'bool',
        'enabled_concurrency' => 'bool',
        'quantity_period' => 'int',
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
        'total' => 'float',
        'quotation_id' => 'int',
        'order_note_id' => 'int',
        'technical_service_id' => 'int',
        'order_id' => 'int',
        'total_canceled' => 'bool',
        // 'changed' => 'bool',
        'paid' => 'bool',
        'document_id' => 'int',
        'user_rel_suscription_plan_id' => 'int',
        'seller_id' => 'int',
        'date_of_issue' => 'date',
        'automatic_date_of_issue' => 'date',
        'due_date' => 'date',

        'point_system' => 'bool',
        'created_from_pos' => 'bool',
        'dispatch_ticket_pdf' => 'bool',

    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }
    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class);
    }
    public function production_order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function getChangePayment()
    {

        return 0;
    }
    public function isPointSystem()
    {
        return false;
    }
    public function getNumberFullAttribute()
    {
        $number_full = ($this->series && $this->number) ? $this->series . '-' . $this->number : $this->prefix . '-' . $this->id;

        return $number_full;
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

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }
    public function payment_method_type(){
        return $this->belongsTo(PaymentMethodType::class, 'payment_method_type_id');
    }
    public function getCollectionDataRoute()
    {


        $payments = $this->payments;
        $sale_note = SaleNote::find($this->sale_note_id);
        $payments_methods = [];
        if ($this->sale_note->payment_condition_id == "01") {



            $payment_method_type = $this->sale_note->payment_method_type;
            if ($payment_method_type) {
                $payments_methods = [$payment_method_type->description];
            } else {
                $payments_methods = $this->sale_note->payments->map(function ($row) {
                    return $row["payment_method_type"]["description"];
                });
            }
        } else {
            $payments_methods = $this->sale_note->fee->map(function ($row) {
                if ($row->payment_method_type) {
                    return $row->payment_method_type->description;
                } else {
                    return 'Credito';
                }
            });
        }
        $customer_db = Person::find($this->customer_id);
        $district = District::find($this->customer->district_id);
        $district_name = ($district) ? $district->description : '';
        $sale_note = SaleNote::find($this->sale_note_id);
        $shipping_address =  "";
        $total_pending_paid = 0;
        $customer_location = $customer_db->location;
        $observation = $this->observation;
        $dispatch_address = $customer_db->dispatch_addresses->first();
        if ($dispatch_address) {
            $shipping_address = $dispatch_address->address;
            $location_id = $dispatch_address->location_id;
            if ($location_id) {
                $district_id = isset($location_id[2]) ? $location_id[2] : null;
                if ($district_id) {
                    $district = District::find($district_id);
                    $district_name = $district->description;
                }
                // $district_id = $location_id->district_id;
                // $district = District::find($location_id);
                // $district_name = $district->description;
            }
            if($dispatch_address->google_location){
                $customer_location = $dispatch_address->google_location;
            }
        }
        if ($sale_note) {
            $total_paid = number_format($sale_note->payments->sum('payment'), 2, '.', '');
            $total_pending_paid = number_format($sale_note->total - $total_paid, 2, '.', '');
            $observation = $sale_note->additional_information;
            // if ($sale_note->dispatch) {
            //     $dispatch = $sale_note->dispatch;
            // }else{
            //     $dispatch = $this->dispatches->first();
            // }
            // if($dispatch){
            //     $delivery = $dispatch->delivery;
            //     $location_id = $delivery->location_id;
            //     if($location_id){
            //         $district = District::find($location_id);
            //         $district_name = $district->description;
            //     }
            //     $shipping_address = $delivery->address;
            // }

        }
        return [
            'package_number' => $this->package_number,
            'dispatches' => $this->dispatches,
            'total_pending_paid' => $total_pending_paid,
            'observation' => $observation,
            'shipping_address' => $shipping_address,
            'district_name' => $district_name,
            'payments_methods' => $payments_methods,
            'state_payment_id' => optional($sale_note)->state_payment_id,
            'total_canceled' => (bool) $sale_note->total_canceled,
            'customer_location' => $customer_location,
            'id' => $this->id,
            'external_id' => $this->external_id,
            'full_number' => $this->prefix . '-' . $this->number,
            'identifier' => $this->identifier,
            'seller_name' => optional($this->seller)->name,
            'customer_name' => $this->customer->name,
            'state' => $this->dispatch_order_state->description,
            'state_id' => $this->dispatch_order_state->id,
            'payments' => $payments,
            'currency_type_id' => $this->currency_type_id,
            'customer_number' => $this->customer->number,
            'customer_telephone' => optional($this->customer)->telephone,
            'total' => number_format($this->total, 2),
            'customer_region' => optional($this->customer)->department->description,


        ];
    }
    public function getCollectionData()
    {
        $state_sale_note_order_description = null;
        $btn_generate = false;
        $isIntegrateSystem = BusinessTurn::isIntegrateSystem();
        $sale_note = SaleNote::find($this->sale_note_id);
        $dispatch_address = DispatchAddress::where('person_id', $this->customer_id)->first();
        $department_name = null;
        $province_name = null;
        $district_name = null;
        $customer_db = Person::find($this->customer_id);
        $customer_location = $customer_db->location;
        if($dispatch_address && $isIntegrateSystem){
            $location_id = $dispatch_address->location_id;
            $google_location = $dispatch_address->google_location;
            $state_sale_note_order_description = $dispatch_address->reason;
            if ($location_id) {
                if (is_string($location_id)) {
                    $location_id = json_decode($location_id, true);
                }
                if($google_location){
                    $customer_location = $google_location;
                }
                $district_id = $location_id[2];
                $location = func_get_location($district_id);
                $split_location = explode(' - ', $location);
                $department_name = $split_location[2];
                $province_name = $split_location[1];
                $district_name = $split_location[0];
            }
        }else{
            $department_name = $this->customer->department->description;
        }
        $dispatches = Dispatch::where('reference_dispatch_order_id', $this->id)
            ->where('state_type_id', '<>', 11)
            ->where('state_type_id', '<>', 13)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'number' => $row->number_full,
                    'external_id' => $row->external_id,
                ];
            });
        $can_edit = false;
        //if user id is equal to  responsible id
        if (auth()->user()->id == $this->responsible_id) {
            $can_edit = true;
        }
        $miTiendaPe = null;
        $has_agency_dispatch = AgencyDispatchTable::where('dispatch_order_id', $this->id)->count() > 0;

        $payments = $this->payments;
        $can_create_dispatch = count($dispatches) == 0;
        $payments_methods = [];
        if ($this->sale_note->payment_condition_id == "01") {

            $payments_methods = $this->sale_note->payments->map(function ($row) {
                return $row["payment_method_type"]["description"];
            });
        } else {
            $payments_methods = $this->sale_note->fee->map(function ($row) {
                if ($row->payment_method_type) {
                    return $row->payment_method_type->description;
                } else {
                    return 'Credito';
                }
            });
        }
    
        return [
            'department_name' => $department_name,
            'province_name' => $province_name,
            'district_name' => $district_name,
            'state_sale_note_order_description' => $state_sale_note_order_description,
            'payments_methods' => $payments_methods,
            'can_create_dispatch' => $can_create_dispatch,
            'state_payment_id' => optional($sale_note)->state_payment_id,
            'total_canceled' => (bool) $sale_note->total_canceled,
            'customer_location' => $customer_location,
            'can_edit' => $can_edit,
            'id' => $this->id,
            'has_agency_dispatch' => $has_agency_dispatch,
            'soap_type_id' => $this->soap_type_id,
            'external_id' => $this->external_id,
            'date_of_issue' => $this->date_of_issue->format('Y-m-d'),
            'date_of_due' => ($this->date_of_due) ? $this->date_of_due->format('Y-m-d') : null,
            'delivery_date' => ($this->delivery_date) ? $this->delivery_date->format('Y-m-d') : null,
            'full_number' => $this->prefix . '-' . $this->number,
            'identifier' => $this->identifier,
            'user_name' => $this->user->name,
            'seller_name' => optional($this->seller)->name,
            'customer_name' => $this->customer->name,
            'responsible_name' => optional($this->responsible)->name,
            'state' => $this->dispatch_order_state->description,
            'state_id' => $this->dispatch_order_state->id,
            'payments' => $payments,
            'currency_type_id' => $this->currency_type_id,
            'customer_number' => $this->customer->number,
            'customer_telephone' => optional($this->customer)->telephone,
            'customer_email' => optional($this->customer)->email,
            'total_exportation' => number_format($this->total_exportation, 2),
            // 'total_free' => number_format($this->total_free,2),
            'total_unaffected' => number_format($this->total_unaffected, 2),
            'total_exonerated' => number_format($this->total_exonerated, 2),
            'total_taxed' => number_format($this->total_taxed, 2),
            'total_igv' => number_format($this->total_igv, 2),
            'total' => number_format($this->total, 2),

            'customer_region' => optional($this->customer)->department->description,
            'btn_generate' => $btn_generate,
            'mi_tienda_pe' => $miTiendaPe,
            'dispatches' => $dispatches,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'print_a4' => url('') . "/order-notes/print/{$this->external_id}/a4",
            'filename' => $this->filename,
            // 'print_ticket' => $this->getUrlPrintPdf('ticket'),
        ];
    }
    public function dispatch_order_state()
    {
        return $this->belongsTo(StateDispatchOrder::class, 'dispatch_order_state_id');
    }

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'reference_dispatch_order_id');
    }
    public function items()
    {
        return $this->hasMany(DispatchOrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(Person::class);
    }
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
    public function state_type()
    {
        return $this->belongsTo(StateType::class, 'state_type_id');
    }
    public function getCustomerAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setCustomerAttribute($value)
    {
        $this->attributes['customer'] = (is_null($value)) ? null : json_encode($value);
    }
    public function getEstablishmentAttribute($value)
    {

        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setEstablishmentAttribute($value)
    {
        $this->attributes['establishment'] = (is_null($value)) ? null : json_encode($value);
    }


    public function getChargesAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setChargesAttribute($value)
    {
        $this->attributes['charges'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getDiscountsAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setDiscountsAttribute($value)
    {
        $this->attributes['discounts'] = (is_null($value)) ? null : json_encode($value);
    }
    public function payments()
    {
        return $this->hasMany(DispatchOrderPayment::class);
    }
    public function getPrepaymentsAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setPrepaymentsAttribute($value)
    {
        $this->attributes['prepayments'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getGuidesAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setGuidesAttribute($value)
    {
        $this->attributes['guides'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getRelatedAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setRelatedAttribute($value)
    {
        $this->attributes['related'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getPerceptionAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setPerceptionAttribute($value)
    {
        $this->attributes['perception'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getDetractionAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setDetractionAttribute($value)
    {
        $this->attributes['detraction'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getLegendsAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setLegendsAttribute($value)
    {
        $this->attributes['legends'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getFormatDueDate()
    {
        return $this->due_date ? $this->generalFormatDate($this->due_date) : null;
    }
}
