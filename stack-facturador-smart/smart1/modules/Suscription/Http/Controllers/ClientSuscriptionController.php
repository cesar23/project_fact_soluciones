<?php

namespace Modules\Suscription\Http\Controllers;

use App\Http\Controllers\SearchCustomerController;
use App\Http\Controllers\Tenant\PersonController;
use App\Http\Requests\Tenant\PersonRequest;

use Modules\Suscription\Http\Resources\PersonCollection;
use App\Http\Resources\Tenant\PersonResource;
use App\Models\System\Configuration;
use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Document;
use App\Models\Tenant\Person;
use App\Models\Tenant\PersonFiles;
use App\Models\Tenant\PersonType;
use App\Models\Tenant\SaleNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\FullSuscription\Models\Tenant\UserRelSuscriptionPlan;
use Modules\Suscription\Export\ClientSuscriptionExport;
use Modules\Suscription\Models\Tenant\SuscriptionNames;
use Modules\Suscription\Models\Tenant\SuscriptionPayment;

class ClientSuscriptionController extends SuscriptionController
{
    /**
     * @return string[]
     */
    public function Columns()
    {
        return [
            'name' => 'Nombre / Documento',
            'person_date' => 'Fecha',

            // 'number' => 'Número',
            // 'document_type' => 'Tipo de documento',
            // 'childrens' => 'Nombre de hijos',
        ];
    }

