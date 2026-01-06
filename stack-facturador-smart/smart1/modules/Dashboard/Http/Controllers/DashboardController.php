<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Exports\AccountsReceivable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Dashboard\Helpers\DashboardData;
use Modules\Dashboard\Helpers\DashboardUtility;
use Modules\Dashboard\Helpers\DashboardSalePurchase;
use Modules\Dashboard\Helpers\DashboardView;
use Modules\Dashboard\Helpers\DashboardStock;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Document;
use App\Models\Tenant\Company;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;
use Modules\Dashboard\Helpers\DashboardInventory;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\SunatPurchaseSale;
use Carbon\Carbon;
use Modules\Dashboard\Helpers\DashboardSaleSunatPurchase;

/**
 * Class DashboardController
 *
 * @package Modules\Dashboard\Http\Controllers
 * @mixin Controller
 */
class DashboardController extends Controller
{
    public function get_sum_year($year)
    {
        $records = (new DashboardSaleSunatPurchase)->data($year);
        return [
            'records' => $records
        ];
    }
    public function sunat_purchase_sale($year)
    {
        if ($year == null) {
            $year = date('Y');
        }

        $records = SunatPurchaseSale::whereYear('period', $year)->get();

        return compact('records');
    }
    public function save_sunat_purchase_sale(Request $request)
    {
        $periods = $request->input('periods');
        $first_period = $periods[0]['period'];
        $first_period = Carbon::parse($first_period);
        $year = $first_period->year;
        SunatPurchaseSale::whereYear('period', $year)->delete();
        foreach ($periods as $period) {
            $date = Carbon::parse($period['period']);
            $sunat_purchase_sale = new SunatPurchaseSale();
            $sunat_purchase_sale->sunat_sale = $period['sunat_sale'];
            $sunat_purchase_sale->internal_sale = $period['internal_sale'];
            $sunat_purchase_sale->purchase_expense = $period['purchase_expense'];
            $sunat_purchase_sale->period = $date;
            $sunat_purchase_sale->show = $period['show'];
            $sunat_purchase_sale->save();
        }
        return [
            'success' => true,
            'message' => 'Se guardó correctamente'
        ];

    }
    public function index()
    {
        if (!auth()->user()->searchModule('dashboard'))
            return redirect()->route('tenant.documents.index');
        
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::first();

        return view('dashboard::index', compact('soap_company', 'configuration'));
    }
    public function sales_purchases()
    {

        return view('dashboard::dashboard_sales');
    }
    public function filter()
    {
        return [
            'establishments' => DashboardView::getEstablishments()
        ];
    }

    public function globalData(Request $request)
    {
        return response()->json((new DashboardData())->globalData($request), 200);
    }

    public function data(Request $request)
    {
        return [
            'data' => (new DashboardData())->data($request->all()),
        ];
    }

    // public function unpaid(Request $request)
    // {
    //     return [
    //             'records' => (new DashboardView())->getUnpaid($request->all())
    //     ];
    // }

    // public function unpaidall()
    // {

    //     return Excel::download(new AccountsReceivable, 'Allclients.xlsx');

    // }

    public function data_aditional(Request $request)
    {
        return [
            'data' => (new DashboardSalePurchase())->data($request->all()),
        ];
    }

    public function stockByProduct(Request $request)
    {
        return (new DashboardStock())->data($request);
    }


    public function utilities(Request $request)
    {
        return [
            'data' => (new DashboardUtility())->data($request->all()),
        ];
    }

    public function df()
    {
        $path = app_path();
        //df -m -h --output=used,avail,pcent /

        $used = new Process('df -m -h --output=used /');
        $used->run();
        if (!$used->isSuccessful()) {
            return ['error'];
            throw new ProcessFailedException($used);
        }
        $disc_used = $used->getOutput();
        $array[] = str_replace("\n", "", $disc_used);

        $avail = new Process('df -m -h --output=avail /');
        $avail->run();
        if (!$avail->isSuccessful()) {
            return ['error'];
            throw new ProcessFailedException($avail);
        }
        $disc_avail = $avail->getOutput();
        $array[] = str_replace("\n", "", $disc_avail);

        $pcent = new Process('df -m -h --output=pcent /');
        $pcent->run();
        if (!$pcent->isSuccessful()) {
            return ['error'];
            throw new ProcessFailedException($pcent);
        }
        $disc_pcent = $pcent->getOutput();
        $array[] = str_replace("\n", "", $disc_pcent);

        return $array;
    }

    /**
     * Extensión de ventas por producto
     *
     */
    public function salesByProduct()
    {
        return view('dashboard::sales_by_product');
    }

    public function productOfDue(Request $request)
    {
        return (new DashboardInventory())->data($request);
    }
    public function productOfDueLots(Request $request)
    {
        return (new DashboardInventory())->data_lots($request);
    }
    public function topClientes()
{
    $topClientes = DashboardData::getTopClientes(); // Ajusta este método según tu lógica para obtener los top clientes
    return response()->json($topClientes);
}
}
