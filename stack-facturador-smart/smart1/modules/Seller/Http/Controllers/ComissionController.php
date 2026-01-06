<?php

namespace Modules\Seller\Http\Controllers;

use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Seller\Http\Resources\ComissionCollection;
use Modules\Seller\Models\Comission;

class ComissionController extends Controller
{
    public function columns()
    {
        return [
            'percentage' => 'Porcentaje',
            'margin' => 'Margen',
        ];
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('seller::comission.index');
    }
    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return Response
     */
    public function record($id){
        $record = Comission::findOrFail($id);
        return $record;
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return new ComissionCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {
        $comissions = Comission::query();
        $column = $request->column;
        $value = $request->value;
        if ($column) {
            $comissions = $comissions->where($column, 'like', "%{$value}%");
        }

        return $comissions;
    }


    public function store(Request $request)
    {
        $id = $request->input('id');
        $comission = Comission::firstOrNew(['id' => $id]);
        $comission->fill($request->all());
        $comission->save();
        return [
            'success' => true,
            'message' => ($id) ? 'Comisión actualizada' : 'Comisión registrada'
        ];
    }


    public function destroy($id)
    {
        //
    }
}
