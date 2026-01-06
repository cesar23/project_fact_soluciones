<?php

namespace Modules\Restaurant\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\StatusOrden;
use Modules\Restaurant\Http\Requests\StatusOrdenRequest;
use Modules\Restaurant\Http\Resources\WorkersTypeCollection;

class StatusOrdenController extends Controller
{


    public function index()
    {
        $configurations=Configuration::first();
        return view('restaurant::configuration.status_orden',compact('configurations'));
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
        // $status_ordens = StatusOrden::all();

        // return [
        //     'success' => true,
        //     'data' => $status_ordens
        // ];
        if($request->value=="Activado"){
            $records = StatusOrden::where($request->column, '=', 1);
        }else{
            $records = StatusOrden::where($request->column, 'like', "%{$request->value}%");
        }

        return new WorkersTypeCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function active($id)
    {
        $status_orden = StatusOrden::find($id);
        $status_orden->active = !$status_orden->active;
        $status_orden->save();
        return [
            'success' => true,
            'data' => $status_orden,
            'message' => 'Estado ' . ($status_orden->active ? 'activado' : 'desactivado')
        ];
    }
    public function record($id)
    {
        $status_orden = StatusOrden::find($id);

        return [
            'success' => true,
            'data' => $status_orden
        ];
    }
    public function store(StatusOrdenRequest $request)
    {
        $id = $request->input('id');
        $status_orden = StatusOrden::firstOrNew(['id' => $id]);
        $status_orden->fill($request->all());
        $status_orden->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Estado actualizado con éxito' : 'Estado creado con éxito'
        ];
    }
    public function destroy($id)
    {
        //
    }
}
