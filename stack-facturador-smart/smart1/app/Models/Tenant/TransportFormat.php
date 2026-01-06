<?php
namespace App\Models\Tenant;


class TransportFormat extends ModelTenant
{
    protected $table = 'transport_format';
    protected $fillable = [
        'date_of_issue',
    ];

    public function transportFormatItems()
    {
        return $this->hasMany(TransportFormatItem::class);
    }
}
