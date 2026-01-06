<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;
use App\Traits\CacheTrait;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Warehouse;

class Establishment extends ModelTenant
{
    use CacheTrait;

    protected $with = ['department', 'province', 'district'];
    protected $fillable = [
        'gekawa_url',
        'gekawa_1',
        'gekawa_2',
        'active',
        'print_format',
        'yape_owner',
        'yape_number',
        'yape_logo',
        'plin_owner',
        'plin_number',
        'plin_logo',
        'description',
        'country_id',
        'department_id',
        'province_id',
        'district_id',
        'address',
        'email',
        'telephone',
        'code',
        'trade_address',
        'web_address',
        'aditional_information',
        'customer_id',
        'logo',
        'template_pdf',
        'template_ticket_pdf',
        'has_igv_31556',
        'template_documents',
        'template_sale_notes',
        'template_dispatches',
        'template_quotations'

    ];


    protected $casts = [
        'has_igv_31556' => 'boolean'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function scopeWhereActive($query)
    {
        return $query->where('active', true);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function getAddressFullAttribute()
    {
        $address = ($this->address != '-') ? $this->address . ' ,' : '';
        return "{$address} {$this->department->description} - {$this->province->description} - {$this->district->description}";
    }

    public function customer()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function series()
    {
        return $this->hasMany(Series::class);
    }
    public function warehouse()
    {
        return $this->hasOne(Warehouse::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMyEstablishment(\Illuminate\Database\Eloquent\Builder $query)
    {
        $user = \Auth::user();
        if (null === $user) {
            $user = new User();
        }
        return $query->where('id', $user->establishment_id);
    }


    /**
     * Filtro para no incluir relaciones y obtener campos necesarios
     * Usado para obtener data para filtros, dependencias.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterDataForTables($query)
    {
        $query->whereFilterWithOutRelations()->select('id', 'description');
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
        return $query->withOut(['country', 'department', 'province', 'district']);
    }


    /**
     * 
     * Obtener id del almacén
     *
     * @return int
     */
    public function getCurrentWarehouseId()
    {
        return $this->warehouse->id;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function getVcEstablishment()
    {
        $cache_key = CacheTrait::getCacheKey('vc_establishment');
        $vc_establishment = CacheTrait::getCache($cache_key);
        if (!$vc_establishment) {
            $vc_establishment = DB::connection('tenant')->table('establishments')->select('id', 'logo')->first();
            CacheTrait::storeCache($cache_key, $vc_establishment);
            
        }
        return $vc_establishment;
    }

    public static function getVcEstablishments()
    {
        $cache_key = CacheTrait::getCacheKey('vc_establishments');
        $vc_establishments = CacheTrait::getCache($cache_key);
        if (!$vc_establishments) {
            $vc_establishments = DB::connection('tenant')->table('establishments')->select('id', 'description')->where('active', 1)->get()->transform(function ($establishment) {
                return (object) [
                    'id' => $establishment->id,
                    'description' => $establishment->description,
                ];
            });
            CacheTrait::storeCache($cache_key, $vc_establishments);
            
        }
        return $vc_establishments;
    }
}
