<?php

namespace App\Console\Commands;

use App\Models\System\Client;
use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Traits\OfflineTrait;
use App\Models\Tenant\{
    Company,

};
use Hyn\Tenancy\Facades\TenancyFacade;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Document\Http\Controllers\DocumentController;
use Modules\Document\Http\Controllers\DocumentRegularizeShippingController;

class GetCdrDocumentsSunat extends Command
{
    use CommandTrait, OfflineTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:cdr-regularizare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obtiene el cdr de los documentos por regularizar a SUNAT';

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
    public function handle()
    {
        $company = Company::active();
        $tenant = TenancyFacade::tenant();
        $is_smart = false;
        if ($tenant) {
            $tenantId = $tenant->id;
            $hostname = Hostname::where('website_id', $tenantId)->first();
            $client = Client::where('hostname_id', $hostname->id)->first();
            if ($client) {
                $is_smart = $client->cert_smart;
            }
        }
        if ($company->pse || $is_smart) return;
        $documents = [];
        $request = new Request();
        $documents = (new DocumentRegularizeShippingController)->getRecords($request)->pluck('id')->toArray();

        foreach ($documents as $document_id) {
            try {
                (new DocumentController)->consultCdr($document_id);
            } catch (\Exception $e) {
                Log::error($e);
            }
        }

        $this->info('The command is finished');
    }
}
