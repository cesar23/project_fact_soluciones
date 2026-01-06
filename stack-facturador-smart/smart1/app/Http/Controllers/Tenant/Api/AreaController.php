<?php

namespace App\Http\Controllers\Tenant\Api;


use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\Area;
use Modules\Restaurant\Http\Requests\AreaRequest;
use Modules\Restaurant\Http\Resources\AreaCollection;



class AreaController extends Controller
{


    public function index()
    {
        $configurations = Configuration::first();
        return view('restaurant::configuration.areas',compact('configurations'));
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
        // $areas = Area::all();
        // return [
        //     'success' => true,
        //     'data' => $areas
        // ];
        if($request->value=="Activado"){
            $records = Area::where($request->column, '=', 1);
        }else{
            $records = Area::where($request->column, 'like', "%{$request->value}%");
        }
        return new AreaCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function actives()
    {
        $areas = Area::where('active',1)->get();

        return [
            'success' => true,
            'data' => $areas
        ];
    }
    public function active($id)
    {
        $area = Area::find($id);
        $area->active = !$area->active;
        $area->save();
        return [
            'success' => true,
            'data' => $area,
            'message' => 'Área ' . ($area->active ? 'activada' : 'desactivada')
        ];
    }
    public function record($id)
    {
        $area = Area::find($id);

        return [
            'success' => true,
            'data' => $area
        ];
    }
    public function store(AreaRequest $request)
    {
        $id = $request->input('id');
        $area = Area::firstOrNew(['id' => $id]);
        $area->fill($request->all());
        $area->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Área actualizada con éxito' : 'Área creada con éxito'
        ];
    }
    public function destroy($id)
    {
        $area = Area::find($id);
        $area->delete();
        return [
            'success' => true,
            'message' =>  'Área eliminado con éxito' 
        ];
    }
}
