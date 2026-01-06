<?php

namespace Modules\Restaurant\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\StatusTable;
use Modules\Restaurant\Http\Requests\StatusTableRequest;
use Modules\Restaurant\Http\Resources\WorkersTypeCollection;

class StatusTableController extends Controller
{


    public function index()
    {
        $configurations = Configuration::first();
        return view('restaurant::configuration.status_table',compact('configurations'));
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
        // $status_tables = StatusTable::all();
        // return [
        //     'success' => true,
        //     'data' => $status_tables
        // ];
        if($request->value=="Activado"){
            $records = StatusTable::where($request->column, '=', 1);
        }else{
            $records = StatusTable::where($request->column, 'like', "%{$request->value}%");
        }
        return new WorkersTypeCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function active($id)
    {
        $status_tables = StatusTable::find($id);
        $status_tables->active = !$status_tables->active;
        $status_tables->save();
        return [
            'success' => true,
            'data' => $status_tables,
            'message' => 'Estado ' . ($status_tables->active ? 'activado' : 'desactivado')
        ];
    }
    public function record($id)
    {
        $status_table = StatusTable::find($id);

        return [
            'success' => true,
            'data' => $status_table
        ];
    }
    public function store(StatusTableRequest $request)
    {
        $id = $request->input('id');
        $status_table = StatusTable::firstOrNew(['id' => $id]);
        $status_table->fill($request->all());
        $status_table->save();

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
