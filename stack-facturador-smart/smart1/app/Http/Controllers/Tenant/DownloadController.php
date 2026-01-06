<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\CoreFacturalo\Facturalo;
use App\Http\Controllers\Tenant\QuotationController;
use App\CoreFacturalo\Template;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use Mpdf\Mpdf;
use Exception;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    use StorageDocument;

    public function downloadExternal($model, $type, $external_id, $format = null)
    {
        $copy_model = ucfirst($model);
        $model = "App\\Models\\Tenant\\" . ucfirst($model);
        if ($type == "pdf" && in_array($copy_model, ['Document', 'SaleNote', 'Quotation', 'OrderNote'])) {
            $configuration = Configuration::getConfig();
            $paper_size_modal_documents = $configuration->paper_size_modal_documents;
            $format = $paper_size_modal_documents;
        }
        $document = $model::where('external_id', $external_id)->first();

        if (!$document) {
            $document = $model::whereRaw('LOWER(external_id) = LOWER(?)', [$external_id])->first();
        }

// throw new Exception("El código {$external_id} es inválido, no se encontro documento relacionado")
        if (!$document){
            $company = Company::active();
            $info = [
                'company' => $company->name,
                'external_id' => $external_id,
                'model' => $model,
            ];
            // Agregar información de la solicitud si existe
            if (function_exists('request') && request()) {
                $request_info = [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'referer' => request()->header('referer'),
                ];
                $info = array_merge($info, $request_info);
            }
            Log::error("El código {$external_id} es inválido, no se encontro documento relacionado", $info);
            return response()->json(['error' => "El código {$external_id} es inválido, no se encontro documento relacionado"], 404);
        }
        if ($format != null) $this->reloadPDF($document, 'invoice', $format);

        if (in_array($document->document_type_id, ['09', '31']) && $type === 'cdr') {
            $company = Company::active();
        }
        try {
            return $this->download($type, $document);
        } catch (\Exception $e) {
            $format_pdf = "a4";
    
            $this->reloadPDF($document, 'invoice', $format_pdf);
            return $this->download($type, $document);
        }
    }

    public function download($type, $document)
    {
        switch ($type) {
            case 'pdf':
                $folder = 'pdf';
                break;
            case 'xml':
                $folder = 'signed';
                break;
            case 'cdr_xml':
                $folder = 'cdr_xml';
                break;
            case 'cdr':
                $folder = 'cdr';
                break;
            case 'quotation':
                $folder = 'quotation';
                break;
            case 'sale_note':
                $folder = 'sale_note';
                break;

            default:
                throw new Exception('Tipo de archivo a descargar es inválido');
        }


        return $this->downloadStorage($document->filename, $folder);
    }

    /**
     * @param      $model
     * @param      $external_id
     * @param null $format
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     */
    public function toPrint($model, $external_id, $format = null)
    {
        $document_type = $model;
        $model = "App\\Models\\Tenant\\" . ucfirst($model);
        
        // Normalize external_id for consistent matching across environments
        $external_id = trim($external_id);
        
        // Try case-sensitive match first, then case-insensitive for Ubuntu compatibility
        $document = $model::where('external_id', $external_id)->first();
        
        if (!$document) {
            // Try case-insensitive search for Ubuntu compatibility
            $document = $model::whereRaw('LOWER(external_id) = LOWER(?)', [$external_id])->first();
        }
        
        $configuration = Configuration::getConfig();
        if ($format == null && $configuration->paper_size_modal_documents) {
            $format = $configuration->paper_size_modal_documents;
        }
        
        if (!$document) {
            // Enhanced error message with debugging information
            throw new Exception("El código '{$external_id}' es inválido, no se encontró documento relacionado en el modelo {$model}. Verifique que el external_id sea correcto y coincida exactamente.");
        }

        if ($document_type == 'quotation') {
            // Las cotizaciones tienen su propio controlador, si se generan por este medio, dará error
            $quotation = new QuotationController();
            return $quotation->toPrint($external_id, $format);
        } elseif ($document_type == 'salenote') {
            $saleNote = new SaleNoteController();
            return $saleNote->toPrint($external_id, $format);
        }
        $type = 'invoice';
        if ($document_type == 'dispatch') {
            $type = 'dispatch';
        }
        if ($document->document_type_id === '07') {
            $type = 'credit';
        }
        if ($document->document_type_id === '08') {
            $type = 'debit';
        }

        $this->reloadPDF($document, $type, $format);

        $temp = tempnam(sys_get_temp_dir(), 'pdf');



        file_put_contents($temp, $this->getStorage($document->filename, 'pdf'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document->filename.'.pdf'.'"'
        ];
        */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($document->filename));
    }

    public function toTicket($model, $external_id, $format = null)
    {
        $model = "App\\Models\\Tenant\\" . ucfirst($model);
        $document = $model::where('id', $external_id)->first();

        if (!$document) throw new Exception("El código {$external_id} es inválido, no se encontro documento relacionado");

        if ($format != null) return $this->reloadTicket($document, 'invoice', $format);
    }

    /**
     * Reload Ticket
     * @param  ModelTenant $document
     * @param  string $format
     * @return void
     */
    private function reloadTicket($document, $type, $format)
    {
        return (new Facturalo)->createPdf($document, $type, $format, 'html');
    }

    /**
     * Reload PDF
     * @param  ModelTenant $document
     * @param  string $format
     * @return void
     */
    private function reloadPDF($document, $type, $format)
    {
        (new Facturalo)->createPdf($document, $type, $format);
    }
}
