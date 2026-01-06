<?php

    namespace Modules\Survey\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;

    class Respondent extends Authenticatable
    {
        use UsesTenantConnection;

        protected $table = 'respondents';
        protected $fillable = [
            'name',
            'number',
            'sex',
            'email',
            'phone',
            'uuid',
            'password',
            'dob',
            'address',
            'country_id',
            'department_id',
            'province_id',
            'district_id',
        ];

    
    
        public function responses()
        {
            return $this->hasMany(SurveyResponse::class, 'respondent_id');
        }


    }
