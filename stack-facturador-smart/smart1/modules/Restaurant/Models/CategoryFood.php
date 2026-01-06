<?php

namespace Modules\Restaurant\Models;
use App\Models\Tenant\ModelTenant;
class CategoryFood extends ModelTenant
{

    public $timestamps = false;
    protected $table = "category_food";
    protected $fillable = [
        'description',
        'active',
    ];
}
