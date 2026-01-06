<?php

namespace Modules\Restaurant\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Tenant\Box;
use App\Models\Tenant\Cash;
use App\Models\Tenant\User;
use App\Imports\ItemsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Models\Tenant\Company;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant\CashDocument;
use App\Models\Tenant\DocumentItem;
use App\Http\Controllers\Controller;
use App\Models\Catalogs\UnitType;
use App\Models\Tenant\PaymentMethodType;
use App\Http\Requests\CashRequest;
use App\Http\Resources\CashResource;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Http\Resources\CashCollection;





class CashController extends Controller
{
    public function index()
    {
        $userid = auth()->user()->id;
        return view('restaurant::cash.index', compact('userid'));
    }

    public function columns()
    {
        return [
            'income' => 'Ingresos',
            // 'expense' => 'Egresos',
        ];
    }

    public function records(Request $request)
    {
        $records = Cash::where($request->column, 'like', "%{$request->value}%")
            ->whereTypeUser()
            ->orderBy('date_opening', 'desc');


        return new CashCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function create()
    {
        return view('tenant.items.form');
    }

    public function tables()
    {
        $user = auth()->user();
        $type = $user->type;
        $users = array();

        switch ($type) {
            case 'admin':
                $users = User::where('type', 'seller')->get();
                $users->push($user);
                break;
            case 'seller':
                $users = User::where('id', $user->id)->get();
                break;
        }

        return compact('users', 'user');
    }

    public function opening_cash()
    {

        $cash = Cash::where([['user_id',  User::getUserCashId()], ['state', true]])->first();

        return compact('cash');
    }

    public function opening_cash_check($user_id)
    {
        $cash = Cash::where([['user_id', $user_id], ['state', true]])->first();
        return compact('cash');
    }


    public function record($id)
    {
        $record = new CashResource(Cash::findOrFail($id));

        return $record;
    }

    public function store(CashRequest $request)
    {

        $id = $request->input('id');
        $cash = Cash::firstOrNew(['id' => $id]);
        $cash->fill($request->all());
        $cash->date_opening = \Carbon\Carbon::parse($request->ddate_openingate)->format('Y-m-d');
        if (!$id) {
            $cash->time_opening = date('H:i:s');
        }

        $cash->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Caja actualizada con éxito' : 'Caja aperturada con éxito'
        ];
    }

    public function close($id, $final_balance)
    {

        $cash = Cash::findOrFail($id);
        $cash->final_balance = $final_balance;
        // // $cash->date_closed = date('Y-m-d');
        // // $cash->time_closed = date('H:i:s');

        // $d_start =$cash->date_opening;
        // $d_end = date('Y-m-d');
        // $efectivo = Box::where('type', '1')->where('method', 'Efectivo')->where('expenses',0)->where('incomes',0)->where('state',0)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // $gastos = Box::where('type', '2')->whereBetween('date', [$d_start, $d_end])->where('expenses',1)->where('state',0)->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // $transferencia = Box::where('type', '1')->where('method', 'Transferencia')->where('expenses',0)->where('incomes',0)->where('state',0)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // $depositos = Box::where('type', '1')->where('method', 'Deposito Bancario')->where('expenses',0)->where('incomes',0)->where('state',0)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // $tarjeta = Box::where('type', '1')->where('method', 'Tarjeta')->where('expenses',0)->where('incomes',0)->where('state',0)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // $yape = Box::where('type', '1')->where('method', 'Yape')->where('expenses',0)->where('incomes',0)->where('state',0)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // $plin = Box::where('type', '1')->where('method', 'PLIN')->where('expenses',0)->where('incomes',0)->where('state',0)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // $otros_ingresos = Box::where('type', '1')->where('method', 'Efectivo')->where('incomes',1)->where('state',0)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->OrderBy('date', 'asc')->sum('amount');
        // // $cash->final_balance = round(($efectivo+$transferencia+$depositos+$tarjeta+$yape+$plin+$otros_ingresos)-$gastos, 2);
        // // $cash->income = round($otros_ingresos, 2);
        // // $cash->state = false;
        $cash->save();
        // $close_boxe = Box::where('state',1)->whereBetween('date', [$d_start, $d_end])->where('user_id',auth()->user()->id)->get();
        // foreach ($close_boxe as $key => $value) {
        //     $box= Box::findOrFail($value->id);
        //     $box->close = date('Y-m-d');
        //     $box->state = 0;
        //     $box->save();
        // }

        return [
            'success' => true,
            'message' => 'Caja cerrada con éxito',
        ];
    }


    public function cash_document(Request $request)
    {

        $cash = Cash::where([['user_id', User::getUserCashId()], ['state', true]])->first();
        $cash->cash_documents()->create($request->all());


        return [
            'success' => true,
            'message' => 'Venta con éxito',
        ];
    }


    public function destroy($id)
    {
        $cash = Cash::findOrFail($id);

        if ($cash->global_destination->count() > 0) {
            return [
                'success' => false,
                'message' => 'No puede eliminar la caja, tiene transacciones relacionadas'
            ];
        }

        $cash->delete();

        return [
            'success' => true,
            'message' => 'Caja eliminada con éxito'
        ];
    }


    public function report($cash)
    {

        $cash = Cash::findOrFail($cash);
        $company = Company::first();

        $methods_payment = collect(PaymentMethodType::all())->transform(function ($row) {
            return (object)[
                'id' => $row->id,
                'name' => $row->description,
                'sum' => 0
            ];
        });

        set_time_limit(0);

        $pdf = PDF::loadView('tenant.cash.report_pdf', compact("cash", "company", "methods_payment"));

        $filename = "Reporte_POS - {$cash->user->name} - {$cash->date_opening} {$cash->time_opening}";

        return $pdf->stream($filename . '.pdf');
    }

    public function report_general()
    {
        $cashes = Cash::select('id')->whereDate('date_opening', date('Y-m-d'))->pluck('id');
        $cash_documents =  CashDocument::whereIn('cash_id', $cashes)->get();

        $company = Company::first();
        set_time_limit(0);

        $pdf = PDF::loadView('tenant.cash.report_general_pdf', compact("cash_documents", "company"));
        $filename = "Reporte_POS";
        return $pdf->download($filename . '.pdf');
    }

    public function report_products($id)
    {
        $cash = Cash::findOrFail($id);
        $company = Company::first();
        $cash_documents =  CashDocument::select('document_id')->where('cash_id', $cash->id)->get();

        $source = DocumentItem::with('document')->whereIn('document_id', $cash_documents)->get();

        $documents = collect($source)->transform(function ($row) {
            return [
                'id' => $row->id,
                'number_full' => $row->document->number_full,
                'description' => $row->item->description,
                'quantity' => $row->quantity,
            ];
        });

        $pdf = PDF::loadView('tenant.cash.report_product_pdf', compact("cash", "company", "documents"));

        $filename = "Reporte_POS_PRODUCTOS - {$cash->user->name} - {$cash->date_opening} {$cash->time_opening}";

        return $pdf->stream($filename . '.pdf');
    }
}
