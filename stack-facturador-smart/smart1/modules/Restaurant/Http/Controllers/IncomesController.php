<?php

namespace Modules\Restaurant\Http\Controllers;

use Carbon\Carbon;
use App\Models\Box;
use App\Models\User;
use App\Models\Group;
use App\Models\Person;
use App\Models\Company;
use App\Models\Category;
use App\Models\SoapType;
use App\Models\Warehouse;
use App\Models\PersonType;
use App\Models\Subcategory;
use App\Models\Catalogs\Tag;
use Illuminate\Http\Request;
use App\Models\Configuration;
use App\Models\Establishment;
use Modules\Item\Models\Brand;
use App\Models\Catalogs\Country;
use App\Models\Catalogs\District;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\UnitType;
use App\Models\PaymentMethodType;
use Illuminate\Routing\Controller;
use Modules\Format\Models\Account;
use App\Models\Catalogs\Department;
use Modules\Restaurant\Models\Food;
use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Models\Table;
use App\Http\Resources\BoxCollection;
use App\Models\Catalogs\CurrencyType;
use App\Models\Catalogs\AttributeType;
use App\Models\Catalogs\SystemIscType;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Restaurant\Events\StockEvent;
use App\Models\Catalogs\AffectationIgvType;
use App\Models\Catalogs\IdentityDocumentType;
use Modules\Restaurant\Events\OrdenPaidEvent;
use Modules\Restaurant\Http\Requests\IncomesRequest;
use Modules\Restaurant\Http\Resources\OrdenCollection;
use Modules\Restaurant\Http\Resources\ExpensesResource;
use Modules\Restaurant\Http\Resources\OrdenItemCollection;

class IncomesController extends Controller
{
    public function foods()
    {
        $foods = Food::all();
        return ['foods' => $foods];
    }
    public function pos()
    {
        $establishments= Establishment::where('id',auth()->user()->establishment_id)->first();
        $configuration=Configuration::first();

        return view('tenant.pos.index',compact('configuration','establishments'));
    }


    public function index()
    {
        $establishments= Establishment::where('id',auth()->user()->establishment_id)->first();
        $configuration=Configuration::first();
        return view('restaurant::pos',compact('configuration','establishments'));
    }

    
    public function incomes()
    {
        $group=Group::where('type','1')->first();
        $category=Category::where('type','1')->first();
        $subcategory=Subcategory::where('type','1')->first();
        $groupid=$group->id;
        $categoryid=$category->id;
        $userid=auth()->user()->id;
        $subcategoryid=$subcategory->id;
        $company=Company::first();
        $soaptypeid=$company->soap_type_id;
        //dd($categoryid);
        return view('restaurant::incomes.index',compact('groupid','categoryid','userid','subcategoryid','soaptypeid'));
    }
    public function tables()
    {
        $gruop = Group::all();
        $category = Category::all();
        $subcategory = Subcategory::all();
        $company = Company::first();
        $methods = PaymentMethodType::where('active',1)->get();
        return compact('gruop', 'category', 'subcategory', 'company', 'methods');
    }
    public function columns() //buscador x campo
    {
        return [
            'id'                => 'Código',
            'date'              => 'Fecha',
            'description'       => 'Descripcion - Detalle',
        ];
    }
    public function record($id)
    {
        $record = new ExpensesResource(Box::findOrFail($id));
        return $record;
    }    

