<?php

namespace Modules\Payroll\Models;

use App\Models\Tenant\ModelTenant;

class Payroll extends ModelTenant
{


    protected $table = 'payroll';

    protected $fillable = [
        'code',
        'name',
        'last_name',
        'age',
        'sex',
        'job_title',
        'admission_date',
        'cessation_date',
    ];

    public function getCollectionData()
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'age' => $this->age,
            'sex' => $this->sex,
            'job_title' => $this->job_title,
            'admission_date' => $this->admission_date,
            'cessation_date' => $this->cessation_date,
        ];
    }
}
