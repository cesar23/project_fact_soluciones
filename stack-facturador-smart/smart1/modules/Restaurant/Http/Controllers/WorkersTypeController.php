<?php

namespace Modules\Restaurant\Http\Controllers;


use App\Models\Tenant\Configuration;

use Illuminate\Routing\Controller;

use Modules\Restaurant\Models\WorkersType;
use Modules\Restaurant\Http\Requests\WorkersTypeRequest;
use Modules\Restaurant\Http\Resources\WorkersTypeCollection;
use Illuminate\Http\Request;

class WorkersTypeController extends Controller
{

    public function index()
    {
        $configurations=Configuration::first();
        return view('restaurant::configuration.workers_type',compact('configurations'));
    }
    public function columns()
    {
        return [
            'description' => 'Descripción',
            'active'      => 'Estado'
        ];
    }
    public function records(Request $request)
    {
        if($request->value=="Activado"){
            $records = WorkersType::where($request->column, '=', 1);
        }else{
            $records = WorkersType::where($request->column, 'like', "%{$request->value}%");
        }
       
        return new WorkersTypeCollection($records->paginate(config('tenant.items_per_page')));
     }
    public function actives()
    {
        $workers_type = WorkersType::where('active',1)->get();
        return [
            'success' => true,
            'data' => $workers_type
        ];
    }

    public function record($id)
    {
        $workers_type = WorkersType::find($id);

        return [
            'success' => true,
            'data' => $workers_type
        ];
    }
    public function active($id)
    {
        $workers_type = WorkersType::find($id);
        $workers_type->active = !$workers_type->active;
        $workers_type->save();
        return [
            'success' => true,
            'data' => $workers_type,
            'message' => 'Tipo ' . ($workers_type->active ? 'activado' : 'desactivado')
        ];
    }
    public function store(WorkersTypeRequest $request)
    {
        $id = $request->input('id');
        $worker_type = WorkersType::firstOrNew(['id' => $id]);
        $worker_type->fill($request->all());
        $worker_type->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Tipo actualizado con éxito' : 'Tipo creado con éxito'
        ];
    }



    public function destroy($id)
    {
        //
    }
}
