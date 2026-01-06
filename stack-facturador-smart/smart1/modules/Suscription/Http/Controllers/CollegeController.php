<?php

namespace Modules\Suscription\Http\Controllers;

use App\Models\Tenant\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Suscription\Models\Tenant\SuscriptionGrade;
use Modules\Suscription\Models\Tenant\SuscriptionPayment;
use Modules\Suscription\Models\Tenant\SuscriptionSection;

class CollegeController extends Controller
{


    public function documents($month, $year, $id, $parent_id)
    {

        //create a date with year and month, use carbon
        $date = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');

        $documents = SuscriptionPayment::where('child_id', $id)
            ->where('client_id', $parent_id)
            ->where('period', $date)->with(['document.periods', 'sale_note.periods'])
            ->get();

        return compact('documents');
    }
    public function save_observation(Request $request)
    {
        $id = $request->input('id');
        $observation = $request->input('observation');
        $person = Person::findOrFail($id);
        $person->observation = $observation;
        $person->save();
        return [
            'success' => true,
            'message' => 'Observación registrada con éxito',
        ];
    }
    public function records(Request $request)
    {

        $grades = SuscriptionGrade::all();
        $sections = SuscriptionSection::all();

        return compact('grades', 'sections');
    }
}
