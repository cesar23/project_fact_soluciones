<?php

namespace App\Http\Controllers\Tenant\Api;


use App\Models\Configuration;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Http\Requests\TableRequest;
use Modules\Restaurant\Http\Resources\TableCollection;

class TableController extends Controller
{


    public function index()
    {
        $configurations = Configuration::first();
        return view('restaurant::configuration.tables',compact('configurations'));
    }
    public function columns()
    {
        return [
            'number' => 'Nº Mesa',
         ];
    }
    public function recordsByArea($id)
    {
        $tables = new TableCollection(Table::where('area_id', $id)->get());

        return [
            'success' => true,
            'data' => $tables
        ];
    }
    public function records()
    {
        $records=Table::query();
       return new TableCollection($records->paginate(config('tenant.items_per_page')));

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
    public function store(TableRequest $request)
    {
        $id = $request->input('id');
           
            if($request->multiple==false){
                $table = Table::firstOrNew(['id' => $id]);
                $table->fill($request->all());
                $table->save();
            }else{
                for ($i=1; $i <= $request->number; $i++) { 
                    $table = Table::firstOrNew(['id' => $id]);
                    $table->fill($request->all());
                    $table->number = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $table->save();
                }
            }
                      
    
       

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
