<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\PlateNumberCollection;
use App\Models\Tenant\PlateNumber;
use App\Models\Tenant\PlateNumberKm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlateNumberController extends Controller
{
    public function index()
    {

        return view('tenant.plate_numbers.index');
    }
    public function updateKilometers(Request $request, $id)
    {
        $plate_number_km = PlateNumberKm::create([
            'plate_number_id' => $id,
            'description' => $request->initial_km
        ]);

        return response()->json(['success' => true]);
    }
    public function columns()
    {
        return [
            'description' => 'Placa',
            'brand' => 'Marca',
            'model' => 'Modelo',
            'color' => 'Color',
            'type' => 'Tipo',
            'year' => 'Año',
            'initial_km' => 'Kilometraje'
        ];
    }
    public function records(Request $request)
    {
        $query = PlateNumber::query();
        $column = $request->column;
        $value = $request->value;
        if ($column == 'description' && $value) {
            $query->where('description', 'like', "%{$value}%");
        }
        if ($column == 'brand' && $value) {
            $query->whereHas('brand', function ($query) use ($value) {
                $query->where('description', 'like', "%{$value}%");
            });
        }
        if ($column == 'model' && $value) {
            $query->whereHas('model', function ($query) use ($value) {
                $query->where('description', 'like', "%{$value}%");
            });
        }
        if ($column == 'color' && $value) {
            $query->whereHas('color', function ($query) use ($value) {
                $query->where('description', 'like', "%{$value}%");
            });
        }
        if ($column == 'type' && $value) {
            $query->whereHas('type', function ($query) use ($value) {
                $query->where('description', 'like', "%{$value}%");
            });
        }

        if ($column == 'year' && $value) {
            $query->where('year', 'like', "%{$value}%");
        }

        if ($column == 'initial_km' && $value) {
            $query->where('initial_km', 'like', "%{$value}%");
        }

        return new PlateNumberCollection($query->paginate(20));
    }

    public function getById($id)
    {
        $plateNumber = PlateNumber::where('id', $id);;
        return new PlateNumberCollection($plateNumber->paginate(20));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|unique:tenant.plate_numbers',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plateNumber = PlateNumber::create($request->all());

        // Crear el registro de kilometraje inicial
        if ($request->has('initial_km')) {
            $plateNumber->kms()->create([
                'description' => $request->initial_km
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $plateNumber->load(['brand', 'model', 'color', 'type', 'kms'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $plateNumber = PlateNumber::findOrFail($id);
        $plateNumber->update($request->all());
        return response()->json(['data' => $plateNumber], 200);
    }

    public function destroy($id)
    {
        $plateNumber = PlateNumber::findOrFail($id);
        PlateNumberKm::where('plate_number_id', $id)->delete();
        $plateNumber->delete();
        return response()->json([
            'success' => true,
            'message' => 'Placa eliminada correctamente'
        ], 204);
    }

    public function show($id)
    {
        $plateNumber = PlateNumber::with(['brand', 'model', 'color', 'type'])->findOrFail($id);
        $kms = $plateNumber->kms()->latest()->first();
        $description_kms = $kms->description;
        $last_document = $plateNumber->documents()->latest()->first();
        if($last_document && $last_document->km){
            $plateNumber->kms = [
                ['description' => $last_document->km]
            ];
        }else{
            $plateNumber->kms = [
                ['description' => $description_kms]
            ];
        }



        return response()->json([
            'success' => true,
            'data' => $plateNumber
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('input');
        $limit = $request->input('limit', 10);

        $plateNumbers = PlateNumber::query()
            ->select('id', 'description')  // Solo seleccionamos id y description
            ->where('description', 'like', "%{$query}%")
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plateNumbers
        ]);
    }
}
