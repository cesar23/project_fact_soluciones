<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\PurchaseLicenseCollection;
use App\Http\Resources\Tenant\PurchaseResponsibleCollection;
use App\Models\Tenant\PurchaseLicense;
use App\Models\Tenant\PurchaseResponsible;
use Illuminate\Http\Request;

class PurchaseResponsibleLicenseController extends Controller
{
    public function license_records(Request $request)
    {
        $records = $this->license_data($request);
        return new PurchaseLicenseCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function license_record($id)
    {
        $row = PurchaseLicense::find($id);
        return [
            'id' => $row->id,
            'license' => $row->license,
        ];
    }
    public function responsible_record($id)
    {
        $row = PurchaseResponsible::find($id);
        if(!$row){
            return response()->json(['error' => 'No se encontró el responsable'], 404);
        }
        return [
            'id' => $row->id,
            'name' => $row->name,
            'number' => $row->number,
        ];
    }
    public function responsible_records(Request $request)
    {
        $records = $this->responsible_data($request);
        return new PurchaseResponsibleCollection($records->paginate(config('tenant.items_per_page')));
    }
    function responsible_data($request)
    {
        $query = PurchaseResponsible::query();
        $value = $request->input('value');
        if ($value) {
            $query->where('name', 'like', "%{$value}%")
                ->orWhere('number', 'like', "%{$value}%");
        }
        return $query;
    }
    function license_data($request)
    {
        $query = PurchaseLicense::query();
        $column = $request->input('column');
        $value = $request->input('value');
        if ($column && $value) {
            $query->where($column, 'like', "%{$value}%");
        }
        return $query;
    }
    public function store_license(Request $request)
    {   
        $request->validate([
            'license' => 'required|unique:tenant.purchase_licenses,license,'.$request->input('id'),
        ]);
        $id = $request->input('id');
        $license = PurchaseLicense::firstOrNew(['id' => $id]);
        $license->license = $request->input('license');
        $license->save();

        return [
            'id' => $license->id,
            'success' => true,
            'message' => $id ? 'Licencia editada con éxito' : 'Licencia registrada con éxito'
        ];
    }
    public function store_responsible(Request $request)
    {
        $request->validate([
            'number' => 'required|unique:tenant.purchase_responsibles,number,'.$request->input('id'),
        ]);

        $id = $request->input('id');
        $responsible = PurchaseResponsible::firstOrNew(['id' => $id]);
        $responsible->identity_document_type_id = $request->input('identity_document_type_id');
        
        $responsible->number = $request->input('number');
        $responsible->name = $request->input('name');
        $responsible->telephone = $request->input('telephone');
        $responsible->country_id = "PE";

        $responsible->save();

        return [
            'id' => $responsible->id,
            'success' => true,
            'message' => $id ? 'Responsable editado con éxito' : 'Responsable registrado con éxito'
        ];
    }
}
