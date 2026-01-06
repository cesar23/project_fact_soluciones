<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\SummaryController;
use App\Http\Controllers\Tenant\VoidedController;
use App\Models\System\Configuration as SystemConfiguration;
use Facades\App\Http\Controllers\Tenant\DocumentController;
use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Models\Tenant\{
    Company,
    Configuration,
    Document,
    Summary,
    Voided
};
use App\Services\PseServiceTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class PseCheckStatus extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pse:check';
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

    function get_documents_multi_companies($state_type_id, $company_name)
    {
        try {
            $documents = DB::connection('tenant')->table('documents')
                ->where('date_of_issue', '>=', '2025-09-01')
                ->where('state_type_id', $state_type_id)
                ->where('company', $company_name)
                ->orderBy('date_of_issue', 'asc')
                ->limit(100)
                ->get();
            return $documents;
        } catch (\Exception $e) {
            Log::error('Error getting documents for company: ' . $company_name, ['error' => $e->getMessage()]);
            return collect();
        }
    }

    function get_documents($state_type_id)
    {
        try {
            $documents = DB::connection('tenant')->table('documents')
                ->where('date_of_issue', '>=', '2025-09-01')
                ->where('state_type_id', $state_type_id)
                ->orderBy('date_of_issue', 'asc')
                ->limit(100)
                ->get();

            return $documents;
        } catch (\Exception $e) {
            Log::error('Error getting documents for state: ' . $state_type_id, ['error' => $e->getMessage()]);
            return collect();
        }
    }
    public function handle()
    {
        $configuration = Configuration::first();
        $systemConfiguration = SystemConfiguration::first();
        $multi_companies = $configuration->multi_companies;
        if ($multi_companies) {
            Log::info('Pse multi_companies');
            $companies = Company::all();
            foreach ($companies as $company) {
                $this->company = $company;
                if ($company->pse && $company->pse_url && $company->pse_token && $company->number && $company->type_send_pse == 2) {
                    foreach (['03', '13'] as $state_type_id) {
                        $documents = $this->get_documents_multi_companies($state_type_id, $company->name);
                        if ($documents->count() > 0) {
                            new PseServiceTask($documents, $state_type_id, $company);
                        }
                    }
                } else {
                    $request = new Request();
                    $records = (new VoidedController)->getRecords($request);

                    $recordsToUpdate = [];
                    foreach ($records as $record) {
                        try {
                            $type = $record->type;
                            $id = $record->id;

                            if ($type == 'voided' && $company->pse) {
                                if ($systemConfiguration->max_attempt_pse > $record->attempt_pse) {
                                    (new VoidedController)->status($id);
                                    $recordsToUpdate[] = [
                                        'id' => $id,
                                        'attempt_pse' => $record->attempt_pse + 1
                                    ];
                                }
                            }
                        } catch (\Exception $e) {
                            // Log::error('Error processing voided record: ' . $id, ['error' => $e->getMessage()]);
                        }
                    }

                    if (!empty($recordsToUpdate)) {
                        DB::transaction(function () use ($recordsToUpdate) {
                            foreach ($recordsToUpdate as $update) {
                                Voided::where('id', $update['id'])->update([
                                    'attempt_pse' => $update['attempt_pse']
                                ]);
                            }
                        });
                    }

                    $records = (new SummaryController)->recordsToSend();

                    $recordsToUpdate = [];
                    foreach ($records as $record) {
                        try {
                            if ($company->pse) {
                                if (isset($record->attempt_pse) && $systemConfiguration->max_attempt_pse > $record->attempt_pse) {
                                    (new SummaryController)->query($record);
                                    $recordsToUpdate[] = [
                                        'id' => $record,
                                        'attempt_pse' => $record->attempt_pse + 1
                                    ];
                                }
                            } else {
                                (new SummaryController)->query($record);
                            }
                        } catch (\Exception $e) {
                            // Log::error('Error sending summary: ' . $record, ['error' => $e->getMessage()]);
                        }
                    }

                    if (!empty($recordsToUpdate)) {
                        DB::transaction(function () use ($recordsToUpdate) {
                            foreach ($recordsToUpdate as $update) {
                                Summary::where('id', $update['id'])->update([
                                    'attempt_pse' => $update['attempt_pse']
                                ]);
                            }
                        });
                    }
                }
            }
        } else {
            $company = Company::firstOrFail();
            $this->company = $company;
            if ($company->pse && $company->pse_url && $company->pse_token && $company->number && $company->type_send_pse == 2) {
                foreach (['03', '13'] as $state_type_id) {
                    $documents = $this->get_documents($state_type_id);
                    if ($documents->count() > 0) {
                        new PseServiceTask($documents, $state_type_id, $company);
                    }
                }
            } else {

                $request = new Request();
                $records = (new VoidedController)->getRecords($request);

                $recordsToUpdate = [];
                foreach ($records as $record) {
                    try {
                        $type = $record->type;
                        $id = $record->id;

                        if ($type == 'voided') {
                            if ($company->pse) {
                                if (isset($record->attempt_pse) && $systemConfiguration->max_attempt_pse > $record->attempt_pse) {
                                    (new VoidedController)->status($id);
                                    $recordsToUpdate[] = [
                                        'id' => $id,
                                        'attempt_pse' => $record->attempt_pse + 1
                                    ];
                                }
                            } else {
                                (new VoidedController)->status($id);
                            }
                        }
                    } catch (\Exception $e) {
                        // Log::error('Error processing voided record: ' . $id, ['error' => $e->getMessage()]);
                    }
                }

                if (!empty($recordsToUpdate)) {
                    DB::transaction(function () use ($recordsToUpdate) {
                        foreach ($recordsToUpdate as $update) {
                            Voided::where('id', $update['id'])->update([
                                'attempt_pse' => $update['attempt_pse']
                            ]);
                        }
                    });
                }

                try {
                    $records = (new SummaryController)->recordsToSend();

                    $recordsToUpdate = [];
                    foreach ($records as $record) {
                        try {
                            if ($company->pse) {
                                if (isset($record->attempt_pse) && $systemConfiguration->max_attempt_pse > $record->attempt_pse) {
                                    (new SummaryController)->query($record);
                                    $recordsToUpdate[] = [
                                        'id' => $record,
                                        'attempt_pse' => $record->attempt_pse + 1
                                    ];
                                }
                            } else {
                                (new SummaryController)->query($record);
                            }
                        } catch (\Exception $e) {
                            // Log::error('Error sending summary for company: ' . $company->name, [
                            //     'record' => $record,
                            //     'error' => $e->getMessage()
                            // ]);
                        }
                    }

                    if (!empty($recordsToUpdate)) {
                        DB::transaction(function () use ($recordsToUpdate) {
                            foreach ($recordsToUpdate as $update) {
                                Summary::where('id', $update['id'])->update([
                                    'attempt_pse' => $update['attempt_pse']
                                ]);
                            }
                        });
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing summaries for company: ' . $company->name, ['error' => $e->getMessage()]);
                }
            }
        }
        $this->info('The command is finished');
    }
}
