<?php

namespace App\Console\Commands;

use App\Models\System\Client;
use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Traits\OfflineTrait;
use App\Models\Tenant\{
    Company,
    Configuration,
};
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdatePseInfo extends Command
{
    use CommandTrait, OfflineTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pse:update-info';
    // tenancy:run pse:update-info
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los datos de PSE de las empresas';

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
        $configuration = Configuration::firstOrFail();

        if ($configuration->multi_company) {
            $companies = Company::all();
            foreach ($companies as $company) {
                if ($company->pse && $company->pse_token && $company->pse_url) {
                    if ($company->type_send_pse == 2) {
                        $this->updateCompanyPseData($company);
                    }
                }
            }
        } else {
            $company = Company::active();
            if ($company->pse && $company->pse_token && $company->pse_url && $company->type_send_pse == 2) {
                Log::info('Actualizando datos de PSE de la empresa: ' . $company->name);
                $this->updateCompanyPseData($company);
            }
        }
    }

    private function updateCompanyPseData($company)
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $company->pse_token,
                'Ruc' => $company->number
            ])->post($company->pse_url . '/api/pse/get_variables/sub_client', [
                'number' => $company->number,
            ])->json();
            Log::info(json_encode($response));
            if ($response['success']) {
                $name = 'certificate_smart.pem';
                if ($response['pse_url'] && $response['pse_username'] && $response['pse_password']) {
                    $company->fill([
                        'soap_url' => $response['pse_url'],
                        'soap_send_id' => '02',
                        'soap_username' => $response['pse_username'],
                        'soap_password' => $response['pse_password'],
                        'certificate' => $name,
                        'type_send_pse' => 1
                    ]);



                    if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name))) {
                        $path_smart = storage_path('smart' . DIRECTORY_SEPARATOR . 'certificate_smart.pem');
                        if (file_exists($path_smart)) {
                            $pem = file_get_contents($path_smart);
                            file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
                        }
                    }

                    $company->save();
                }
            } else {
                $message = isset($response['message']) ? $response['message'] : 'No se pudo obtener los datos de PSE';
                Log::error('Error al actualizar los datos de PSE de la empresa: ' . $company->name . ' - ' . $message);
            }
        } catch (Exception $e) {
            Log::error('Error al actualizar los datos de PSE de la empresa: ' . $company->name . ' - ' . $e->getMessage());
        }

        $this->info('The command is finished');
    }
}
