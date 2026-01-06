<?php
namespace App\Http\Controllers\Tenant;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Imports\PersonsImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Tutorial;
use App\Models\Tenant\PersonType;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\Country;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Catalogs\Department;
use App\Http\Requests\Tenant\TutorialRequest;
use Modules\Finance\Helpers\UploadFileHelper;
use App\Http\Requests\Tenant\PersonTypeRequest;
use App\Http\Resources\Tenant\TutorialResource;
use App\Http\Resources\Tenant\PersonTypeResource;
use App\Http\Resources\Tenant\TutorialCollection;
use App\Http\Resources\Tenant\PersonTypeCollection;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Traits\CacheTrait;

class TutorialsController extends Controller
{

    use CacheTrait;
    public function index()
    {
        return view('tenant.shortcuts.index');
    }

    public function columns()
    {
        return [
            'title' => 'Titulo',
        ];
    }

    public function records(Request $request)
    {

        $records = Tutorial::where($request->column, 'like', "%{$request->value}%")
                            ->latest();

        return new TutorialCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function create()
    {
        return view('tenant.customers.form');
    }
    public function subir_imagen(Request $request)
    {
       
        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg');
        if (!$validate_upload['success']) {
            return $validate_upload;
        }
        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];
            return $this->upload_image($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }
    function upload_image($request)
    {
        $file = $request['file'];
        $type = $request['type'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }

    public function uploads(Request $request){
        if ($request->hasFile('file')) {
         
            $company = Company::active();
            $type = $request->input('type');
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $name = $type.'_'.$company->number.'.'.$ext;
       
            if (($type === 'shortcuts')) {
                $v = request()->validate(['file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048']);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $path ='app/public/uploads/shortcuts';
                $request->file->move(storage_path($path), $name);
            }   
            return [
                'success' => true,
                'message' => __('app.actions.upload.success'),
                'name' => $name,
                'type' => $type
            ];
        }
    }
    public function record($id)
    {
        $record = new TutorialResource(Tutorial::findOrFail($id));
        return $record;
    }

    public function store(TutorialRequest $request)
    {
        CacheTrait::clearCache('tutorials_shortcuts_right');
        CacheTrait::clearCache('tutorials_shortcuts_left');
        CacheTrait::clearCache('tutorials_videos');

        $id = $request->input('id');
        $shortcuts = Tutorial::firstOrNew(['id' => $id]);
        $shortcuts->fill($request->all());
        $temp_path = $request->input('temp_path');
        if ($temp_path) {
            $slug_name = Str::slug($request->title);
            $prefix_name = Str::limit($slug_name, 20, '');
            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'shortcuts' . DIRECTORY_SEPARATOR;
            $file_name_old = $request->input('filename');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path);
            $datenow = date('YmdHis');
            $file_name = $prefix_name . '-' . $datenow . '.' . $file_name_old_array[1];
            UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);
            Storage::put($directory . $file_name, $file_content);
            $shortcuts->image = $file_name;
        }
        $shortcuts->save();
  

        return [
            'success' => true,
            'message' => ($id) ? 'Editado con éxito':'Registrado con éxito',
        ];
    }

    public function destroy($id)
    {
        try {            
            
            $person_type = Tutorial::findOrFail($id);
            $person_type->delete(); 

            return [
                'success' => true,
                'message' => 'Eliminado con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => "Esta siendo usado por otros registros, no puede eliminar"] : ['success' => false,'message' => "Error inesperado, no se pudo eliminar el {$person_type_type}"];

        }
        
    }
  
}