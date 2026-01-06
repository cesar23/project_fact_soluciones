<?php

namespace App\Http\Controllers\Tenant;

use Exception;
use Illuminate\Http\Request;
use App\Models\Tenant\LabelColor;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\LabelColorCollection;

class LabelColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tenant.label_colors.index');
    }

    public function columns()
    {
        return [
            'description' => 'Descripción',
            'color' => 'Color'
        ];
    }
    
    /**
     * Get all labels for selection
     */
    public function options()
    {
        $labelColors = LabelColor::orderBy('description', 'asc')->get();
        
        return $labelColors->map(function($label) {
            return [
                'id' => $label->id,
                'description' => $label->description,
                'color' => $label->color,
            ];
        });
    }

    /**
     * Get records for datatable
     */
    public function records(Request $request)
    {
        $records = LabelColor::query();

        if ($request->has('column') && $request->filled('value')) {
            $records->where($request->column, 'like', "%{$request->value}%");
        }

        $records = $records->orderBy('description', 'asc')->paginate(config('tenant.items_per_page'));

        return new LabelColorCollection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'description' => 'required|string|max:255',
                'color' => 'required|string|max:7'
            ]);

            $id = $request->input('id');
            
            $labelColor = LabelColor::firstOrNew(['id' => $id]);
            $labelColor->fill($request->all());
            $labelColor->save();

            return [
                'success' => true,
                'message' => ($id) ? 'Color de etiqueta editado con éxito' : 'Color de etiqueta registrado con éxito',
                'id' => $labelColor->id
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $record = LabelColor::findOrFail($id);
        return $record;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $labelColor = LabelColor::findOrFail($id);
            $labelColor->delete();

            return [
                'success' => true,
                'message' => 'Color de etiqueta eliminado con éxito'
            ];
        } catch (Exception $e) {
            return ($e->getCode() == '23000') ? 
                ['success' => false, 'message' => 'El color de etiqueta está siendo usado por otros registros, no puede eliminar'] : 
                ['success' => false, 'message' => 'Error inesperado, no se pudo eliminar el color de etiqueta'];
        }
    }
}