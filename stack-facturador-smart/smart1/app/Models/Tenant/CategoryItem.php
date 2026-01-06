<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;

class CategoryItem extends ModelTenant
{
    protected $hidden = ["created_at", "updated_at"];
    protected $table="categories";             

    protected $fillable = [ 
        'name',
        'icono',
    ];
 
    public function items()
    {
        return $this->hasMany(Item::class);
    }
 

}