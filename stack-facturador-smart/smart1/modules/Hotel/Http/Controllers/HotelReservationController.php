<?php

namespace Modules\Hotel\Http\Controllers;

use App\Http\Controllers\Tenant\SaleNoteController;
use App\Http\Requests\Tenant\SaleNoteRequest;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hotel\Models\HotelReservation;
use Modules\Hotel\Http\Resources\HotelReservationCollection;
use Exception;
use Modules\Hotel\Models\HotelRoom;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Series;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Hotel\Http\Resources\HotelReservationResource;
use Modules\Hotel\Models\HotelRentItem;
use Illuminate\Support\Facades\Log;

class HotelReservationController extends Controller
{
    use FinanceTrait;
    public function index()
    {
        $rooms = HotelRoom::all()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->getName(),
                'number' => $row->number,
                'category_id' => $row->category_id,
                'category_description' => $row->category->description,
                'category_color' => $row->category->color,
            ];
        });
        $percentageIgv = $this->getIgv();
        return view('hotel::reservations.index', compact('rooms', 'percentageIgv'));
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
    public function pdf($hotel_reservation_id)
    {
        $company = Company::active();
        $document = HotelReservation::find($hotel_reservation_id);

        $pdf = Pdf::loadView('hotel::reservations.format', compact("document", "company"))->setPaper('a4', 'portrait');
        $filename = 'Reserva-' . $document->id . '.pdf';

        return $pdf->stream($filename);
    }
    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $config = Configuration::first();
            $id = $request->id;
            if ($config->hotel_reservation_type_2 && !$id) {
                $this->checkRoomAvailabilityByHours($request);
            } else {
                $this->checkRoomAvailability($request);
            }
            if (!$request->created_by) {
                $request->merge(['created_by' => auth()->user()->name]); // Assuming the user's name is available via auth
            }

            // Fetch customer details
            $customer = Person::findOrFail($request->customer_id);
            $request->merge([
                'name' => $customer->name,
                'document' => $customer->number,
            ]);

            if ($id) {
                $sale_note_id = null;
                $reservation = HotelReservation::find($id);
                if ($reservation->sale_note_id) {
                    $sale_note_id = $reservation->sale_note_id;
                    (new SaleNoteController())->anulate($sale_note_id);
                }
            }

            $reservation = HotelReservation::updateOrCreate(['id' => $id], $request->all());
            $saleNote = $request->saleNote;

            if ($saleNote) {
                $sale_note_request = new SaleNoteRequest();
                $series_id = Series::where('document_type_id', '80')
                    ->where('establishment_id', auth()->user()->establishment_id)
                    ->first()->id;
                $saleNote['series_id'] = $series_id;
                $saleNote['customer_id'] = $request->customer_id;
                $saleNote['establishment_id'] = auth()->user()->establishment_id;
                $sale_note_request->merge($saleNote);

                $response =    (new SaleNoteController())->store($sale_note_request);
                if (!$response['success']) {
                    throw new Exception($response['message']);
                } else {
                    $reservation->sale_note_id = $response['data']['id'];
                    $reservation->save();
                }
            }
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => $id ? 'Reserva actualizada con éxito' : 'Reserva registrada con éxito',
                'data' => $reservation
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    function searchOrCreateServiceItem()
    {
        Item::createItemService();
        $item = Item::whereWarehouse()
            ->where('internal_id', 'ZZ001')
            ->first();

        return $item;
    }
    function getFormattedServiceItem()
    {
        $row = $this->searchOrCreateServiceItem();
        $configuration = Configuration::first();
        $item =   [
            'id' => $row->id,
            'internal_id' => $row->internal_id,
            'description' => $row->description,
            'sale_unit_price' => number_format(round($row->sale_unit_price, 6), $configuration->decimal_quantity, ".", ""),
            'purchase_unit_price' => number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ","),
            'unit_type_id' => $row->unit_type_id,
            'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
            'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
            'has_igv' => (bool)$row->has_igv,
            'is_set' => (bool)$row->is_set,
            'IdLoteSelected' => null,
            'lots_group' => [],
            'lots_enabled' => false,
            'currency_type_id' => $row->currency_type_id,
            'lots' => [],
            'sizes_selected' => [],
            'presentation' => $row->presentation,
        ];

        return $item;
    }

    private function checkRoomAvailability($request)
    {
        $id = $request->id;
        $checkInDate = $request->check_in_date;
        $checkOutDate = $request->check_out_date;
        $roomId = $request->room_id;
        $arrivalTime = $request->arrival_time;
        $departureTime = $request->departure_time ?? '23:59:59';
    
        $config = Configuration::first();
        $isHourlyReservation = $config->hotel_reservation_type_2;
    
        // Log::info('Checking room availability', [
        //     'room_id' => $roomId,
        //     'check_in_date' => $checkInDate,
        //     'check_out_date' => $checkOutDate,
        //     'arrival_time' => $arrivalTime,
        //     'departure_time' => $departureTime,
        //     'is_hourly_reservation' => $isHourlyReservation
        // ]);
    
        $conflictingReservations = HotelReservation::where('room_id', $roomId)
            ->where('id', '!=', $id)
            ->where('active', true)
            ->where(function ($query) use ($checkInDate, $checkOutDate, $arrivalTime, $departureTime, $isHourlyReservation) {
    
                // 1. Solapamiento entre rangos de días
                $query->whereRaw('check_in_date < ? AND check_out_date > ?', [$checkOutDate, $checkInDate])
    
                // 2. Nueva reserva termina en un día donde otra comienza (incluye NULL como "todo el día")
                ->orWhere(function ($q) use ($checkOutDate, $departureTime) {
                    $q->where('check_in_date', '=', $checkOutDate)
                      ->where(function ($q2) use ($departureTime) {
                          $q2->whereRaw('TIME_TO_SEC(arrival_time) < TIME_TO_SEC(?)', [$departureTime])
                             ->orWhereNull('arrival_time');
                      });
                })
    
                // 3. Nueva reserva comienza en un día donde otra termina (incluye NULL como "todo el día")
                ->orWhere(function ($q) use ($checkInDate, $arrivalTime) {
                    $q->where('check_out_date', '=', $checkInDate)
                      ->where(function ($q2) use ($arrivalTime) {
                          $q2->whereRaw('TIME_TO_SEC(departure_time) > TIME_TO_SEC(?)', [$arrivalTime])
                             ->orWhereNull('departure_time');
                      });
                })
    
                // 4. Mismo día: reserva por horas (incluye NULL como "todo el día")
                ->orWhere(function ($q) use ($checkInDate, $checkOutDate, $arrivalTime, $departureTime, $isHourlyReservation) {
                    if ($isHourlyReservation && $checkInDate === $checkOutDate) {
                        $q->where('check_in_date', '=', $checkInDate)
                          ->where('check_out_date', '=', $checkOutDate)
                          ->where(function ($q2) use ($arrivalTime, $departureTime) {
                              $q2->where(function ($q3) use ($arrivalTime, $departureTime) {
                                  $q3->whereRaw('
                                      (TIME_TO_SEC(arrival_time) < TIME_TO_SEC(?) AND TIME_TO_SEC(departure_time) > TIME_TO_SEC(?)) OR
                                      (TIME_TO_SEC(arrival_time) >= TIME_TO_SEC(?) AND TIME_TO_SEC(arrival_time) < TIME_TO_SEC(?)) OR
                                      (TIME_TO_SEC(departure_time) > TIME_TO_SEC(?) AND TIME_TO_SEC(departure_time) <= TIME_TO_SEC(?))
                                  ', [
                                      $departureTime, $arrivalTime,
                                      $arrivalTime, $departureTime,
                                      $arrivalTime, $departureTime
                                  ]);
                              })
                              ->orWhereNull('departure_time') // Si no tiene hora de salida, bloquea todo el día
                              ->orWhereNull('arrival_time');   // Si no tiene hora de llegada, bloquea todo el día
                          });
                    }
                });
            })
            ->get();
    
        // Log::info('Conflicting reservations found', [
        //     'count' => $conflictingReservations->count(),
        //     'reservations' => $conflictingReservations->map(function ($r) {
        //         return [
        //             'id' => $r->id,
        //             'check_in_date' => $r->check_in_date,
        //             'check_out_date' => $r->check_out_date,
        //             'arrival_time' => $r->arrival_time,
        //             'departure_time' => $r->departure_time
        //         ];
        //     })
        // ]);
    
        if ($conflictingReservations->count() > 0) {
            throw new Exception('La habitación no está disponible en las fechas y horarios seleccionados.');
        }
    
        return true;
    }
    /**
     * Verifica la disponibilidad de una habitación en una fecha y rango horario específicos
     * 
     * @param Request $request
     * @throws Exception
     */
    private function checkRoomAvailabilityByHours($request)
    {
        $checkInDate = $request->check_in_date;
        $roomId = $request->room_id;
        $arrivalTime = $request->arrival_time;
        $departureTime = $request->departure_time;

        $exists = HotelReservation::where('room_id', $roomId)
            ->where('active', true)
            ->where('check_in_date', $checkInDate)
            ->where('arrival_time', '=', $arrivalTime)
            ->exists();
        if ($exists) {
            throw new Exception('La habitación no está disponible en las fechas seleccionadas.');
        }

        // Convertir las horas de llegada y salida a minutos para facilitar la comparación
        $requestArrivalMinutes = $this->timeToMinutes($arrivalTime);
        $requestDepartureMinutes = $this->timeToMinutes($departureTime);

        // Detectar si la reserva cruza la medianoche
        $crossesMidnight = $requestDepartureMinutes < $requestArrivalMinutes || $requestDepartureMinutes == 0;

        // Si cruza la medianoche, ajustar los minutos de salida para la comparación
        $adjustedDepartureMinutes = $crossesMidnight ? 1440 : $requestDepartureMinutes; // 1440 = 24 horas * 60 minutos

        // Buscar reservas que se superpongan en el rango horario
        $conflictingReservations = HotelReservation::where('room_id', $roomId)
            ->where('active', true)
            ->where('check_in_date', $checkInDate)
            ->where(function ($query) use ($requestArrivalMinutes, $adjustedDepartureMinutes, $crossesMidnight, $requestDepartureMinutes) {
                // Verificar si alguna reserva existente se superpone con la nueva reserva
                $query->whereRaw(
                    "
                    -- Caso 1: La reserva existente comienza antes y termina durante nuestra reserva
                    (TIME_TO_SEC(arrival_time)/60 < ? AND TIME_TO_SEC(departure_time)/60 > ?) OR
                    -- Caso 2: La reserva existente comienza durante nuestra reserva
                    (TIME_TO_SEC(arrival_time)/60 >= ? AND TIME_TO_SEC(arrival_time)/60 < ?)",
                    [
                        $requestArrivalMinutes,
                        $requestArrivalMinutes,
                        $requestArrivalMinutes,
                        $adjustedDepartureMinutes
                    ]
                );

                // Para reservas existentes que cruzan la medianoche
                $query->orWhereRaw(
                    "
                    TIME_TO_SEC(departure_time)/60 < TIME_TO_SEC(arrival_time)/60 AND (
                        -- Caso 3: Nuestra reserva comienza después del inicio pero antes de medianoche
                        (? >= TIME_TO_SEC(arrival_time)/60) OR
                        -- Caso 4: Nuestra reserva termina después de medianoche pero antes del final
                        (? > 0 AND ? < TIME_TO_SEC(departure_time)/60)
                    )",
                    [
                        $requestArrivalMinutes,
                        $requestDepartureMinutes,
                        $requestDepartureMinutes
                    ]
                );
            })
            ->exists();





        if ($conflictingReservations) {
            throw new Exception('La habitación no está disponible en el horario seleccionado.');
        }

        return true;
    }

    /**
     * Convierte una hora en formato HH:MM a minutos desde medianoche
     * 
     * @param string $time
     * @return int
     */
    private function timeToMinutes($time)
    {
        if (!$time) return 0;

        list($hours, $minutes) = explode(':', $time);
        return ((int)$hours * 60) + (int)$minutes;
    }

    public function records(Request $request)
    {
        $query = HotelReservation::query();
        $hotel_room_id = $request->hotel_room_id;

        if ($hotel_room_id) {
            $query->where('room_id', $hotel_room_id);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('document')) {
            $query->where('document', 'like', '%' . $request->document . '%');
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('check_in_date')) {
            $query->whereDate('check_in_date', '>=', $request->check_in_date);
        }

        if ($request->filled('check_out_date')) {
            $query->whereDate('check_out_date', '<=', $request->check_out_date);
        }

        if ($request->filled('active')) {
            $query->where('active', true);
        }

        $records = $query->orderBy('id', 'desc')->paginate(20);

        return new HotelReservationCollection($records);
    }

    public function record($id)
    {
        $record = HotelReservation::findOrFail($id);
        return new HotelReservationResource($record);
    }

    public function delete($id)
    {
        try {
            $reservation = HotelReservation::findOrFail($id);
            $reservation->delete();

            return [
                'success' => true,
                'message' => 'Reserva eliminada con éxito'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function tables()
    {

        $rooms = HotelRoom::all()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->getName(),
                'number' => $row->number,
                'category_id' => $row->category_id,
                'category_description' => $row->category->description,
                'category_color' => $row->category->color,
            ];
        });
        $affectation_igv_types = AffectationIgvType::where('active', true)->get();
        $payment_method_types = PaymentMethodType::getPaymentMethodTypes();
        $payment_destinations = $this->getPaymentDestinations();
        $series = Series::where('document_type_id', '80')
            ->where('establishment_id', auth()->user()->establishment_id)
            ->get();
        return [
            'affectation_igv_types' => $affectation_igv_types,
            'rooms' => $rooms,
            'payment_method_types' => $payment_method_types,
            'payment_destinations' => $payment_destinations,
            'service_item' => $this->getFormattedServiceItem(),
            'series' => $series,
            'establishment_id' => auth()->user()->establishment_id
        ];
    }

    /**
     * Obtiene las reservas agrupadas por día para un mes específico
     * 
     * @param int $year
     * @param int $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthReservations($year, $month, Request $request)
    {
        try {
            $hotel_room_id = $request->hotel_room_id;
            $hotel_floor_id = $request->hotel_floor_id;
            // Crear fechas de inicio y fin para el mes
            $startDate = "{$year}-{$month}-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            // Obtener todas las reservas activas para el mes
            $reservations = HotelReservation::where('active', true)
                ->where('check_in_date', '>=', $startDate)
                ->where('check_in_date', '<=', $endDate);
            if ($hotel_room_id) {
                $reservations->where('room_id', $hotel_room_id);
            }
            if ($hotel_floor_id) {
                $reservations->where('floor_id', $hotel_floor_id);
            }
            $reservations = $reservations->with(['room']) // Obtener datos de la habitación relacionada
                ->get();

            // Agrupar reservas por día
            $groupedReservations = $reservations->groupBy(function ($reservation) {
                return date('Y-m-d', strtotime($reservation->check_in_date));
            });

            // Formatear la respuesta para el calendario
            $calendarData = [];

            foreach ($groupedReservations as $date => $dayReservations) {
                $day = date('j', strtotime($date)); // Obtener solo el día del mes (1-31)

                $roomCounts = $dayReservations->groupBy('room_id')
                    ->map(function ($roomReservations) {
                        return $roomReservations->count();
                    });

                $calendarData[$day] = [
                    'date' => $date,
                    'total_reservations' => $dayReservations->count(),
                    'rooms' => $roomCounts,
                    'reservations' => $dayReservations->map(function ($reservation) {
                        return [
                            'id' => $reservation->id,
                            'customer_name' => $reservation->name,
                            'room_name' => $reservation->room->name,
                            'arrival_time' => $reservation->arrival_time,
                            'duration_hours' => $reservation->number_of_nights,
                            'color' => $reservation->room->category->color ?? '#3788d8'
                        ];
                    })
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $calendarData,
                'year' => $year,
                'month' => $month
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las reservas detalladas para una semana específica
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeekReservations($year, $month, $day)
    {
        try {
            Log::info("Fecha objetivo: {$year}-{$month}-{$day}");
            // Calcular la fecha de inicio de la semana (lunes)
            $targetDate = "{$year}-{$month}-{$day}";
            $dayOfWeek = date('N', strtotime($targetDate)); // 1 (lunes) a 7 (domingo)
            $daysToSubtract = $dayOfWeek - 1;
            Log::info("Día de la semana: {$dayOfWeek}");

            // Fecha del lunes de esa semana
            $startDate = date('Y-m-d', strtotime("-{$daysToSubtract} days", strtotime($targetDate)));
            Log::info("Fecha del lunes: {$startDate}");
            // Fecha del domingo de esa semana
            $endDate = date('Y-m-d', strtotime("+6 days", strtotime($startDate)));
            Log::info("Fecha del domingo: {$endDate}");

            // Log para depuración
            Log::info("Consultando reservas de semana: {$startDate} a {$endDate}");

            // Formatear datos para la vista de semana - inicializar estructura básica
            $weekData = [];

            // Crear estructura para cada día de la semana
            for ($i = 0; $i < 7; $i++) {
                $currentDate = date('Y-m-d', strtotime("+{$i} days", strtotime($startDate)));
                $weekData[$currentDate] = [
                    'date' => $currentDate,
                    'day_name' => date('l', strtotime($currentDate)),
                    'day' => date('j', strtotime($currentDate)),
                    'hours' => []
                ];
            }

            // Obtener todas las reservas activas para la semana - optimizando la consulta
            $reservations = HotelReservation::where('active', true)
                ->whereBetween('check_in_date', [$startDate, $endDate])
                ->with(['room:id,name,hotel_category_id', 'room.category:id,description']) // Seleccionar solo los campos necesarios
                ->get([
                    'id', 'name', 'room_id', 'check_in_date',
                    'arrival_time', 'departure_time', 'number_of_nights',
                    'observations', 'custom_telephone'
                ]);

            // Log para depuración
            Log::info("Reservas encontradas: " . $reservations->count());

            // Agregar reservas a las horas correspondientes
            foreach ($reservations as $reservation) {
                $reservationDate = $reservation->check_in_date;
                $arrivalTime = $reservation->arrival_time;

                // Verificar que arrival_time tenga un formato válido
                if (!$arrivalTime || strlen($arrivalTime) < 2) {
                    Log::warning("Reserva {$reservation->id} tiene arrival_time inválido: {$arrivalTime}");
                    continue;
                }

                $arrivalHour = (int)substr($arrivalTime, 0, 2);
                $departureTime = $reservation->departure_time;
                $departureHour = $departureTime ? (int)substr($departureTime, 0, 2) : $arrivalHour + $reservation->number_of_nights;

                // Detectar si la reserva cruza la medianoche
                $crossesMidnight = false;
                if ($departureHour < $arrivalHour || $departureHour == 0) {
                    $crossesMidnight = true;
                    // Para el día actual, ocupamos hasta la medianoche (hora 23)
                    $firstDayDepartureHour = 24;
                } else {
                    $firstDayDepartureHour = $departureHour;
                }

                // Ocupar cada hora de la reserva para el día actual
                for ($hour = $arrivalHour; $hour < $firstDayDepartureHour; $hour++) {
                    // Inicializar el array para esta hora si no existe
                    if (!isset($weekData[$reservationDate]['hours'][$hour])) {
                        $weekData[$reservationDate]['hours'][$hour] = [];
                    }

                    // Añadir indicador de si es hora de inicio o continuación
                    $isStartHour = ($hour == $arrivalHour);

                    // Agregar la reserva a la hora correspondiente
                    $weekData[$reservationDate]['hours'][$hour][] = [
                        'id' => $reservation->id,
                        'customer_name' => $reservation->name,
                        'room_id' => $reservation->room_id,
                        'room_name' => $reservation->room->name ?? 'Habitación sin asignar',
                        'arrival_time' => $reservation->arrival_time,
                        'departure_time' => $reservation->departure_time,
                        'duration_hours' => $reservation->number_of_nights,
                        'observations' => $reservation->observations,
                        'custom_telephone' => $reservation->custom_telephone,
                        'crosses_midnight' => $crossesMidnight
                    ];
                }

                // Si la reserva cruza la medianoche, agregar entradas para el día siguiente
                if ($crossesMidnight && $departureHour > 0) {
                    // Calcular la fecha del día siguiente
                    $nextDate = date('Y-m-d', strtotime("+1 day", strtotime($reservationDate)));

                    // Verificar si el día siguiente está en nuestro rango de semana
                    if (isset($weekData[$nextDate])) {
                        // Ocupar las horas del día siguiente hasta la hora de salida
                        for ($hour = 0; $hour < $departureHour; $hour++) {
                            if (!isset($weekData[$nextDate]['hours'][$hour])) {
                                $weekData[$nextDate]['hours'][$hour] = [];
                            }

                            // Agregar la reserva a la hora correspondiente del día siguiente
                            $weekData[$nextDate]['hours'][$hour][] = [
                                'id' => $reservation->id,
                                'customer_name' => $reservation->name,
                                'room_id' => $reservation->room_id,
                                'room_name' => $reservation->room->name,
                                'arrival_time' => $reservation->arrival_time,
                                'departure_time' => $reservation->departure_time,
                                'duration_hours' => $reservation->number_of_nights,
                                'is_start_hour' => false, // Nunca es hora de inicio en el día siguiente
                                'hour_position' => $hour + (24 - $arrivalHour), // Continúa la cuenta del día anterior
                                'color' => '#3788d8',
                                'observations' => $reservation->observations,
                                'custom_telephone' => $reservation->custom_telephone,
                                'crosses_midnight' => $crossesMidnight,
                                'continuation_day' => true // Indica que es continuación del día anterior
                            ];
                        }
                    }
                }
            }

            // Log para depuración - contar cuántas reservas procesamos por día
            foreach ($weekData as $date => $dayData) {
                $count = 0;
                foreach ($dayData['hours'] as $hour => $reservations) {
                    $count += count($reservations);
                }
                Log::info("Día {$date}: {$count} reservas procesadas");
            }

            return response()->json([
                'success' => true,
                'data' => $weekData,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_reservations' => $reservations->count()
            ]);
        } catch (Exception $e) {
            Log::error("Error en getWeekReservations: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las reservas detalladas para un día específico organizadas por hora
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDayReservations($year, $month, $day)
    {
        try {
            // Crear fecha objetivo
            $targetDate = "{$year}-{$month}-{$day}";
            // Obtener todas las reservas activas para el día
            $reservations = HotelReservation::where('active', true)
                ->where('check_in_date', $targetDate)
                ->with(['room', 'room.category']) // Obtener datos de habitación y categoría
                ->get();
            // Organizar reservas por hora
            $hourlyData = [];

            // Inicializar horas del día (7am a 12am)
            for ($hour = 7; $hour <= 23; $hour++) {
                $formattedHour = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                $hourlyData[$formattedHour] = [];
            }

            // Agregar reservas a sus horas correspondientes
            foreach ($reservations as $reservation) {
                $hour = $reservation->arrival_time->format('H:i');
                // Si la hora existe en nuestro arreglo, agregar la reserva
                if (isset($hourlyData[$hour])) {
                    $hourlyData[$hour][] = [
                        'id' => $reservation->id,
                        'customer_name' => $reservation->name,
                        'room_id' => $reservation->room_id,
                        'room_name' => $reservation->room->name,
                        'arrival_time' => $reservation->arrival_time,
                        'departure_time' => $reservation->departure_time,
                        'duration_hours' => $reservation->number_of_nights,
                        'color' => $reservation->room->category->color ?? '#3788d8',
                        'observations' => $reservation->observations,
                        'customer_document' => $reservation->document,
                        'total_amount' => $reservation->total_amount
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $hourlyData,
                'date' => $targetDate,
                'total_reservations' => $reservations->count()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Versión ligera y optimizada para obtener reservas semanales
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeekReservationsLite($year, $month, $day, Request $request)
    {
        try {
            // Calcular fechas de inicio y fin de semana
            $targetDate = "{$year}-{$month}-{$day}";
            $dayOfWeek = date('N', strtotime($targetDate));
            $daysToSubtract = $dayOfWeek - 1;

            $startDate = date('Y-m-d', strtotime("-{$daysToSubtract} days", strtotime($targetDate)));
            $endDate = date('Y-m-d', strtotime("+6 days", strtotime($startDate)));

            // Consulta base
            $query = "
                SELECT 
                    hr.id, 
                    hr.name as customer_name, 
                    hr.check_in_date, 
                    hr.arrival_time, 
                    hr.departure_time, 
                    hr.number_of_nights as duration_hours, 
                    hr.observations, 
                    hr.custom_telephone, 
                    hr.room_id,
                    r.name as room_name,
                    f.id as hotel_floor_id
                FROM 
                    hotel_reservations hr
                JOIN 
                    hotel_rooms r ON hr.room_id = r.id
                JOIN
                    hotel_floors f ON r.hotel_floor_id = f.id
                WHERE 
                    hr.active = 1 
                    AND hr.check_in_date BETWEEN ? AND ?
            ";

            $bindings = [$startDate, $endDate];

            $hotel_room_id = $request->hotel_room_id;
            $hotel_floor_id = $request->hotel_floor_id;

            if (!is_null($hotel_room_id)) {
                $query .= " AND hr.room_id = ?";
                $bindings[] = $hotel_room_id;
            }

            if (!is_null($hotel_floor_id)) {
                $query .= " AND hr.floor_id = ?";
                $bindings[] = $hotel_floor_id;
            }

            $query .= " ORDER BY hr.check_in_date, hr.arrival_time";

            $reservations = DB::connection('tenant')->select($query, $bindings);

            // Formatear datos para la vista semanal
            $weekData = [];

            // Crear estructura para cada día de la semana
            for ($i = 0; $i < 7; $i++) {
                $currentDate = date('Y-m-d', strtotime("+{$i} days", strtotime($startDate)));
                $weekData[$currentDate] = [
                    'date' => $currentDate,
                    'day_name' => date('l', strtotime($currentDate)),
                    'day' => date('j', strtotime($currentDate)),
                    'hours' => []
                ];
            }

            // Organizar reservas por día y hora
            foreach ($reservations as $reservation) {
                $date = $reservation->check_in_date;
                if (!isset($weekData[$date])) continue;

                // Obtener la hora de llegada y salida
                $arrivalTime = $reservation->arrival_time;
                $departureTime = $reservation->departure_time;

                if (!$arrivalTime || strlen($arrivalTime) < 2) continue;

                // Obtener las horas como números enteros para calcular la duración
                $arrivalHour = (int)substr($arrivalTime, 0, 2);
                $departureHour = $departureTime ? (int)substr($departureTime, 0, 2) : $arrivalHour + $reservation->duration_hours;

                // Detectar si la reserva cruza la medianoche
                $crossesMidnight = false;
                if ($departureHour < $arrivalHour || $departureHour == 0) {
                    $crossesMidnight = true;
                    // Para el día actual, ocupamos hasta la medianoche (hora 23)
                    $firstDayDepartureHour = 24;
                } else {
                    $firstDayDepartureHour = $departureHour;
                }

                // Ocupar cada hora de la reserva para el día actual
                for ($hour = $arrivalHour; $hour < $firstDayDepartureHour; $hour++) {
                    // Inicializar el array para esta hora si no existe
                    if (!isset($weekData[$date]['hours'][$hour])) {
                        $weekData[$date]['hours'][$hour] = [];
                    }

                    // Añadir indicador de si es hora de inicio o continuación
                    $isStartHour = ($hour == $arrivalHour);

                    // Agregar la reserva a la hora correspondiente
                    $weekData[$date]['hours'][$hour][] = [
                        'id' => $reservation->id,
                        'customer_name' => $reservation->customer_name,
                        'room_id' => $reservation->room_id,
                        'room_name' => $reservation->room_name,
                        'arrival_time' => $reservation->arrival_time,
                        'departure_time' => $reservation->departure_time,
                        'duration_hours' => $reservation->duration_hours,
                        'is_start_hour' => $isStartHour,
                        'hour_position' => $hour - $arrivalHour, // 0 para la primera hora, 1 para la segunda, etc.
                        'color' => '#3788d8', // Color predeterminado
                        'observations' => $reservation->observations,
                        'custom_telephone' => $reservation->custom_telephone,
                        'crosses_midnight' => $crossesMidnight
                    ];
                }

                // Si la reserva cruza la medianoche, agregar entradas para el día siguiente
                if ($crossesMidnight && $departureHour > 0) {
                    // Calcular la fecha del día siguiente
                    $nextDate = date('Y-m-d', strtotime("+1 day", strtotime($date)));

                    // Verificar si el día siguiente está en nuestro rango de semana
                    if (isset($weekData[$nextDate])) {
                        // Ocupar las horas del día siguiente hasta la hora de salida
                        for ($hour = 0; $hour < $departureHour; $hour++) {
                            if (!isset($weekData[$nextDate]['hours'][$hour])) {
                                $weekData[$nextDate]['hours'][$hour] = [];
                            }

                            // Agregar la reserva a la hora correspondiente del día siguiente
                            $weekData[$nextDate]['hours'][$hour][] = [
                                'id' => $reservation->id,
                                'customer_name' => $reservation->customer_name,
                                'room_id' => $reservation->room_id,
                                'room_name' => $reservation->room_name,
                                'arrival_time' => $reservation->arrival_time,
                                'departure_time' => $reservation->departure_time,
                                'duration_hours' => $reservation->duration_hours,
                                'is_start_hour' => false, // Nunca es hora de inicio en el día siguiente
                                'hour_position' => $hour + (24 - $arrivalHour), // Continúa la cuenta del día anterior
                                'color' => '#3788d8',
                                'observations' => $reservation->observations,
                                'custom_telephone' => $reservation->custom_telephone,
                                'crosses_midnight' => $crossesMidnight,
                                'continuation_day' => true // Indica que es continuación del día anterior
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $weekData,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_reservations' => count($reservations)
            ]);
        } catch (Exception $e) {
            Log::error("Error en getWeekReservationsLite: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Error al cargar las reservas: " . $e->getMessage()
            ], 500);
        }
    }
}
