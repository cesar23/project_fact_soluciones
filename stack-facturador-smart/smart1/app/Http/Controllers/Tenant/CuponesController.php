<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Coupon;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Carbon\Carbon;

class CuponesController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();
    
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%$search%")
                  ->orWhere('titulo', 'LIKE', "%$search%");
            });
        }
    
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        $coupons = $query->get();
    
        $generator = new BarcodeGeneratorPNG();
    
        foreach ($coupons as $coupon) {
            if (!empty($coupon->titulo)) {
                $barcode = $generator->getBarcode($coupon->titulo, $generator::TYPE_CODE_128);
                $coupon->barcode = 'data:image/png;base64,' . base64_encode($barcode);
            }
        }
    
        return view('cupones.index', compact('coupons'));
    }

    public function create()
{
    return view('cupones.create');
}


public function show($id)
{
    $coupon = Coupon::findOrFail($id);

    if ($coupon->fecha_caducidad) {
        $coupon->fecha_caducidad = Carbon::parse($coupon->fecha_caducidad);
    }

    $generator = new BarcodeGeneratorPNG();
    
    if (!empty($coupon->titulo)) {
        $barcode = $generator->getBarcode($coupon->titulo, $generator::TYPE_CODE_128);
        $coupon->barcode = 'data:image/png;base64,' . base64_encode($barcode);
    }

    return view('cupones.show', compact('coupon'));
}

public function store(Request $request)
{
    $request->validate([
        'imagen' => 'nullable|image|max:2048',
        'nombre' => 'nullable|string|max:255',
        'titulo' => 'nullable|string|max:255',
        'descripcion' => 'nullable|string',
        'descuento' => 'nullable|numeric',
        'fecha_caducidad' => 'nullable|date',
    ]);

    try {
        $coupon = new Coupon();

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('coupons', 'public');
            $coupon->imagen = $path;
        }

        $coupon->nombre = $request->input('nombre', '');
        $coupon->titulo = $request->input('titulo', '');
        $coupon->descripcion = $request->input('descripcion', '');
        $coupon->descuento = $request->input('descuento', 0);
        $coupon->fecha_caducidad = $request->input('fecha_caducidad');

        if (!empty($coupon->titulo)) {
            $generator = new BarcodeGeneratorPNG();
            $barcode = $generator->getBarcode($coupon->titulo, $generator::TYPE_CODE_128);
            $coupon->barcode = 'data:image/png;base64,' . base64_encode($barcode);
        }

        $coupon->save();

        return redirect()->route('tenant.coupons.index')->with('success', 'Cupón agregado con éxito');

    } catch (\Exception $e) {
        return redirect()->route('tenant.coupons.index')->with('error', 'Hubo un error al agregar el cupón: ' . $e->getMessage());
    }
}
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('tenant.coupons.index')->with('success', 'Cupón eliminado con éxito');
    }

public function update(Request $request, $id)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'titulo' => 'required|string|max:255',
        'descripcion' => 'required|string',
        'imagen' => 'nullable|image|max:2048',
        'descuento' => 'required|numeric',
    ]);

    $coupon = Coupon::findOrFail($id);
    $coupon->nombre = $request->input('nombre');
    $coupon->titulo = $request->input('titulo');
    $coupon->descripcion = $request->input('descripcion');
    $coupon->descuento = $request->input('descuento');

    if ($request->hasFile('imagen')) {
        $path = $request->file('imagen')->store('coupons', 'public');
        $coupon->imagen = $path;
    }

    $coupon->save();

    return redirect()->route('tenant.coupons.index')->with('success', 'Cupón actualizado con éxito');
}
    public function getCoupons()
    {
        $coupons = Coupon::all();
        return response()->json($coupons);
    }
public function edit($id)
{
    $coupon = Coupon::findOrFail($id);
    return view('cupones.edit', compact('coupon'));
}



}
