<?php

namespace Modules\Hotel\Http\Controllers;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use Illuminate\Routing\Controller;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelFloor;
use Modules\Hotel\Models\HotelRent;
use Illuminate\Http\Request;

class HotelReceptionController extends Controller
{
    public function index()
    {
        $rooms = $this->getRooms();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'rooms'   => $rooms,
            ], 200);
        }
        $floors = HotelFloor::where('active', true)
            ->orderBy('description')
            ->get();

        $roomStatus = HotelRoom::$status;

        $config = Configuration::first();
        $percentageIgv = $this->getIgv();
        return view('hotel::rooms.reception', compact(
            'percentageIgv',
            'rooms',
            'floors',
            'roomStatus',
            'config'
        ));
    }
    function getIgv()
    {

        $establishment_id = auth()->user()->establishment_id;
        $date = date('Y-m-d');
        $date_start = config('tenant.igv_31556_start');
        $date_end = config('tenant.igv_31556_end');
        $date_percentage = config('tenant.igv_31556_percentage');
        $establishment = Establishment::query()
            ->select('id', 'has_igv_31556')
            ->find($establishment_id);
        if ($establishment->has_igv_31556) {
            if ($date >= $date_start && $date <= $date_end) {
                return $date_percentage;
            }
        }
        return 0.18;
    }
    /**
     * Busqueda avanzada de cuartos.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function  searchRooms(Request $request)
    {

        $rooms = HotelRoom::with('category', 'floor', 'rates');

        if ($request->has('hotel_floor_id') && !empty($request->hotel_floor_id)) {
            $rooms->where('hotel_floor_id', $request->hotel_floor_id);
        }
        if ($request->has('hotel_status_room') && !empty($request->hotel_status_room)) {
            $rooms->where('status',  $request->hotel_status_room);
        }
        if ($request->has('hotel_name_room') && !empty($request->hotel_name_room)) {
            $rooms->where('name', 'LIKE',  "%{$request->hotel_name_room}%");
        }
        $rooms =  $rooms->orderBy('name')->get()->each(function ($room) {
            if ($room->status === 'OCUPADO') {
                $rent = HotelRent::where('hotel_room_id', $room->id)
                    ->orderBy('id', 'DESC')
                    ->first();
                $room->rent = $rent;
            } else {
                $room->rent = [];
            }

            return $room;
        });

        return response()->json([
            'success' => true,
            'rooms'   => $rooms,
        ], 200);
    }
    /**
     * Devuelve informacion de cuartos disponibles
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|\Modules\Hotel\Models\HotelRoom[]
     */
    private function getRooms()
    {
        $rooms = HotelRoom::with('category', 'floor', 'rates');

        if (request('hotel_floor_id')) {
            $rooms->where('hotel_floor_id', request('hotel_floor_id'));
        }
        if (request('status')) {
            $rooms->where('status', request('status'));
        }

        $rooms->orderBy('name');
        return $rooms->get()->each(function ($room) {
            if ($room->status === 'OCUPADO') {
                $rent = HotelRent::where('hotel_room_id', $room->id)
                    ->orderBy('id', 'DESC')
                    ->first();
                $room->rent = $rent;
            } else {
                $room->rent = [];
            }

            return $room;
        });
    }
}
