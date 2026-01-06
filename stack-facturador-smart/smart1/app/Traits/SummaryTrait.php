<?php

namespace App\Traits;

use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Summary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SummaryTrait
{
    public function save($request)
    {
        $fact =  DB::connection('tenant')->transaction(function () use ($request) {
            $documents = Functions::valueKeyInArray($request, 'documents', []);
            $company = Company::active();

            if (!empty($documents)) {
                $document = $documents[0];
                $document_id = Functions::valueKeyInArray($document, 'document_id');

                if ($document_id) {
                    $document_db = Document::find($document_id);

                    if ($document_db && $document_db->website_id) {
                        $alter_company = Company::where('website_id', $document_db->website_id)->first();

                        if ($alter_company) {
                            $company = $alter_company;
                        }
                    }
                }
            }
            $facturalo = new Facturalo($company);
            $facturalo->save($request->all());
            $facturalo->createXmlUnsigned();
            $facturalo->signXmlUnsigned();
            $facturalo->senderXmlSignedSummary();

            return $facturalo;
        });

        $document = $fact->getDocument();

        return [
            'voided_id'   => $document->id,
            'type'    => 'summary',
            'success' => true,
            'message' => "El resumen {$document->identifier} fue creado correctamente",
        ];
    }

    public function query($id)
    {
        $document = Summary::find($id);

        $fact =  DB::connection('tenant')->transaction(function () use ($document) {
            $facturalo = new Facturalo();
            $facturalo->setDocument($document);
            $facturalo->setType('summary');
            $facturalo->statusSummary($document->ticket);
            return $facturalo;
        });

        $response = $fact->getResponse();

        return [
            'success' => ($response['status_code'] === 99) ? false : true,
            'message' => $response['description'],
        ];
    }


    public function getCustomErrorMessage($message, $exception)
    {

        $this->setCustomErrorLog($exception);

        return [
            'success' => false,
            'message' => $message
        ];
    }

    public function setCustomErrorLog($exception)
    {
        Log::error("Code: {$exception->getCode()} - Line: {$exception->getLine()} - Message: {$exception->getMessage()} - File: {$exception->getFile()}");
    }

    public function updateUnknownErrorStatus($id, $exception)
    {

        Summary::findOrFail($id)->update([
            'unknown_error_status_response' => true,
            'error_manually_regularized' => [
                'message' => $exception->getMessage(),
            ],
        ]);
    }
}
