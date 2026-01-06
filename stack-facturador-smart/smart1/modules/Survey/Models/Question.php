<?php

    namespace Modules\Survey\Models;

use App\Models\Tenant\ModelTenant;

    
    class Question extends ModelTenant
    {

        protected $table = 'survey_questions';
        protected $with = ['options'];
        protected $fillable = [
            'survey_section_id',
            'question_text',
            'question_type',
            'allow_custom_option',
        ];

        
        public function section()
        {
            return $this->belongsTo(Section::class, 'survey_section_id');
        }

        public function options()
        {
            return $this->hasMany(QuestionOption::class, 'survey_question_id');
        }

    }
