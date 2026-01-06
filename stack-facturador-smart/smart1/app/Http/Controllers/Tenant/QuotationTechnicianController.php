<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\QuotationTechnicianCollection;
use App\Models\Tenant\QuotationsTechnicians;
use Illuminate\Http\Request;
use Exception;

class QuotationTechnicianController extends Controller
{
    public function index()
    {
        return view('tenant.quotations_technicians.index');
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'number' => 'Número',
            'email' => 'Email',
            'phone' => 'Teléfono',
            'image' => 'Imagen'
        ];
    }

    public function records(Request $request)
    {
        $records = QuotationsTechnicians::latest();

        return new QuotationTechnicianCollection($records->paginate(20));
    }

    public function create()
    {
        return view('tenant.quotations_technicians.create');
    }

    public function allRecords()
    {
        $records = QuotationsTechnicians::all();
        return new QuotationTechnicianCollection($records);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|string|max:255'
        ]);

        try {
            $technician = QuotationsTechnicians::create($request->all());
            return response()->json($technician, 201);
        } catch (Exception $e) {

            return response()->json(['error' => 'Error al crear el técnico'], 500);
        }
    }

    public function edit($id)
    {
        $technician = QuotationsTechnicians::findOrFail($id);
        return view('tenant.quotations_technicians.edit', compact('technician'));
    }

    public function show($id)
    {
        $technician = QuotationsTechnicians::findOrFail($id);
        return response()->json($technician);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|string|max:255'
        ]);

        try {
            $technician = QuotationsTechnicians::findOrFail($id);
            $technician->update($request->all());
            return response()->json($technician);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al actualizar el técnico'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $technician = QuotationsTechnicians::findOrFail($id);
            $technician->delete();
            return response()->json(['message' => 'Técnico eliminado correctamente']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al eliminar el técnico'], 500);
        }
    }

    public function tables()
    {
        return response()->json([]);
    }

    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                $uploadPath = public_path('storage/uploads/quotations_technicians');
                
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $file->move($uploadPath, $filename);

                return response()->json([
                    'success' => true,
                    'filename' => $filename,
                    'url' => asset('storage/uploads/quotations_technicians/' . $filename)
                ]);
            }

            return response()->json(['error' => 'No se encontró el archivo'], 400);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al subir la imagen'], 500);
        }
    }
}
