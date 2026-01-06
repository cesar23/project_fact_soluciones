<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\DocumentController as TenantDocumentController;
use App\Models\System\Configuration as SystemConfiguration;
use Facades\App\Http\Controllers\Tenant\DocumentController;
use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Models\Tenant\{
    Company,
    Configuration,
    Document
};
use App\Services\PseService;
use App\Services\PseServiceTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class PseSendDocument extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pse:send';
    protected $company = null;
    protected $token = null;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of PSE documents';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    private function formatNumber($number, $zeros = 8)

    {

        return str_pad($number, $zeros, '0', STR_PAD_LEFT);
    }
    // function format_to_check($documents)
    // {
    //     $xml = new SimpleXMLElement('<NewDataSet></NewDataSet>');
    //     foreach ($documents as $document) {
    //         $table1 = $xml->addChild('Table1');
    //         $table1->addChild('numruc', $this->company->number);
    //         $table1->addChild('altido', $document->document_type_id);
    //         $table1->addChild('sersun', $document->series);
    //         $table1->addChild('numsun',  $this->formatNumber($document->number));
    //     }
    //     $xmlString = $xml->asXML();
    //     $xml_string = str_replace("\n", "", $xmlString);
    //     $startIndex = strpos($xml_string, '<NewDataSet>');
    //     $newDataSetXml = substr($xml_string, $startIndex);
    //     return $newDataSetXml;
    // }
    function get_documents($state_type_id)
    {
        try {
            $documents = DB::connection('tenant')->table('documents')
                ->where('date_of_issue', '>=', '2023-09-01')
                ->where('state_type_id', $state_type_id)
                ->get();

            return $documents;
        } catch (\Exception $e) {
        }
    }
    function get_documents_multi_companies($state_type_id, $company_name)
    {
        try {
            $documents = DB::connection('tenant')->table('documents')
                ->where('date_of_issue', '>=', '2023-09-01')
                ->where('state_type_id', $state_type_id)
                ->where('company', $company_name)
                ->get();

            return $documents;
        } catch (\Exception $e) {
        }
    }
    public function handle()
    {
        $systemConfiguration = SystemConfiguration::first();

        $configuration = Configuration::first();
        if(!$configuration->send_auto){
            $this->info("El envío de comprobantes automático está desactivado");
            return;
        }
        $multi_companies = $configuration->multi_companies;
        if ($multi_companies) {
            $companies = Company::all();
            foreach ($companies as $company) {
                $this->company = $company;
                if ($company->pse && $company->pse_url && $company->pse_token && $company->number && $company->type_send_pse == 2) {
                    $documents = $this->get_documents_multi_companies("01", $company->name);
                    foreach ($documents as $document) {
                        try {
                            (new PseService(Document::find($document->id)))->sendToPse();
                        } catch (\Exception $e) {
                            Log::info($e->getMessage());
                        }
                    }
                }else{
                    $documents = $this->get_documents_multi_companies("01", $company->name);
                    foreach ($documents as $document) {
                        try {
                            if($company->pse){
                                if($systemConfiguration->max_attempt_pse > $document->attempt_pse){
                                    (new TenantDocumentController())->send($document->id);
                                    Document::where('id', $document->id)->update([
                                        'attempt_pse' => $document->attempt_pse + 1
                                    ]);

                                }else{
                                    Document::where('id', $document->id)->update([
                                        'response_regularize_shipping' => [
                                            'code' => '0',
                                            'description' => 'El documento ha superado el máximo de intentos de envío automáticos, intente enviarlo manualmente'                                        ]
                                    ]);
                                }

                            }else{
                                (new TenantDocumentController())->send($document->id);
                            }
                        } catch (\Exception $e) {
                            Log::info($e->getMessage());
                        }

                    }
                }
            }
        } else {
            $company = Company::firstOrFail();
            $this->company = $company;
            if ($company->pse && $company->pse_url && $company->pse_token && $company->number && $company->type_send_pse == 2) {
                $documents = $this->get_documents("01");
                foreach ($documents as $document) {
                    try {
                        (new PseService(Document::find($document->id)))->sendToPse();
                    } catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }
                }



                $this->info('The command is finished');
            }else{
                $documents = $this->get_documents("01");
                foreach ($documents as $document) {

                    try {
                        if($company->pse){
                            if($systemConfiguration->max_attempt_pse > $document->attempt_pse){
                                (new TenantDocumentController())->send($document->id);
                                Document::where('id', $document->id)->update([
                                    'attempt_pse' => $document->attempt_pse + 1
                                ]);
                            }else{
                                Document::where('id', $document->id)->update([
                                    'response_regularize_shipping' => [


                                        'code' => '0',
                                        'description' => 'El documento ha superado el máximo de intentos de envíos automáticos, intente enviarlo manualmente'                                        ]
                                ]);
                            }

                        }else{
                            (new TenantDocumentController())->send($document->id);
                        }

                    } catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }
                }

            }
        }
    }
}
