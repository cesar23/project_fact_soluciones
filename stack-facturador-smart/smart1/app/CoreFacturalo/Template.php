<?php

namespace App\CoreFacturalo;

use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\Log;

class Template
{
    public function pdf($base_template, $template, $company, $document, $format_pdf)
    {
        $configuration = Configuration::getConfig();
        if($template === 'credit' || $template === 'debit') {
            $template = 'note';
        }
        if($document->document_type_id === '31') {
            $template = 'dispatch_carrier';
        }

        $path_template =  $this->validate_template($base_template, $template, $format_pdf);
        if(isset($document->document_type_id) && $document->document_type_id === '01'|| $document->document_type_id === '03') {
            $sale_note = $document->sale_note;
            if($sale_note && !$document->has_prepayment){
                if(( $sale_note->total_canceled == 1 || $sale_note->paid == 1 )&& count($document->payments) == 0 && count($sale_note->payments) > 0){
                    $payments = $sale_note->payments;
                    $allPaymentsHavePrepaymentId = $payments->every(function($payment){
                        return $payment->document_prepayment_id !== null;
                    });
                    
                    if($allPaymentsHavePrepaymentId){
                        $payments = new \Illuminate\Database\Eloquent\Collection([$payments->last()]);
                    }
                    $document->setRelation('payments', $payments);
                }
            }
            if($sale_note && $document->has_prepayment){
                $payments = \App\Models\Tenant\SaleNotePayment::where('document_prepayment_id', $document->id)->get();
                $document->setRelation('payments', $payments);
             } 

             if($document->fee){
                $document->setRelation('fee', $document->fee->transform(function($fee){
                    $fee->amount = $fee->original_amount ?? $fee->amount;
                    return $fee;
                }));
             }
        }
        if(auth()->user() && $configuration->kit_pdf){
            $document = $this->checkGroupItems($document);
        }

        if($configuration->plate_number_config){
        
                $plate_number_document = $document->plateNumberDocument;
                if($plate_number_document){
                    $plate_number_info = $plate_number_document->plateNumber;
                    $document->plate_number_info = $plate_number_info->getInfo();
                }
        }
        return self::render($path_template, $company, $document);
    }

    function checkGroupItems($document) {
        
        if (empty($document->items)) {
            // Si no hay items, devolver el documento tal como está
            return $document;
        }
        // Obtener los items del documento
        $items = $document->items;
        
        // Agrupar items por groupId
        $grouped_items = [];
        $ungrouped_items = [];
        
        foreach($items as $item) {
            // Verificar si el item tiene groupId
            if(isset($item->item->groupId) && $item->item->groupId) {
                $group_id = $item->item->groupId;
                
                if(!isset($grouped_items[$group_id])) {
                    // Primer item del grupo
                    $grouped_items[$group_id] = $item;
                    // Establecer cantidad en 1
                    $grouped_items[$group_id]->quantity = 1;
                    // Usar groupName como descripción
        
                    $grouped_items[$group_id]->item->description = $item->item->groupName;
                    $grouped_items[$group_id]->item->name_product_pdf = $item->item->groupName;
                    $grouped_items[$group_id]->name_product_pdf = $item->item->groupName;
            
                } else {
                    // Sumar totales para items del mismo grupo
                    $grouped_items[$group_id]->total += $item->total;
                    $grouped_items[$group_id]->total_value += $item->total_value;
                    $grouped_items[$group_id]->total_igv += $item->total_igv;
                    
                    // Mantener el color del grupo
                    $grouped_items[$group_id]->item->groupColor = $item->item->groupColor;
                }
            
            
                // Actualizar precio unitario para que sea igual al total
                $grouped_items[$group_id]->unit_price = $grouped_items[$group_id]->total;
                $grouped_items[$group_id]->unit_value = $grouped_items[$group_id]->total_value;
                $grouped_items[$group_id]->item->unit_price = $grouped_items[$group_id]->total;
                $grouped_items[$group_id]->item->sale_unit_price = $grouped_items[$group_id]->total;
            } else {
                // Items sin grupo
                $ungrouped_items[] = $item; 
            }
        }
        
        // Combinar items agrupados con los no agrupados
        $final_items = array_merge(array_values($grouped_items), $ungrouped_items);
        $final_items = new \Illuminate\Database\Eloquent\Collection($final_items);

        // Actualizar items en el documento
        $document->setRelation('items', $final_items);
        // $document->items = $final_items;

        return $document;
    }
    public function preprintedpdf($base_template, $template, $company, $format_pdf)
    {
        if($template === 'credit' || $template === 'debit') {
            $template = 'note';
        }

        $path_template =  $this->validate_preprinted_template($base_template, $template, $format_pdf);

        return self::preprintedrender($path_template, $company);
    }

