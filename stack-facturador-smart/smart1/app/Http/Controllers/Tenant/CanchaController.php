<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\CanchasCollection;
use App\Http\Resources\Tenant\CanchasTypeCollection;
use App\Models\Tenant\Cancha;
use App\Models\Tenant\CanchasTipo;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;

class CanchaController extends Controller
{
    public function indexTypes()
    {

        return view('canchas.tipo_reservas_list');
    }
    public function recordsTypes(Request $request)
    {
        $query = CanchasTipo::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nombre', 'LIKE', "%$search%")
                ->orWhere('ubicacion', 'LIKE', "%$search%")
                ->orWhere('capacidad', 'LIKE', "%$search%");
        }


        return new CanchasTypeCollection($query->paginate(20));
    }
    public function columnsTypes()
    {
        return [
            'nombre' => 'Nombre',
            'ubicacion' => 'Ubicación',
            'capacidad' => 'Capacidad',
        ];
    }
    public function recordTypes($id)

    {
        $record = CanchasTipo::findOrFail($id);

        return $record;
    }
    public function storeTypes(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'capacidad' => 'required|integer',
        ]);
        $id = $request->input('id');
        $canchaTipo = CanchasTipo::firstOrNew(['id' => $id]);
        $canchaTipo->nombre = $request->input('nombre');
        $canchaTipo->ubicacion = $request->input('ubicacion');
        $canchaTipo->capacidad = $request->input('capacidad');
        $canchaTipo->description = $request->input('description');
        $canchaTipo->save();

        return response()->json(['success' => true, 'message' => 'Tipo de reserva agregada con éxito']);
    }
    public function destroyTypes($id)
    {
        $canchaTipo = CanchasTipo::findOrFail($id);
        $canchaTipo->delete();

        return response()->json(['success' => true, 'message' => 'Tipo de reserva eliminada con éxito']);
    }
    public function records(Request $request)
    {
        $query = Cancha::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reservante_nombre', 'LIKE', "%$search%")
                    ->orWhere('ticket', 'LIKE', "%$search%");
            });
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('fecha_reserva', [$startDate, $endDate]);
        }

        if ($request->has('tipo_reserva') && !empty($request->input('tipo_reserva'))) {
            $tipoReserva = $request->input('tipo_reserva');
            $query->whereHas('canchasTipo', function ($q) use ($tipoReserva) {
                $q->where('nombre', $tipoReserva);
            });
        }
        return new CanchasCollection($query->paginate(20));
        // $records = $query->get();

        // foreach ($records as $record) {
        //     $qrCode = new QrCode($record->ticket);
        //     $qrCode->setSize(300);
        //     $writer = new PngWriter();
        //     $qrCodeImage = $writer->write($qrCode);

        //     $record->qr_code = $qrCodeImage->getDataUri();
        // }

        // Cargar todos los tipos de reservas
        // $tiposReservas = CanchasTipo::all();
        // Obtener todas las reservas con las fechas y horas
        // $reservedHours = Cancha::select('fecha_reserva', 'hora_reserva')->get();
        // return view('canchas.index', compact('records', 'tiposReservas'));
    }

    public function columns()
    {
        return [
            'reservante_nombre' => 'Nombre',
        ];
    }
    public function index()
    {

        return view('canchas.index');
    }
    public function reservaciones(Request $request)
    {
        $types = CanchasTipo::all()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->nombre,
                'location' => $row->ubicacion,
                'description' => $row->description,
            ];
        });
        return view('canchas.reservaciones', compact('types'));
        // $query = Cancha::query();

        // if ($request->has('search')) {
        //     $search = $request->input('search');
        //     $query->where('reservante_nombre', 'LIKE', "%$search%")
        //         ->orWhere('reservante_apellidos', 'LIKE', "%$search%");
        // }

        // $records = $query->get();

        // foreach ($records as $record) {
        //     $qrCode = new QrCode($record->ticket);
        //     $qrCode->setSize(300);
        //     $writer = new PngWriter();
        //     $qrCodeImage = $writer->write($qrCode);

        //     $record->qr_code = $qrCodeImage->getDataUri();
        // }

        // // Cargar todos los tipos de reservas
        // $tiposReservas = CanchasTipo::all();

        // return view('canchas.reservaciones', compact('records', 'tiposReservas'));
    }
    public function anular($id)
    {
        $cancha = Cancha::findOrFail($id);
        $cancha->anulado = 1;
        $cancha->save();

        return response()->json(['success' => true]);
    }
    public function record($id)
    {
        $record = Cancha::findOrFail($id);
        $qrCode = new QrCode($record->ticket);
        $writer = new PngWriter();
        $qrCodeImage = $writer->write($qrCode);
        $qrCodeDataUri = $qrCodeImage->getDataUri();

        $record->qr_code = $qrCodeDataUri;

        return view('canchas.show', compact('record'));
    }
    function checkIfExistReservation($date, $time, $duration)
    {
        $existingReservation = Cancha::where('fecha_reserva', $date)
            ->where('hora_reserva', $time)
            ->where('tiempo_reserva', $duration)
            ->where('anulado', 0)
            ->first();

        return $existingReservation;
    }
    public function store(Request $request)
    {
        $request->validate([
            'time' => 'required|date_format:H:i',
            'date' => 'required|date',
            'duration' => 'required|integer',
            'customer_id' => 'required|integer',
            'type_id' => 'required|integer',
        ]);

        $existingReservation = $this->checkIfExistReservation($request->input('fecha_reserva'), $request->input('hora_reserva'), $request->input('tiempo_reserva'));
        if ($existingReservation) {
            return [
                'success' => false,
                'message' => 'Hora no disponible'
            ];
        }



        $cancha = new Cancha();
        $cancha->customer_id = $request->input('customer_id');
        $cancha->type_id = $request->input('type_id');
        $cancha->hora_reserva = $request->input('time');
        $cancha->fecha_reserva = $request->input('date');
        $cancha->tiempo_reserva = $request->input('duration');
        $cancha->nombre = $request->input('name');
        $cancha->ubicacion = $request->input('location');
        $cancha->description = $request->input('description');
        $cancha->ticket = $this->generateTicket();



        $cancha->save();

        return [
            'success' => true,
            'message' => 'Reserva agregada con éxito',
            'cancha' => $cancha
        ];
    }



    public function reservas($id)
    {
        $cancha = Cancha::findOrFail($id);
        $company = Company::first();
        $establishment = Establishment::first();
        $pdf = PDF::loadView('canchas.reservas', compact('cancha', 'company', 'establishment'))
            ->setPaper(array(0, 0, 180, 360));
        $filename = 'Reserva_' . date('YmdHis') . '.pdf';
        return $pdf->stream($filename);
    }
    private function generateTicket()
    {
        return strtoupper(bin2hex(random_bytes(4)));
    }

    public function destroy($id)
    {
        $cancha = Cancha::findOrFail($id);
        $cancha->delete();

        return redirect()->route('tenant.canchas.index')->with('success', 'Cancha eliminada con éxito');
    }
    public function publicStore(Request $request)
    {


        $existingReservation = Cancha::where('fecha_reserva', $request->input('date'))
            ->where('hora_reserva', $request->input('time'))
            ->where('anulado', 0)
            ->first();

        if ($existingReservation) {
            return response()->json(['success' => false, 'message' => 'Hora no disponible'], 409);
        }

        $cancha = new Cancha();
        $cancha->numero = $request->input('phone');
        $cancha->nombre = $request->input('name');
        $cancha->ubicacion = $request->input('location');
        $cancha->type_id = $request->input('type_id');
        $cancha->description = $request->input('description');
        $cancha->reservante_nombre = $request->input('name_client');
        $cancha->reservante_apellidos = $request->input('last_name_client');
        $cancha->hora_reserva = $request->input('time');
        $cancha->fecha_reserva = $request->input('date');
        $cancha->tiempo_reserva = $request->input('duration');
        $cancha->ticket = $this->generateTicket();

        // Generate QR code and save to file


        $cancha->save();

        return response()->json(['success' => true, 'cancha' => $cancha]);
    }




    public function storeCanchasTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'description' => 'required|string',
            // 'capacidad' => 'required|integer',1
        ]);

        $canchaTipo = new CanchasTipo();
        $canchaTipo->nombre = $request->input('nombre');
        $canchaTipo->ubicacion = $request->input('ubicacion');
        $canchaTipo->capacidad = $request->input('capacidad');
        $canchaTipo->description = $request->input('description');
        $canchaTipo->save();

        return redirect()->route('tenant.canchas.index')->with('success', 'Tipo de reserva agregada con éxito');
    }

    public function filterCanchasTipo(Request $request)
    {
        $query = CanchasTipo::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nombre', 'LIKE', "%$search%")
                ->orWhere('ubicacion', 'LIKE', "%$search%")
                ->orWhere('capacidad', 'LIKE', "%$search%");
        }

        $tiposReservas = $query->get();

        return view('canchas.tipo_reservas_list', compact('tiposReservas'));
    }
    public function updateCanchasTipo(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'capacidad' => 'required|integer',
        ]);

        $canchaTipo = CanchasTipo::findOrFail($id);
        $canchaTipo->nombre = $request->input('nombre');
        $canchaTipo->ubicacion = $request->input('ubicacion');
        $canchaTipo->capacidad = $request->input('capacidad');
        $canchaTipo->save();

        return redirect()->route('tenant.canchas.index')->with('success', 'Tipo de reserva actualizada con éxito');
    }

    public function editCanchasTipo($id)
    {
        $canchaTipo = CanchasTipo::findOrFail($id);
        return view('canchas.edit_tipo', compact('canchaTipo'));
    }


    public function destroyCanchasTipo($id)
    {
        $canchaTipo = CanchasTipo::findOrFail($id);
        $canchaTipo->delete();

        return redirect()->route('tenant.canchas.index')->with('success', 'Tipo de reserva eliminada con éxito');
    }
}
