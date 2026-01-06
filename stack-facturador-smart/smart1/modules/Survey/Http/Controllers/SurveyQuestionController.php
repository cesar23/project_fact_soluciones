<?php

namespace Modules\Survey\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Survey\Http\Resources\SurveyCollection;
use Modules\Survey\Models\Survey;
use Illuminate\Support\Str;
use Modules\Survey\Http\Resources\SectionCollection;
use Modules\Survey\Models\Answer;
use Modules\Survey\Models\Question;
use Modules\Survey\Models\Section;

class SurveyQuestionController extends Controller
{



    public function removeRecord($id){
        try {
            DB::beginTransaction();

            $record = Question::find($id);
            Answer::where('survey_question_id', $id)->delete();
            $record->options()->delete();
            $record->delete();
            DB::commit();
            return ['success' => true, 'message' => 'Pregunta eliminada'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function record($id)
    {
        $record = Question::find($id);
        return response()->json($record);
    }
    public function records($section_id)
    {
        $sections = Question::where('survey_section_id', $section_id)->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'question_text' => $item->question_text,
                    'question_type' => $this->question_type($item->question_type),
                    'allow_custom_option' => (bool) $item->allow_custom_option,
                    'survey_section_id' => $item->survey_section_id,
                ];
            });

        return ['data' => $sections];
    }

    function question_type($type)
    {
        $description = '';
        switch ($type) {
            case 'date':
                $description = 'Fecha';
                break;
            case 'text':
                $description = 'Texto';
                break;
            case 'number':
                $description = 'Número';
                break;
            case 'single_choice':
                $description = 'Selección simple';
                break;
            case 'multiple_choice':
                $description = 'Selección múltiple';
                break;
            case 'interval':
                $description = 'Intervalo';
                break;
            case 'boolean':
                $description = 'Sí/No';
                break;
            default:
                $description = 'No definido';
                break;
        }
        return $description;
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $record = Question::firstOrNew(['id' => $id]);
            if ($id != null) {
                Answer::where('survey_question_id', $id)->delete();
                $record->options()->delete();
            }
            $options = $request->options;
            $record->question_text = $request->question_text;
            $record->question_type = $request->question_type;

            $record->allow_custom_option = $request->allow_custom_option;
            $record->survey_section_id = $request->survey_section_id;
            $record->save();
            if ($options) {
                $record->options()->delete();
                foreach ($options as $option) {
                    $record->options()->create(['option_text' => $option]);
                }
            }
            DB::commit();
            return ['success' => true, 'message' => $id ? 'Pregunta actualizada' : 'Pregunta creada', 'data' => $record];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
