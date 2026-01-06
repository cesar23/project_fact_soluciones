<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sale\Models\TechnicalServiceState;

class TechnicalServiceStateController extends Controller
{

    public function index()
    {
        return view('sale::state-technical-services.index');
    }

    public function records()
    {
        return TechnicalServiceState::get();
    }
 

    public function record($id)
    {
        $record = TechnicalServiceState::findOrFail($id);

        return $record;
    }

    
    public function store(Request $request)
    {
        $id = $request->input('id');
        $record = TechnicalServiceState::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->save();

        return [
            'success' => true,
            'message' => ($id)? 'Estado de servicio técnico editado con éxito':'Estado de servicio técnico registrada con éxito'
        ];
    }


    public function destroy($id)
    {
            
        $record = TechnicalServiceState::findOrFail($id);
        $record->delete(); 

        return [
            'success' => true,
            'message' => 'Estado de servicio técnico eliminada con éxito'
        ];

    }


}
