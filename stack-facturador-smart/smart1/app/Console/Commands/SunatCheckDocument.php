<?php

namespace App\Console\Commands;

use App\Models\System\Client;
use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Traits\OfflineTrait;
use App\Models\Tenant\{
    Company,
    Configuration,
    Document,
};
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SunatCheckDocument extends Command
{
    use CommandTrait, OfflineTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:check-document';
    protected $initDate = '2025-01-01';
    // tenancy:run sunat:check-document
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el estado de los documentos de la empresa en SUNAT';

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
                if ($company->pse) {
                        $this->checkDocumentByCompany($company);
                }
            }
        } else {
            $company = Company::active();
            if ($company->pse) {
                Log::info('Actualizando datos de PSE de la empresa: ' . $company->name);
                $this->checkDocument();
            }
        }
    }

    private function checkDocument()
    {
        try {
            $documents = Document::select('id','document_type_id','series','number','date_of_issue','total')
                ->whereDate('date_of_issue', '>=', $this->initDate)
                ->whereNotIn('state_type_id', ['01', '03'])
                ->chunk(1000, function($documents) {
                    $correlativo = 0;
                    $conteo = 0;
                    $contenido = "";
                    $ids = $documents->pluck('id');
                    foreach ($documents as $row) {
                        if ($num_cpe == $cantidad_rows) {
                            break;
                        } else {
                            $conteo = $conteo + 1;
                            $contenido .= $company->number . "|";
                            $contenido .= $row->document_type_id . "|";
                            $contenido .= $row->series . "|";
                            $contenido .= intval($row->number) . "|";
                            $contenido .= substr($row->date_of_issue, 8, 2) . "/" . substr($row->date_of_issue, 5, 2) . "/" . substr($row->date_of_issue, 0, 4) . "|";
                            $contenido .= $row->total . "\n";
                            if ($conteo == 250) {
                                Storage::disk('tenant')->put("txt/" . $correlativo . "_" . $company->number . "_validarcpe.txt", $contenido);
                                $correlativo++;
                                $conteo = -1;
                                $contenido = "";
                            }
                            Storage::disk('tenant')->put("txt/" . $correlativo . "_" . $company->number . "_validarcpe.txt", $contenido);
                            $success = true;
                        }
                    }
                });

        } catch (Exception $e) {
            Log::error('Error al actualizar los datos de PSE de la empresa: ' . $company->name . ' - ' . $e->getMessage());
        }

        $this->info('The command is finished');
    }
}
