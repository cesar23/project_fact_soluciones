<?php

namespace Modules\Report\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\SalenotePayment;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNoteItem;
use Illuminate\Support\Facades\DB;

class StateAccountCollection extends ResourceCollection
{


    public function toArray($request)
    {


        return $this->collection->transform(function ($row, $key) use ($request) {

            $to_export = $request->to_export == 'true' ? true : false;
        
            $customer_address = $to_export ? $row->person->address : '';
            $customer_contact_name = '';
            $customer_contact_phone = '';
            if ($to_export) {
                $contact = $row->person->contact;
                if ($contact) {
                    $full_name = $contact->full_name;
                    $phone = $contact->phone;
                    if ($full_name) {
                        $customer_contact_name = $full_name;
                        $customer_contact_phone = $phone;
                    }
                }
            }
            $date_of_due = null;
            $affected_document = null;
            $payment_state = '';
            $description = '';
            $type_description = '';
            $items = collect([]);
            if (strpos($row->series, 'F') !== false || strpos($row->series, 'B') !== false) {

                if (in_array($row->document_type_id, ['07', '08']) && $row->note) {

                    $series = ($row->note->affected_document) ? $row->note->affected_document->series : $row->note->data_affected_document->series;
                    $number = ($row->note->affected_document) ? $row->note->affected_document->number : $row->note->data_affected_document->number;
                    $affected_document = $series . ' - ' . $number;
                }
                $pays = DocumentPayment::where('document_id', $row->id);

                $total_paid = number_format($pays->sum('payment'), 2, '.', '');
                $payment_state = number_format($row->total - $total_paid, 2, '.', '');

                $description = $row->state_type ? $row->state_type->description : null;


                $document_type_id = Document::where('id', $row->id)->select('document_type_id')->get();
                $document_type_id = $document_type_id[0]['document_type_id'];
                $type_description = DocumentType::where('id', $document_type_id)->select('description')->get();
                $type_description = $type_description[0]['description'];
            
            } else {
                $document_type = DocumentType::find('80');

                $document_type_id = $document_type->id;

                $row->document_type = $document_type;
                /** @var SaleNote $row */

                $affected_document = null;

                if (in_array($document_type_id, ['07', '08']) && $row->note) {

                    $series = ($row->note->affected_document) ? $row->note->affected_document->series : $row->note->data_affected_document->series;
                    $number = ($row->note->affected_document) ? $row->note->affected_document->number : $row->note->data_affected_document->number;
                    $affected_document = $series . ' - ' . $number;
                }

                $pays = SalenotePayment::where('sale_note_id', $row->id);
                $total_paid = number_format($pays->sum('payment'), 2, '.', '');
                $payment_state = number_format($row->total - $total_paid, 2, '.', '');
                $description = $row->state_type ? $row->state_type->description : null;
                $date_of_due = SaleNote::where('id', $row->id)->select('due_date')->get();

                $date_of_due = $date_of_due[0]['due_date'];
                $type_description = 'NOTA DE VENTA';
            
            }
    
            $items = $this->getItemsForRecord($row, $document_type_id);
            $payments = $to_export ? $this->getPaymentsForRecord($row, $document_type_id): [];
            $seller = $row->getSellerData();
            return [
                'customer_address' => $customer_address,
                'customer_contact_name' => $customer_contact_name,
                'customer_contact_phone' => $customer_contact_phone,
                'customer_id' => $row->customer_id,
                'payments' => $payments,
                'id' => $row->id,
                'payment_condition_id' => $row->payment_condition_id,
                'group_id' => $row->group_id,
                'soap_type_id' => $row->soap_type_id,
                'soap_type_description' => isset($row->soap_type) ? $row->soap_type->description : null,
                'date_of_issue' => $to_export  ?  $row->date_of_issue->format('d/m/Y') :  $row->date_of_issue->format('Y-m-d') ,
                'date_of_due' => (in_array($document_type_id, ['01', '03'])) ? $row->invoice->date_of_due->format('Y-m-d') : (($date_of_due) ? $date_of_due->format('Y-m-d') : null),
                'number' => $row->number_full,
                'customer_name' => (in_array($document_type_id, ['01', '03']) && isset($row->person)) ? $row->person->name : ($document_type_id == '80' ? $row->person->name : null),
                'customer_number' => (in_array($document_type_id, ['01', '03']) && isset($row->person)) ? $row->person->number : ($document_type_id == '80' ? $row->person->number : null),
                'currency_type_id' => $row->currency_type_id,
                'series' => $row->series,
                'establishment_id' => $row->establishment_id,
                'alone_number' => $row->number,
                'purchase_order' => $row->purchase_order,
                'guides' => !empty($row->guides) ? (array)$row->guides : null,

                'total_exportation' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total_exportation, 2, ".", ""),
                'total_exonerated' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total_exonerated, 2, ".", ""),
                'total_unaffected' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total_unaffected, 2, ".", ""),
                'total_free' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total_free, 2, ".", ""),
                'total_taxed' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total_taxed, 2, ".", ""),
                'total_igv' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total_igv, 2, ".", ""),
                'total' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total, 2, ".", ""),
                'total_isc' => (in_array($document_type_id, ['01', '03', '07']) && in_array($row->state_type_id, ['09', '11'])) ? number_format(0, 2, ".", "") : number_format($row->total_isc, 2, ".", ""),
                'total_charge' => $row->total_charge,
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $description,
                'document_type_description' => $type_description,
                'document_type_id' => $document_type_id,
                'affected_document' => $affected_document,
                'user_name' => $seller->name,
                'seller_name' => $seller->name,
                'user_email' => $seller->email,

                'notes' => (in_array($document_type_id, ['01', '03'])) ? $row->affected_documents->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'document_id' => $row->document_id,
                        'note_type_description' => ($row->note_type == 'credit') ? 'NC' : 'ND',
                        'description' => $row->document->number_full,
                    ];
                }) : null,
                'quotation_number_full' => ($row->quotation) ? $row->quotation->number_full : '',
                'sale_opportunity_number_full' => isset($row->quotation->sale_opportunity) ? $row->quotation->sale_opportunity->number_full : '',
                'plate_number' => $row->plate_number,
                'payment_state' => $payment_state,
                'items' =>$items,

            ];
        });
    }

    private function getPaymentsForRecord($record, $document_type_id)
    {
        if (in_array($document_type_id, ['01', '03', '07', '08'])) {
            return DocumentPayment::query()
                ->where('document_id', $record->id)
                ->get()->toArray();
        }
        if ($document_type_id == '80') {
            return SaleNotePayment::query()
                ->where('sale_note_id', $record->id)
                ->get()->toArray();
        }
        return [];
    }
    
    private function getItemsForRecord($record, $document_type_id)
    {
        try {
            // Primero intentar usar la relación original, pero validando el tipo correcto
            if (isset($record->items) && $record->items->count() > 0) {
                // Para Documents - verificar que realmente sea un Document
                if (in_array($document_type_id, ['01', '03', '07', '08'])) {
                    // Validar con query directo que los items corresponden a este document
                    $itemCount = DocumentItem::query()
                        ->where('document_id', $record->id)
                        ->get();
                    
                    if ($itemCount->count() > 0) {
                        return $itemCount->transform(function ($item, $key) {
                            return [
                                'key' => $key + 1,
                                'id' => $item->id,
                                'description' => $item->item->description ?? 'Sin descripción',
                                'quantity' => round($item->quantity ?? 0, 2),
                                'unit_price' => round($item->unit_price ?? 0, 2),
                                'unit_type_id' => $item->item->unit_type_id ?? '',
                                'internal_id' => $item->item->internal_id ?? '',
                                'total' => round($item->total ?? 0, 2),
                            ];
                        });
                    }
                }
                
                // Para SaleNotes - verificar que realmente sea un SaleNote
                if ($document_type_id == '80') {
                    // Validar con query directo que los items corresponden a este sale_note
                    $itemCount = SaleNoteItem::query()
                        ->where('sale_note_id', $record->id)
                        ->get();

                    
                    if ($itemCount->count() > 0) {
                        return $itemCount->transform(function ($item, $key) {
                            return [
                                'key' => $key + 1,
                                'id' => $item->id,
                                'description' => $item->item->description ?? 'Sin descripción',
                                'quantity' => round($item->quantity ?? 0, 2),
                                'unit_price' => round($item->unit_price ?? 0, 2),
                                'unit_type_id' => $item->item->unit_type_id ?? '',
                                'internal_id' => $item->item->internal_id ?? '',
                                'total' => round($item->total ?? 0, 2),
                            ];
                        });
                    }
                }
            }
            
            // Fallback: usar query directo si la relación no funciona
            if (in_array($document_type_id, ['01', '03', '07', '08'])) {
                $items = DB::connection('tenant')->table('document_items')
                    ->join('items', 'document_items.item_id', '=', 'items.id')
                    ->where('document_items.document_id', $record->id)
                    ->select(
                        'document_items.id',
                        'document_items.quantity',
                        'document_items.unit_price',
                        'items.description',
                        'items.unit_type_id',
                        'items.internal_id',
                        'items.total'
                    )
                    ->get();
                    
                return collect($items)->transform(function ($item, $key) {
                    return [
                        'key' => $key + 1,
                        'id' => $item->id,
                        'description' => $item->description ?? 'Sin descripción',
                        'quantity' => round($item->quantity ?? 0, 2),
                        'unit_price' => round($item->unit_price ?? 0, 2),
                        'unit_type_id' => $item->unit_type_id ?? '',
                        'internal_id' => $item->internal_id ?? '',
                        'total' => round($item->total ?? 0, 2),
                    ];
                });
            }
            
            if ($document_type_id == '80') {
                $items = DB::connection('tenant')->table('sale_note_items')
                    ->join('items', 'sale_note_items.item_id', '=', 'items.id')
                    ->where('sale_note_items.sale_note_id', $record->id)
                    ->select(
                        'sale_note_items.id',
                        'sale_note_items.quantity',
                        'sale_note_items.unit_price',
                        'items.description',
                        'items.unit_type_id',
                        'items.internal_id',
                        'items.total'
                    )
                    ->get();
                    
                return collect($items)->transform(function ($item, $key) {
                    return [
                        'key' => $key + 1,
                        'id' => $item->id,
                        'description' => $item->description ?? 'Sin descripción',
                        'quantity' => round($item->quantity ?? 0, 2),
                        'unit_price' => round($item->unit_price ?? 0, 2),
                        'unit_type_id' => $item->unit_type_id ?? '',
                        'internal_id' => $item->internal_id ?? '',
                        'total' => round($item->total ?? 0, 2),
                    ];
                });
            }
            
            return collect([]);
            
        } catch (\Exception $e) {
            return collect([]);
        }
    }
}
