<?php

    namespace Modules\Survey\Models;

use App\Models\Tenant\ModelTenant;

    
    class Section extends ModelTenant
    {

        protected $table = 'survey_sections';
        protected $fillable = [
            'survey_id',
            'order',
            'title',
            'subtitle',
            'image',
        ];

        public function survey()
        {
            return $this->belongsTo(Survey::class);
        }

        public function questions()
        {
            return $this->hasMany(Question::class, 'survey_section_id');
        }



    }