    public function xml($template, $company, $document)
    {
        return self::render('xml.'.$template, $company, $document);
    }

    private function render($view, $company, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view($view, compact('company', 'document'))->render();
    }

    private function preprintedrender($view, $company)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view($view, compact('company'))->render();
    }

    public function pdfFooter($base_template, $document = null,$format = null)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer', compact('document','format'))->render();
    }

    public function pdfHeader($base_template, $company, $document = null)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.header', compact('company', 'document'))->render();
    }

    public function validate_template($base_template, $template, $format_pdf)
    {
        $path_app_template = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates');
        $path_template_default = 'pdf'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;
        $path_template = 'pdf'.DIRECTORY_SEPARATOR.$base_template.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;



        if(file_exists($path_app_template.DIRECTORY_SEPARATOR.$path_template.'.blade.php')) {
            return str_replace(DIRECTORY_SEPARATOR, '.', $path_template);
        }

        return str_replace(DIRECTORY_SEPARATOR, '.', $path_template_default);
    }

    public function validate_preprinted_template($base_template, $template, $format_pdf)
    {
        $path_app_template = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates');
        $path_template_default = 'preprinted_pdf'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;
        $path_template = 'preprinted_pdf'.DIRECTORY_SEPARATOR.$base_template.DIRECTORY_SEPARATOR.$template.'_'.$format_pdf;



        if(file_exists($path_app_template.DIRECTORY_SEPARATOR.$path_template.'.blade.php')) {
            return str_replace(DIRECTORY_SEPARATOR, '.', $path_template);
        }

        return str_replace(DIRECTORY_SEPARATOR, '.', $path_template_default);
    }


    public function pdfFooterTermCondition($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_term_condition', compact('document'))->render();
    }


    public function pdfFooterLegend($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_legend', compact('document'))->render();
    }

    public function pdfFooterBlank($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_blank', compact('document'))->render();
    }

    public function pdfFooterDispatch($base_template, $document)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_dispatch', compact('document'))->render();
    }


    /**
     *
     * Renderizar pdf por nombre sin considerar formato
     *
     * @param  string $base_template
     * @param  string $template
     * @param  mixed $company
     * @param  mixed $document
     * @return mixed
     */
    public function pdfWithoutFormat($base_template, $template, $company, $document)
    {
        $path_template =  $this->validateTemplateWithoutFormat($base_template, $template);
        return self::render($path_template, $company, $document);
    }


    /**
     *
     * Validar si existe el template
     *
     * @param  string $base_template
     * @param  string $template
     * @return string
     */
    public function validateTemplateWithoutFormat($base_template, $template)
    {
        $path_app_template = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates');
        $path_template_default = 'pdf'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$template;
        $path_template = 'pdf'.DIRECTORY_SEPARATOR.$base_template.DIRECTORY_SEPARATOR.$template;

        if(file_exists($path_app_template.DIRECTORY_SEPARATOR.$path_template.'.blade.php')) return str_replace(DIRECTORY_SEPARATOR, '.', $path_template);

        return str_replace(DIRECTORY_SEPARATOR, '.', $path_template_default);
    }


    /**
     * Imagenes en footer pdf
     *
     * Disponible para cotizacion a4, en template default/default3
     *
     * @param  string $base_template
     * @return string
     */
    public function pdfFooterImages($base_template, $images)
    {
        view()->addLocation(__DIR__.'/Templates');

        return view('pdf.'.$base_template.'.partials.footer_images', compact('images'))->render();
    }

}
