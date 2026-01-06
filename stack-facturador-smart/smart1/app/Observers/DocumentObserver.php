<?php

namespace App\Observers;

use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;

class DocumentObserver
{
    /**
     * Handle the document "creating" event.
     *
     * @param  \App\Models\Tenant\Document  $document
     * @return void
     */
    public function creating(Document $document)
    {
        $configuration = Configuration::first();
        if ($configuration->multi_companies && $document->website_id) {
            $company_found = Company::where('website_id', $document->website_id)->first();
            if ($company_found) {
                $company = $company_found;
            } else {
                $company = Company::active();
            }
        } else {
            $company = Company::active();
        }
        $number = Functions::newNumber(
            $document->soap_type_id,
            $document->document_type_id,
            $document->series,
            $document->number,
            Document::class,
        );
        
        $document->number = $number;

        $document->filename = Functions::filename($company, $document->document_type_id, $document->series, $number);
        $document->unique_filename = $document->filename; //campo único para evitar duplicados
        
    }

    /**
     * Handle the document "updated" event.
     *
     * @param  \App\Models\Tenant\Document  $document
     * @return void
     */
    public function updated(Document $document)
    {
        //
    }

    /**
     * Handle the document "deleted" event.
     *
     * @param  \App\Models\Tenant\Document  $document
     * @return void
     */
    public function deleted(Document $document)
    {
        //
    }

    /**
     * Handle the document "restored" event.
     *
     * @param  \App\Models\Tenant\Document  $document
     * @return void
     */
    public function restored(Document $document)
    {
        //
    }

    /**
     * Handle the document "force deleted" event.
     *
     * @param  \App\Models\Tenant\Document  $document
     * @return void
     */
    public function forceDeleted(Document $document)
    {
        //
    }
}