    public function records(Request $request)
    {
        $records = Box::where($request->column, 'like', "%{$request->value}%")->where('type', 1)->where('incomes', '1')->where('user_id',auth()->user()->id)->orderBy('id', 'desc'); //para ordenar
        return new BoxCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function store(IncomesRequest $request)
    {
        $company=Company::first();
        $id          = $request->input('id');
        $box         = Box::firstOrNew(['id' => $id]);
        $box->fill($request->all());
        $box->date = Carbon::parse($request->input('date'))->format('Y-m-d');
        $box->user_id = auth()->user()->id;
        $box->soap_type_id=$company->soap_type_id;
        $box->establishment_id=auth()->user()->establishment_id;
        $box->save();
        return [
            'success' => true,
            'message' => ($id) ? 'Actualizado con éxito' : 'Registrado con éxito',
            'data'    => $box
        ];
    }
    public function selecttabled($idOrden){
        $table= Table::where('number',"caja")->first();
        if($table==null){
           $table= Table::create([
                "number"          => "caja",
                "area_id"         =>  auth()->user()->area_id,
                "status_table_id" => "2"
            ]);
        }
        $list_tables = collect(Table::where('id',$table->id)->get())->transform(function ($row) use($idOrden) {
        $ordens = new OrdenCollection(Orden::where('id', '=', $idOrden)->where('status_orden_id', '=', 1)->get());
            return [
                'id' => $row->id,
                'number' =>  $row->number,
                'status_table_id' => $row->status_table_id,
                'ordens' => $ordens,
             ];
        });
            return $list_tables;
    }
    public function listtables(){
      $data=Table::where('status_table_id','2');

      if($data->count()==0){
            return [
                "success" => false,
                "message" => "No existe mesas Ocupadas"
            ];
      }
      $list_tables = collect($data->get())->transform(function ($row) {
      $ordens = new OrdenCollection(Orden::where('table_id', '=', $row->id)->where('status_orden_id', '=', 1)->get());

        return [
            'id' => $row->id,
            'number' =>  $row->number,
            'status_table_id' => $row->status_table_id,
            'ordens' => $ordens,
         ];
    });

        return $list_tables;
    }
    public function payment(Request $request)
    {
        $customer_id = $request->customer_id;
        $id = $request->id;
        $orden = Orden::find($id);
        if ($orden != null) {
            $table = Table::find($orden->table_id);
        }
        $orden->customer_id = $customer_id;
        $orden->status_orden_id = 4;
        $isNoteSale = $request->document['isNoteSale'];
        if ($isNoteSale) {
            $orden->sale_note_id = $request->document['id'];
        } else {
            $orden->document_id = $request->document['id'];
        }
        $orden->save();

        $tableIsFree = Orden::where('table_id', $table->id)->where(function ($query) {
            $query->where('status_orden_id',  1)
                ->orWhere('status_orden_id',  2)
                ->orWhere('status_orden_id',  3);
        })
            ->count();

        if ($tableIsFree == 0) {
            $table->status_table_id = 1;
            $table->save();
        }
         event(new OrdenPaidEvent(true));
         event(new StockEvent($orden->id));
        return [
            'success' => true
        ];
    }
    public function search_orden_document(Request $request)
    {
        $orden_id = $request->input_item;
        $ordens = Orden::where('id', $orden_id)->first();
        if ($ordens) {
            return [
                'ordens' => $ordens,
                'success' => true
            ];
        } else {
            return [
                'message' => 'No se encontraron ordenes.',
                'ordens' => [],
                'success' => false
            ];
        }
    }
    public function search(Request $request)
    {

        $orden_id = $request->input_item;
        $ordens = Orden::where('id', $orden_id)->where('status_orden_id', 1)->first();

        if ($ordens) {
            $items = OrdenItem::where('orden_id', $ordens->id)->get();
            foreach ($items as $item) {
                if ($item->status_orden_id == 1) {
                    return [
                        'message' => 'La orden tiene pedidos por atender.',
                        'ordens' => [],
                        'success' => false
                    ];
                }
            }
            return [
                'ordens' => $ordens,
                'success' => true
            ];
        } else {
            return [
                'message' => 'No se encontraron ordenes.',
                'ordens' => [],
                'success' => false
            ];
        }
    }
    public function destroy($id) //Eliminar
    {
        $box = Box::findOrFail($id);
        $box->delete();
        return [
            'success' => true,
            'message' => 'Eliminado con éxito'
        ];
    }
}
