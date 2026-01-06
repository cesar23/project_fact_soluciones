<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Models\Tenant\{
    Company,
    Configuration,
    Dispatch,
    User,
};
use App\Services\PseServiceDispatchTask;
use App\Services\PseServiceTask;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ApiPeruDev\Http\Controllers\ServiceDispatchController;

class PseDispatchCheckStatus extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pse:dispatchcheck';
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

    function get_documents_multi_companies($state_type_id, $company_name)
    {
        try {
            $documents = DB::connection('tenant')->table('dispatches')
                ->where('date_of_issue', '>=', '2025-09-01')
                ->where('state_type_id', $state_type_id)
                ->where('company', $company_name)
                ->orderBy('date_of_issue', 'asc')
                ->limit(100)
                ->get();
            return $documents;
        } catch (\Exception $e) {
            Log::error('Error getting dispatches for company: ' . $company_name, ['error' => $e->getMessage()]);
            return collect();
        }
    }
    function get_documents($state_type_id)
    {
        try {
            $documents = DB::connection('tenant')->table('dispatches')
                ->where('date_of_issue', '>=', '2025-09-01')
                ->where('state_type_id', $state_type_id)
                ->orderBy('date_of_issue', 'asc')
                ->limit(100)
                ->get();

            return $documents;
        } catch (\Exception $e) {
            Log::error('Error getting dispatches for state: ' . $state_type_id, ['error' => $e->getMessage()]);
            return collect();
        }
    }
    public function handle()

    {
        $configuration = Configuration::first();
        $multi_companies = $configuration->multi_companies;
        if ($multi_companies) {
            $companies = Company::all();
            foreach ($companies as $company) {
                $this->company = $company;
                if ($company->pse && $company->pse_url && $company->pse_token && $company->number && $company->type_send_pse == 2) {
                    foreach (['03'] as $state_type_id) {
                        $documents = $this->get_documents_multi_companies($state_type_id, $company->name);
                        if ($documents->count() > 0) {
                            new PseServiceDispatchTask($documents, $state_type_id);
                        }
                    }
                }
            }
        } else {
            $company = Company::firstOrFail();
            $this->company = $company;
            if ($company->pse && $company->pse_url && $company->pse_token && $company->number && $company->type_send_pse == 2) {
                foreach (['03'] as $state_type_id) {
                    $documents = $this->get_documents($state_type_id);
                    if ($documents->count() > 0) {
                        new PseServiceDispatchTask($documents, $state_type_id);
                    }
                }
            } else {
                
                foreach (['01'] as $state_type_id) {
                    $documents = $this->get_documents($state_type_id);
                    if ($documents->count() > 0) {
                        $errors = [];
                        foreach ($documents as $document) {
                            try{
                                (new ServiceDispatchController)->send($document->external_id);
                            } catch(\Exception $e){
                                $errors[] = [
                                    'date' => $document->date_of_issue,
                                    'external_id' => $document->external_id,
                                    'error' => $e->getMessage()
                                ];
                                $this->info("{$document->date_of_issue} - {$e->getMessage()}");
                            }
                        }

                        if (!empty($errors)) {
                            Log::warning('Errors sending dispatches', ['errors' => $errors]);
                        }
                    }
                }

                foreach (['03'] as $state_type_id) {
                    $documents = $this->get_documents($state_type_id);
                    if ($documents->count() > 0) {
                        $errors = [];
                        foreach ($documents as $document) {
                            try{
                                (new ServiceDispatchController)->statusTicket($document->external_id);
                            } catch(\Exception $e){
                                $errors[] = [
                                    'date' => $document->date_of_issue,
                                    'external_id' => $document->external_id,
                                    'error' => $e->getMessage()
                                ];
                                $this->info("{$document->date_of_issue} - {$e->getMessage()}");
                            }
                        }

                        if (!empty($errors)) {
                            Log::warning('Errors checking dispatch status', ['errors' => $errors]);
                        }
                    }
                }

            }
        }



        $this->info('The command is finished');
    }
}
