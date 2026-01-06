<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\Tenant\PersonRequest;
use App\Http\Resources\Tenant\PersonCollection;
use App\Http\Resources\Tenant\PersonResource;
use App\Imports\PersonsImport;
use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Province;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Person;
use App\Models\Tenant\PersonType;
use App\Models\Tenant\Zone;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Modules\Finance\Helpers\UploadFileHelper;
use Carbon\Carbon;
use App\Exports\ClientExport;
use App\Exports\ClientMigrationExport;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Http\Resources\Tenant\SaleNoteHistoryCollection;
use App\Models\System\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\PersonAddress;
use App\Models\Tenant\PersonRegModel;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\SaleNote;
use App\Traits\CacheTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Dispatch\Models\DispatchAddress;
use Modules\Suscription\Models\Tenant\SuscriptionNames;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class PersonController extends Controller
{
    use CacheTrait;

    public function testZone($person_id)
    {
        // Habilitar query logging
        // Limpiar cache de Eloquent
        DB::flushQueryLog();

        $person = Person::find($person_id);

        // Forzar recarga del modelo
        $person->refresh();

        $zone = $person->zoneRelation;
        $zone_id = $person->zone_id;
        $zone_db = Zone::find($zone_id);

        return response()->json([
            'success' => true,
            'data' => $zone,
            'zone_id' => $zone_id,
            'zone_db' => $zone_db,
        ], 200);
    }
    public function checkCustomerFieldToCreateSaleNote($id)
    {
        $person = Person::find($id);
        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Persona no encontrada',
            ], 200);
        }
        if ($person->type != 'customers') {
            return response()->json([
                'success' => false,
                'message' => 'Persona no es cliente',
            ], 200);
        }
        $address = $person->address;
        $email = $person->email;
        $phone = $person->telephone;
        $department = $person->department_id;
        $province = $person->province_id;
        $district = $person->district_id;
        if (!$address || !$email || !$phone || !$department || !$province || !$district) {
        
            return response()->json([
                'success' => false,
                'message' => 'No se puede generar la nota, verificar: Dirección, email, teléfono, ubigeo',
            ], 200);
        }

        $addresses = DispatchAddress::where('person_id', $id)->get();
        $pass_addresses = true;
        if($addresses->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'No se puede generar la nota, verificar: Dirección, email, teléfono, ubigeo',
            ], 200);
        }

        foreach ($addresses as $dispatch_address) {
            $reason = $dispatch_address->reason;
            $address = $dispatch_address->address;
            $reference = $dispatch_address->reference;
            $location_id = $dispatch_address->location_id;
            $google_location = $dispatch_address->google_location;
            $agency = $dispatch_address->agency;
            $identity_document_type_id = $dispatch_address->identity_document_type_id;
            $person_document = $dispatch_address->person_document;
            $person = $dispatch_address->person;
            $person_telephone = $dispatch_address->person_telephone;
            if (!$address || !$reference || !$location_id || !$google_location || !$agency || !$identity_document_type_id || !$person_document || !$person || !$person_telephone || !$reason) {
                $pass_addresses = false;
            
                break;
            }
        }

        if (!$pass_addresses) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede generar la nota, verificar los datos de dirección de envío',
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Datos correctos',
        ], 200);
    }
    public function searchCustomersById($id)
    {
        $customer = DB::connection('tenant')->table('persons')
            ->select('id', 'number', 'name')
            ->where('id', $id)
            ->where('type', 'customers')
            ->first();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'description' => $customer->number . ' - ' . $customer->name,
            ],
        ], 200);
    }
    public function generateDocumentNumberToNoDoc()
    {
        do {
            $number = $this->generateNumber();
            $exists = Person::where('number', $number)
                ->where('identity_document_type_id', '0')
                ->where('type', 'customers')
                ->exists();
        } while ($exists);

        return $number;
    }
    private function generateNumber()
    {
        //crea un numero de 8 digitos aleatorios
        $number = rand(10000000, 99999999);
        return strval($number);
    }
    public function personHistorySalesTotalsById(Request $request)
    {
        $records =  $this->getRecordsSaleById($request)->get();
        // $records_sale = $records->where('is_pay',false);
        $records_pay = $records->where('total_canceled', true);
        $total_pen = 0;
        $total_pen_aux = 0;
        $total_paid_pen = 0;
        $total_pending_paid_pen = 0;

        $total_pen_pay = 0;
        $total_pen_pay_aux = 0;
        $total_paid_pen_pay = 0;
        $total_pending_paid_pen_pay = 0;


        $total_pen = $records->sum('total');
        $total_pen_pay = $records_pay->sum('total');
        // $records->where('is_pay',false);

        foreach ($records as $sale_note) {
            if ($sale_note->state_type_id == "11") {
                $total_pen_aux += $sale_note->total;
            } else {
                $total_paid_pen += $sale_note->payments->sum('payment');
            }
        }
        $total_pen = $total_pen -  $total_pen_aux;
        foreach ($records_pay as $sale_note) {
            if ($sale_note->state_type_id == "11") {
                $total_pen_pay_aux += $sale_note->total;
            } else {
                $total_paid_pen_pay += $sale_note->payments->sum('payment');
            }
        }
        $total_pen_pay = $total_pen_pay -  $total_pen_pay_aux;

        $total_pending_paid_pen = $total_pen - $total_paid_pen;
        $total_pending_paid_pen_pay = $total_pen_pay - $total_paid_pen_pay;

        $totals = $total_pending_paid_pen + $total_pending_paid_pen_pay;

        return [
            'total_pen' => number_format($total_pen, 2, ".", ""),
            'total_paid_pen' => number_format($total_paid_pen, 2, ".", ""),
            'total_pending_paid_pen' => number_format($total_pending_paid_pen, 2, ".", ""),
            'total_pen_pay' => number_format($total_pen_pay, 2, ".", ""),
            'total_paid_pen_pay' => number_format($total_paid_pen_pay, 2, ".", ""),
            'total_pending_paid_pen_pay' => number_format($total_pending_paid_pen_pay, 2, ".", ""),
            'totals' => number_format($totals, 2, ".", "")
        ];
    }
    public function zones()
    {
        $zones = Zone::all();
        return response()->json($zones);
    }

    private function getRecordsSaleById($request)
    {
        $customer_id = $request->customer_id;

        $sale_notes = SaleNote::where('customer_id', $customer_id)
            ->where('state_type_id', '!=', '11');
        if ($request->value != null) {
            $sale_notes = $sale_notes->where($request->column, $request->value);
        }
        if ($request->establishments) {
            $sale_notes = $sale_notes->where('establishment_id', $request->establishments);
        }
        if ($request->total_canceled != null) {
            $sale_notes = $sale_notes->where('total_canceled', $request->total_canceled);
        }
        return $sale_notes;
    }
    public function personHistorySalesById(Request $request)
    {
        $sale_notes = $this->getRecordsSaleById($request);

        return new SaleNoteHistoryCollection($sale_notes->orderBy('created_at', 'desc')->paginate(config('tenant.items_per_page_simple_d_table_params')));
    }
    public function personHistoryPurchasesByCustomerId(Request $request)
    {
        $purchases = $this->getRecordsPurchaseByCustomerId($request);
        return new SaleNoteHistoryCollection($purchases->orderBy('created_at', 'desc')->paginate(config('tenant.items_per_page_simple_d_table_params')));
    }

    private function getRecordsPurchaseByCustomerId($request)
    {
        $customer_id = $request->customer_id;
        $customer = Person::select('number')
            ->where('id', $customer_id)
            ->where('type', 'customers')
            ->first();
        $supplier_id = 0;
        if ($customer) {
            $supplier = Person::select('id')->where('number', $customer->number)->where('type', 'suppliers')->first();
            if ($supplier) {
                $supplier_id = $supplier->id;
            }
        }

        $purchases = Purchase::where('supplier_id', $supplier_id)
            ->where('state_type_id', '!=', '11');
        if ($request->value != null) {
            $purchases = $purchases->where($request->column, $request->value);
        }
        if ($request->establishments) {
            $purchases = $purchases->where('establishment_id', $request->establishments);
        }
        if ($request->total_canceled != null) {
            $purchases = $purchases->where('total_canceled', $request->total_canceled);
        }
        return $purchases;
    }

    public function personHistoryPurchasesTotalsByCustomerId(Request $request)
    {
        $records =  $this->getRecordsPurchaseByCustomerId($request)->get();
        $records_pay = $records->where('total_canceled', true);
        $total_pen = 0;
        $total_pen_aux = 0;
        $total_paid_pen = 0;
        $total_pending_paid_pen = 0;

        $total_pen_pay = 0;
        $total_pen_pay_aux = 0;
        $total_paid_pen_pay = 0;
        $total_pending_paid_pen_pay = 0;


        $total_pen = $records->sum('total');
        $total_pen_pay = $records_pay->sum('total');

        foreach ($records as $purchase) {
            if ($purchase->state_type_id == "11") {
                $total_pen_aux += $purchase->total;
            } else {
                $total_paid_pen += $purchase->payments->sum('payment');
            }
        }
        $total_pen = $total_pen -  $total_pen_aux;
        foreach ($records_pay as $purchase) {
            if ($purchase->state_type_id == "11") {
                $total_pen_pay_aux += $purchase->total;
            } else {
                $total_paid_pen_pay += $purchase->payments->sum('payment');
            }
        }
        $total_pen_pay = $total_pen_pay -  $total_pen_pay_aux;

        $total_pending_paid_pen = $total_pen - $total_paid_pen;
        $total_pending_paid_pen_pay = $total_pen_pay - $total_paid_pen_pay;

        $totals = $total_pending_paid_pen + $total_pending_paid_pen_pay;

        return [
            'total_pen' => number_format($total_pen, 2, ".", ""),
            'total_paid_pen' => number_format($total_paid_pen, 2, ".", ""),
            'total_pending_paid_pen' => number_format($total_pending_paid_pen, 2, ".", ""),
            'totals' => number_format($totals, 2, ".", "")
        ];
    }

    public function updateInfo(Request $request)
    {
        $customer_id = $request->customer_id;
        $customer_address_id = $request->address_id;
        $address = $request->address;
        $type = $request->type;
        $trade_name = $request->trade_name;

        if ($type == 'address') {
            $customer_address = PersonAddress::find($customer_address_id);
            if ($customer_address) {
                $customer_address->address = $request->address;
                $customer_address->save();
            } else {
                $person = Person::find($customer_id);
                $person->address = $address;
                $person->save();
            }
        } else if ($type == 'trade_name') {
            $person = Person::find($customer_id);
            $person->trade_name = $trade_name;
            $person->save();
        }

        return [
            'success' => true,
            'message' => 'Información actualizada con éxito',
        ];
    }
    public function ubigeo(Request $request)
    {
        $id = $request->id;
        $district = District::where('id', $id)->first();
        if ($district) {
            return [
                'success' => false,
                'message' => 'El distrito ya existe',

            ];
        }
        $district = new District();
        $district->id = $id;
        $district->description = $request->description;
        $district->province_id = $request->province_id;
        $district->save();
        $locations = func_get_locations();
        return [
            'success' => true,
            'message' => 'Distrito registrado con éxito',
            'data' => $district,
            'locations' => $locations
        ];
    }
    public function savePhoto(&$user, $request)
    {
        $temp_path = $request->photo_temp_path;

        if ($temp_path) {
            $old_filename = $request->photo_filename;
            $user->photo_filename = UploadFileHelper::uploadImageFromTempFile('users', $old_filename, $temp_path, $user->id, true);
            $user->save();
        }
    }
    public function drivers()
    {
        $is_comercial  = auth()->user()->integrate_user_type_id == 2;
        $type = 'customers';
        $is_integrate_system = BusinessTurn::isIntegrateSystem();

        $api_service_token = \App\Models\Tenant\Configuration::getApiServiceToken();
        $driver = true;
        $suscriptionames = SuscriptionNames::create_new();
        return view('tenant.persons.index', compact(
            'is_comercial',
            'type',
            'driver',
            'api_service_token',
            'suscriptionames',
            'is_integrate_system'
        ));
    }
    public function index($type)
    {
        $is_comercial  = auth()->user()->integrate_user_type_id == 2;

        // $configuration = Configuration::first();
        // $api_service_token = $configuration->token_apiruc =! '' ? $configuration->token_apiruc : config('configuration.api_service_token');
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $api_service_token = \App\Models\Tenant\Configuration::getApiServiceToken();
        $driver = false;
        $suscriptionames = SuscriptionNames::create_new();
        return view('tenant.persons.index', compact(
            'type',
            'is_comercial',
            'is_integrate_system',
            'driver',
            'api_service_token',
            'suscriptionames'
        ));
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'internal_code' => 'Código interno',
            'barcode' => 'Código de barras',
            'number' => 'Número',
            'document_type' => 'Tipo de documento',
            'person_type_id' => 'Tipo de cliente',
            'zone_id' => 'Zona',
            'department_id' => 'Departamento',
            'province_id' => 'Provincia',
            'district' => 'Distrito',
            // 'observation' => 'Observaci',
        ];
    }
    function get_ids($column, $value)
    {
        $ids = [];
        switch ($column) {
            case 'zone_id':
                $ids = Zone::where('name', 'like', '%' . $value . '%')->pluck('id')->toArray();
                break;
            case 'department_id':
                $ids = Department::where('description', 'like', '%' . $value . '%')->pluck('id')->toArray();
                break;
            case 'province_id':
                $ids = Province::where('description', 'like',  '%' . $value . '%')->pluck('id')->toArray();
                break;
            case 'district_id':
                $ids = District::where('description', 'like', '%' . $value . '%')->pluck('id')->toArray();
                break;
        }

        return $ids;
    }
    public function records($type, Request $request)
    {
        $column = $request->column;
        $value = $request->value;
        $order = $request->order ?? 'asc';
        $driver = filter_var($request->driver ?? "false", FILTER_VALIDATE_BOOLEAN);
        $records = Person::where('type', $type);
        if ($column && $value) {
            if ($column == 'zone_id' || $column == 'department_id' || $column == 'province_id' || $column == 'district_id') {
                $ids = $this->get_ids($column, $value);
                $records = $records->whereIn($column, $ids);
            } else {
                $records = $records->where($column, 'like', "%{$value}%");
            }
        }
        if ($driver) {
            $records = $records->where('is_driver', true);
        } else {
            $records = $records->where('is_driver', false);
        }


        $records = $records->whereFilterCustomerBySeller($type);
        if ($column == 'name' || $column == 'internal_code' && $order) {
            $records = $records->orderBy($column, $order);
        } else {

            $records = $records->orderBy('name');
        }
        return new PersonCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function create()
    {
        return view('tenant.customers.form');
    }

    public function getLastDocument()
    {
        $last_no_document = Person::where('identity_document_type_id', '0')
            ->whereRaw('number REGEXP "^[0-9]{7}$"')
            ->max('number');
        if ($last_no_document == null) {
            $last_no_document = "0000001";
        } else {
            $last_no_document = $last_no_document + 1;
            $last_no_document = str_pad($last_no_document, 7, "0", STR_PAD_LEFT);
        }
        return [
            'success' => true,
            'data' => $last_no_document

        ];
    }
    public function tables()
    {
        $departments = Department::whereActive()->orderByDescription()->get();
        $provinces = Province::whereActive()->orderByDescription()->get();
        $districts = District::whereActive()->orderByDescription()->get();
        $person_regs = PersonRegModel::where('active', true)->orderBy('description')->get();

        $countries = Country::whereActive()->orderByDescription()->get();
        $identity_document_types = IdentityDocumentType::whereActive()->get();
        $person_types = PersonType::get();
        $locations = func_get_locations();
        $zones = Zone::all();
        $sellers = $this->getSellers();
        $api_service_token = \App\Models\Tenant\Configuration::getApiServiceToken();

        return compact(
            'person_regs',
            'departments',
            'provinces',
            'districts',
            'countries',
            'identity_document_types',
            'locations',
            'person_types',
            'api_service_token',
            'zones',
            'sellers'
        );
    }

    public function record($id)
    {
        $record = new PersonResource(Person::findOrFail($id));

        return $record;
    }

    public function store(PersonRequest $request)
    {
        if (!$request->barcode) {
            if ($request->internal_id) {
                $request->merge(['barcode' => $request->internal_id]);
            }
        }

        if ($request->state) {
            if ($request->state != "ACTIVO") {
                return [
                    'success' => false,
                    'message' => 'El estado del contribuyente no es activo, no puede registrarlo',
                ];
            }
        }

        $id = $request->input('id');
        $person = Person::firstOrNew(['id' => $id]);
        $data = $request->all();
        unset($data['optional_email'], $data['id']);
        $person->fill($data);

        $location_id = $request->input('location_id');
        if (is_array($location_id) && count($location_id) === 3) {
            $person->district_id = $location_id[2];
            $person->province_id = $location_id[1];
            $person->department_id = $location_id[0];
        }
        $person->person_aval()->delete();
        $line_credit = $request->input('line_credit');
        if ($line_credit) {
            $person->line_credit = str_replace(',', '', $line_credit);
        }
        $person->save();
        $telephones = $request->input('telephones')  ?? [];
        $person->telephones()->delete();
        foreach ($telephones as $row) {
            $person->telephones()->create([
                'telephone' => $row,
                'person_id' => $person->id,
            ]);
        }
        $this->savePhoto($person, $request);
        $person->addresses()->delete();
        $this->saveAval($person, $request);
        $dispatch_addresses_ids = $person->dispatch_addresses()->pluck('id')->toArray();
        Dispatch::whereIn('receiver_address_id', $dispatch_addresses_ids)
            ->update(['receiver_address_id' => null]);
        Dispatch::whereIn('sender_address_id', $dispatch_addresses_ids)
            ->update(['sender_address_id' => null]);

        $person->dispatch_addresses()->delete();
        $addresses = $request->input('addresses');
        foreach ($addresses as $row) {
            $person->addresses()->updateOrCreate(['id' => $row['id']], $row);
        }
        $dispatch_addresses = $request->input('dispatch_addresses')  ?? [];
        foreach ($dispatch_addresses as $row) {
            $person->dispatch_addresses()->updateOrCreate(['id' => $row['id']], $row);
        }

        $optional_email = $request->optional_email;
        if (!empty($optional_email)) {
            $person->setOptionalEmailArray($optional_email)->push();
        }

        $msg = '';
        if ($request->type === 'suppliers') {
            $msg = ($id) ? 'Proveedor editado con éxito' : 'Proveedor registrado con éxito';
        } else {
            $msg = ($id) ? 'Cliente editado con éxito' : 'Cliente registrado con éxito';
        }
        CacheTrait::clearCache('customers_documents');
        return [
            'success' => true,
            'message' => $msg,
            'id' => $person->id
        ];
    }

    public function saveAval($person, $request)
    {
        $name_aval = $request->name_aval;
        $trade_name_aval = $request->trade_name_aval;
        $identity_document_type_id_aval = $request->identity_document_type_id_aval;
        $address_aval = $request->address_aval;
        $telephone_aval = $request->telephone_aval;
        $location_id_aval = $request->location_id_aval;
        $country_id_aval = $request->country_id_aval;
        $number_aval = $request->number_aval;

        if ($name_aval) {
            $person->person_aval()->create([
                'name' => $name_aval,
                'trade_name' => $trade_name_aval,
                'identity_document_type_id' => $identity_document_type_id_aval,
                'address' => $address_aval,
                'telephone' => $telephone_aval,
                'location_id' => $location_id_aval,
                'country_id' => $country_id_aval,
                'number' => $number_aval,
            ]);
        }
    }
    public function destroy($id)
    {
        try {

            $person = Person::findOrFail($id);
            $person_type = ($person->type == 'customers') ? 'Cliente' : 'Proveedor';
            $person->delete();
            CacheTrait::clearCache('customers_documents');
            return [
                'success' => true,
                'message' => $person_type . ' eliminado con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => "El {$person_type} esta siendo usado por otros registros, no puede eliminar"] : ['success' => false, 'message' => "Error inesperado, no se pudo eliminar el {$person_type}"];
        }
    }

    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new PersonsImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                CacheTrait::clearCache('customers_documents');
                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }

    public function getLocationCascade()
    {
        $locations = [];
        $departments = Department::where('active', true)->get();
        foreach ($departments as $department) {
            $children_provinces = [];
            foreach ($department->provinces as $province) {
                $children_districts = [];
                foreach ($province->districts as $district) {
                    $children_districts[] = [
                        'value' => $district->id,
                        'label' => $district->id . " - " . $district->description
                    ];
                }
                $children_provinces[] = [
                    'value' => $province->id,
                    'label' => $province->description,
                    'children' => $children_districts
                ];
            }
            $locations[] = [
                'value' => $department->id,
                'label' => $department->description,
                'children' => $children_provinces
            ];
        }

        return $locations;
    }


    public function enabled($type, $id)
    {

        $person = Person::findOrFail($id);
        $person->enabled = $type;
        $person->save();

        $type_message = ($type) ? 'habilitado' : 'inhabilitado';
        CacheTrait::clearCache('customers_documents');

        return [
            'success' => true,
            'message' => "Cliente {$type_message} con éxito"
        ];
    }
    public function export_migration($type, Request $request)
    {

        $d_start = null;
        $d_end = null;
        $period = $request->period;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
        }

        if ($period == 'all') {
            $records = Person::where('type', $type)->get();
        } elseif ($period == 'seller') {
            $records = Person::where(['type' => $type, 'seller_id' => $request->seller_id,])->get();
        } else {
            $records = Person::where('type', $type)->whereBetween('created_at', [$d_start, $d_end])->get();
        }

        $filename = ($type == 'customers') ? 'Reporte_Clientes_' : 'Reporte_Proveedores_';

        return (new ClientMigrationExport)
            ->records($records)
            ->type($type)
            ->download($filename . Carbon::now() . '.xlsx');
    }
    public function export($type, Request $request)
    {

        $d_start = null;
        $d_end = null;
        $period = $request->period;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
        }

        if ($period == 'all') {
            $records = Person::where('type', $type)->get();
        } elseif ($period == 'seller') {
            $records = Person::where(['type' => $type, 'seller_id' => $request->seller_id,])->get();
        } else {
            $records = Person::where('type', $type)->whereBetween('created_at', [$d_start, $d_end])->get();
        }

        $filename = ($type == 'customers') ? 'Reporte_Clientes_' : 'Reporte_Proveedores_';

        return (new ClientExport)
            ->records($records)
            ->type($type)
            ->download($filename . Carbon::now() . '.xlsx');
    }

    public function clientsForGenerateCPEById($id)
    {

        $persons = Person::without(['identity_document_type', 'country', 'department', 'province', 'district'])
            ->select('id', 'name', 'identity_document_type_id', 'number')
            ->where('id', $id)
            ->first();
        $sellers = $this->getSellers();
        $person_aval = $persons->person_aval;
        if ($person_aval) {
            $persons->aval_name = $person_aval->name;
            $persons->aval_number = $person_aval->number;
        }

        return response()->json([
            'success' => true,
            'data' => $persons,
            'sellers' => $sellers,
        ], 200);
    }
    public function suppliersForGenerateCPE()
    {
        $typeFile = request('type');
        $filter = request('name');
        $persons = Person::without(['identity_document_type', 'country', 'department', 'province', 'district'])
            ->select('id', 'name', 'identity_document_type_id', 'number')
            ->where('type', 'suppliers')
            ->orderBy('name');
        if ($filter && $typeFile) {
            if ($typeFile === 'document') {
                $persons = $persons->where('number', 'like', "{$filter}%");
            }
            if ($typeFile === 'name') {
                $persons = $persons->where('name', 'like', "%{$filter}%");
            }
        }
        $persons = $persons->take(10)
            ->get();
        return response()->json([
            'success' => true,
            'data' => $persons,
        ], 200);
    }
    public function clientsForGenerateCPE()
    {
        $typeFile = request('type');
        $filter = request('name');
        $persons = Person::without(['identity_document_type', 'country', 'department', 'province', 'district'])
            ->select('id', 'name', 'identity_document_type_id', 'number')
            ->where('type', 'customers')
            ->orderBy('name');
        if ($filter && $typeFile) {
            if ($typeFile === 'document') {
                $persons = $persons->where('number', 'like', "{$filter}%");
            }
            if ($typeFile === 'name') {
                $persons = $persons->where('name', 'like', "%{$filter}%");
            }
        }
        $persons = $persons->take(10)
            ->get()->transform(function ($person) {
                return [
                    'id' => $person->id,
                    'name' => $person->name,
                    'number' => $person->number,
                    'trade_name' => $person->trade_name,
                    'aval_name' => optional($person->person_aval)->name,
                    'aval_number' => optional($person->person_aval)->number,
                ];
            });
        return response()->json([
            'success' => true,
            'data' => $persons,
        ], 200);
    }

    public function printBarCode(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->id;

        $record = Person::find($id);


        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                104.1,
                24
            ],
            'margin_top' => 2,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);
        $html = view('tenant.persons.exports.persons-barcode-id', compact('record'))->render();

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_clientes_' . now()->format('Y_m_d') . '.pdf', 'I');
    }

    public function generateBarcode($id)
    {

        $person = Person::findOrFail($id);

        $colour = [150, 150, 150];

        $generator = new BarcodeGeneratorPNG();

        $temp = tempnam(sys_get_temp_dir(), 'person_barcode');

        file_put_contents($temp, $generator->getBarcode($person->barcode, $generator::TYPE_CODE_128, 5, 70, $colour));

        $headers = [
            'Content-Type' => 'application/png',
        ];

        return response()->download($temp, "{$person->barcode}.png", $headers);
    }

    public function getPersonByBarcode($request)
    {
        $value = $request;

        $customers = Person::with('addresses')->whereType('customers')
            ->where('id', $value)->get()->transform(function ($row) {
                /** @var  Person $row */
                return $row->getCollectionData();
                /* Movido al modelo */
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'addresses' => $row->addresses,
                    'address' => $row->address
                ];
            });

        return compact('customers');
    }


    /**
     *
     * Obtener puntos acumulados por cliente
     *
     * @param int $id
     * @return float
     */
    public function getAccumulatedPoints($id)
    {
        $accumulated_points = Person::getOnlyAccumulatedPoints($id);
        if ($accumulated_points < 0) {
            Person::where('id', $id)->update(['accumulated_points' => 0]);
            $accumulated_points = 0;
        }
        return $accumulated_points;
    }
}
