<?php
namespace App\Models\Tenant;

class PlateNumber extends ModelTenant
{
    protected $fillable = [
        'description',
        'year',
        'plate_number_brand_id',
        'plate_number_model_id',
        'plate_number_color_id',
        'plate_number_type_id',
        'person_id'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function brand()
    {
        return $this->belongsTo(PlateNumberBrand::class, 'plate_number_brand_id');
    }

    public function model()
    {
        return $this->belongsTo(PlateNumberModel::class, 'plate_number_model_id');
    }

    public function color()
    {
        return $this->belongsTo(PlateNumberColor::class, 'plate_number_color_id');
    }

    public function type()
    {
        return $this->belongsTo(PlateNumberType::class, 'plate_number_type_id');
    }

    public function kms()
    {
        return $this->hasMany(PlateNumberKm::class);
    }

    public function documents()
    {
        return $this->hasMany(PlateNumberDocument::class, 'plate_number_id');
    }

    public function getInfo()
    {
        return [
            'description' => $this->description,
            'year' => $this->year,
            'brand' => $this->brand->description,
            'model' => $this->model->description,
            'color' => $this->color->description,
            'type' => $this->type->description
        ];
    }
    
} 