    public function set_date(Request $request)
    {
        $date = $request->date;
        $person_id = $request->person_id;
        $person = Person::find($person_id);
        $person->person_date = $date;
        $person->save();
        return [
            'success' => true,
            'message' => 'Se guardo correctamente'
        ];
    }
    public function set_color(Request $request)
    {
        $color = $request->color;
        $person_id = $request->person_id;
        $person = Person::find($person_id);
        $person->color = $color;
        $person->save();
        return [
            'success' => true,
            'message' => 'Se guardo correctamente'
        ];
    }
    public function get_child_from_document($document_id, $parent_id, $type)
    {
        $type_document = $type == '80' ? 'sale_note_id' : 'document_id';
        $record = SuscriptionPayment::where($type_document, $document_id)
            ->where('client_id', $parent_id)
            ->first();

        if ($record) {
            return [
                'success' => true,
                'child_id' => $record->child_id
            ];
        }
        return [
            'success' => false,
            'message' => 'No se encontro el registro'
        ];
    }
    public function get_periods($document_type_id, $document_id)
    {
        $document_column = $document_type_id == "80" ? 'sale_note_id' : 'document_id';
        $periods = SuscriptionPayment::where($document_column, $document_id)->get();
        $document_child = null;
        if (count($periods) > 0) {
            $child_id = $periods[0]->child_id;
            $person = Person::find($child_id);
            $document_child = $person->number;
        }
        return [
            'document_child' => $document_child,
            'success' => true,
            'periods' => $periods,
        ];
    }
    public function save_periods(Request $request)
    {
        $child_id = $request->child_id;
        $periods = $request->months;
        $document_id = $request->document_id;
        $document_type_id = $request->document_type_id;
        $column = $document_type_id == "80" ? 'sale_note_id' : 'document_id';
        SuscriptionPayment::where($column, $document_id)->delete();
        $model = $document_type_id == "80" ? SaleNote::class : Document::class;
        $document = $model::find($document_id);
        $client_id = $document->customer_id;
        foreach ($periods as  $period) {
            $date = Carbon::createFromDate($period['year'], $period['value'], 1);
            SuscriptionPayment::create([
                'child_id' => $child_id,
                'client_id' => $client_id,
                'sale_note_id' => $document_type_id == "80" ? $document_id : null,
                'document_id' => $document_type_id != "80" ? $document_id : null,
                'period' => $date,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Periodos guardados correctamente'
        ];
    }

    public function itemPlan(Request $request)
    {
        $parent_customer_id = $request->parent_customer_id;
        $children_customer_id = $request->children_customer_id;
        $suscription = UserRelSuscriptionPlan::where('parent_customer_id', $parent_customer_id)
            ->where('children_customer_id', $children_customer_id)
            ->first();
        $items = [];
        if ($suscription) {
            $plan = $suscription->suscription_plan;
            if ($plan) {
                $items = $plan->items;
            }
        }

        return [
            'success' => true,
            'items' => $items
        ];
    }
    public function Record(Request $request)
    {
        $personId = (int)($request->has('person') ? $request->person : 0);
        $records = SearchCustomerController::getCustomersToSuscriptionList($request, $personId);
        if ($request->has('users')) {
            if ($request->users == 'parent') {
                $records->where('parent_id', 0);
            } elseif ($request->users == 'children') {
                $records->where('parent_id', '!=', 0);
            }
        }
        $records = $records->firstOrFail();

        return ['data' => $records->getCollectionData(true, true)];
    }
    function getTotals()
    {
        $currentYear = date('Y');
        $months = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $i + 1;
            $date = Carbon::createFromDate($currentYear, $month, 1)->format('Y-m-d');

            $payments = SuscriptionPayment::with('document', 'sale_note')
                ->where('period', $date)
                ->get();

            $total = $payments->sum(function ($payment) {
                $document = $payment->document ?? $payment->sale_note;
                if ($payment->document) {
                    $periods = SuscriptionPayment::where('document_id', $payment->document_id)

                        ->count();
                } else {
                    $periods = SuscriptionPayment::where('sale_note_id', $payment->sale_note_id)
                        ->count();
                }


                return $document->total / $periods;
            });

            $months[] = $total;
        }

        return $months;
    }

    // function getTotals()
    // {
    //     $currentYear = date('Y');
    //     $months = [];
    //     //for loop for last 12 months
    //     for ($i = 0; $i < 12; $i++) {
    //         $month = $i + 1;
    //         $date = Carbon::createFromDate($currentYear, $month, 1)->format('Y-m-d');

    //         $payments = SuscriptionPayment::where('period', $date)->get();
    //         $total = 0;
    //         foreach ($payments as $payment) {
    //             if($payment->document_id != null){
    //                 $document = Document::find($payment->document_id);
    //                 $periods = SuscriptionPayment::where('document_id', $payment->document_id)->count();



    //         }else{
    //             $document = SaleNote::find($payment->sale_note_id);
    //             $periods = SuscriptionPayment::where('sale_note_id', $payment->sale_note_id)->count();

    //         }
    //         $total = $document->total / $periods;

    //         }

    //         $months[] = $total;
    //     }

    //     return $months;
    // }

    public function ExcelRecords(Request $request)
    {
        $records = SearchCustomerController::getCustomersToSuscriptionList($request);
        // getCustomersToSuscriptionList(Request $request = null, ?int $id = 0, $onlyParent = true){
        if ($request->has('users')) {
            if ($request->users == 'parent') {
                $records->where('parent_id', 0);
            } elseif ($request->users == 'children') {
                $records->where('parent_id', '!=', 0);
            }
        }
        $currentYear = $request->year ?? date('Y');
        // users
        $data = new PersonCollection($records->get());



        return (new ClientSuscriptionExport())
        ->records($data)
        ->currentYear($currentYear)
        ->download('Suscripciones_'.Carbon::now().'_.xlsx')

        ;
    }
    public function Records(Request $request)
    {
        $page = $request->input('page') ?? 1;
        $records = SearchCustomerController::getCustomersToSuscriptionList($request);
        // getCustomersToSuscriptionList(Request $request = null, ?int $id = 0, $onlyParent = true){
        if ($request->has('users')) {
            if ($request->users == 'parent') {
                $records->where('parent_id', 0);
            } elseif ($request->users == 'children') {
                $records->where('parent_id', '!=', 0);
            }
        }
        // users
        $paginate = $request->users == 'children' ? 20 : config('tenant.items_per_page');
        $data = new PersonCollection($records->paginate($paginate));

        $data->additional([
            'totals' => $page == 1 || $request->value ? $this->getTotals() : null,
        ]);
        return $data;
    }

    public function Tables()
    {

        $countries = Country::whereActive()->orderByDescription()->get();
        $departments = Department::whereActive()->orderByDescription()->get();
        $provinces = Province::whereActive()->orderByDescription()->get();
        $districts = District::whereActive()->orderByDescription()->get();
        $identity_document_types = IdentityDocumentType::whereActive()->get();
        $person_types = PersonType::get();
        $locations = $this->getLocationCascade();
        // $configuration = Configuration::first();
        // $api_service_token = $configuration->token_apiruc === 'false' ? config('configuration.api_service_token') : $configuration->token_apiruc;
        $api_service_token = \App\Models\Tenant\Configuration::getApiServiceToken();

        return compact('countries', 'departments', 'provinces', 'districts', 'identity_document_types', 'locations', 'person_types', 'api_service_token');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */

    public function suscription_name_store(Request $request)
    {
        $id = $request->input('id');
        $parents = $request->input('parents');
        $children = $request->input('children');
        $grades = $request->input('grades');
        $sections = $request->input('sections');


        $suscriptionName = SuscriptionNames::findOrNew($id);
        $suscriptionName->parents = $parents;
        $suscriptionName->children = $children;
        $suscriptionName->grades = $grades;
        $suscriptionName->sections = $sections;
        $suscriptionName->save();

        return [
            'success' => true,
            'message' => 'Se guardo correctamente'
        ];
    }
    public function suscription_name_records(Request $request)
    {
        $records = SuscriptionNames::create_new();


        return [
            'success' => true,
            'data' => $records
        ];
    }
    public function upload_files($person_id, Request $request)
    {


        $file = $request->file('file');
        $original_name = $file->getClientOriginalName();
        //random name
        $path = 'app/public/uploads/person_files';
        $name = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(storage_path($path), $name);
        $personFile = new PersonFiles();
        $personFile->person_id = $person_id;
        $personFile->file = $name;
        $personFile->original_name = $original_name;
        $personFile->save();
        return [
            'success' => true,
            'message' => 'El archivo se guardo correctamente'
        ];
    }





    public function delete_file($id)
    {
        $file = PersonFiles::find($id);
        $file_to_delete = $file->file;
        $path = 'app/public/uploads/person_files';
        $file_path = storage_path($path) . '/' . $file_to_delete;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $file->delete();
        return [
            'success' => true,
            'message' => 'El archivo se elimino correctamente'
        ];
    }
    public function get_files($person_id)
    {

        $files = PersonFiles::where('person_id', $person_id)->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'file' => $row->file,
                    'original_name' => $row->original_name,
                    'url' => url('storage/uploads/person_files/' . $row->file),
                ];
            });
        return [
            'success' => true,
            'data' => $files
        ];
    }
    public function suscription_name()
    {

        return view('suscription::suscription_names.index');
    }
    public function index()
    {

        $suscriptionames = SuscriptionNames::create_new();
        return view('suscription::clients.index', compact('suscriptionames'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function indexChildren()
    {

        $suscriptionames = SuscriptionNames::create_new();
        return view('suscription::clients.index_child', compact('suscriptionames'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        return view('suscription::create');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit($id)
    {
        return view('suscription::edit');
    }


    /**
     * Show the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show($id)
    {
        return view('suscription::show');
    }



    /**
     * Almacena los datos de persona basado en el funcion amiento de su controlador
     *
     * @param PersonRequest $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function store(PersonRequest $request)
    {

        $personController = new PersonController();
        $data  =  $personController->store($request);
        $childrens = $request->childrens;

        $demo = [];
        foreach ($childrens as $child) {
            $name = $child['name'];
            $number = $child['number'];
            $person = Person::where('name', $name)->where('number', $number)->first();
            if ($person) {
                $person->parent_id = $data['id'];
                $person->save();
                $demo[] = $person;
            } else {
                $child['parent_id'] = $data['id'];
                $child['addresses'] = $request->input('addresses');
                $childRequest = new PersonRequest();
                $childRequest->merge($child);
                $demo[] = $personController->store($childRequest);
            }
        }
        $data[] = $demo;
        return $data;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function update(Request $request, $id)
    {
        //
    }
}
