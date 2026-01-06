<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\DocumentHotelExport;
use Illuminate\Http\Request;
use Modules\Report\Traits\ReportTrait;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Company;
use App\Models\Tenant\{
    Document,
    SaleNote,
    Dispatch
};
use Carbon\Carbon;
use Modules\Report\Traits\MassiveDownloadTrait;

class ReportMassiveDownloadController extends Controller
{

    use ReportTrait, MassiveDownloadTrait;

    public function index()
    {
        return view('report::massive-downloads.index');
    }


    public function filter()
    {

        $document_types = DocumentType::whereIn('id', ['01', '03', '80', '09'])->get();
        $sellers = $this->getSellers();
        $series = $this->getSeries($document_types);

        $persons = $this->getPersons('customers');

        return compact('document_types', 'persons', 'sellers', 'series');
    }


    public function records(Request $request)
    {

        $params = json_decode($request->form);
        $document_types = $params->document_types;

        if (count($document_types) == 0) {
            $document_types = ['all'];
        }

        return [
            'total' => $this->getTotals($document_types, $params)
        ];
    }


    public function pdf(Request $request)
    {

        //dd($request->all());
        $array = json_decode($request->form, true);
        $params = json_decode($request->form);

        $document_types = $params->document_types;

        if (count($document_types) == 0) {
            $document_types = ['all'];
        }
        $height = isset($array['height']) ? $array['height'] : 'a4';

        $data = $this->getData($document_types, $params);
        $view =  $this->createPdf($data, $height, $array);

        return $this->toPrintByView('massive_downloads', $view);
    }

    
    public function document_pdf(Request $request)
    {

        $params = $request->all();

        $document_types = ["03"];

    
        $height = $params['height'];
        $object_params = (object)$params;
        $object_params->person_id = null;
        $object_params->seller_id = null;
        $object_params->series = null;
        $object_params->date_start = null;
        $object_params->date_end = null;
        $object_params->sellers = null;
        $data = $this->getData($document_types, $object_params);
        $view =  $this->createPdf($data, $height, $params);

        return $this->toPrintByView('massive_downloads', $view);
    }
}
