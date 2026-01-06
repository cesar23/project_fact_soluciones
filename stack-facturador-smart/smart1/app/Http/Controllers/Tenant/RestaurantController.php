<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Support\Str;
use App\Events\OrderEvent;
use App\Events\ReceiveOrder;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\ConditionBlockPaymentMethod;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Restaurant\Http\Requests\AreaRequest;
use Modules\Restaurant\Http\Requests\WorkersTypeRequest;
use Modules\Restaurant\Models\Area;
use Modules\Restaurant\Models\WorkersType;

use Illuminate\Support\Facades\Session;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Store\Http\Controllers\StoreController;

class RestaurantController extends Controller
{
    use FinanceTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('restaurant::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('restaurant::create');
    }
    public function workers_type()
    {
        return view('restaurant::configuration.workers_type');
    }
    public function workers_type_records()
    {
        $workers_type = WorkersType::all();

        return [
            'success' => true,
            'data' => $workers_type
        ];
    }
    public function workers_type_record($id)
    {
        $workers_type = WorkersType::find($id);

        return [
            'success' => true,
            'data' => $workers_type
        ];
    }
    public function workers_type_store(WorkersTypeRequest $request)
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
    public function areas()
    {
        return view('restaurant::configuration.areas');
    }
    public function areas_records()
    {
        $areas = Area::where('active', 1)->get();

        return [
            'success' => true,
            'data' => $areas
        ];
    }
    public function areas_record($id)
    {
        $area = Area::find($id);

        return [
            'success' => true,
            'data' => $area
        ];
    }
    public function areas_store(AreaRequest $request)
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
    public function sendOrder(Request $request)
    {
        $data = $request->all();
        OrderEvent($data);
        return [
            'success' => true,
            'message' => 'Orden enviada'
        ];
    }
    public function receiveOrder(Request $request)
    {
        $data = $request->all();
        event(new ReceiveOrder($data));
        return [
            'success' => true,
            'message' => 'Orden enviada'
        ];
    }

    public function loginWorker()
    {
        $configuration = Configuration::first();
        $event_name = $configuration->socket_channel;
        if (!$event_name) {
            $configuration->socket_channel = Str::random(10);
            $configuration->save();
            $event_name = $configuration->socket_channel;
        }

        return view('restaurant::worker.login');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('restaurant::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('restaurant::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function logout(Request $request)
    {
        Session::flush();
        Auth::logout();
        return redirect('login');
    }



    public function getItemsByCategory($category_id)
    {
        $configuration = Configuration::select('id', 'decimal_quantity')->first();

        $items = Item::select('id', 'image', 'description', 'internal_id', 'sale_unit_price', 'has_igv', 'sale_affectation_igv_type_id')
            ->where('active', 1);
        if ($category_id) {
            $items = $items->where(function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            });
        }
        $items = $items->orderBy('description')
            ->limit(50)
            ->get()
            ->transform(function ($row) use ($configuration) {
                /** @var Item $row */
                return [
                    'id' => $row->id,
                    'has_igv' => $row->has_igv,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity),
                    'image' => ($row->image !== 'imagen-no-disponible.jpg')
                        ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image)
                        : asset("/logo/{$row->image}"),
                    'description' => ($row->internal_id) ? "{$row->internal_id} - {$row->description}" : $row->description,

                ];
            });

        return $items;
    }
    public function getTableConfig(Request $request){
        $user = Auth::user();
        $igv_percentage = (new StoreController)->getIgv(new Request());
        $default_customer = Person::whereType('customers')->where('number', '99999999')->first();
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods();
        $payment_destinations =  $this->getPaymentDestinations();
        $all_series = Series::whereIn('document_type_id', ["01","03","80"])
        ->where('establishment_id', $user->establishment_id)
        ->get();
        $document_types_invoice = DocumentType::whereIn('id', ["01","03","80"])->get();
        $affectation_igv_types = AffectationIgvType::all();
        $areas = Area::where('active', 1)->get();
        return [
            'success' => true,
            'data' => [
                'areas' => $areas,
                'igv_percentage' => $igv_percentage,
                'payment_method_types' => $payment_method_types,
                'payment_destinations' => $payment_destinations,
                'all_series' => $all_series,
                'document_types_invoice' => $document_types_invoice,
                'affectation_igv_types' => $affectation_igv_types,
                'default_customer' => [
                    'id' => $default_customer->id,
                    'description' => $default_customer->number . ' - ' . $default_customer->name,
                    'name' => $default_customer->name,
                    'number' => $default_customer->number,
                    'identity_document_type_id' => $default_customer->identity_document_type_id,
                ],
            ]
        ];
    }
    public function getDataTableItem(Request $request)
    {
        $input = $request->input;
        $configuration = Configuration::select('id', 'decimal_quantity')->first();
        $items = Item::select('id', 'image', 'description', 'internal_id', 'sale_unit_price', 'has_igv', 'sale_affectation_igv_type_id')
            ->where('active', 1);
        if ($input) {
            $items = $items->where(function ($query) use ($input) {
                $query->where('description', 'like', "%{$input}%")
                    ->orWhere('internal_id', 'like', "%{$input}%");
            });
        }
        $items = $items->orderBy('description')
            ->limit(50)
            ->get()
            ->transform(function ($row) use ($configuration) {
                /** @var Item $row */
                return [
                    'id' => $row->id,
                    'has_igv' => $row->has_igv,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity),
                    'image' => ($row->image !== 'imagen-no-disponible.jpg')
                        ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image)
                        : asset("/logo/{$row->image}"),
                    'description' => ($row->internal_id) ? "{$row->internal_id} - {$row->description}" : $row->description,

                ];
            });

        return $items;
    }
}
