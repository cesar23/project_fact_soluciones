<?php

namespace Modules\Sire\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sire\Helpers\SireService;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use Carbon\Carbon;
use Modules\Sire\Appendixes\Appendixes;
use Modules\Sire\Export\SireExport;

class SireController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function appendix()
    {
        return view('sire::appendix');
    }
    public function index()
    {
        return view('sire::index');
    }
    function get_appendix_column($appendix)
    {
        switch ($appendix) {
            case 2:
                return 'appendix_2';
                break;
            case 3:
                return 'appendix_3';
                break;
            case 4:
                return 'appendix_4';
                break;
            case 5:
                return 'appendix_5';
                break;
            default:
                return 'appendix_2';
                break;
        }
    }
    public function generate(Request $request)
    {
        $company = Company::active();
        $date = $request->month;
        $date_name = date('Ym', strtotime($date));
        $name = 'LE' . $company->number . $date_name . '00140400021112';
        //00140400021112
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));
        $appendix = $request->appendix;
        $appendix_column = $this->get_appendix_column($appendix);
        $documents = Document::whereMonth('date_of_issue', $month)
            ->whereYear('date_of_issue', $year)
            ->where($appendix_column, true)
            ->get();
        $txt = '';
        foreach ($documents as $key => $document) {
            $appendix_txt = new Appendixes($document, $key + 1, $appendix);
            $txt .= $appendix_txt->generate_txt() . "\n";
        }

        return [
            'success' => true,
            'content' => $txt,
            'name' => $name . '.txt'
        ];
    }


    /*
     * route get sire/{type}/tables
     */
    public function tables($type)
    {
        $sire = new SireService();
        $periods = $sire->getPeriods($type);
        return $periods;
    }

    /*
     * route post sire/configuration/update
     * view company
     */
    public function updateConfig(Request $request)
    {
        $company = Company::first();
        $company->sire_client_id = $request->sire_client_id;
        $company->sire_client_secret = $request->sire_client_secret;
        $company->sire_username = $request->sire_username;
        $company->sire_password = $request->sire_password;
        $company->save();

        return [
            'success' => true,
            'message' => 'SIRE actualizado correctamente'
        ];
    }

    /*
     * route get sire/configuration
     * view company
     */
    public function getConfig()
    {
        $company = Company::select('sire_client_id', 'sire_client_secret', 'sire_username', 'sire_password')->first();

        return [
            'success' => true,
            'data' => $company,
        ];
    }

    /*
     * route get sire/{type}/{period}/ticket
     */
    public function getTicket($type, $period)
    {
        $sire = new SireService();
        return $sire->getTicket($type, $period);
    }

    /*
     * route get sire/{type}/query
     */
    public function queryTicketExcel(Request $request)
    {

        $sire = new SireService();
        $type = $request->type;
        $response = $sire->queryTicket($request->page, $request->period, $request->ticket, $request->type);
        $records = [];
        if ($response['success']) {
            $records = $response['data']['documents'];
        }
        $label = $type == 'sale' ? 'Ventas' : 'Compras';
        return (new SireExport())
            ->records($records)
            ->type($request->type)
            ->download('Reporte_Sire_' . $label . '_' . Carbon::now() . '.xlsx');
    }
    public function queryTicket(Request $request, $type)
    {
        $sire = new SireService();
        $response = $sire->queryTicket($request->page, $request->period, $request->ticket, $type);
        return $response;
    }

    public function accept($type, $period)
    {
        $sire = new SireService();
        $response = $sire->sendAccept($period);
        return $response;
    }
}
