<?php

namespace Modules\Survey\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Survey\Http\Middleware\SurveyResolve;
use Modules\Survey\Http\Resources\RespondetCollection;
use Modules\Survey\Models\Respondent;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyResponse;

class RespondetController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('survey::respondets.index');
    }
    public function columns()
    {
        return [
            'name' => 'Nombre',
            'number' => 'Número',
            'email' => 'Correo',
            'phone' => 'Teléfono',
        ];
    }
    public function record($id)
    {
        $record = Respondent::find($id);
        //remove password
        $record->password = null;
        return response()->json($record);
    }
    public function updatePassword(Request $request, $id)
    {
        $record = Respondent::find($id);
        $password = $request->password;
        if ($password) {
            $record->password = bcrypt($password);
        }
        $record->save();
        return ['success' => true, 'message' => 'Contraseña actualizada', 'data' => $record];
    }
    public function recordsSurvey(Request $request,$id)
    {
        $survey = Survey::where('id', $id)->first();
        $records = Respondent::whereHas('responses',function($query) use ($survey){
            $query->where('survey_id',$survey->id);
        });
        return new RespondetCollection($records->paginate(50));
    }
    public function records(Request $request)
    {
        $records = Respondent::query();

        return new RespondetCollection($records->paginate(50));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $id = $request->id;
        $record = Respondent::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        if (!$id) {
            $password = $request->password;
            if ($password) {
                $record->password = bcrypt($password);
            }
        }
        if (!$record->uuid) {
            $record->uuid = Str::uuid();
        }


        $record->save();
        return ['success' => true, 'message' => $id ? 'Participante actualizado' : 'Participante creado', 'data' => $record];
    }
}
