<?php
//report_hotels
    namespace Modules\Report\Http\Controllers;

    use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Person;
use Carbon\Carbon;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Database\Query\Builder;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\View\View;
    use Modules\BusinessTurn\Models\BusinessTurn;
    use Modules\BusinessTurn\Models\DocumentHotel;
    use Modules\Hotel\Models\HotelFloor;
    use Modules\Hotel\Models\HotelRent;
    use Modules\Hotel\Models\HotelRoom;
    use Modules\Report\Exports\DocumentHotelExport;
    use Modules\Report\Exports\ReportHotelExport;
use Modules\Report\Exports\ReportHotelMinceturExport;
use Modules\Report\Http\Resources\DocumentHotelCollection;
    use Modules\Report\Http\Resources\RentHotelCollection;
use Modules\Report\Http\Resources\RentHotelMinceturCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

    class ReportHotelController extends Controller
    {
        // use ReportTrait;


        public function mincetur_index(){
            $configuration = Configuration::first()->getCollectionData(); 

            return view('report::report_hotels.mincetur_index', compact('configuration'));

        }
        public function filter_mincetur()
        {

            $document_types = [];

            $establishments = Establishment::whereActive()->get()->transform(function (Establishment $row) {
                return [
                    'id' => $row->id,
                    'name' => $row->description
                ];
            });

            return compact('document_types', 'establishments');
        }
        public function records_mincetur(Request $request)

        {
            $records = $this->getRecords($request->all());
            // $records = $this->getRecords($request->all());

            return new RentHotelMinceturCollection($records->paginate(config('tenant.items_per_page')));
        }
        public function excel_mincetur(Request $request)
        {

            $company = Company::first();

            $records = $this->getRecords($request->all())
            ->get()->transform(function($row){
                $reg = null;
            $country = null;
            
            $customer_general = Person::find($row->customer_id);
            if($customer_general){
                $department = $customer_general->department;
                if($department){
                    $reg = $department->description;
                }
                $country_general = $customer_general->country;
                if($country_general){
                    $country = $country_general->description;
                }

            }
            $customer = (array)$row->customer;
            $customer_name = $customer["name"];
            $sex =  isset($customer["sex"]) ? ($customer["sex"] == 'M' ? 'Masculino' : 'Femenino') : null;
            $reason = $row->destiny;
            $document_type_id = $customer["identity_document_type_id"];
            $document_type = IdentityDocumentType::find($document_type_id)->description;
            $document_number = $customer["number"];
            $start_date = Carbon::parse($row->input_date)->format('d/m/Y');
            $end_date = Carbon::parse($row->output_date)->format('d/m/Y');
            $category = $row->room->category->description;
            $room = $row->room->name;
            $room_rastes = $row->room->rates->sum('price');
            return  [
                "customer_name" => $customer_name,
                "sex" => $sex,
                "reg" => $reg,
                "country" => $country,
                "reason" => $reason,
                "customer_document_type" => $document_type,
                "customer_document_number" => $document_number,                
                "start_date" => $start_date,
                "end_date" => $end_date,
                "category" => $category,
                "room" => $room,
                "room_rastes" => $room_rastes,
            ];

            });
            ;

            $documentHotelExport = new ReportHotelMinceturExport();
            $documentHotelExport
                ->records($records)
                ->company($company);

            return $documentHotelExport->download('Reporte_Mincetur_' . Carbon::now() . '.xlsx');

        }
        /**
         * @return array
         */
        public function filter()
        {

            $document_types = [];

            $establishments = Establishment::whereActive()->get()->transform(function (Establishment $row) {
                return [
                    'id' => $row->id,
                    'name' => $row->description
                ];
            });

            return compact('document_types', 'establishments');
        }


        /**
         * @return Factory|Application|RedirectResponse|View
         */
        public function index()
        {
            $record = BusinessTurn::where('value', 'hotel')->where('active', true)->first();

            if (!$record) {
                return redirect()->route('tenant.reports.sale_notes.index');
            }
            return view('report::report_hotels.index');

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

            return view('hotel::report_hotels.index', compact('rooms', 'floors', 'roomStatus'));
        }

        /**
         * @param Request $request
         *
         * @return RentHotelCollection
         */
        public function records(Request $request)
        {
            $records = $this->getRecords($request->all());

            return new RentHotelCollection($records->paginate(config('tenant.items_per_page')));
        }

        /**
         * @param $request
         *
         * @return \Illuminate\Database\Eloquent\Builder|Builder|DocumentHotel
         */
        public function getRecords($request)
        {

            $date_start = $request['date_start'];
            $date_end = $request['date_end'];

            return $this->data($date_start, $date_end);

        }

        /**
         * @param $date_start
         * @param $date_end
         *
         * @return \Illuminate\Database\Eloquent\Builder|Builder|HotelRent
         */
        private function data($date_start, $date_end)
        {
            $rooms = HotelRent::with('room','room.rates', 'rate','room.category')
            ->whereHas('room')
			->orderBy('id', 'DESC');
            return $rooms=$rooms->SearchByDate($date_start,$date_end)->latest();



        }

        /**
         * @param Request $request
         *
         * @return Response|BinaryFileResponse
         */
        public function excel(Request $request)
        {

            $company = Company::first();

            $records = $this->getRecords($request->all())->get();

            $rooms = HotelRoom::get();

            $documentHotelExport = new ReportHotelExport();
            $documentHotelExport
                ->records($records)
                ->rooms($rooms)
                ->company($company);

            return $documentHotelExport->download('Reporte_Hoteles_' . Carbon::now() . '.xlsx');

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
