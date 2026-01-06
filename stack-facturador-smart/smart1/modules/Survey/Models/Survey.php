<?php

    namespace Modules\Survey\Models;

use App\Models\Tenant\ModelTenant;

    
    class Survey extends ModelTenant
    {

        protected $fillable = [
            'title',
            'description',
            'uuid',
            'image',
        ];

        public function sections()
        {
            return $this->hasMany(Section::class);
        }

    }
