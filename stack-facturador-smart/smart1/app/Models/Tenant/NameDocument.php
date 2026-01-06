<?php

namespace App\Models\Tenant;


class NameDocument extends ModelTenant
{

    protected $table = 'name_documents';
    protected $fillable = [
        "sale_note",
        "orden_note",
        "quotation",
        "sale_opportunity",
        "technical_service",
        "contract",

    ];

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }
}
