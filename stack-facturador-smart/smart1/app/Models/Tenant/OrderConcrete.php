<?php

namespace App\Models\Tenant;

class OrderConcrete extends ModelTenant
{
    protected $table = 'order_concretes';

    protected $fillable = [
        'sale_note_id',
        'document_id',
        'series',
        'number',
        'establishment_code',
        'address',
        'master_id',
        'customer_id', 
        'user_id',
        'establishment_id',
        'date',
        'hour',
        'electro',
        'volume',
        'mix_kg_cm2',
        'type_cement',
        'pump',
        'other',
        'observations',
        'treasury_reviewed_name',
        'treasury_reviewed_date',
        'treasury_reviewed_signature',
        'plant_manager_reviewed_name',
        'plant_manager_reviewed_date',
        'plant_manager_reviewed_signature',
        'plant_operator_reviewed_name',
        'plant_operator_reviewed_date',
        'plant_operator_reviewed_signature',
        'manager_approved_name',
        'manager_approved_date',
        'manager_approved_signature'
    ];

    public function master()
    {
        return $this->belongsTo(Person::class, 'master_id');
    }

    public function customer()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function supplies()
    {
        return $this->hasMany(OrderConcreteSupply::class);
    }

    public function attentions()
    {
        return $this->hasMany(OrderConcreteAttention::class);
    }

    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class, 'sale_note_id', 'id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }
}


