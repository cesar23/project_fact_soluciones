<?php

namespace Modules\Survey\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SurveyAnswerCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) use($request) {
            $question = $row->question;
            $section = $question->section->title;
            $question_type = $question->question_type;
            $question_text = $question->question_text;
            $answer_text = $row->answer_text;
            if($question_type == 'interval' && $answer_text != null){
                $answer_text = json_decode($answer_text);
                $answer_text = $answer_text->years . ' años ' . $answer_text->months . ' meses ' . $answer_text->days . ' días';
            }
            if($question_type == 'date' && $answer_text != null){
                $answer_text = date('d-m-Y', strtotime($answer_text));
            }
            if($question_type == 'boolean' && $answer_text != null){
                $answer_text = $answer_text == 1 ? 'Sí' : 'No';
            }
            if($question_type == 'single_choice' && $answer_text != null){
                $answer_text = $question->options->where('id', $answer_text)->first()->option_text;
            }
            if($question_type == 'multiple_choice' && $answer_text != null){
                $answer_text = explode('|', $answer_text);
                $answer_text = $question->options->whereIn('id', $answer_text)->pluck('option_text')->implode(', ');
            }
            //is type is boolean and $answer_text is null, set 1 as default value
            // if($question_type == 'boolean' && $answer_text == null){
            //     $answer_text = 'Sí';
            //     $row->update(['answer_text' => 1]);

            // }


            return [
                'id' => $row->id,
                'description' => $question_text,
                'answer' => $answer_text,
                'section' => $section,
            ];
        });
    }


    

}
