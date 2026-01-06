<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\EmailSendLog;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MallDocumentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
        
        
        
            

        
            return [

                'id' => $row->id,
                'soap_type_id' => $row->soap_type_id,
                'soap_type_description' => $row->soap_type->description,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'date_of_due' => (in_array($row->document_type_id, ['01', '03'])) ? $row->invoice->date_of_due->format('Y-m-d') : null,
                'number' => $row->number_full,
                'customer_name' => $row->customer->name,
                'customer_number' => $row->customer->number,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => $row->exchange_rate_sale,
                
                'total_igv' => $row->total_igv,
                'total' => $row->total,
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $row->state_type->description,
                'document_type_description' => $row->document_type->description,
                'document_type_id' => $row->document_type->id,
            
                'download_pdf' => $row->download_external_pdf,
            
                
                'user_name' => ($row->user) ? $row->user->name : '',
                'user_email' => ($row->user) ? $row->user->email : '',
                'user_id' => $row->user_id,
            
            ];
        });
    }


    
}
