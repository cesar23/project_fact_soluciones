<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ClientDocumentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row) {
            return [
                'id' => $row->client_id,
                'hostname' => $row->hostname,
                'token' => $row->token,
                'company_name' => $row->company_name,
                'company_number' => $row->company_number,
                'document_type_id' => $row->document_type_id,
                'external_id' => $row->external_id,
                'document_id' => $row->document_id,
                'document_type' => $row->document_type,
                'document_series' => $row->series,
                'document_number' => $row->number,
                'date_of_issue' => \Carbon\Carbon::parse($row->date_of_issue)->format('d-m-Y'),
                'customer_name' => $row->customer_name,
                'customer_number' => $row->customer_number,
                'total' => $row->total,
                'total_taxed' => $row->total_taxed,
                'total_igv' => $row->total_igv,
                'state_type' => $row->state_type,
                'state_type_id' => $row->state_type_id,
                'voided_id' => $row->voided_id,
                'download_xml' => $row->hostname . "/downloads/document/xml/" . $row->external_id,
                'download_pdf' => $row->hostname . "/downloads/document/pdf/" . $row->external_id,
                'download_cdr' => $row->hostname . "/downloads/document/cdr/" . $row->external_id,
            ];
        });
    }
}