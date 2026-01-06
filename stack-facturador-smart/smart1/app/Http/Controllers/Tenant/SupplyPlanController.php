<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\SupplyPlanCollection;
use App\Http\Resources\Tenant\SupplyPlanResource;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\SupplyPlan;
use Illuminate\Http\Request;

class SupplyPlanController extends Controller
{
    public function index()
    {
        return view('tenant.supplies.plans.index');
    }

    public function columns()
    {
        return [
            'description' => 'Descripción',
            'type_zone' => 'Tipo Zona',
            'type_plan' => 'Tipo Plan',
            'price_c_m' => 'Precio C/M',
            'price_s_m' => 'Precio S/M',
            'price_alc' => 'Precio Alc',
            'total' => 'Total',
            'active' => 'Estado'
        ];
    }

    public function tables()
    {
        $configuration = Configuration::first();
        $affectation_igv_type_id = $configuration->affectation_igv_type_id;
        $affectation_types = AffectationIgvType::all();
        return response()->json([
            'affectation_types' => $affectation_types,
            'affectation_igv_type_id' => $affectation_igv_type_id
        ]);
    }
    public function allRecords(Request $request)
    {
        $records = SupplyPlan::orderBy('id', 'desc');

        return new SupplyPlanCollection($records->get());
    }

    public function records(Request $request)
    {
        $records = SupplyPlan::orderBy('id', 'desc');

        return new SupplyPlanCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'type_zone' => 'required|in:URBANO,RURAL',
            'type_plan' => 'required|in:DOMICILIARIO,COMERCIAL',
            'price_c_m' => 'required|numeric|min:0',
            'price_s_m' => 'required|numeric|min:0',
            'price_alc' => 'required|numeric|min:0',
            'observation' => 'nullable|string',
            'active' => 'required|boolean',
            'affectation_type_id' => 'nullable|exists:tenant.cat_affectation_igv_types,id'
        ]);

        // Calculate total as sum of all prices
        $total = $request->price_c_m + $request->price_s_m + $request->price_alc;
        $request->merge(['total' => $total]);

        $plan = SupplyPlan::create($request->all());

        return response()->json($plan, 201);
    }

    public function show($id)
    {
        $plan = SupplyPlan::findOrFail($id);

        return new SupplyPlanResource($plan);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'type_zone' => 'required|in:URBANO,RURAL',
            'type_plan' => 'required|in:DOMICILIARIO,COMERCIAL',
            'price_c_m' => 'required|numeric|min:0',
            'price_s_m' => 'required|numeric|min:0',
            'price_alc' => 'required|numeric|min:0',
            'observation' => 'nullable|string',
            'active' => 'required|boolean',
            'affectation_type_id' => 'nullable|exists:tenant.cat_affectation_igv_types,id'
        ]);

        // Calculate total as sum of all prices
        $total = $request->price_c_m + $request->price_s_m + $request->price_alc;
        $request->merge(['total' => $total]);

        $plan = SupplyPlan::findOrFail($id);
        $plan->update($request->all());

        return response()->json($plan);
    }

    public function destroy($id)
    {
        $plan = SupplyPlan::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Tarifa de predio eliminado correctamente']);
    }
}