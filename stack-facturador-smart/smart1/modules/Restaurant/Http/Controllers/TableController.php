<?php

namespace Modules\Restaurant\Http\Controllers;


use App\Models\Tenant\Configuration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Http\Requests\TableRequest;
use Modules\Restaurant\Http\Resources\TableCollection;
use Modules\Restaurant\Models\Orden;

class TableController extends Controller
{
    public function enable($id)
    {
        $table = Table::find($id);
        $table->active = true;
        $table->save();
        return [
            'success' => true,
            'message' => 'Mesa habilitada con éxito'
        ];
    }

    public function disable($id)
    {
        $table = Table::find($id);
        if($table->status_table_id == 2){
            return [
                'success' => false,
                'message' => 'Mesa ocupada, para deshabilitarla, primero debe finalizar la orden'
            ];
        }
        $table->active = false;
        $table->save();
        return [
            'success' => true,
            'message' => 'Mesa deshabilitada con éxito'
        ];
    }

    public function index()
    {
        $configurations = Configuration::first();
        return view('restaurant::configuration.tables', compact('configurations'));
    }
    public function columns()
    {
        return [
            'number' => 'Nº Mesa',
        ];
    }
    public function recordsByArea($id)
    {
        $tables = new TableCollection(Table::where('area_id', $id)->where('active', true));

        return [
            'success' => true,
            'data' => $tables
        ];
    }


    public function recordsAttention(){
        $tables = Table::where('active', true)->get();
        return [
            'success' => true,
            'data' => $tables
        ];
    }
    public function allRecords(){
        $records = Table::query();  
        return new TableCollection($records->paginate(50));
    }
    public function records()
    {
        $records = Table::where('active', true);  
        return new TableCollection($records->paginate(50));

        // return [
        //     'success' => true,
        //     'data' => $tables
        // ];
    }
    public function record($id)
    {
        $table = Table::find($id);

        return [
            'success' => true,
            'data' => $table
        ];
    }
    public function massive(Request $request)
    {
        $tables = $request->input('tables');
        $exist = Table::whereIn('number', $tables)->count();
        if ($exist > 0) {
            return [
                'success' => false,
                'message' => 'Ya existe una mesa el prefijo y número enviado, revise los datos'
            ];
        }
        foreach ($tables as $tableInsert) {
            $table = new Table;
            $table->fill($tableInsert);
            $table->status_table_id = 1;
            $table->created_at = now();
            $table->updated_at = now();
            $table->save();
        }
        return [
            'success' => true,
            'message' => 'Mesa creada con éxito'
        ];
    }

    public function store(TableRequest $request)
    {
        $id = $request->input('id');
        $status_table_id = $request->input('status_table_id');
        $table = Table::firstOrNew(['id' => $id]);
        $table->fill($request->all());
        if($status_table_id == null && $id == null){
            $table->status_table_id = 1;
        }
        $table->save();





        return [
            'success' => true,
            'message' => ($id) ? 'Área actualizada con éxito' : 'Área creada con éxito'
        ];
    }
    public function destroy($id)
    {

        $area = Table::find($id);
        $area->delete();
        return [
            'success' => true,
            'message' =>  'Mesa eliminado con éxito'
        ];
    }
}
