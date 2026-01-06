<?php

namespace Modules\Survey\Http\Controllers;

use App\Models\Tenant\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Survey\Http\Resources\SurveyCollection;
use Modules\Survey\Models\Survey;
use Illuminate\Support\Str;
use Modules\Finance\Helpers\UploadFileHelper;
use Modules\Survey\Exports\SurveyExport;
use Modules\Survey\Http\Resources\SurveyAnswerCollection;
use Modules\Survey\Models\Answer;
use Modules\Survey\Models\Section;
use Modules\Survey\Models\SurveyResponse;
use Intervention\Image\Facades\Image;
use Modules\Survey\Exports\SurveyExportAll;

class SurveyController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('survey::index');
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

        $record->image_url = $record->image ? asset('storage/uploads/surveys/' . $record->image) : null;
        return response()->json($record);
    }
    public function getRespondet($survey_response_id)
    {
        $respondet = SurveyResponse::find($survey_response_id)->respondent;
        
        $record = [];
        $record['id'] = $respondet->id;
        $record['name'] = $respondet->name;
        $record['number'] = $respondet->number;
        $record['email'] = $respondet->email;
        $record['phone'] = $respondet->phone;
        $record['dob'] =  $respondet->dob ? $respondet->dob->format('Y-m-d') : null;
        $record['address'] = $respondet->address;
        $record['location'] = func_get_location($respondet->district_id);


        return response()->json($record);
    }
    function  totals($survey_id)
    {
        $sections = Section::where('survey_id', $survey_id)->get();
        $sections_result = [];

        foreach ($sections as $section) {
            $questions_result = [];
            foreach ($section->questions as $question) {
                $question_id = $question->id;
                $answers = Answer::where('survey_question_id', $question_id)->get()
                    ->groupBy(function ($answer) use ($question) {
                        if ($question->question_type == 'multiple_choice') {
                            $ids = explode('|', $answer->answer_text);
                            sort($ids);
                            return implode('|', $ids);
                        }
                        return $answer->answer_text;
                    });

                $total_answers = $answers->flatten()->count();
                $answer_counts = $answers->map(function ($group) {
                    return $group->count();
                });

                $answer_percentages = $answer_counts->map(function ($count) use ($total_answers) {
                    return $total_answers > 0 ? ($count / $total_answers) * 100 : 0;
                });

                $questions_result[] = [
                    'question' => $question->question_text,
                    'question_type' => $question->question_type,
                    'total' => $total_answers,
                    'answers' => $answers->map(function ($group, $answer_text) use ($answer_counts, $answer_percentages, $question) {
                        $answer_result = $answer_text;
                        if ($question->question_type == 'multiple_choice') {
                            $answer_result = explode('|', $answer_result);
                            $answer_result = $question->options->whereIn('id', $answer_result)->pluck('option_text')->implode(', ');
                        }
                        if ($question->question_type == 'single_choice') {
                            $answer_result = $question->options->where('id', $answer_result)->pluck('option_text')->first();
                        }
                        if ($question->question_type == 'boolean') {
                            $answer_result = $answer_text == 1 ? 'Sí' : 'No';
                        }
                        if ($question->question_type == 'interval') {
                            $answer_result = json_decode($answer_result);
                            $answer_result = $answer_result->years . ' años ' . $answer_result->months . ' meses ' . $answer_result->days . ' días';
                        }
                        if ($question->question_type == 'date') {
                            $answer_result = date('d-m-Y', strtotime($answer_result));
                        }
                        return [
                            'answer_text' => $answer_result,
                            'count' => $answer_counts[$answer_text],
                            'percentage' => $answer_percentages[$answer_text]
                        ];
                    })->values()
                ];
            }

            $sections_result[] = [
                'section' => $section->title,
                'questions' => $questions_result
            ];
        }
        return $sections_result;
    }
    public function getTotals($survey_id)
    {
        $sections_result = $this->totals($survey_id);

        return response()->json(['sections' => $sections_result]);
    }
    public function pdf($survey_id)
    {
        $sections = $this->totals($survey_id);
        foreach ($sections as &$section) {
            $section['questions'] = collect($section['questions'])->chunk(3)->toArray();
        }
        $survey = Survey::find($survey_id);
        $title = $survey->title;
        $company = Company::first();
        $number_participants = SurveyResponse::where('survey_id', $survey_id)->count();
        $pdf = \PDF::loadView('survey::exports.pdf', compact('sections', 'title', 'company', 'number_participants'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('Encuesta_' . Carbon::now() . '.pdf');
    }
    // function 
    public function excelAll($survey_id)
    {
        $sections_result = [];
    
        $survey = Survey::find($survey_id);
    
    
        
        $all_questions = $survey->sections->map(function ($section) {
            return $section->questions;
        })->flatten()->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
            ];
        })->toArray();

        $all_responses = SurveyResponse::where('survey_id', $survey_id)->get()->map(function ($response) use ($all_questions) {
            $answers = $response->answers->keyBy('survey_question_id');
            $respondent_info = [
                'respondent_id' => $response->respondent->id,
                'respondent_name' => $response->respondent->name,
                // Agrega más campos según sea necesario
            ];
            $questions = collect($all_questions)->sortBy('id')->map(function ($question) use ($answers) {
                $answered = $answers->has($question['id']);
                $answer_text = null;
                if ($answered) {
                    $answer = $answers->get($question['id']);
                    $answer_text = $answer->answer_text;
                    if ($answer->question->question_type == 'multiple_choice') {
                        $answer_text = explode('|', $answer_text);
                        $answer_text = $answer->question->options->whereIn('id', $answer_text)->pluck('option_text')->implode(', ');
                    }
                    if ($answer->question->question_type == 'single_choice') {
                        $answer_text = $answer->question->options->where('id', $answer_text)->pluck('option_text')->first();
                    }
                    if ($answer->question->question_type == 'boolean') {
                        $answer_text = $answer_text == 1 ? 'Sí' : 'No';
                    }
                    if ($answer->question->question_type == 'interval') {
                        $answer_text = json_decode($answer_text);
                        $answer_text = $answer_text->years . ' años ' . $answer_text->months . ' meses ' . $answer_text->days . ' días';
                    }
                    if ($answer->question->question_type == 'date') {
                        $answer_text = date('d-m-Y', strtotime($answer_text));
                    }
                }
                return [
                    'id' => $question['id'],
                    'question_text' => $question['question_text'],
                    'answered' => $answered,
                    'answer_text' => $answer_text,
                ];
            });
            return [
                'respondent_info' => $respondent_info,
                'questions' => $questions,
            ];
        })->toArray();
        $title = $survey->title;
        $company = Company::first();
        $number_participants = SurveyResponse::where('survey_id', $survey_id)->count();
        $survey = new SurveyExportAll();
        $survey
            ->sections($sections_result)
            ->all_questions($all_questions)
            ->all_responses($all_responses)
            ->numberParticipants($number_participants)
            ->title($title)
            ->company($company);

        return $survey->download('Encuesta_' . Carbon::now() . '.xlsx');
    }
    public function excel($survey_id)
    {
        $sections_result = $this->totals($survey_id);
        foreach ($sections_result as &$section) {
            $section['questions'] = collect($section['questions'])->chunk(3)->toArray();
        }
        $survey = Survey::find($survey_id);
        $title = $survey->title;
        $company = Company::first();
        $number_participants = SurveyResponse::where('survey_id', $survey_id)->count();
        $survey = new SurveyExport();
        $survey
            ->sections($sections_result)
            ->numberParticipants($number_participants)
            ->title($title)
            ->company($company);

        return $survey->download('Encuesta_' . Carbon::now() . '.xlsx');
    }
    public function getAnswers($survey_response_id)
    {
        $answers = SurveyResponse::find($survey_response_id)->answers;

        return response()->json(new SurveyAnswerCollection($answers));
    }
    public function answers($respondet_id, $survey_id)
    {
        $record = SurveyResponse::where('respondent_id', $respondet_id)->where('survey_id', $survey_id)->first();
        if ($record) {
            return view('survey::answers', compact('record'));
        } else {
            return  view('survey::answers');
        }
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
        $temp_path = $request->input('temp_path');
        if ($temp_path) {

            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surveys' . DIRECTORY_SEPARATOR;

            // $slug_name = Str::slug($record->title);
            $prefix_name = "survey";

            $file_name_old = $request->input('image');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path);
            $datenow = date('YmdHis');
            $file_name = $prefix_name . '-' . $datenow . '.' . $file_name_old_array[1];

            UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);

            Storage::put($directory . $file_name, $file_content);
            $record->image = $file_name;

            //--- IMAGE SIZE MEDIUM
            $image = Image::make($temp_path);
            $file_name = $prefix_name . '-' . $datenow . '_medium' . '.' . $file_name_old_array[1];
            $image->resize(512, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::put($directory . $file_name,  (string) $image->encode('jpg', 30));

            //--- IMAGE SIZE SMALL
            $image = Image::make($temp_path);
            $file_name = $prefix_name . '-' . $datenow . '_small' . '.' . $file_name_old_array[1];

            Storage::put($directory . $file_name,  (string) $image->encode('jpg', 20));
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
