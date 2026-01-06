<?php

    namespace Modules\Survey\Models;

use App\Models\Tenant\ModelTenant;

    
    class Answer extends ModelTenant
    {

        protected $table = 'survey_answers';
        protected $fillable = [
            'survey_response_id',
            'survey_question_id',
            'answer_text',
            'custom_option',
        ];

        public function response()
        {
            return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
        }

        public function question()
        {
            return $this->belongsTo(Question::class, 'survey_question_id');
        }
    



    }
