<?php

namespace Modules\Survey\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Survey\Http\Resources\SurveyCollection;
use Modules\Survey\Models\Survey;
use Illuminate\Support\Str;
use Modules\Survey\Http\Resources\SectionCollection;
use Modules\Survey\Models\Section;

class SurveySectionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index($uuid)
    {
        $survey = Survey::where('uuid', $uuid)->first();
        return view(
            'survey::sections.index',
            compact( 'survey')
        );
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
    public function records($uuid)
    {
        $sections = Section::whereHas('survey', function ($query) use ($uuid) {
            $query->where('uuid', $uuid);
        });

        return new SectionCollection($sections->paginate(50));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $id = $request->id;
        $record = Section::firstOrNew(['id' => $id]);
        $record->title = $request->title;
        $record->subtitle = $request->subtitle;
        $record->order = 1;
        $record->survey_id = $request->survey_id;
    
        $record->save();
        return ['success' => true, 'message' => $id ? 'Sección actualizada' : 'Sección creada', 'data' => $record];
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
