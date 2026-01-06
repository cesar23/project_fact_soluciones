<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\Department;
use App\Traits\CacheTrait;
use Illuminate\Support\Facades\Cache;
use Modules\BusinessTurn\Models\AgencyTransport;
use Modules\Sale\Http\Resources\AgencyTransportCollection;
use \Hyn\Tenancy\Facades\TenancyFacade;
class AgencyTransportController extends Controller
{

    use CacheTrait;

 
    public function locations(){

        $locations = [];
        $departments = Department::whereActive()->get();
        foreach ($departments as $department)
        {
            $children_provinces = [];
            foreach ($department->provinces as $province)
            {
                $children_districts = [];
                foreach ($province->districts as $district)
                {
                    $children_districts[] = [
                        'value' => $district->id,
                        'label' => $district->description
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
        return compact('locations');
    }
    public function columns()
    {
        return [
            'description' => 'Descripción',
        ];
    }
 

    public function records(Request $request)
    {
        $records = AgencyTransport::all();

        return new AgencyTransportCollection($records);
    }


    public function record($id)
    {
        return AgencyTransport::findOrFail($id)->getRowResource();
    }
 

    public function store(Request $request) 
    {
        $id = $request->input('id');
        $record = AgencyTransport::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->save();
        $this->clearCache('business_turn_data_transports');

        return [
            'success' => true,
            'message' => ($id)? 'Agencia de transporte editada con éxito' : 'Agencia de transporte registrada con éxito'
        ];
    }
 
  
    public function destroy($id)
    {
        $record = AgencyTransport::findOrFail($id);
        $record->delete();

        return [
            'success' => true,
            'message' => 'Agente eliminado con éxito'
        ];
    }
    



}
