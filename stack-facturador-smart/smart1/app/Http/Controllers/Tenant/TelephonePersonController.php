<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TelephonePerson;
use Illuminate\Http\Request;

class TelephonePersonController extends Controller
{



    public function store(Request $request)
    {
        $person_id = $request->input('person_id');
        $telephone = $request->input('telephone');
        $telephone_person = new TelephonePerson();
        $telephone_person->person_id = $person_id;
        $telephone_person->telephone = $telephone;
        $telephone_person->save();
        return [
            'success' => true,
            'message' => 'Telefono guardado con éxito',
        ];
    }

    public function destroy($id)
    {
        $telephone_person = TelephonePerson::findOrFail($id);
        $telephone_person->delete();
        return [
            'success' => true,
            'message' => 'Telefono eliminado con éxito',
        ];
    }
}
