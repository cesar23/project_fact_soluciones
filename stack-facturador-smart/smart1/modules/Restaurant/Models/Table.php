<?php

namespace Modules\Restaurant\Models;
use App\Models\Tenant\ModelTenant;

class Table extends ModelTenant
{

    public $timestamps = false;
    protected $with = ["area", "status_table"];
    protected $fillable = [
        'number',
        'area_id',
        'status_table_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function status_table()
    {
        return $this->belongsTo(StatusTable::class);
    }
}
