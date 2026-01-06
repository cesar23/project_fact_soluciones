<?php

namespace App\Models\Tenant;

class QuotationsTechnicians extends ModelTenant
{
    protected $table = 'quotation_technicians';

    protected $fillable = [
        "name",
        "number",
        "email",
        "phone",
        "image",
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getPhotoUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/uploads/quotations_technicians/' . $this->image);
        }
        return asset('storage/uploads/no-image.jpg');
    }

    public function getImagePathAttribute()
    {
        return $this->image ? 'storage/uploads/quotations_technicians/' . $this->image : null;
    }

    public static function boot()
    {
        parent::boot();
        
        static::deleting(function ($technician) {
            if ($technician->image && file_exists(public_path('storage/uploads/quotations_technicians/' . $technician->image))) {
                unlink(public_path('storage/uploads/quotations_technicians/' . $technician->image));
            }
        });
    }
}
