<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Province;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class PersonAval extends ModelTenant
{

    protected $table = 'person_avals';

    protected $fillable = [
        'id','number', 'name', 'trade_name', 'identity_document_type_id', 'address', 'telephone', 'country_id', 'location_id', 'person_id',
    ];

    protected $casts = [
        'location_id' => 'array',
    ];

    public function identity_document_type()
    {
        return $this->belongsTo(IdentityDocumentType::class, 'identity_document_type_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }


    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function setLocationIdAttribute($value)
    {
        $this->attributes['location_id'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getLocationIdAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function getCollectionData()
    {
        return [
            "number_aval" => $this->number,
            "name_aval" => $this->name,
            "trade_name_aval" => $this->trade_name,
            "identity_document_type_id_aval" => $this->identity_document_type_id,
            "address_aval" => $this->address,
            "telephone_aval" => $this->telephone,
            "location_id_aval" => (array)$this->location_id,
            "country_id_aval" => $this->country_id,
            
        ];
    }

    
    
}


