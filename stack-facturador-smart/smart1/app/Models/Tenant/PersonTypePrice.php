<?php

namespace App\Models\Tenant;


class PersonTypePrice extends ModelTenant
{
    protected $table = 'client_type_prices';
    protected $fillable = [
        'person_type_id',
        'item_id',
        'price',

    ];

    public function person_type()
    {
        return $this->belongsTo(PersonType::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}
