<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\{Company, Configuration, Document, StateType};
use Illuminate\Support\Facades\{Storage, Log};
use Carbon\Carbon;
use Modules\Services\Data\ServiceData;

class ValidateDocumentsTask extends Command
{
    protected $signature = 'documents:validate';
    protected $description = 'Valida documentos masivamente con state_type_id = 05';

    private const BATCH_SIZE = 250;
    private const LIMIT_DOCUMENTS = 30;
    private const VALID_STATES = ['ACEPTADO', 'RECHAZADO', 'ANULADO'];

    public function handle()
    {
        $configuration = Configuration::first();
        if (!$configuration->validate_automatic) {
            $this->info('Validación de documentos desactivada');
            return;
        }
        if ($configuration->multi_companies) {
            $this->processMultipleCompanies();
        } else {
            $this->processSingleCompany();
        }
    }

    private function processMultipleCompanies()
    {
        Company::all()->each(function ($company) {
            $this->processDocumentsForCompany($company);
        });
    }

    private function processSingleCompany()
    {
        $company = Company::first();

        if (!$company) {
            $this->error('No se encontró la compañía');
            return;
        }

        $this->processDocumentsForCompany($company);
        $this->info('Validación de documentos completada');
    }

    private function processDocumentsForCompany(Company $company)
    {

        $configuration = Configuration::first();
        $documents = $this->getDocumentsQuery($configuration->multi_companies ? $company->name : null)->get();

        if ($documents->isEmpty()) {
            $this->info('No hay documentos para validar');
            return;
        }

        $this->processDocumentsBatch($documents, $company);
    }

    private function getDocumentsQuery($company_name = null)
    {
        $query = Document::whereNull('state_validate')
            ->whereYear('date_of_issue', '>=', 2025)
            ->where('validate_attemps', '<', 5)
            ->limit(self::LIMIT_DOCUMENTS);

        if ($company_name) {
            $query->where('company', $company_name);
        }

        return $query;
    }

    private function processDocumentsBatch($documents, Company $company)
    {
        $service = new ServiceData();
        $correlativo = 0;
        $conteo = 0;
        $contenido = "";

        foreach ($documents as $document) {
            $conteo++;
            $contenido .= $this->formatDocumentLine($company, $document);

            if ($conteo == self::BATCH_SIZE) {
                $this->processBatch($company, $correlativo, $contenido, $service);
                $correlativo++;
                $conteo = 0;
                $contenido = "";
            }
        }

        if (!empty($contenido)) {
            $this->processBatch($company, $correlativo, $contenido, $service);
        }
    }

    private function formatDocumentLine(Company $company, Document $document): string
    {
        return implode("|", [
            $company->number,
            $document->document_type_id,
            $document->series,
            intval($document->number),
            $this->formatDate($document->date_of_issue),
            $document->total
        ]) . "\n";
    }

    private function formatDate(string $date): string
    {
        return substr($date, 8, 2) . "/" . substr($date, 5, 2) . "/" . substr($date, 0, 4);
    }

    protected function processBatch($company, $correlativo, $contenido, $service)
    {
        try {
            $filename = $this->generateFilename($correlativo, $company);
            Storage::disk('tenant')->put("txt/" . $filename, $contenido);

            $response = $service->validar_cpe_http($filename);
            $this->processServiceResponse(json_decode($response, true));
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }

    private function generateFilename($correlativo, $company): string
    {
        return "{$correlativo}_{$company->number}_validarcpe.txt";
    }

    private function processServiceResponse($data_response)
    {
        if (!isset($data_response['data'])) {
            return;
        }

        foreach ($data_response['data'] as $value) {
            $this->updateDocumentState($value);
        }
    }

    private function updateDocumentState(array $value)
    {
        $sales = $this->findDocument($value);

        if (!$sales) {
            Log::info("No se encontró el documento: {$value['Serie']} {$value['Numero']} {$value['TipoComprobante']}");
            return;
        }

        $estado_comprobante = strtoupper($value['EstadoComprobante']);

        if ($estado_comprobante == "NO EXISTE") {
            $state_type_id = "01";
            $sales->date_validate = Carbon::now();
            $sales->state_validate = "NO EXISTE";
        } else {
            $sales->date_validate = Carbon::now();

            $state_type_id = "01"; // valor por defecto

            if (in_array($estado_comprobante, self::VALID_STATES)) {
                $state_type = StateType::where('description', 'like', "%{$estado_comprobante}%")->first();
                if ($state_type) {
                    $state_type_id = $state_type->id;
                }
            }
            if (in_array($sales->state_type_id, ['11', '13']) && $estado_comprobante == 'ACEPTADO') {
                $estado_comprobante .= " POR VERIFICAR";
            }

            $sales->state_validate = $estado_comprobante;
            
            $sales->state_type_id = $state_type_id;
        }


        $sales->validate_attemps = $sales->validate_attemps + 1;
        if($sales->validate_attemps > 5) {
            $sales->validate_attemps = 5;
        }
        $sales->save();
    }

    private function findDocument(array $value)
    {
        return Document::where('series', $value['Serie'])
            ->where('number', $value['Numero'])
            ->where('document_type_id', $value['TipoComprobante'])
            ->first();
    }

    private function logError(\Exception $e)
    {
        Log::error("Error en processBatch: " . $e->getMessage());
        Log::error("Línea del error: " . $e->getLine());
        Log::error("Archivo: " . $e->getFile());
    }
}
