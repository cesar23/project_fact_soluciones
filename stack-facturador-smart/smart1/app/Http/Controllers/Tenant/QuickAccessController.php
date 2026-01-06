<?php

namespace App\Http\Controllers\Tenant;
use Exception;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use App\Models\Tenant\QuickAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\QuickAccessResource;
use App\Http\Resources\Tenant\ChickAccessColletion;
use App\Http\Resources\Tenant\QuickAccessColletion;

class QuickAccessController extends Controller
{
    public function columns()
    {
        return [
            'description' => 'Descripción',
        ];
    }
    public function index()
    {
        return view('tenant.quickaccess.index');
    }
    public function records(Request $request)
    {
        $records = QuickAccess::where($request->column,'like',"%{$request->value}%");
        //return $records->get();
        return new QuickAccessColletion($records->paginate(config('tenant.items_per_page')));
    }
    public function quickaccess(){
        $quickaccess=QuickAccess::all();
        return view('tenant.shortcuts_icons.shortcuts',compact('quickaccess'));
    }
    public function record($id)
    {
        $record = new QuickAccessResource(QuickAccess::findOrFail($id));
        return $record;
    }

    public function store(Request $request)
    {
        $id = $request->input('id');
        $quickaccess = QuickAccess::firstOrNew(['id' => $id]);
        $quickaccess->fill($request->all());
        $quickaccess->save();

        return [
            'success' => true,
            'message' => ($id)?'Se a editado con éxito':'Registrado con éxito'
        ];
    }

    public function destroy($id)
    {
        try {

            $bank = QuickAccess::findOrFail($id);
            $bank->delete();

            return [
                'success' => true,
                'message' => 'Banco eliminado con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'El acceso rapido esta siendo usado por otros registros, no puede eliminar'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar el banco'];

        }
    }
}
