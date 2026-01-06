<?php

    namespace Modules\Survey\Models;

use App\Models\Tenant\ModelTenant;

    
    class QuestionOption extends ModelTenant
    {

        protected $table = 'survey_question_options';
        protected $fillable = [
            'survey_question_id',
            'option_text',
        ];

        public function question()
        {
            return $this->belongsTo(Question::class);
        }
    



    }
