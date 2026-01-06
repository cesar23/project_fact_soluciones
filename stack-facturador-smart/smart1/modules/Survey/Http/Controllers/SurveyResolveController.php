<?php

namespace Modules\Survey\Http\Controllers;

use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Survey\Http\Resources\SurveyCollection;
use Modules\Survey\Models\Survey;
use Illuminate\Support\Str;
use Modules\Survey\Http\Resources\SectionResolveCollection;
use Modules\Survey\Models\Answer;
use Modules\Survey\Models\Question;
use Modules\Survey\Models\Respondent;
use Modules\Survey\Models\SurveyResponse;

class SurveyResolveController extends Controller
{

    public function getAnswers($uuid, $section_id)
    {
        $answers = [];
        $user = Auth::guard('respondent')->user();
        $survey = Survey::where('uuid', $uuid)->first();
        $survey_response = SurveyResponse::where('survey_id', $survey->id)
            ->where('respondent_id', $user->id)
            ->first();
        if ($survey_response) {
            $answers = Answer::where('survey_response_id', $survey_response->id)
                ->whereHas('question', function ($query) use ($section_id) {
                    $query->where('survey_section_id', $section_id);
                })
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'question_id' => $item->survey_question_id,
                        'answer_text' => $item->answer_text,
                        'custom_option' => $item->custom_option,
                    ];
                });
        }
        return response()->json($answers);
    }
    // public function checkAnswersSections($uuid)
    // {
    //     //check all required questions are answered in all sections
    //     $user = Auth::guard('respondent')->user();
    //     $survey = Survey::where('uuid', $uuid)->first();
    //     $survey_response = SurveyResponse::where('survey_id', $survey->id)
    //         ->where('respondent_id', $user->id)
    //         ->first();
    //     $sections = $survey->sections;
    //     $sections_ids = $sections->pluck('id');
    //     $answers = Answer::whereHas(
    //         'question',
    //         function ($query) {
    //             $query->where('is_required', 1);
    //         }
    //     )->where('survey_response_id', $survey_response->id)
    //         ->whereIn('survey_question_id', $sections_ids)
    //         ->pluck('survey_question_id')
    //         ->toArray();
    //     $diff = array_diff($sections_ids->toArray(), $answers);
    //     if (!empty($diff)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Faltan responder preguntas'
    //         ]);
    //     }
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Todas las preguntas fueron respondidas'
    //     ]);
    // }
    public function checkCompleted($uuid)
    {
        $user = Auth::guard('respondent')->user();
        $survey = Survey::where('uuid', $uuid)->first();
        $survey_response = SurveyResponse::where('survey_id', $survey->id)
            ->where('respondent_id', $user->id)
            ->first();
        $survey_response->is_completed = true;
        $survey_response->save();
        return response()->json(['message' => 'Encuesta completada']);
    }
    public function checkAnswersSections($uuid)
    {
        $user = Auth::guard('respondent')->user();

        $survey = Survey::where('uuid', $uuid)->first();

        $survey_response = SurveyResponse::where('survey_id', $survey->id)
            ->where('respondent_id', $user->id)
            ->first();

        $sections = $survey->sections;

        $required_questions = Question::whereIn('survey_section_id', $sections->pluck('id'))
            ->where('is_required', 1)
            ->pluck('id');

        $answered_questions = Answer::where('survey_response_id', $survey_response->id)
            ->whereIn('survey_question_id', $required_questions)
            ->pluck('survey_question_id')
            ->toArray();

        $unanswered_questions = array_diff($required_questions->toArray(), $answered_questions);

        if (!empty($unanswered_questions)) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan responder preguntas obligatorias'
            ]);
        }
        $survey_response->is_completed = true;
        $survey_response->save();
        Auth::guard('respondent')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Todas las preguntas obligatorias fueron respondidas'
        ]);
    }
    public function checkAnswers(Request $request, $uuid)
    {
        $request->validate([
            'questions_ids' => 'required|array'
        ]);

        $questions_ids = $request->questions_ids;
        $user = Auth::guard('respondent')->user();

        $survey = Survey::where('uuid', $uuid)->firstOrFail();
        $survey_response = SurveyResponse::where('survey_id', $survey->id)
            ->where('respondent_id', $user->id)
            ->firstOrFail();

        $answers = Answer::where('survey_response_id', $survey_response->id)
            ->whereIn('survey_question_id', $questions_ids)
            ->pluck('survey_question_id')
            ->toArray();

        $diff = array_diff($questions_ids, $answers);

        if (!empty($diff)) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan responder preguntas'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Todas las preguntas fueron respondidas'
        ]);
    }
    public function setAnswer(Request $request, $uuid)
    {
        $user = Auth::guard('respondent')->user();
        $survey = Survey::where('uuid', $uuid)->first();
        $survey_response = SurveyResponse::where('survey_id', $survey->id)->where('respondent_id', $user->id)->first();
        if (!$survey_response) {
            $survey_response = new SurveyResponse;
            $survey_response->survey_id = $survey->id;
            $survey_response->respondent_id = $user->id;
            $survey_response->save();
        }
        Answer::where('survey_response_id', $survey_response->id)
            ->where('survey_question_id', $request->question_id)
            ->delete();
        $answer = $request->answer;
        $custom_option = $request->custom_option;
        $question_id = $request->question_id;
        $question = Question::find($question_id);
        $question_type = $question->question_type;
        if (($answer != null || $custom_option != null) || $question_type == 'boolean') {
            $surveyAnswer = new Answer;
            $survey_response_id = $survey_response->id;

            $surveyAnswer->survey_response_id = $survey_response_id;
            $surveyAnswer->custom_option = $custom_option;
            $surveyAnswer->survey_question_id = $question_id;
            $surveyAnswer->answer_text = $answer;
            $surveyAnswer->save();
        }
        return response()->json(['message' => 'Respuesta guardada']);
    }
    public function getLocationCascade()
    {
        $locations = [];
        $departments = Department::where('active', true)->get();
        foreach ($departments as $department) {
            $children_provinces = [];
            foreach ($department->provinces as $province) {
                $children_districts = [];
                foreach ($province->districts as $district) {
                    $children_districts[] = [
                        'value' => $district->id,
                        'label' => $district->id . " - " . $district->description
                    ];
                }
                $children_provinces[] = [
                    'value' => $province->id,
                    'label' => $province->description,
                    'children' => $children_districts
                ];
            }
            $locations[] = [
                'value' => $department->id,
                'label' => $department->description,
                'children' => $children_provinces
            ];
        }

        return $locations;
    }
    public function setUbigeo(Request $request, $uuid)
    {
        $location_id = $request->location_id;

        /** @var \Modules\Survey\Model\Respondet $user */
        $user = Auth::guard('respondent')->user();

        if (!$user instanceof Respondent) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $department_id = $location_id[0];
        $province_id = $location_id[1];
        $district_id = $location_id[2];

        $user->update([
            'country_id' => 'PE',
            'department_id' => $department_id,
            'province_id' => $province_id,
            'district_id' => $district_id,
        ]);
        return response()->json(['message' => 'Ubigeo actualizado']);
    }
    public function tables($uuid)
    {
        $locations = $this->getLocationCascade();
        return response()->json([
            'locations' => $locations,
        ]);
    }
    public function sectionsResolve($uuid)
    {
        $user = Auth::guard('respondent')->user();
        $sections = Survey::where('uuid', $uuid)->first()->sections;
        $sections = $sections->map(function ($section) use ($user) {
            $survey = $section->survey;
            $survey_response = SurveyResponse::where('survey_id', $survey->id)
                ->where('respondent_id', $user->id)
                ->first();
            //check if all question has answers, and get percentage resolved
            $questions = $section->questions;
            $questions_ids = $questions->pluck('id');
            $answers = Answer::where('survey_response_id', $survey_response->id)
                ->whereIn('survey_question_id', $questions_ids)
                ->pluck('survey_question_id')
                ->toArray();
            $diff = array_diff($questions_ids->toArray(), $answers);
            $percentage = 0;
            if (count($questions_ids) > 0) {
                $percentage = (count($answers) * 100) / count($questions_ids);
            }
            return [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'percentage' => number_format($percentage, 2) . '%',
                'resolved' => empty($diff),
            ];
        });

        return response()->json($sections);
    }
    public function sections($uuid)
    {
        $survey = Survey::where('uuid', $uuid)->first();
        $sections = new SectionResolveCollection($survey->sections);
        return response()->json($sections);
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index($uuid)
    {
        $user = Auth::guard('respondent')->user();
        $user_name = $user->name;
        $user_email = $user->email;
        $has_location = $user->country_id && $user->department_id && $user->province_id && $user->district_id;
        $this->setDefaultAnswers($uuid, $user->id);
        return view('survey::resolve.index', compact('uuid', 'user_name', 'user_email', 'has_location'));
    }
    function setDefaultAnswers($uuid, $respondent_id)
    {
        $survey = Survey::where('uuid', $uuid)->first();
        $sections = $survey->sections;
        $survey_response = SurveyResponse::where('survey_id', $survey->id)
            ->where('respondent_id', $respondent_id)
            ->first();
        if (!$survey_response) {
            $survey_response = new SurveyResponse;
            $survey_response->survey_id = $survey->id;
            $survey_response->respondent_id = $respondent_id;
            $survey_response->save();
        }
        foreach ($sections as $section) {
                $questions = $section->questions->where('question_type', 'boolean');
                foreach ($questions as $question) {
                    $answer = Answer::where('survey_response_id', $survey_response->id)
                        ->where('survey_question_id', $question->id)
                        ->first();
                    if (!$answer) {
                        $surveyAnswer = new Answer;
                        $surveyAnswer->survey_response_id = $survey_response->id;
                        $surveyAnswer->survey_question_id = $question->id;
                        $surveyAnswer->answer_text = 1;
                        $surveyAnswer->save();
                    }
                }
            
                
        }
    }

    public function login_check(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('respondent')->attempt($credentials)) {
            $request->session()->regenerate();
            $uuid = $request->uuid;
            $user = Auth::guard('respondent')->user();
            $survey = Survey::where('uuid', $uuid)->first();
            $survey_response = SurveyResponse::where('survey_id', $survey->id)
                ->where('respondent_id', $user->id)
                ->first();
            $is_completed = false;
            if ($survey_response && $survey_response->is_completed == 1) {
                $is_completed = true;
            }
            if ($is_completed) {
                Auth::guard('respondent')->logout();
            }
            return response()->json([
                'success' => true, 'message' => 'Bienvenido', 'is_completed' => $is_completed
            ]);
        }

        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }
    public function login($uuid)
    {
        // $uuid = $uuid;
        $survey = Survey::where('uuid', $uuid)->first();
        $image_url = $survey->image ? asset('storage/uploads/surveys/' . $survey->image) : 'no found';
        $title = $survey->title;
        $company = Company::first();

        return view('survey::resolve.login', compact('company', 'title', 'uuid', 'image_url'));
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('survey::create');
    }

    public function record($id)
    {
        $record = Survey::find($id);
        return response()->json($record);
    }
    public function records()
    {
        $records = Survey::query();

        return new SurveyCollection($records->paginate(50));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $id = $request->id;
        $record = Survey::firstOrNew(['id' => $id]);
        $record->title = $request->title;
        $record->description = $request->description;
        if (!$record->uuid) {
            $record->uuid = Str::uuid();
        }
        $record->save();
        return ['success' => true, 'message' => $id ? 'Encuesta actualizada' : 'Encuesta creada', 'data' => $record];
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('survey::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('survey::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
