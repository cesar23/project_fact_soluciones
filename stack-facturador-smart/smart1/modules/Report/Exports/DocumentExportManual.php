<?php

namespace Modules\Report\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\CoreFacturalo\Helpers\Template\TemplateHelper;
use App\Models\Tenant\Series;
use App\Models\Tenant\Catalogs\DocumentType as DocumentTypeModel; // Renombrado para evitar conflicto de nombre


class DocumentExportManual implements FromCollection, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $company;
    protected $establishment;
    protected $filters;
    protected $categories;
    protected $categories_services;
    protected $columns; // Objeto con la visibilidad de las columnas
    protected $enabled_sales_agents;


    public function records($records) {
        $this->records = $records;
        return $this;
    }

    public function company($company) {
        $this->company = $company;
        return $this;
    }

    public function establishment($establishment) {
        $this->establishment = $establishment;
        return $this;
    }

    public function filters($filters) {
        $this->filters = $filters;
        return $this;
    }

    public function categories($categories) {
        $this->categories = $categories ?: collect(); // Asegurar que sea una colección
        return $this;
    }

    public function categories_services($categories_services) {
        $this->categories_services = $categories_services ?: collect(); // Asegurar que sea una colección
        return $this;
    }

    public function columns($columns) {
        $this->columns = $columns; // Debería ser un objeto como se usa en el Blade
        return $this;
    }
    
    public function enabled_sales_agents($enabled_sales_agents) {
        $this->enabled_sales_agents = $enabled_sales_agents;
        return $this;
    }

    private function _buildHeadingsArray(): array
    {
        $headings = ['#'];

        if ($this->columns && property_exists($this->columns, 'user_seller') && $this->columns->user_seller->visible) {
            $headings[] = 'Usuario/Vendedor';
        }
        $headings = array_merge($headings, ['Tipo Doc', 'Serie', 'Número', 'Fecha emisión', 'Fecha Vencimiento']);

        if ($this->columns && property_exists($this->columns, 'doc_affect') && $this->columns->doc_affect->visible) {
            $headings[] = 'Doc. Afectado';
        }
        if ($this->columns && property_exists($this->columns, 'guides') && $this->columns->guides->visible) {
            $headings[] = '# Guía';
        }
        if ($this->columns && property_exists($this->columns, 'quote') && $this->columns->quote->visible) {
            $headings[] = 'Cotización';
        }
        if ($this->columns && property_exists($this->columns, 'case') && $this->columns->case->visible) {
            $headings[] = 'Caso';
        }
        if ($this->columns && property_exists($this->columns, 'district') && $this->columns->district->visible) {
            $headings[] = 'DIST';
        }
        if ($this->columns && property_exists($this->columns, 'department') && $this->columns->department->visible) {
            $headings[] = 'DPTO';
        }
        if ($this->columns && property_exists($this->columns, 'province') && $this->columns->province->visible) {
            $headings[] = 'PROV';
        }
        if ($this->columns && property_exists($this->columns, 'client_direction') && $this->columns->client_direction->visible) {
            $headings[] = 'Direccion de cliente';
        }
        $headings[] = 'Cliente';
        if ($this->columns && property_exists($this->columns, 'ruc') && $this->columns->ruc->visible) {
            $headings[] = 'RUC';
        }
        $headings[] = 'Estado';
        if ($this->columns && property_exists($this->columns, 'currency_type_id') && $this->columns->currency_type_id->visible) {
            $headings[] = 'Moneda';
        }
        if ($this->columns && property_exists($this->columns, 'web_platforms') && $this->columns->web_platforms->visible) {
            $headings[] = 'Plataforma';
        }
        if ($this->columns && property_exists($this->columns, 'purchase_order') && $this->columns->purchase_order->visible) {
            $headings[] = 'Orden de compra';
        }
        if ($this->columns && property_exists($this->columns, 'note_sale') && $this->columns->note_sale->visible) {
            $headings[] = 'Nota de venta';
        }
        if ($this->columns && property_exists($this->columns, 'date_note') && $this->columns->date_note->visible) {
            $headings[] = 'Fecha N. Venta';
        }
        if ($this->columns && property_exists($this->columns, 'payment_form') && $this->columns->payment_form->visible) {
            $headings[] = 'Forma de pago';
        }
        if ($this->columns && property_exists($this->columns, 'payment_method') && $this->columns->payment_method->visible) {
            $headings[] = 'MÉTODO DE PAGO';
        }
        if ($this->columns && property_exists($this->columns, 'total_charge') && $this->columns->total_charge->visible) {
            $headings[] = 'Total Cargos';
        }
        if ($this->columns && property_exists($this->columns, 'total_exonerated') && $this->columns->total_exonerated->visible) {
            $headings[] = 'Total Exonerado';
        }
        if ($this->columns && property_exists($this->columns, 'total_unaffected') && $this->columns->total_unaffected->visible) {
            $headings[] = 'Total Inafecto';
        }
        if ($this->columns && property_exists($this->columns, 'total_free') && $this->columns->total_free->visible) {
            $headings[] = 'Total Gratuito';
        }
        if ($this->columns && property_exists($this->columns, 'total_taxed') && $this->columns->total_taxed->visible) {
            $headings[] = 'Total Gravado';
        }
        $headings[] = 'Descuento total';
        if ($this->columns && property_exists($this->columns, 'total_igv') && $this->columns->total_igv->visible) {
            $headings[] = 'Total IGV';
        }
        if ($this->columns && property_exists($this->columns, 'total_isc') && $this->columns->total_isc->visible) {
            $headings[] = 'Total ISC';
        }
        if ($this->columns && property_exists($this->columns, 'total') && $this->columns->total->visible) {
            $headings[] = 'Total';
        }
        if ($this->columns && property_exists($this->columns, 'items') && $this->columns->items->visible) {
            $headings[] = 'Total de productos';
        }

        foreach ($this->categories as $category) {
            $headings[] = $category->name;
        }

        foreach ($this->categories_services as $category) {
            $headings[] = $category->name;
        }
        
        $headings[] = 'TC';

        if ($this->enabled_sales_agents) {
            $headings[] = 'Agente';
            $headings[] = 'Datos de referencia';
        }
        
        return $headings;
    }

    private function _mapRecordToArray($row, int $current_map_idx): array
    {
        $document_type = $row->getDocumentType();
        $apply_conversion_to_pen = true;
        $userCreator = $row->user->name;
        $seller = \App\CoreFacturalo\Helpers\Template\ReportHelper::getSellerData($row);
        $user_seller_name = '';
        try {
            $user_seller_name = $seller->name;
        } catch (\ErrorException $e) {
            // do nothing
        }

        $total = $row->total;
        $total_taxed = $row->total_taxed;
        $total_igv = $row->total_igv;
        $total_charge = $row->total_charge;
        $total_exonerated = $row->total_exonerated;
        $total_unaffected = $row->total_unaffected;
        $total_free = $row->total_free;
        $total_discount = $row->total_discount;
        $total_isc = $row->total_isc;
        $currency_type_id = $row->currency_type_id;
        $exchange_rate_sale = $row->exchange_rate_sale;
        if($currency_type_id !== 'PEN' && $apply_conversion_to_pen){
            $total = $row->total * $exchange_rate_sale;
            $total_taxed = $row->total_taxed * $exchange_rate_sale;
            $total_igv = $row->total_igv * $exchange_rate_sale;
            $total_charge = $row->total_charge * $exchange_rate_sale;
            $total_exonerated = $row->total_exonerated * $exchange_rate_sale;
            $total_unaffected = $row->total_unaffected * $exchange_rate_sale;
            $total_free = $row->total_free * $exchange_rate_sale;
            $total_discount = $row->total_discount * $exchange_rate_sale;
            $total_isc = $row->total_isc * $exchange_rate_sale;
            $currency_type_id = 'PEN';
        }

        $stablihsment = \App\CoreFacturalo\Helpers\Template\ReportHelper::getLocationData($row);
        $serie_affec = '';
        if(in_array($document_type->id,["07","08"]) && $row->note) {
            $serie = ($row->note->affected_document) ? $row->note->affected_document->series : $row->note->data_affected_document->series;
            $number =  ($row->note->affected_document) ? $row->note->affected_document->number : $row->note->data_affected_document->number;
            $serie_affec = $serie.' - '.$number;
        }

        $guides = '';
        if(!empty($row->guides)){
            foreach($row->guides as $guide){
                $guides .= $guide->number . " \n";
            }
            $guides = rtrim($guides, " \n");
        }
        
        $payments_description = '';
        if (method_exists($row, 'getDetailedPayment')) { 
             $payments = TemplateHelper::getDetailedPayment($row);
        } else if (method_exists($row, 'payments')) { 
             $payments = TemplateHelper::getDetailedPayment($row);
        } else {
            $payments = [];
        }

        foreach ($payments as $payment_group) {
            foreach ($payment_group as $pay) {
                $payments_description .= $pay['description'] . " / ";
            }
        }
        $payments_description = rtrim($payments_description, " / ");

        $signal = $document_type->id;
        $state = $row->state_type_id;
        
        $total_charge_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_charge;
        if ($signal == '07') $total_charge_val = ($state !== '11') ? -$total_charge : 0;

        $total_exonerated_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_exonerated;
        if ($signal == '07') $total_exonerated_val = ($state !== '11') ? -$total_exonerated : 0;
        
        $total_unaffected_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_unaffected;
        if ($signal == '07') $total_unaffected_val = ($state !== '11') ? -$total_unaffected : 0;

        $total_free_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_free;
        if ($signal == '07') $total_free_val = ($state !== '11') ? -$total_free : 0;

        $total_taxed_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_taxed;
        if ($signal == '07') $total_taxed_val = ($state !== '11') ? -$total_taxed : 0;

        $total_discount_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_discount;

        $total_igv_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_igv;
        if ($signal == '07') $total_igv_val = ($state !== '11') ? -$total_igv : 0;
        
        $total_isc_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total_isc;
        if ($signal == '07') $total_isc_val = ($state !== '11') ? -$total_isc : 0;

        $total_val = (in_array($document_type->id,['01','03']) && in_array($state,['09','11'])) ? 0 : $total;
        if ($signal == '07') $total_val = ($state !== '11') ? -$total : 0;

        $quality_item = 0;
        foreach ($row->items as $itm) {
            $quality_item += $itm->quantity;
        }

        $mapped_data = [];
        $mapped_data[] = $current_map_idx; // Usar el parámetro para el índice

        if ($this->columns && property_exists($this->columns, 'user_seller') && $this->columns->user_seller->visible) {
            $mapped_data[] = ($this->filters['user_type'] === 'CREADOR') ? $userCreator : $user_seller_name;
        }
        $mapped_data = array_merge($mapped_data, [
            $document_type->id,
            $row->series,
            $row->number,
            $row->date_of_issue->format('Y-m-d'),
            isset($row->invoice) ? $row->invoice->date_of_due->format('Y-m-d') : '',
        ]);

        if ($this->columns && property_exists($this->columns, 'doc_affect') && $this->columns->doc_affect->visible) {
            $mapped_data[] = $serie_affec;
        }
        if ($this->columns && property_exists($this->columns, 'guides') && $this->columns->guides->visible) {
            $mapped_data[] = $guides;
        }
        if ($this->columns && property_exists($this->columns, 'quote') && $this->columns->quote->visible) {
            $mapped_data[] = ($row->quotation) ? $row->quotation->number_full : '';
        }
        if ($this->columns && property_exists($this->columns, 'case') && $this->columns->case->visible) {
            $mapped_data[] = isset($row->quotation->sale_opportunity) ? $row->quotation->sale_opportunity->number_full : '';
        }
        if ($this->columns && property_exists($this->columns, 'district') && $this->columns->district->visible) {
            $mapped_data[] = $stablihsment['district'];
        }
        if ($this->columns && property_exists($this->columns, 'department') && $this->columns->department->visible) {
            $mapped_data[] = $stablihsment['department'];
        }
        if ($this->columns && property_exists($this->columns, 'province') && $this->columns->province->visible) {
            $mapped_data[] = $stablihsment['province'];
        }
        if ($this->columns && property_exists($this->columns, 'client_direction') && $this->columns->client_direction->visible) {
            $mapped_data[] = $row->customer->address;
        }
        $mapped_data[] = $row->customer->name;
        if ($this->columns && property_exists($this->columns, 'ruc') && $this->columns->ruc->visible) {
            $mapped_data[] = $row->customer->number;
        }
        $mapped_data[] = $row->state_type->description;
        if ($this->columns && property_exists($this->columns, 'currency_type_id') && $this->columns->currency_type_id->visible) {
            $mapped_data[] = $currency_type_id;
        }

        if ($this->columns && property_exists($this->columns, 'web_platforms') && $this->columns->web_platforms->visible) {
            $platforms_str = '';
            foreach ($row->getPlatformThroughItems() as $platform) {
                $platforms_str .= $platform->name . " / ";
            }
            $mapped_data[] = rtrim($platforms_str, " / ");
        }
        if ($this->columns && property_exists($this->columns, 'purchase_order') && $this->columns->purchase_order->visible) {
            $mapped_data[] = $row->purchase_order;
        }
        if ($this->columns && property_exists($this->columns, 'note_sale') && $this->columns->note_sale->visible) {
            $mapped_data[] = $row->sale_note ? $row->sale_note->number_full : '';
        }
        if ($this->columns && property_exists($this->columns, 'date_note') && $this->columns->date_note->visible) {
            $mapped_data[] = $row->sale_note ? $row->sale_note->date_of_issue->format('Y-m-d') : '';
        }
        if ($this->columns && property_exists($this->columns, 'payment_form') && $this->columns->payment_form->visible) {
            $mapped_data[] = ($row->payments()->count() > 0) ? $row->payments()->first()->payment_method_type->description : '';
        }
        if ($this->columns && property_exists($this->columns, 'payment_method') && $this->columns->payment_method->visible) {
            $mapped_data[] = $payments_description;
        }
        if ($this->columns && property_exists($this->columns, 'total_charge') && $this->columns->total_charge->visible) {
            $mapped_data[] = $total_charge_val;
        }
        if ($this->columns && property_exists($this->columns, 'total_exonerated') && $this->columns->total_exonerated->visible) {
            $mapped_data[] = $total_exonerated_val;
        }
        if ($this->columns && property_exists($this->columns, 'total_unaffected') && $this->columns->total_unaffected->visible) {
            $mapped_data[] = $total_unaffected_val;
        }
        if ($this->columns && property_exists($this->columns, 'total_free') && $this->columns->total_free->visible) {
            $mapped_data[] = $total_free_val;
        }
        if ($this->columns && property_exists($this->columns, 'total_taxed') && $this->columns->total_taxed->visible) {
            $mapped_data[] = $total_taxed_val;
        }
        $mapped_data[] = $total_discount_val;
        if ($this->columns && property_exists($this->columns, 'total_igv') && $this->columns->total_igv->visible) {
            $mapped_data[] = $total_igv_val;
        }
        if ($this->columns && property_exists($this->columns, 'total_isc') && $this->columns->total_isc->visible) {
            $mapped_data[] = $total_isc_val;
        }
        if ($this->columns && property_exists($this->columns, 'total') && $this->columns->total->visible) {
            $mapped_data[] = $total_val;
        }
        if ($this->columns && property_exists($this->columns, 'items') && $this->columns->items->visible) {
            $mapped_data[] = $quality_item;
        }

        foreach ($this->categories as $category) {
            $amount = 0;
            foreach ($row->items as $item) {
                if($item->relation_item && $item->relation_item->category_id == $category->id){
                    $amount += $item->total;
                }
            }
            $mapped_data[] = $amount;
        }

        foreach ($this->categories_services as $category) {
            $quantity = 0;
            foreach ($row->items as $item) {
                if($item->relation_item && $item->relation_item->category_id == $category->id){
                    $quantity += $item->quantity;
                }
            }
            $mapped_data[] = $quantity;
        }

        $mapped_data[] = $row->exchange_rate_sale;

        if ($this->enabled_sales_agents) {
            $mapped_data[] = optional($row->agent)->search_description;
            $mapped_data[] = $row->reference_data;
        }

        return $mapped_data;
    }
    
    public function collection()
    {
        $data = collect();
        $current_row_index = 0; // Contador local para el índice de fila '#'

        $data->push(['Empresa:', $this->company->name, 'Fecha:', now()->format('Y-m-d')]);
        $data->push(['Ruc:', $this->company->number, 'Establecimiento:', $this->establishment->address .' - '. $this->establishment->department->description .' - '. $this->establishment->district->description]);
        
        $reportService = app('Modules\Report\Services\ReportService');
        $filter_row3 = [];
        if(isset($this->filters['seller_id']) && $this->filters['seller_id']) {
            $filter_row3[] = 'Usuario:';
            $filter_row3[] = $reportService->getUserName($this->filters['seller_id']);
        } else {
            $filter_row3[] = '';
            $filter_row3[] = '';
        }
        if(isset($this->filters['person_id']) && $this->filters['person_id']) {
            $filter_row3[] = 'Cliente:';
            $filter_row3[] = $reportService->getPersonName($this->filters['person_id']);
        } else {
            $filter_row3[] = '';
            $filter_row3[] = '';
        }
        $data->push($filter_row3);
        $data->push([]); 

        if ($this->records->isEmpty()) {
            $data->push(['No se encontraron registros.']);
            return $data;
        }

        $all_document_types_from_db = DocumentTypeModel::OnlyAvaibleDocuments()->get()->keyBy('id');
        $all_series_from_db = Series::all()->groupBy('document_type_id');

        $clear_type_ids = $this->records->map(function($value) {
            return $value->getDocumentType()->id;
        })->unique()->values();

        $clear_series_numbers = $this->records->map(function($value) {
            return $value->series;
        })->unique()->values();

        $overall_totals_pen = $this->initializeTotalsArray();
        $overall_totals_usd = $this->initializeTotalsArray();
        $summary_by_series_doc = collect();

        foreach ($clear_type_ids as $doc_type_id) {
            $document_type_info = $all_document_types_from_db->get($doc_type_id);
            if (!$document_type_info) continue;

            $series_for_doc_type = $all_series_from_db->get($doc_type_id, collect())->pluck('number')->unique();
            
            foreach ($clear_series_numbers as $serie_number) {
                if (!$series_for_doc_type->contains($serie_number)) continue;

                $records_filtered = $this->records->filter(function($value) use ($doc_type_id, $serie_number) {
                    return $value->getDocumentType()->id == $doc_type_id && $value->series == $serie_number;
                });

                if ($records_filtered->isEmpty()) continue;

                $data->push([$document_type_info->description . ' - ' . $serie_number]);
                $data->push($this->_buildHeadingsArray()); // Usar el método privado para las cabeceras

                $acum_totals_pen = $this->initializeTotalsArray();
                $acum_totals_usd = $this->initializeTotalsArray();

                foreach ($records_filtered as $key => $value) {
                    $current_row_index++; // Incrementar el índice de fila
                    $data->push($this->_mapRecordToArray($value, $current_row_index)); // Usar el método privado para mapear
                    
                    $this->accumulateTotals($value, $acum_totals_pen, $acum_totals_usd);
                }

                $data->push($this->buildTotalRow('Totales PEN', $acum_totals_pen));
                $data->push($this->buildTotalRow('Totales USD', $acum_totals_usd));
                $data->push([]); 

                $summary_by_series_doc->push([
                    'document_description' => $document_type_info->description,
                    'series_number' => $serie_number,
                    'total_pen' => $acum_totals_pen['total'],
                    'total_usd' => $acum_totals_usd['total'] 
                ]);
                
                foreach ($acum_totals_pen as $key => $val) {
                    $overall_totals_pen[$key] += $val;
                }
                foreach ($acum_totals_usd as $key => $val) {
                    $overall_totals_usd[$key] += $val;
                }
            }
        }
        
        $data->push(['TOTAL POR SERIE Y DOCUMENTO']);
        $data->push(['DOC', 'SERIE', 'TOTAL PEN', 'TOTAL USD']); 
        $total_general_pen_summary = 0;
        $total_general_usd_summary = 0;

        foreach($summary_by_series_doc as $summary) {
            $data->push([
                $summary['document_description'], 
                $summary['series_number'], 
                number_format($summary['total_pen'], 2, '.', ''),
                number_format($summary['total_usd'], 2, '.', '')
            ]);
            $total_general_pen_summary += $summary['total_pen'];
            $total_general_usd_summary += $summary['total_usd'];
        }
        $data->push(['TOTAL GENERAL', '', number_format($total_general_pen_summary, 2, '.', ''), number_format($total_general_usd_summary, 2, '.', '')]);

        return $data;
    }

    private function initializeTotalsArray()
    {
        return [
            'total_charge' => 0,
            'total_exonerated' => 0,
            'total_unaffected' => 0,
            'total_free' => 0,
            'total_taxed' => 0,
            'total_discount' => 0, 
            'total_igv' => 0,
            'total_isc' => 0,
            'total' => 0,
        ];
    }

    private function accumulateTotals($value, &$totals_pen, &$totals_usd)
    {
        $apply_conversion_to_pen = true;
        $document_type_id = $value->getDocumentType()->id;
        $state_type_id = $value->state_type_id;
        $exchange_rate_sale = $value->exchange_rate_sale;
        $currency_type_id = $value->currency_type_id;
        $total_charge = $value->total_charge;
        $total_exonerated = $value->total_exonerated;
        $total_unaffected = $value->total_unaffected;
        $total_free = $value->total_free;
        $total_igv = $value->total_igv;
        $total = $value->total;
        $total_isc = $value->total_isc;
        $total_taxed = $value->total_taxed;
        $total_discount = $value->total_discount;
        if($currency_type_id !== 'PEN' && $apply_conversion_to_pen){
            $total_charge = $value->total_charge * $exchange_rate_sale;
            $total_exonerated = $value->total_exonerated * $exchange_rate_sale;
            $total_unaffected = $value->total_unaffected * $exchange_rate_sale;
            $total_free = $value->total_free * $exchange_rate_sale;
            $total_taxed = $value->total_taxed * $exchange_rate_sale;
            $total_discount = $value->total_discount * $exchange_rate_sale;
            $total_igv = $value->total_igv * $exchange_rate_sale;
            $total_isc = $value->total_isc * $exchange_rate_sale;
            $total = $value->total * $exchange_rate_sale;
            $currency_type_id = 'PEN';
        }

        // Seleccionar el array de totales correcto (PEN o USD)
        $current_totals_ref = ($currency_type_id == 'PEN') ? $totals_pen : $totals_usd;

        $factor = 1;
        if ($document_type_id == '07' && $state_type_id !== '11') { 
            $factor = -1;
        } elseif (in_array($document_type_id, ['01', '03']) && in_array($state_type_id, ['09', '11'])) { 
            $factor = 0;
        }
        
        if ($factor === 0) {
             // No se suma nada
        } else {
            $current_totals_ref['total_charge'] += $total_charge * $factor;
            $current_totals_ref['total_exonerated'] += $total_exonerated * $factor;
            $current_totals_ref['total_unaffected'] += $total_unaffected * $factor;
            $current_totals_ref['total_free'] += $total_free * $factor;
            $current_totals_ref['total_taxed'] += $total_taxed * $factor;
            $current_totals_ref['total_discount'] += $total_discount * ($factor === 0 ? 0 : 1); 
            $current_totals_ref['total_igv'] += $total_igv * $factor;
            $current_totals_ref['total_isc'] += $total_isc * $factor;
            $current_totals_ref['total'] += $total * $factor;
        }

        // Reasignar al array original pasado por referencia
        if ($currency_type_id == 'PEN') {
            $totals_pen = $current_totals_ref;
        } else {
            $totals_usd = $current_totals_ref;
        }
    }

    private function buildTotalRow($label, $totals_array)
    {
        // Construir la fila de totales basada en las cabeceras actuales (obtenidas de _buildHeadingsArray)
        $headings_for_totals = $this->_buildHeadingsArray();
        $row = array_fill(0, count($headings_for_totals), ''); 
        
        $client_col_index = array_search('Cliente', $headings_for_totals);
        if ($client_col_index !== false && $client_col_index > 0) {
             $row[$client_col_index -1] = $label; 
        } else {
            $row[0] = $label; 
        }

        if ($this->columns && property_exists($this->columns, 'total_charge') && $this->columns->total_charge->visible) {
            $idx = array_search('Total Cargos', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total_charge'], 2, '.', '');
        }
        if ($this->columns && property_exists($this->columns, 'total_exonerated') && $this->columns->total_exonerated->visible) {
            $idx = array_search('Total Exonerado', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total_exonerated'], 2, '.', '');
        }
        if ($this->columns && property_exists($this->columns, 'total_unaffected') && $this->columns->total_unaffected->visible) {
            $idx = array_search('Total Inafecto', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total_unaffected'], 2, '.', '');
        }
        if ($this->columns && property_exists($this->columns, 'total_free') && $this->columns->total_free->visible) {
            $idx = array_search('Total Gratuito', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total_free'], 2, '.', '');
        }
        if ($this->columns && property_exists($this->columns, 'total_taxed') && $this->columns->total_taxed->visible) {
            $idx = array_search('Total Gravado', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total_taxed'], 2, '.', '');
        }
        
        $idx_discount = array_search('Descuento total', $headings_for_totals);
        if($idx_discount !== false) $row[$idx_discount] = number_format($totals_array['total_discount'], 2, '.', '');

        if ($this->columns && property_exists($this->columns, 'total_igv') && $this->columns->total_igv->visible) {
            $idx = array_search('Total IGV', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total_igv'], 2, '.', '');
        }
        if ($this->columns && property_exists($this->columns, 'total_isc') && $this->columns->total_isc->visible) {
            $idx = array_search('Total ISC', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total_isc'], 2, '.', '');
        }
        if ($this->columns && property_exists($this->columns, 'total') && $this->columns->total->visible) {
            $idx = array_search('Total', $headings_for_totals);
            if($idx !== false) $row[$idx] = number_format($totals_array['total'], 2, '.', '');
        }
        return $row;
    }

} 