<?php

namespace Modules\Restaurant\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tenant\Box;
use App\Models\Tenant\Cash;
use App\Models\Tenant\User;
use App\Models\Tenant\Group;
use App\Models\Order;
use App\Models\Tenant\Company;
use App\Models\Tenant\Category;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\PaymentMethodType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\Area;
use Modules\Restaurant\Models\Orden;
use App\Http\Resources\BoxCollection;
use Modules\Report\Exports\BoxesExport;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Report\Exports\BoxesExportPos;
use Modules\Restaurant\Models\WorkersType;
use Modules\Report\Exports\BoxesResumenExportPos;
use Modules\Report\Exports\BoxesExportBancarioPos;
use Modules\Dashboard\Helpers\DashboardSalePurchase;

class BoxesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        //$date_start=Cash::where('user_id',auth()->user()->id)->get()->last();

        $workersType = WorkersType::where('description', 'like', '%caja%')->first()->id;
        if ($user->type == 'admin') {
            $users = User::where('type', 'admin')->orWhere('worker_type_id', $workersType)->get();
        } else {
            $users = User::where('worker_type_id', $workersType)->get();
        }
        return view('restaurant::boxes', compact('users'));
    }
    public function tables()
    {
        $gruop = Group::all();
        $category = Category::all();
        $subcategory = Subcategory::all();
        $company = Company::first();
        $methods = PaymentMethodType::all();
        return compact('gruop', 'category', 'subcategory', 'company', 'methods');
    }
    public function reports_categoria_type(Request $request)
    {
        $group_id = $request['group_id'];
        $category_id = $request['category_id'];
        $subcategory_id = $request['subcategory_id'];
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $user_id = $request['user_id'];
        $d_start = null;
        $d_end = null;
        $array_data = [
            "ingresos" => [],
            "egresos" => []
        ];
        $user = User::find($user_id);
        $categories = Category::all();
        $subcategories = Subcategory::all();
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $data1 = Box::where('method', 'Efectivo')
            ->whereBetween('date', [$d_start, $d_end])->orderBy('date', 'asc')->orderBy('type', 'asc')->get();

        if ($user_id != null) {
            $data1 = $data1->where('user_id', $user_id);
        }
        //dd($request->all(),$data1);
        $data_by_group = $data1->groupBy('group_id');

        $records = [];

        foreach ($data_by_group as $group_id => $records_by_group) {
            $data_by_category = $records_by_group->groupBy('category_id');
            $categories = [];
            $group_name = '';
            foreach ($data_by_category as $category_id => $records_by_category) {
                //dd('1');
                $data_by_subcategory = $records_by_category->groupBy('subcategory_id');
                //dd($records_by_category->groupBy('subcategory_id'));
                $subcategories = [];
                $category_name = '';
                foreach ($data_by_subcategory as $subcategory_id => $rows) {
                    $rows_data = [];
                    $subcategory_name = '';
                    foreach ($rows as $row) {
                        $group_name = $row->groups->group;
                        $category_name = $row->categories->category;
                        $subcategory_name = $row->subcategories->subcategory;
                        if ($row->sale_note_id != null) {
                            $customer = $row->salenote->customer->name;
                        } else {
                            $customer = "";
                        }
                        if ($row->document_id != null) {
                            $customer = $row->document->customer->name;
                        } else {
                            $customer = "";
                        }
                        $rows_data[] = [
                            'id' => $row->id,
                            'description' => $row->description,
                            'type' => $row->type,
                            'sale_note_id' => $row->sale_note_id,
                            'document_id' => $row->document_id,
                            'method' => $row->method,
                            'customer_salenote' => ($row->sale_note_id == null) ? "-" : $row->salenote->customer->name,
                            'customer_document' => ($row->document_id == null) ? "-" : $row->document->customer->name,
                            'date' => Carbon::parse($row->date)->format('d-m-Y') . " " . Carbon::parse($row->created_at)->format('H:m:s'),
                            'monto' => $row->amount,
                            'user' => $row->user->name
                        ];
                    }
                    $subcategories[] = [
                        'subcategory_id' => $subcategory_id,
                        'subcategory_name' => $subcategory_name,
                        'rows' => $rows_data
                    ];
                }
                $categories[] = [
                    'category_id' => $category_id,
                    'category_name' => $category_name,
                    'subcategories' => $subcategories
                ];
            }
            $records[] = [
                'group_id' => $group_id,
                'group_name' => $group_name,
                'categories' => $categories
            ];
        }


        //$result=$data1->groupBy('group_id')->groupBy('category_id')->groupBy('subcategory_id')[''];
        //return $result;

        $pdf = PDF::loadView('report::boxes.report_category_pos', compact("user", "records", "company", "establishment", "date_start", "date_end"))->setPaper('a4', 'landscape');
        return $pdf->stream('Reporte_Ventas_' . date('YmdHis') . '.pdf');
    }
    public function balance_final($date_closed)
    {

        $cash = Cash::where('date_closed', $date_closed)->where('user_id', auth()->user()->id)->get()->last();
        $data1 = Box::where('type', '1')->where('method', 'Efectivo')->where('expenses', 0)->where('incomes', 0)->where('state', 1)->where('date', $date_closed)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');
        $data2 = Box::where('type', '2')->where('date', $date_closed)->where('expenses', 1)->where('state', 1)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');
        $data3 = Box::where('type', '1')->where('method', 'Transferencia')->where('expenses', 0)->where('incomes', 1)->where('state', 1)->where('date', $date_closed)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');
        $data4 = Box::where('type', '1')->where('method', 'Deposito Bancario')->where('expenses', 0)->where('incomes', 1)->where('state', 1)->where('date', $date_closed)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');
        $data5 = Box::where('type', '1')->where('method', 'Tarjeta')->where('expenses', 0)->where('incomes', 0)->where('state', 1)->where('date', $date_closed)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');
        $data6 = Box::where('type', '1')->where('method', 'Yape')->where('expenses', 0)->where('incomes', 0)->where('state', 1)->where('date', $date_closed)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');
        $data7 = Box::where('type', '1')->where('method', 'PLIN')->where('expenses', 0)->where('incomes', 0)->where('state', 1)->where('date', $date_closed)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');
        $dataingresos_all = Box::where('type', '1')->where('method', 'Efectivo')->where('incomes', 1)->where('state', 1)->where('date', $date_closed)->where('user_id', auth()->user()->id)->OrderBy('date', 'asc');

        $row_ingresos = $data1->get();

        // if ($request->type_box == "1") {
        $data_ingresos = $data1->sum('amount');

        $data_ingresos_all = $dataingresos_all->sum('amount');

        $row_egresos = $data2->sum('amount');
        $row_transferencia = $data3->sum('amount');

        $row_depositos = $data4->sum('amount');

        $row_tarjetas = $data5->sum('amount');

        $row_yape = $data6->sum('amount');

        $row_plin = $data7->sum('amount');

        if ($row_egresos != null || $row_egresos != 0.0) {
            $data_egresos = $data2->sum('amount');
        } else {
            $data_egresos = "0.00";
        }


        // $row_cierre = Box::where('state', '0')->get()->last();
        // $row_apertura = Cash::orderBy('state')->get()->last();

        if ($row_transferencia != null || $row_transferencia != 0.0) {
            $transferencias = $data3->sum('amount');
        } else {
            $transferencias = "0.00";
        }

        if ($row_depositos != null) {
            $depositos = $row_depositos->depositos;
        } else {
            $depositos = "0.00";
        }

        if ($row_tarjetas != null) {
            $tarjetas = $row_tarjetas;
        } else {
            $tarjetas = "0.00";
        }

        if ($row_yape != null) {
            $yape = $row_yape;
        } else {
            $yape = "0.00";
        }
        if ($row_plin != null) {
            $plin = $row_plin;
        } else {
            $plin = "0.00";
        }
        $balance_total = ($data_ingresos + $yape + $plin + $transferencias + $tarjetas + $data_ingresos_all + $depositos) - $data_egresos;
        return compact('balance_total');
    }
    public function paymentorden($type, $id, $orderId)
    {

        $Orden = Orden::FindOrFail($orderId);
        if ($type == "80") {
            $saleNote = SaleNote::where('id', $id)->first();
            $Orden->sale_note_id = $saleNote->id;
            $Orden->status_orden_id = 4;
            $Orden->customer_id = $saleNote->customer_id;
            $Orden->save();
        }
        if ($type == "01") {
            $document = Document::where('id', $id)->first();
            $Orden->document_id = $document->id;
            $Orden->status_orden_id = 4;
            $Orden->customer_id = $document->customer_id;
            $Orden->save();
        }
        return [
            "success" => true,
            "message" => "Se genero con  exito"
        ];
    }
    public function listfoods($date)
    {
        // $items=$this->list_food_sales($request);

        $data_item = DB::table('orden_item')->select(DB::raw("DISTINCT(orden_item.food_id) as food_id"))->where('date', $date)->get();
        $items = [];
        foreach ($data_item as $key => $data) {
            $orden_item = OrdenItem::where('food_id', $data->food_id)->where('date', $date);
            $quantity_total = $orden_item->sum('quantity');
            array_push($items, [
                'id' => $orden_item->first()->food->id,
                'description' => $orden_item->first()->food->description,
                'move_quantity' => $quantity_total,
                'price' => $orden_item->first()->food->price,
                'total' => $orden_item->first()->food->price * $quantity_total,
            ]);
        }
        $items = collect($items);
        return $items;
        return [
            "success" => true,
            "data"    => $items,
        ];
    }
    public function reports_resumen_type(Request $request)
    {
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $user_id = $request['user_id'];
        $type_box = $request['type_box'];
        $request->request->add(['establishment_id' => auth()->user()->establishment_id]);
        $request->request->add(['enabled_move_item' => false]);
        $request->request->add(['enabled_transaction_customer' => false]);
        $d_start = null;
        $d_end = null;
        $user = User::find($user_id);

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }
        $time_closed = date('H:i:s');
        $caja = Box::where('date', $date_start)
            ->where('user_id', auth()->user()->id)
            ->update(['state' => '0', 'close' => $date_start]);
        $cash_open = Cash::where('date_opening', $date_start)->where('user_id', auth()->user()->id)->get()->last();

        $cash_close = Cash::where('date_opening', $cash_open->date_opening)
            ->where('state', 1)
            ->where('user_id', auth()->user()->id)
            ->update(['state' => '0', 'date_closed' => $date_start, 'time_closed' => $time_closed]);

        $date = \Carbon\Carbon::now();
        $hoy = $date->format('Y-m-d');

        $ordens = Orden::whereBetween('date', [$d_start, $d_end])->OrderBy('date', 'asc')->get();
        foreach ($ordens as $row) {
            if ($row->sale_note_id != null) {
                $saleNotes = SaleNote::where('orden_id', $row->id);
                $conteo = $saleNotes->count() - 1;
                $num = 0;
                if ($saleNotes->count() > 1) {
                    foreach ($saleNotes->get() as  $row) {
                        $num++;
                        $row->delete();
                        if ($conteo == $num) {
                            break;
                        }
                    }
                }
            }
            if ($row->document_id != null) {
                $document = Document::where('orden_id', $row->id);
                $conteo = $document->count() - 1;
                $num = 0;
                if ($document->count() > 1) {
                    foreach ($document->get() as  $row) {
                        $num++;
                        $row->delete();
                        if ($conteo == $num) {
                            break;
                        }
                    }
                }
            }
            $boxes = Box::where('orden_id', $row->id);
            $conteo = $boxes->count() - 1;
            $num = 0;
            if ($boxes->count() > 1) {
                foreach ($boxes->get() as  $row_box) {
                    $num++;
                    $row_box->delete();
                    if ($conteo == $num) {
                        break;
                    }
                }
            }
        }

        $data_result = collect(DB::table('boxes')->whereBetween('date', [$d_start, $d_end])->where('user_id', auth()->user()->id)->OrderBy('date', 'asc')->get());
        $expenses = $data_result->where('type', '2')->where('expenses', 1);
        $data1 = $data_result->where('method', 'Efectivo')->where('expenses', 0)->where('incomes', 0)->where('state', 0);
        $data2 = $data_result->where('type', '2')->where('expenses', 1)->where('state', 0);
        $data3 = $data_result->where('type', '1')->where('method', 'Transferencia')->where('expenses', 0)->where('incomes', 0)->where('state', 0);
        $data4 = $data_result->where('type', '1')->where('method', 'Deposito Bancario')->where('expenses', 0)->where('incomes', 0)->where('state', 0);
        $data5 = $data_result->where('type', '1')->where('method', 'Tarjeta')->where('expenses', 0)->where('incomes', 0)->where('state', 0);
        $data6 = $data_result->where('type', '1')->where('method', 'Yape')->where('expenses', 0)->where('incomes', 0);
        $data7 = $data_result->where('type', '1')->where('method', 'PLIN')->where('expenses', 0)->where('incomes', 0)->where('state', 0);
        $dataingresos_all = $data_result->where('type', '1')->where('method', 'Efectivo')->where('incomes', 1)->where('state', 0);
        $row_ingresos = $data1;
        // if ($request->type_box == "1") {
        $data_ingresos = $data1->sum('amount');
        $data_ingresos_all = $dataingresos_all->sum('amount');
        $row_egresos       = $data2->sum('amount');
        $row_transferencia = $data3->sum('amount');
        $row_depositos     = $data4->sum('amount');
        $row_tarjetas      = $data5->sum('amount');
        $row_yape          = $data6->sum('amount');
        $row_plin          = $data7->sum('amount');

        if ($request->type_box == "2") {
            if ($row_egresos != null || $row_egresos != 0.0) {
                $data_egresos = $data2->sum('amount');
            } else {
                $data_egresos = "0.00";
            }
        } else {
            $data_egresos = "0.00";
        }
        if ($request->type_box == null) {
            if ($row_egresos != null || $row_egresos != 0.0) {
                $data_egresos = $data2->sum('amount');
            } else {
                $data_egresos = "0.00";
            }
        }
        //dd($request->final_balance);

        $row_cierre = Box::where('state', '0')->get()->last();
        $row_apertura = Cash::orderBy('state')->get()->last();
        $fecha_apertura = $row_apertura->date_opening . " " . $row_apertura->created_at->format('h:m:s');
        $cash = Cash::where('date_closed',)->where('user_id', auth()->user()->id)->get()->last();

        $fecha_cierre = '';
        if ($row_cierre && $row_apertura) {

            $fecha_cierre = $row_cierre->close . " " . $row_cierre->created_at->format('h:m:s');
        }

        if ($row_transferencia != null || $row_transferencia != 0.0) {
            $transferencias = $data3->sum('amount');
        } else {
            $transferencias = "0.00";
        }

        if ($row_depositos != null) {
            $depositos = $row_depositos->depositos;
        } else {
            $depositos = "0.00";
        }

        if ($row_tarjetas != null) {
            $tarjetas = $row_tarjetas;
        } else {
            $tarjetas = "0.00";
        }

        if ($row_yape != null) {
            $yape = $row_yape;
        } else {
            $yape = "0.00";
        }
        if ($row_plin != null) {
            $plin = $row_plin;
        } else {
            $plin = "0.00";
        }

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;


        $items = $this->list_food_sales($request);
        //dd($items);
        $saldo = 0;

        if ($request['type'] == "pdf") {
            $pdf = PDF::loadView('report::boxes.report_resumen_pdf_pos', compact("type_box", 'yape', "plin", "items", "expenses", "cash", "data_ingresos_all", "data_ingresos", "yape", "plin", "data_egresos", "establishment", "date_start", "date_end", "company", "fecha_apertura", "fecha_cierre", "transferencias", "depositos", "tarjetas", "user"))->setPaper('a4', 'landscape');
            return $pdf->download('Resumen_Arqueo_Caja_' . date('Y-m-d') . '.pdf');
        } else if ($request['type'] == "excel") {

            return (new BoxesResumenExportPos)
                ->records($data_ingresos)
                ->records2($data_egresos)
                ->records3($transferencias)
                ->records4($depositos)
                ->records4($tarjetas)
                ->records4($yape)
                ->records4($plin)
                ->company($company)
                ->user($user)
                ->establishment($establishment)
                ->download('Reporte_arqueo_resumen_' . Carbon::now() . '.xlsx');
        }
    }
    public function list_food_sales(Request $request)
    {

        $data_item = DB::table('orden_item')->select(DB::raw("DISTINCT(orden_item.food_id) as food_id"))->where('date', $request['date_start'])->get();

        $items = [];
        foreach ($data_item as $key => $data) {

            $orden = OrdenItem::where('food_id', $data->food_id)->where('date', $request['date_start']);
            $importe_total = 0;
            foreach ($orden->get() as $row) {
                //  $price[]=$row->price;
                $importe_total += $row->price * $row->quantity;
            }
            //$price_mayor=min($price);
            $quantity_total = $orden->sum('quantity');

            array_push($items, [
                'id' => $orden->first()->food->id,
                'description' => $orden->first()->food->description,
                'move_quantity' => $quantity_total,
                'total' => $importe_total,
            ]);
        }
        $items = collect($items);
        return $items;
    }
    public function reports_type(Request $request)
    {

        $group_id = $request['group_id'];
        $category_id = $request['category_id'];
        $subcategory_id = $request['subcategory_id'];
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $user_id = $request['user_id'];
        $user = User::find($user_id);
        $d_start = null;
        $d_end = null;
        $type_box = $request['type_box'];;
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');

                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }
        // Box::whereBetween('date', [$date_start, $date_end])
        //->OrderBy('sale_note_id')->OrderBy('document_id')
        //latest
        $data = Box::whereBetween('date', [$d_start, $d_end])->where('method', 'Efectivo')->latest();
        //  dd($data);
        if ($user_id) {
            $data =  $data->where('user_id', $user_id);
        }
        if ($type_box) {
            $data =  $data->where('type', $type_box);
        }

        $boxes_report = new BoxCollection($data->get());

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $saldo = 0;
        if ($request['type'] == "pdf") {
            $pdf = PDF::loadView('report::boxes.report_pdf', compact("user", "boxes_report", "establishment", "date_start", "date_end", "company", "type_box"))->setPaper('a4', 'landscape');
            return $pdf->stream('Reporte_Ventas_' . date('YmdHis') . '.pdf');
        } else if ($request['type'] == "excel") {

            return (new BoxesExport)
                ->records($data->get())
                ->company($company)
                ->establishment($establishment)
                ->download('Reporte_arqueo_caja_' . Carbon::now() . '.xlsx');
        }
    }
    public function reports_bancario_type(Request $request)
    {
        $group_id = $request['group_id'];
        $category_id = $request['category_id'];
        $subcategory_id = $request['subcategory_id'];
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $user_id = $request['user_id'];
        $type_box = $request['type_box'];
        $d_start = null;
        $d_end = null;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        $data = Box::whereBetween('date', [$d_start, $d_end])->where('amount', '>', "0.00")->where('type', '1')->orderBy('date', 'asc')->latest();
        $data = $data->where('method', '!=', "Efectivo")->Where('method', '!=', 'Credito')->orderBy('date', 'asc')->latest();

        if ($user_id) {
            $data =  $data->where('user_id', $user_id);
        }
        $boxes_report = new BoxCollection($data->get());

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $saldo = 0;
        if ($request['type'] == "pdf") {
            $pdf = PDF::loadView('report::boxes.report_bancario_pdf_pos', compact("boxes_report", "establishment", "company", "date_start", "date_end"))->setPaper('a4', 'landscape');
            return $pdf->stream('Reporte_Ventas_' . date('YmdHis') . '.pdf');
        } else if ($request['type'] == "excel") {

            return (new BoxesExportBancarioPos)
                ->records($data->get())
                ->company($company)
                ->type_box($type_box)
                ->establishment($establishment)
                ->download('Reporte_arqueo_caja_' . Carbon::now() . '.xlsx');
        }
    }
    public function reports_results(Request $request)
    {
        $group_id = $request['group_id'];
        $category_id = $request['category_id'];
        $subcategory_id = $request['subcategory_id'];
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $user_id = $request['user_id'];
        $d_start = null;
        $d_end = null;
        $type_box = $request['type_box'];;
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');

                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');

                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_end;
                // $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }
        $data = Box::whereBetween('date', [$d_start, $d_end])->where('amount', '>', "0.00")->OrderBy("date", "asc")->OrderBy("type", "desc")->latest();
        if ($type_box) {
            $data =  $data->where('type', $type_box);
        }
        if ($user_id) {
            $data =  $data->where('user_id', $user_id);
        }
        return new BoxCollection($data->paginate(config('tenant.items_per_page')));
    }

    public function report()
    {
        $user = auth()->user();
        $caja_area_id = Area::where('description', 'like', '%aja%')->first()->id;
        if ($user->type == 'admin') {
            $users = User::where('type', 'admin')->orWhere('area_id', $caja_area_id)->get();
        } else {
            $users = User::where('id', $user->id)->orWhere('area_id', $caja_area_id)->get();
        }
        return view('restaurant::report.index', compact('users'));
    }
}
