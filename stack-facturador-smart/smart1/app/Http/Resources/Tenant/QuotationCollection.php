<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\OrderNote;
use Modules\Purchase\Models\PurchaseOrder;

class QuotationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $connection = DB::connection('tenant');
        $user = auth()->user();
        $company = Company::query()->first();
        $configuration = Configuration::getConfig();
        $quotationIds = $this->collection->pluck('id')->toArray();

        $itemsData = $connection->table('quotation_items')
        ->select('quotation_items.id', 'items.unit_type_id','quotation_items.quotation_id')
        ->join('items', 'items.id', '=', 'quotation_items.item_id')
        ->whereIn('quotation_items.quotation_id', $quotationIds)
        ->get()
        ->groupBy('quotation_id');

        $state_typesData = $connection->table('state_types')->get()->keyBy('id');

        $orderNotesData = $connection->table('order_notes')->select('id', 'prefix', 'number', 'quotation_id')->whereIn('quotation_id', $quotationIds)->get()->keyBy('quotation_id');

        $usersData = $connection->table('users')->select('id', 'name')->get()->keyBy('id');

        $quotationTechniciansData = $connection->table('quotation_technicians_quotation')->select('quotation_technicians_quotation.quotation_id', 'quotation_technicians_quotation.quotation_technician_id', 'quotation_technicians.name as quotation_technician_name')->whereIn('quotation_id', $quotationIds)
        ->join('quotation_technicians', 'quotation_technicians.id', '=', 'quotation_technicians_quotation.quotation_technician_id')
        ->get()->keyBy('quotation_id');

        // Optimización: Obtener documentos aceptados por bloque
        $documentsAcceptedData = $connection->table('documents')
            ->select('quotation_id')
            ->whereIn('quotation_id', $quotationIds)
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->get()
            ->groupBy('quotation_id')
            ->map(function($group) {
                return $group->count() > 0;
            });

        $quotationServicesNotServicesData = $connection->table('quotation_services_not_services')
            ->select('quotation_id', 'document_service_id', 'document_not_service_id')
            ->whereIn('quotation_id', $quotationIds)
            ->get()
            ->groupBy('quotation_id'); // Cambiado de keyBy a groupBy para mantener todos los registros

        // Optimización: Obtener sale_notes con todos los datos necesarios por bloque
        $saleNotesData = $connection->table('sale_notes')
            ->select('id', 'quotation_id', 'prefix', 'number','series')
            ->whereIn('quotation_id', $quotationIds)
            ->get()
            ->groupBy('quotation_id');

        // Optimización: Obtener contracts por bloque
        $contractsData = $connection->table('contracts')
            ->select('quotation_id', 'external_id')
            ->whereIn('quotation_id', $quotationIds)
            ->get()
            ->keyBy('quotation_id');

        // Optimización: Obtener purchase_orders por bloque
        $purchaseOrdersData = $connection->table('purchase_orders')
            ->select('quotation_id')
            ->whereIn('quotation_id', $quotationIds)
            ->get()
            ->groupBy('quotation_id')
            ->map(function($group) {
                return $group->count() == 0;
            });

        // Optimización: Obtener companies por website_id
        $websiteIds = $this->collection->pluck('website_id')->filter()->unique()->toArray();
        $companiesData = $connection->table('companies')
            ->select('id', 'name', 'number', 'website_id')
            ->whereIn('website_id', $websiteIds)
            ->orWhereNull('website_id')
            ->get()
            ->keyBy('website_id');


        // Company por defecto (sin website_id)
        $defaultCompany = $companiesData->get(null, $companiesData->first());

        return $this->collection->transform(function($row, $key) use($user, $company, $configuration, $orderNotesData, $usersData, $documentsAcceptedData, $saleNotesData, $contractsData, $purchaseOrdersData, $companiesData, $defaultCompany, $state_typesData, $itemsData, $quotationServicesNotServicesData, $quotationTechniciansData) {
            /** @var Quotation $row */
            /** @var User $user */
        
            // Usar datos optimizados en lugar de llamadas individuales
            $hasAcceptedDocuments = $documentsAcceptedData->get($row->id, false);
            $saleNotesCount = $saleNotesData->get($row->id, collect())->count();
            
            $btn_generate = (($hasAcceptedDocuments || ($saleNotesCount > 0)) && !$configuration->generate_multiple_documents_sale_note) ? false : true;
    
            $contract = $contractsData->get($row->id);
            $btn_generate_cnt = $contract ? false : true;
            $external_id_contract = $contract ? $contract->external_id : null;
    
            $btn_options = ($row->state_type_id != '11') && $btn_generate && ($company->soap_type_id !== '03');
            if($user->type === 'seller') {
                $btn_options = $btn_options && ($configuration->quotation_allow_seller_generate_sale);
            } else {
                $btn_options = $btn_options && ($user->type === 'admin'|| $user->type === 'superadmin');
            }
            $quotationTechnician = $quotationTechniciansData->get($row->id);
            $quotationTechnicianName = $quotationTechnician ? $quotationTechnician->quotation_technician_name : null;
    
            $orderNote = $orderNotesData->get($row->id);
            if($orderNote != null){
                $orderNote =[
                  'id'=>$orderNote->id,
                  'full_number'=>$orderNote->prefix . '-' . ($orderNote->number ?? $orderNote->id),
                ];
            }else{
                $orderNote = [];
            }
            $user_name = null;
            if($row->user_id){
                $user_name = $usersData->get($row->user_id)->name;
            }

            $seller = $usersData->get($row->seller_id);
            
            $can_create_purchase_order = $purchaseOrdersData->get($row->id, true);
            
            // Optimización: Usar datos de companies obtenidos por bloque
            $alter_company = [];
            if($row->website_id){
                $companyData = $companiesData->get($row->website_id, $defaultCompany);
            }else{
                $companyData = $defaultCompany;
            }
            if($companyData){
                $alter_company['name'] = $companyData->name;
                $alter_company['number'] = $companyData->number;
            }
            
            $plate_numbers = [];
            $plate_number_brand_description = null;
            $plate_number_model_description = null;
            if($configuration->plate_number_config){
                $plate_numbers_description = optional($row->plateNumberDocument)->plateNumber ? optional($row->plateNumberDocument)->plateNumber->description : null;
                $plate_number_brand_description = optional($row->plateNumberDocument)->plateNumber ? optional($row->plateNumberDocument)->plateNumber->brand->description : null;
                $plate_number_model_description = optional($row->plateNumberDocument)->plateNumber ? optional($row->plateNumberDocument)->plateNumber->model->description : null;
                $plate_numbers = $plate_numbers_description ? [[
                    'description' => $plate_numbers_description
                ]] : [];
            }

            // Usar los datos de sale_notes obtenidos por bloque
            $saleNotes = $saleNotesData->get($row->id, collect())->transform(function($saleNote) {
                return [
                    'number_full' => $saleNote->series . '-' . $saleNote->number,
                ];
            });
            $items = $itemsData->get($row->id, collect())->transform(function($item)  {
                return [
                    'id' => $item->id,
                    'unit_type_id' => $item->unit_type_id,
                ];
            });
            $has_services_and_not_services = false;
            if($configuration->split_quotation_to_document_services_and_not_services){
                $has_services_and_not_services = $items->contains(function($item) {
                    return $item['unit_type_id'] === 'ZZ';
                }) && $items->contains(function($item) {
                    return $item['unit_type_id'] !== 'ZZ';
                });
            }
            
            // Por defecto, si no hay servicios y no servicios, ambos pueden emitirse
            $can_emit_services = true;
            $can_emit_not_services = true;
            
            if($has_services_and_not_services){
                $quotationServicesNotServices = $quotationServicesNotServicesData->get($row->id, collect());
                if($quotationServicesNotServices->isNotEmpty()){
                    // Verificar si ya existe algún documento de servicios
                    $hasServiceDocument = $quotationServicesNotServices->contains('document_service_id', '!=', null);
                    // Verificar si ya existe algún documento de no servicios
                    $hasNotServiceDocument = $quotationServicesNotServices->contains('document_not_service_id', '!=', null);
                    
                    // Si ya existe un documento de servicios, NO puede emitir más servicios
                    $can_emit_services = $hasServiceDocument ? false : true;
                    // Si ya existe un documento de no servicios, NO puede emitir más no servicios
                    $can_emit_not_services = $hasNotServiceDocument ? false : true;
                }
            }
            
            return [
                'plate_number_brand_description' => $plate_number_brand_description,
                'plate_number_model_description' => $plate_number_model_description,
                'quotation_technician_name' => $quotationTechnicianName,
                'has_services_and_not_services' => $has_services_and_not_services,
                'can_emit_services' => $can_emit_services,
                'can_emit_not_services' => $can_emit_not_services,
                'plate_numbers' => $plate_numbers,
                'id' => $row->id,
                'items' => $items,
                'can_create_purchase_order' => $can_create_purchase_order,
                'alter_company' => $alter_company,
                'website_id'  => $row->website_id,
                'order_note' => (object)$orderNote,
                'payment_method_type_id' => $row->payment_method_type_id,
                'soap_type_id' => $row->soap_type_id,
                'external_id' => $row->external_id,
                'number_full' => $row->number_full,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'payment_condition_id' => $row->payment_condition_id,
                'delivery_date' => $row->delivery_date,
                'identifier' => $row->identifier,
                'user_name' => $user_name,
                'seller_name' => $seller ? $seller->name : null,
                'customer_id' => $row->customer_id,
                'customer_name' => $row->customer->name,
                'customer_number' => $row->customer->number,
                'customer_telephone' => $row->customer->telephone,
                'customer_email' => optional($row->customer)->email,
                'exchange_rate_sale' => $row->exchange_rate_sale,
                'currency_type_id' => $row->currency_type_id,
                'total_exportation' => number_format($row->total_exportation,2,'.',''),
                'total_free' => number_format($row->total_free,2,'.',''), 
                'total_unaffected' => number_format($row->total_unaffected,2,'.',''),
                'total_exonerated' => number_format($row->total_exonerated,2,'.',''),
                'total_taxed' => number_format($row->total_taxed,2,'.',''),
                'total_igv' => number_format($row->total_igv,2,'.',''),
                'total' => number_format($row->total,2,'.',''),
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $state_typesData->get($row->state_type_id)->description,
                'documents' => $row->documents->transform(function($row) use($state_typesData) {
                    return [
                        'number_full' => $row->number_full,
                        'is_voided_or_rejected' => $row->isVoidedOrRejected(),
                        'state_type_description' => $state_typesData->get($row->state_type_id)->description,
                    ];
                }),
                'sale_notes' => $saleNotes,
                'sale_opportunity_number_full' => ($row->sale_opportunity) ? $row->sale_opportunity->number_full:null,
                'contract_number_full' => ($row->contract) ? $row->contract->number_full:null,
                'sale_opportunity' => ($row->sale_opportunity) ? $row->sale_opportunity:null,
                'btn_generate' => $btn_generate,
                'btn_generate_cnt' => $btn_generate_cnt,
                'btn_options' => $btn_options,
                'external_id_contract' => $external_id_contract,
                'referential_information' => $row->referential_information,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'print_ticket' => $row->getUrlPrintPdf('ticket'),
                'print_ticket_58' => $row->getUrlPrintPdf('ticket_58'),
                'filename' => $row->filename,
                'message_text' => "Su cotización {$row->number_full} ha sido generado correctamente, " .
                    "puede revisarlo en el siguiente enlace: " . url('') . "/print/quotation/{$row->external_id}/a4" . "",
                'full_number' => $row->number_full,
                'print_a4' => url('')."/print/quotation/{$row->external_id}/a4",
            ];
        });
    }
}
