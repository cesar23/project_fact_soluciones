<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\InventoryReferenceCollection;
use App\Models\Tenant\InventoryReference;
use Exception;
use Illuminate\Http\Request;

class InventoryReferenceController extends Controller
{
    public function records(Request $request)
    {
        $records = InventoryReference::query();
        $column = $request->input('column');
        $value = $request->input('value');
        if($column != null && $value != null){
            $records->where($column, 'like', "%{$value}%");
        }

        return new InventoryReferenceCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function record($id)
    {
        $record = InventoryReference::findOrFail($id);

        return $record;
    }
    public function columns()
    {
        return [
            'code' => 'Código',
            'description' => 'Descripción'
        ];
    }
    public function index()
    {
        return view('tenant.inventory_references.index');
    }

    public function store(Request $request)
    {
        $id = $request->input('id');
        $code = $request->input('code');
        if($id == null){
            $record = InventoryReference::where('code', $code)->first();
            if($record != null){
                return [
                    'success' => false,
                    'message' => 'El código ya existe'
                ];
            }
        }
        $inventory_reference = InventoryReference::firstOrNew(['id' => $id]);
        $inventory_reference->fill($request->all());
        $inventory_reference->save();

        return [
            'success' => true,
            'message' => ($id)?'Referencia editada con éxito':'Referencia registrada con éxito',
            'id' => $inventory_reference->id
        ];

    
    }

    

    public function destroy($id)
    {
        try {
            
            $inventory_reference = InventoryReference::findOrFail($id);
            $inventory_reference->delete(); 

            return [
                'success' => true,
                'message' => 'Referencia eliminada con éxito'
            ];
          

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'La referencia esta siendo usada por otros registros, no puede eliminar'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar la referencia'];

        } 
    }
}