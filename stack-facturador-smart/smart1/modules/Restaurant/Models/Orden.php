<?php

namespace Modules\Restaurant\Models;

use App\Models\Tenant\Person;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\ModelTenant;
use Modules\Restaurant\Models\Table;

class Orden extends ModelTenant
{

    protected $table = "ordens";
    protected $with = ['orden_items', 'status_orden', 'salenote', 'document', 'customer', 'mesa'];
    // , 'document', 'sale_note','mesa'
    protected $fillable = [
        'table_id',
        'status_orden_id',
        'customer_id',
        'status',
        'commands_fisico',
        'to_carry',
        'sale_note_id',
        'document_id',
        'reference'
    ];

    protected $casts = [
        'to_carry' => 'boolean',
    ];

    public function orden_items()
    {
        return $this->hasMany(OrdenItem::class);
    }
    public function customer()
    {
        return $this->belongsTo(Person::class);
    }
    public function status_orden()
    {
        return $this->belongsTo(StatusOrden::class, 'status_orden_id', 'id');
    }
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function salenote()
    {
        return $this->belongsTo(SaleNote::class, 'sale_note_id', 'id');
    }
    public function mesa()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
}
