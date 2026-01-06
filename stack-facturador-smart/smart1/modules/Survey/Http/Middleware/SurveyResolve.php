<?php

namespace Modules\Survey\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyResponse;

class SurveyResolve
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'respondent')
    {
        $uuid = $request->uuid;
        if (Auth::guard($guard)->guest()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if(!$this->SurveyExist($uuid)){
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if($this->SurveyCompleted($uuid)){
            return response()->json(['message' => 'Encuesta completada.'], 401);
        }
        

        return $next($request);
    }

    function SurveyExist($uuid){
        $survey = Survey::where('uuid', $uuid)->first();
        if($survey){
            return true;
        }else{
            return false;
        }
    }

    function SurveyCompleted($uuid){
        $survey = Survey::where('uuid', $uuid)->first();
        $user = Auth::guard('respondent')->user();
        $survey_response = SurveyResponse::where('survey_id', $survey->id)->where('respondent_id', $user->id)->first();
        if($survey_response && $survey_response->is_completed == 1){
            return true;
        }else{
            return false;
        }
    }
}
