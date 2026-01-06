<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Establishment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\EstablishmentRequest;
use App\Http\Resources\Tenant\EstablishmentResource;
use App\Http\Resources\Tenant\EstablishmentCollection;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use Modules\Finance\Helpers\UploadFileHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\CacheTrait;

class EstablishmentController extends Controller
{
    use CacheTrait;
    public function active(Request $request){
        $user = auth()->user();
        $establishment = Establishment::findOrFail($request->input('id'));
        if($user->establishment_id == $establishment->id){
            return [
                'success' => false,
                'message' => 'No se puede desactivar el establecimiento actual'
            ];
        }
        $establishment->active = !$request->input('active');
        $establishment->save();
        self::clearCache();
        $users = User::where('establishment_id', $establishment->id)->get();
        if(!$establishment->active){
            foreach ($users as $user) {
                if($user->type == 'superadmin' || $user->id == 1){
                    continue;
                }
                $user->is_locked = true;
                $user->save();
            }
        }
        return [
            'success' => true,
            'message' => 'Establecimiento actualizado con éxito'
        ];
    }
    public function index()
    {
        return view('tenant.establishments.index');
    }

    public function create()
    {
        return view('tenant.establishments.form');
    }

    public function removeImage($type, Request $request)
    {
        $id = $request->input('id');
        $establishment = Establishment::findOrFail($id);
        $type = $type . '_logo';
        if ($establishment->$type) {
            $path = $establishment->$type;
            $public_path = public_path($path);
            self::clearCache();
            if (file_exists($public_path) && unlink($public_path)) {
                $establishment->$type = null;
                $establishment->save();
                return [
                    'success' => true,
                    'message' => 'Imagen eliminada con éxito'
                ];
            } else {
                $establishment->$type = null;
                $establishment->save();
                return [
                    'success' => true,
                    'message' => 'No se encontró la imagen, se eliminó el registro'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'No se encontró la imagen'
            ];
        }
    }
    public function tables()
    {
        $countries = Country::whereActive()->orderByDescription()->get();
        $departments = Department::whereActive()->orderByDescription()->get();
        $provinces = Province::whereActive()->orderByDescription()->get();
        $districts = District::whereActive()->orderByDescription()->get();

        $customers = Person::whereType('customers')->orderBy('name')->take(1)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });

        return compact('countries', 'departments', 'provinces', 'districts', 'customers');
    }

    public function record($id)
    {
        $record = new EstablishmentResource(Establishment::findOrFail($id));

        return $record;
    }
    public static function clearCache(){
        CacheTrait::clearCache('vc_establishments');
        CacheTrait::clearCache('vc_establishment');
    }

    /**
     *
     * @param  EstablishmentRequest $request
     * @return array
     */
    public function store(EstablishmentRequest $request)
    {
        try {
            self::clearCache();
            $id = $request->input('id');
            $has_igv_31556 = ($request->input('has_igv_31556') === 'true');
            $establishment = Establishment::firstOrNew(['id' => $id]);
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $request->validate(['file' => 'mimes:jpeg,png,jpg|max:1024']);
                $file = $request->file('file');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '.' . $ext;

                UploadFileHelper::checkIfValidFile($filename, $file->getPathName(), true);

                $file->storeAs('public/uploads/logos', $filename);
                $path = 'storage/uploads/logos/' . $filename;
                $request->merge(['logo' => $path]);
            }
            if ($request->hasFile('file_yape') && $request->file('file_yape')->isValid()) {

                $request->validate(['file_yape' => 'mimes:jpeg,png,jpg|max:1024']);
                $file = $request->file('file_yape');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '.' . $ext;

                UploadFileHelper::checkIfValidFile($filename, $file->getPathName(), true);

                $file->storeAs('public/uploads/logos', $filename);
                $path = 'storage/uploads/logos/' . $filename;
                $request->merge(['yape_logo' => $path]);
            }
            if ($request->hasFile('file_plin') && $request->file('file_plin')->isValid()) {
                $request->validate(['file_plin' => 'mimes:jpeg,png,jpg|max:1024']);
                $file = $request->file('file_plin');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '.' . $ext;

                UploadFileHelper::checkIfValidFile($filename, $file->getPathName(), true);

                $file->storeAs('public/uploads/logos', $filename);
                $path = 'storage/uploads/logos/' . $filename;
                $request->merge(['plin_logo' => $path]);
            }
            $establishment->fill($request->all());
            if($request->active == "true"){
                $establishment->active = 1;
            }
            if($request->active == "false"){    
                $establishment->active = 0;
            }

            $establishment->printer = $request->printer;
            $establishment->has_igv_31556 = $has_igv_31556;
            $establishment->save();
            self::clearCache();

            if (!$id) {
                $this->create_series($establishment->id);
                $warehouse = new Warehouse();
                $warehouse->establishment_id = $establishment->id;
                $warehouse->description = 'Almacén - ' . $establishment->description;
                $warehouse->save();
            }
            $this->clearCache('igv');
            return [
                'success' => true,
                'message' => ($id) ? 'Establecimiento actualizado' : 'Establecimiento registrado'
            ];
        } catch (Exception $e) {
            $this->generalWriteErrorLog($e);

            return $this->generalResponse(false, 'Error desconocido: ' . $e->getMessage());
        }
    }

    function create_series($id)
    {
        $document_types = [
            "FTR" => "01",
            "BLT" => "03",
            "NV0" => "80",
            "FTC" => "07",
            "BLC" => "07",
            "FTD" => "08",
            "BLD" => "08",
            "TR0" => "09",
            "VT0" => "31",
            "NIA" => "U2",
            "NSA" => "U3",
            "NTA" => "U4",
            "COT" => "COT",
            "PD0" => "PD",
        ];

        foreach ($document_types as $series => $document_type_id) {
            $serie = $this->format_serie($id, $series);
            $exists = Series::where('establishment_id', $id)->where('document_type_id', $document_type_id)
                ->where('number', $serie)->first();

            if (!$exists) {
                $series = new Series();
                $series->establishment_id = $id;
                $series->document_type_id = $document_type_id;
                $series->number = $serie;
                $series->save();
            }
        }
    }
    function format_serie($id, $serie)
    {
        //obtener el ultimo caracter de id
        //si id tiene 1 digito solo concatenarlo a $serie  y regresar
        return $serie . $id;
    }
    public function recordsAll(){
        $records = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                
            ];
        });
        return $records;
    }
    public function records()
    {
        $records = Establishment::get();

        return new EstablishmentCollection($records);
    }

    public function destroy($id)
    {
        $establishment = Establishment::findOrFail($id);
        $establishment->series()->delete();
        $establishment->delete();

        return [
            'success' => true,
            'message' => 'Establecimiento eliminado con éxito'
        ];
    }
}
