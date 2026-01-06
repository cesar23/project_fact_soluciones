<?php

    namespace Modules\Survey\Models;

use App\Models\Tenant\ModelTenant;

    
    class SurveyResponse extends ModelTenant
    {

        protected $table = 'survey_responses';
        protected $fillable = [
            'respondent_id',
            'survey_id',
            'is_completed',
        ];

        public function survey()
        {
            return $this->belongsTo(Survey::class);
        }

        public function answers()
        {
            return $this->hasMany(Answer::class);
        }

        public function respondent()
        {
            return $this->belongsTo(Respondent::class);
        }
    



    }
