<?php

namespace App\Http\Controllers\Tenant\Api;

use App\CoreFacturalo\Requests\Inputs\Functions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tenant\{
    Quotation,
    Company,
    Series
};
use App\Http\Resources\Tenant\QuotationCollection;
use App\Http\Controllers\Tenant\QuotationController as QuotationControllerWeb;
use App\Mail\Tenant\QuotationEmail;
use App\Http\Controllers\Tenant\EmailController;
use Illuminate\Support\Facades\Log;
use Modules\Document\Models\SeriesConfiguration;

class QuotationController extends Controller
{
    protected $quotation;
    /**
     * 
     * Listado de cotizaciones (app)
     *
     * @param  Request $request
     * @return array
     */
    public function list(Request $request)
    {
        // $records = Quotation::orderBy('prefix', 'desc')->take(50)->get();
        // $records = new QuotationCollection($records); // crear nuevo collection para apis

        $records = Quotation::where('id','like', "%{$request->input}%")
                            ->take(config('tenant.items_per_page'))
                            ->latest()
                            ->get();

        return new QuotationCollection($records);
    }


    public function store(Request $request)
    {
        $request['establishment_id'] = $request['establishment_id'] ? $request['establishment_id'] : auth()->user()->establishment_id;

         DB::connection('tenant')->transaction(function () use ($request) {
            $quotation_web = new QuotationControllerWeb;
            $data = $quotation_web->mergeData($request);
            
            $series = Functions::valueKeyInArray($data, "prefix", null);
            $series_id = Functions::valueKeyInArray($data, "series_id", null);
            $data['terms_condition'] = $quotation_web->getTermsCondition();
            if ($series_id) {
                $series_configuration = Series::find($series_id);
                $data["prefix"] = $series_configuration->number;
            }
            if (
                Quotation::where('prefix', $data["prefix"])
                ->count() == 0 && $series
            ) {
                $series_configuration = SeriesConfiguration::where([['document_type_id', "COT"], ['series', $series]])->first();
                if ($series_configuration) {
                    $number = $series_configuration->number ?? 1;
                    $data["id"] = $number;
                    $data["number"] = $number;
                }
            }
            $number = Functions::valueKeyInArray($data, "number", null);
            if (!$number || $number == "#") {
                
                //get last id from table
                $last_id = Quotation::where('prefix', $data["prefix"])->orderBy('id', 'desc')->first();
                if ($last_id) {
                    if ($last_id->number) {
                        $data["number"] = $last_id->number + 1;
                    } else {
                        $data["number"] = $last_id->id + 1;
                    }
                }else{
                    $data["number"] = 1;
                }
            }
            $this->quotation =  Quotation::create($data);

            foreach ($data['items'] as $row) {
                $this->quotation->items()->create($row);
            }

            $quotation_web->savePayments($this->quotation, $data['payments']);
            $fee = Functions::valueKeyInArray($data, 'fee', []);
            $this->saveFees($this->quotation, $fee);
            $this->setFilename();
            $quotation_web->createPdf($this->quotation, "a4", $this->quotation->filename);
        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->quotation->id,
                'number_full' => $this->quotation->number_full,
                'external_id' => $this->quotation->external_id,
                'filename' => $this->quotation->filename,
                'print_a4'    => url('')."/quotations/print/{$this->quotation->external_id}/a4",
                'print_ticket' => $this->quotation->getUrlPrintPdf('ticket'),
            ],
        ];
    }
    public function saveFees($quotation, $fees)
    {
        foreach ($fees as $row) {
            $quotation->fee()->create($row);
        }
    }
    private function setFilename(){

        $name = [$this->quotation->prefix,$this->quotation->id,date('Ymd')];
        $this->quotation->filename = join('-', $name);
        $this->quotation->save();

    }

    
    /**
     *
     * @param  Request $request
     * @return array
     */
    public function email(Request $request)
    {
        $company = Company::active();
        $quotation = Quotation::find($request->id);
        $email = $request->input('email');
        $mailable =  new QuotationEmail($company, $quotation);
        $id = (int) $request->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 3);

        return [
            'success' => true,
            'message' => 'Email enviado correctamente.'
        ];
    }

}
