<?php

namespace App\Models\Tenant;


class Multipayment extends ModelTenant
{
    protected $table = 'multipayments';

    protected $fillable = [
        'payment',
        'remaining',
        'date_of_issue',
        'user_id',
    ];

    protected $casts = [
        // 'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MultipaymentItem::class);
    }

}
