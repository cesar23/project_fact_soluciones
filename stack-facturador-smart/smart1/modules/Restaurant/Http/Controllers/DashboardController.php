<?php

namespace Modules\Restaurant\Http\Controllers;

use Exception;
use App\Models\Tenant\Cash;
 use App\Models\Tenant\Company;
use App\Models\Tenant\User;
use App\Models\Desarrollador;
use Illuminate\Routing\Controller;
use App\Models\Tenant\CategoryItem;
use Modules\Restaurant\Models\Area;
use Modules\Restaurant\Models\Food;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use Illuminate\Support\Facades\Auth;
use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Models\StatusTable;
use Modules\Restaurant\Models\CategoryFood;
use Modules\Restaurant\Http\Resources\TableCollection;

class DashboardController extends Controller
{
    public function pos()
    {
         
         $opencash=Cash::where('user_id', User::getUserCashId())
                    ->where('state',1)
                    ->get()
                    ->last();
           
        if($opencash==null){
            return redirect()-> route('restaurant.cash.index');
        }
        $date_opencash = $opencash->date_opening; 
        $worker = true;
        $company= Company::first();
        $desarrollador=Desarrollador::first();
        $configuration=Configuration::first();
        $establishments=Establishment::where('id',auth()->user()->establishment_id)->first();
        $auth_login=auth()->user()->id;
        return view('restaurant::pos.dashboard', compact('worker','establishments','configuration','auth_login','company','desarrollador','date_opencash'));
    }
    public function data_tables($area_id,$pin){
       try {
        $user = User::where("pin",$pin)->first();
        $areas =Area::where('id',$area_id)->get();
        
        $table=Table::where('area_id',$areas[0]->id)->first();
        if($table!=null){
 
            $tables_active = new TableCollection(Table::where('area_id',$areas[0]->id)->get());
 
            $tables_active = new TableCollection(Table::where('area_id',$areas[0]->id)->get());
           
             $tables_area = collect(Table::where('area_id',$areas[0]->id)->get())->transform(function ($row) {
                $orden = Orden::where('table_id', $row->id)->where('status_orden_id', '!=', 4)->get();
                    return [
                        'id'                => $row->id,
                        'number'            => $row->number,
                        'area'        => $row->area,
                        'status_table'     => $row->status_table,
                        'orden'          => $orden,
                    ];
                });
        }else{
            $tables_active=[];
            $tables_area=null;
        }
        
      
        $configuration = Configuration::first();
        $company = Company::first();
        $foods = Food::all();
        $select_category = CategoryItem::first();
        $select_category_id = $select_category->id;
        $categories = CategoryItem::all();
        $status_table = StatusTable::all();
        return compact('user','areas','table','tables_active','tables_area','configuration','company','foods','select_category','categories','status_table');
    } catch (Exception $e) {
        return [
            "message" => $e->getMessage(),
            "line" => $e->getLine(),
            "file" => $e->getFile(),
        ];

    }
    }
    public function kitchen()
    {
        $areas = Area::where('active',1)->get();
        $user = Auth::user();
        $area_id=$user->area_id;
        $tables = new TableCollection(Table::where('area_id', $user->area_id)->get());
        $foods = Food::all();

        $configuration = Configuration::first();
        $categories = CategoryItem::all();
        $status_table = StatusTable::all();

        return view('restaurant::kitchen.dashboard', compact('configuration', 'areas','area_id', 'foods', 'categories', 'status_table'));
    }
     public function index()
    {
        
        try {
            $user = Auth::user();
            $areas =Area::where('id',auth()->user()->area_id)->get();
            $table=Table::where('area_id',$areas[0]->id)->first();

            //dd($areas,$table);
            if($table!=null){
                $tables_active = new TableCollection(Table::where('area_id',$areas[0]->id)->first());
                $tables_area = collect(Table::where('area_id',$areas[0]->id)->get())->transform(function ($row) {
                    $orden = Orden::where('table_id', $row->id)->where('status_orden_id', '!=', 4)->get();
                        return [
                            'id'                => $row->id,
                            'number'            => $row->number,
                            'area'        => $row->area,
                            'status_table'     => $row->status_table,
                            'orden'          => $orden,
                        ];
                    });
            }else{
                $tables_active=[];
                $tables_area=null;
            }

            $configuration = Configuration::first();
            $company = Company::first();
            // $tables = new TableCollection(Table::where('area_id', $user->area_id)->get());
            $foods = Food::all();

            $select_category = CategoryItem::first();
            $select_category_id = $select_category->id;
            $categories = CategoryItem::all();
            $status_table = StatusTable::all();
           
            return view('restaurant::worker.index',compact('configuration', 'areas','tables_active','foods','categories', 'status_table', 'company','tables_area'));
        } catch (Exception $e) {
            return [
                "message" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
            ];

        }

    }
    public function tables($area_id)
    {
        $tables = Table::where('area_id', $area_id)->get();

        return [
            'success' => true,
            'data' => $tables
        ];
    }
}
