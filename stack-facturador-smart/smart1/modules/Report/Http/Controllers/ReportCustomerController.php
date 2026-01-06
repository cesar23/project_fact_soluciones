<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\CustomerExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Document;
use App\Models\Tenant\Company;
use App\Models\Tenant\Person;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\SaleNote;
use Barryvdh\Debugbar\Twig\Extension\Dump;
use Carbon\Carbon;
use Modules\Report\Http\Resources\DocumentCollection;
use Modules\Report\Http\Resources\QuotationCollection;
use Modules\Report\Http\Resources\SaleNoteCollection;
use Modules\Report\Traits\ReportTrait;


class ReportCustomerController extends Controller
{
    use ReportTrait;

    public function items_most_sale(Request $request)
    {
        $id = $request->id;
        $r_items = [];
        Document::where('customer_id', $id)->chunk(50, function ($rows) use (&$r_items) {
            foreach ($rows as $row) {
                $items = $row->items;
                foreach ($items as $item) {
                    $id = $item->item_id;
                    if (isset($r_items[$id])) {
                        $r_items[$id]["total"] += $item->quantity;
                    } else {
                        $r_items[$id] = [
                            "item_id" => $id,
                            "description" => $item->m_item->description,
                            "stock" => intval($item->m_item->stock),
                            "total" => intval($item->quantity),
                            "price" => floatval($item->m_item->sale_unit_price),
                            "image" => $item->m_item->image,
                        ];
                    }
                }
            }
        });
        SaleNote::where('customer_id', $id)->chunk(50, function ($rows) use (&$r_items) {
            foreach ($rows as $row) {
                $items = $row->items;
                foreach ($items as $item) {
                    $id = $item->item_id;
                    if (isset($r_items[$id])) {
                        $r_items[$id]["total"] += $item->quantity;
                    } else {
                        $r_items[$id] = [
                            "item_id" => $id,
                            "description" => $item->relation_item->description,
                            "stock" => intval($item->relation_item->stock),
                            "total" => intval($item->quantity),
                            "price" => floatval($item->relation_item->sale_unit_price),
                            "image" => $item->relation_item->image,
                        ];
                    }
                }
            }
        });


        $sorted = collect($r_items)->sortByDesc('total');

        $items = $sorted->take(5);
        return compact("items");
    }
    public function docs_month(Request $request)
    {
        //storage/uploads/items

        $month_d = $request->month;
        $year_d = $request->year;
        $id = $request->id;
        $f = [];
        $b = [];
        $n = [];
        Document::where('state_type_id', '05')->where('customer_id', $id)->where('document_type_id', '01')->whereMonth('date_of_issue', $month_d)
            ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$f) {
                foreach ($rows as $row) {
                    $f[] = [
                        "exchange_rate_sale" => $row->exchange_rate_sale,
                        "currency_type_id" => $row->currency_type_id,
                        "total" => $row->total,
                        "items" => $row->items,
                        'date' => $row->date_of_issue->format("Y-m-d"),
                        "description" => $row->getNumberFullAttribute(),
                    ];
                }
            });
        Document::where('state_type_id', '05')->where('customer_id', $id)->where('document_type_id', '03')->whereMonth('date_of_issue', $month_d)
            ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$b) {

                foreach ($rows as $row) {
                    $b[] = [
                        "exchange_rate_sale" => $row->exchange_rate_sale,
                        "currency_type_id" => $row->currency_type_id,
                        "total" => $row->total,
                        "items" => $row->items,
                        'date' => $row->date_of_issue->format("Y-m-d"),
                        "description" => $row->getNumberFullAttribute(),
                    ];
                }
            });
        SaleNote::where('state_type_id', '01')->where('customer_id', $id)->whereMonth('date_of_issue', $month_d)
            ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$n) {

                foreach ($rows as $row) {
                    $n[] = [
                        "exchange_rate_sale" => $row->exchange_rate_sale,
                        "currency_type_id" => $row->currency_type_id,
                        "total" => $row->total,
                        "items" => $row->items,
                        'date' => $row->date_of_issue->format("Y-m-d"),
                        "description" => $row->getNumberFullAttribute(),
                    ];
                }
            });

        return compact("f", "b", "n");
    }
    public function filter()
    {

        $document_types = [];

        $persons = $this->getPersons('customers');

        $establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });

        return compact('document_types', 'establishments', 'persons');
    }
    public function detail_months(Request $request)
    {
        $descp_months = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre",
            "Octubre", "Noviembre", "Diciembre"
        ];
        $months = $request->months;
        $id = $request->id;
        if ($months) {
            $results = [];
            $months = explode("_", $months);
            foreach ($months as $month) {
                $date_d = Carbon::parse($month);
                $month_d = $date_d->month;
                $year_d = $date_d->year;
                $d_month = $descp_months[$month_d - 1];
                $f_total = 0;
                $b_total = 0;
                $n_total = 0;
                $nc_total = 0;
                $nd_total = 0;
                Document::where('state_type_id', '05')->where('customer_id', $id)->where('document_type_id', '01')->whereMonth('date_of_issue', $month_d)
                    ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$f_total) {

                        $f_total += $rows->sum(function ($row) {
                            if ($row->currency_type_id !==  "PEN") {
                                return $row->total * $row->exchange_rate_sale;
                            } else {
                                return $row->total;
                            }
                        });
                    });
                Document::where('state_type_id', '05')->where('customer_id', $id)->where('document_type_id', '08')->whereMonth('date_of_issue', $month_d)
                    ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$nd_total) {

                        $nd_total += $rows->sum(function ($row) {
                            if ($row->currency_type_id !==  "PEN") {
                                return $row->total * $row->exchange_rate_sale;
                            } else {
                                return $row->total;
                            }
                        });
                    });
                Document::where('state_type_id', '05')->where('customer_id', $id)->where('document_type_id', '07')->whereMonth('date_of_issue', $month_d)
                    ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$nc_total) {

                        $nc_total += $rows->sum(function ($row) {
                            if ($row->currency_type_id !==  "PEN") {
                                return $row->total * $row->exchange_rate_sale;
                            } else {
                                return $row->total;
                            }
                        });
                    });
                Document::where('state_type_id', '05')->where('customer_id', $id)->where('document_type_id', '03')->whereMonth('date_of_issue', $month_d)
                    ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$b_total) {


                        $b_total += $rows->sum(function ($row) {
                            if ($row->currency_type_id !==  "PEN") {
                                return $row->total * $row->exchange_rate_sale;
                            } else {
                                return $row->total;
                            }
                        });
                    });
                SaleNote::where('state_type_id', '01')->where('customer_id', $id)->whereMonth('date_of_issue', $month_d)
                    ->whereYear('date_of_issue', $year_d)->chunk(50, function ($rows) use (&$n_total) {



                        $n_total += $rows->sum(function ($row) {
                            if ($row->currency_type_id !==  "PEN") {
                                return $row->total * $row->exchange_rate_sale;
                            } else {
                                return $row->total;
                            }
                        });
                    });

                $results[] = [
                    'month' => $d_month,
                    'f' => $f_total + $nd_total - $nc_total,
                    'b' => $b_total,
                    'n' => $n_total,
                ];
            }

            return [
                "success" => true,
                "result" => $results
            ];
        }

        return ["success" => false];
    }
    public function detail_customer($number)
    {
        $customer = Person::where('type', 'customers')->where('number', $number)->with(['person_type', 'seller'])->first();


        return compact('customer');
    }
    public function index()
    {

        return view('report::customers.index');
    }

    public function records(Request $request)
    {
        $model = Document::class;
        $type = $request->type;
        if ($type == 'sale_note') {
            $model = SaleNote::class;
        } else if ($type == 'quotation') {
            $model = Quotation::class;
        }

        $records = $this->getRecordsCustomers($request->all(), $model);

        if ($type == 'sale_note') {
            return new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        } else if ($type == 'quotation') {
            return new QuotationCollection($records->paginate(config('tenant.items_per_page')));
        } else {
            return new DocumentCollection($records->paginate(config('tenant.items_per_page')));
        }
    }



    public function getRecordsCustomers($request, $model)
    {

        // dd($request['period']);
        $document_type_id = $request['document_type_id'];
        $establishment_id = $request['establishment_id'];
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $person_id = $request['person_id'];
        $type_person = $request['type_person'];

        $d_start = null;
        $d_end = null;
        /** @todo: Eliminar periodo, fechas y cambiar por

        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                // $d_end = Carbon::parse($month_end.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                // $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        $records = $this->dataCustomers($document_type_id, $establishment_id, $d_start, $d_end, $person_id, $type_person, $model);

        return $records;
    }


    private function dataCustomers($document_type_id, $establishment_id, $date_start, $date_end, $person_id, $type_person, $model)
    {
        
        $data = $model::whereBetween('date_of_issue', [$date_start, $date_end])
            ->where('customer_id', $person_id);

            if($model == Document::class){
                $data = $data->whereIn('document_type_id', ['01', '03']);
            }

            $data = $data->whereIn('state_type_id', ['01', '03', '05', '07', '13', '11'])
            ->latest()
            ->whereTypeUser();

        return $data;
    }



    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $type = $request->model;
        $model = Document::class;
        if ($type == 'sale_note') {
            $model = SaleNote::class;
        } else if ($type == 'quotation') {
            $model = Quotation::class;
        }

        $records = $this->getRecordsCustomers($request->all(),$model)->get();

        return (new CustomerExport)
            ->records($records)
            ->company($company)
            ->type($type)
            ->establishment($establishment)
            ->download('Reporte_Ventas_por_Cliente_' . Carbon::now() . '.xlsx');
    }
}
