<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ChargeDiscountRequest;
use App\Http\Resources\Tenant\ChargeDiscountCollection;
use App\Http\Resources\Tenant\ChargeDiscountResource;
use App\Models\Tenant\Catalogs\ChargeDiscountType;


class ChargeDiscountController extends Controller
{
    public function index($type)    
    {   
        $type = $type ?? 'charge';
        return view('tenant.charge_discounts.index', compact('type'));
    }

    public function records($type)
    {
        $records = ChargeDiscountType::where('type', $type)->get();

        return new ChargeDiscountCollection($records);
    }

    public function create()
    {
        return view('tenant.charge_discounts.form');
    }

    public function tables($type)
    {
        $charge_discount_types = ChargeDiscountType::where('type', $type)->get();

        return compact('charge_discount_types');
    }

    public function record($id)
    {
        $record = new ChargeDiscountResource(ChargeDiscountType::findOrFail($id));

        return $record;
    }

    public function store(ChargeDiscountRequest $request)
    {
        $id = $request->input('id');
        $discount = ChargeDiscountType::firstOrNew(['id' => $id]);
        $discount->fill($request->all());
        $discount->save();

        return [
            'success' => true,
            'message' => ($id)?'Descuento editado con éxito':'Descuento registrado con éxito'
        ];
    }

    public function destroy($id)
    {
        $discount = ChargeDiscountType::findOrFail($id);
        $discount->delete();

        return [
            'success' => true,
            'message' => 'Descuento eliminado con éxito'
        ];
    }
}