<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class DocumentExportTable implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;
    protected $filters;
    protected $records;
    protected $company;
    protected $establishment;
    protected $columns;
    
    public function records($records) {
        $this->records = $records;
        
        return $this;
    }
    
    public function company($company) {
        $this->company = $company;
        
        return $this;
    }
    
    public function filters($filters) {
        $this->filters = $filters;
        
        return $this;
    }
    
    public function establishment($establishment) {
        $this->establishment = $establishment;
        
        return $this;
    }
    
    public function columns($columns) {
        $this->columns = $columns;
        return $this;
    }
    
    public function collection()
    {
        $data = new Collection();
        
        foreach ($this->records as $idx => $row) {
            $item = [];
            $total_payment = $row->payments->sum('payment');
            $balance = number_format($row->total - $total_payment, 2, ".", "");
            if ($row->retention) {
                $balance = number_format($row->total - $row->retention->amount - $total_payment, 2, ".", "");
            } else {
                $balance = number_format($row->total - $total_payment, 2, ".", "");
            }
            if ($balance < 0) {
                $balance = number_format(0, 2, ".", "");
            }
            if($row->document_type_id == "07"){
                $balance = "0.00";
            }
            // Columnas sin condiciones (siempre visibles)
            $item['id'] = $idx + 1;
            $item['date_of_issue'] = $row->date_of_issue->format('d/m/Y');
            $item['number'] = $row->number_full;
            $item['state_type_description'] = $row->state_type->description;
            $item['customer_name'] = $row->customer->name;
            $item['customer_number'] = $row->customer->number;
            $item['total_taxed'] = $row->total_taxed;
            $item['total_igv'] = $row->total_igv;
            $item['total'] = number_format($row->total, 2, '.', '');
            
            // Columnas condicionadas por el objeto columns
            if (in_array('soap_type', $this->columns)) {
                $item['soap_type'] = $row->soap_type->description;
            }
            
            if (in_array('date_of_due', $this->columns)) {
                $item['date_of_due'] = (in_array($row->document_type_id, ['01', '03'])) ? $row->invoice->date_of_due->format('Y-m-d') : null;
            }
            
            if (in_array('date_payment', $this->columns)) {
                $item['date_of_payment'] = $row->date_of_payment;
            }
            
            if (in_array('document_type_id', $this->columns)) {
                $item['document_type'] = $row->document_type->description;
            }
            
            if (in_array('user_name', $this->columns)) {
                $item['user_name'] = $row->user->name;
            }
            
            if (in_array('exchange_rate_sale', $this->columns)) {
                $item['exchange_rate_sale'] = $row->exchange_rate_sale;
            }
            
            if (in_array('currency_type_id', $this->columns)) {
                $item['currency_type_id'] = $row->currency_type_id;
            }
            
            if (in_array('total_exportation', $this->columns)) {
                $item['total_exportation'] = $row->total_exportation;
            }
            
            if (in_array('total_free', $this->columns)) {
                $item['total_free'] = $row->total_free;
            }
            
            if (in_array('total_unaffected', $this->columns)) {
                $item['total_unaffected'] = $row->total_unaffected;
            }
            
            if (in_array('total_exonerated', $this->columns)) {
                $item['total_exonerated'] = $row->total_exonerated;
            }
            
            if (in_array('total_charge', $this->columns)) {
                $item['total_charge'] = $row->total_charge;
            }
            
            if (in_array('balance', $this->columns)) {
                $item['balance'] = $balance;
            }
            
            if (in_array('paid', $this->columns)) {
                $item['paid'] = ($balance == 0) ? 'Pagado' : 'Pendiente';
            }
            
            if (in_array('reference_data', $this->columns)) {
                $item['reference_data'] = $row->reference_data;
            }
            
            if (in_array('purchase_order', $this->columns)) {
                $item['purchase_order'] = $row->purchase_order;
            }
            
            if (in_array('guides', $this->columns) && $row->guides != null) {
                $guides = '';
                    foreach($row->guides as $guide) {
                        $guides .= $guide->number . " ";
                    }
                $item['guides'] = $guides;
            }
            
            if (in_array('plate_numbers', $this->columns)) {
                $plates = '';
                $plate_numbers = $row->getPlateNumbers();
                if (is_iterable($plate_numbers)) {
                    foreach ($plate_numbers as $plate) {
                        try {
                            $plates .= $plate['description'] . ' ';
                        } catch (\Exception $e) {
                            $plates .= ' ';
                        }
                    }
                }
                $item['plate_numbers'] = $plates;
            }
            
            if (in_array('notes', $this->columns)) {
                $notes = (in_array($row->document_type_id, ['01', '03'])) ? $row->affected_documents->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'document_id' => $row->document_id,
                        'note_type_description' => ($row->note_type == 'credit') ? 'NC' : 'ND',
                        'description' => $row->document->number_full,
                    ];
                }) : null;
                $item['notes'] = $notes;
            }
            
            if (in_array('dispatch', $this->columns)) {
                $dispatches = '';
                if (is_iterable($row->dispatches)) {
                foreach($row->dispatches as $dispatch) {
                    $dispatches .= $dispatch->description . ' ';
                    }
                }
                $item['dispatch'] = $dispatches;
            }
            
            if (in_array('sales_note', $this->columns)) {
                $sales_notes = '';
                if (is_iterable($row->sales_note)) {
                    foreach($row->sales_note as $note) {
                        $sales_notes .= $note->number_full;
                    }
                }
                $item['sales_note'] = $sales_notes;
            }
            
            if (in_array('order_note', $this->columns) && isset($row->order_note)) {
                $item['order_note'] = $row->order_note->identifier ?? '';
            }
            
            if (in_array('pending_to_delivery', $this->columns)) {
                $item['pending_to_delivery'] = ($row->pending_to_delivery == 1) ? 'No' : 'Sí';
            }
            
            if (in_array('credit_days', $this->columns)) {
                $item['credit_days'] = $row->credit_days;
            }
            
            if (in_array('send_it', $this->columns)) {
                $item['send_it'] = $row->send_it ? 'Sí' : 'No';
            }
            
            if (in_array('sire', $this->columns)) {
                $item['sire'] = $row->sire ? 'Sí' : 'No';
            }
            
            $data->push($item);
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        // Encabezados que siempre están presentes
        $headers = [
            '#',
            'FECHA DE EMISIÓN',
            'NÚMERO',
            'ESTADO',
            'CLIENTE',
            'NÚMERO DOCUMENTO',
            'TOTAL GRAVADO',
            'TOTAL IGV',
            'TOTAL'
        ];
        
        // Encabezados condicionales según columnas visibles
        if (in_array('soap_type', $this->columns)) {
            $headers[] = 'SOAP';
        }
        
        if (in_array('date_of_due', $this->columns)) {
            $headers[] = 'FECHA VENCIMIENTO';
        }
        
        if (in_array('date_payment', $this->columns)) {
            $headers[] = 'FECHA DE PAGO';
        }
        
        if (in_array('document_type_id', $this->columns)) {
            $headers[] = 'TIPO COMPROBANTE';
        }
        
        if (in_array('user_name', $this->columns)) {
            $headers[] = 'USUARIO';
        }
        
        if (in_array('exchange_rate_sale', $this->columns)) {
            $headers[] = 'TIPO DE CAMBIO';
        }
        
        if (in_array('currency_type_id', $this->columns)) {
            $headers[] = 'MONEDA';
        }
        
        if (in_array('total_exportation', $this->columns)) {
            $headers[] = 'TOTAL EXPORTACIÓN';
        }
        
        if (in_array('total_free', $this->columns)) {
            $headers[] = 'TOTAL GRATUITO';
        }
        
        if (in_array('total_unaffected', $this->columns)) {
            $headers[] = 'TOTAL INAFECTO';
        }
        
        if (in_array('total_exonerated', $this->columns)) {
            $headers[] = 'TOTAL EXONERADO';
        }
        
        if (in_array('total_charge', $this->columns)) {
            $headers[] = 'TOTAL CARGOS';
        }
        
        if (in_array('balance', $this->columns)) {
            $headers[] = 'SALDO';
        }
        
        if (in_array('paid', $this->columns)) {
            $headers[] = 'ESTADO DE PAGO';
        }
        
        if (in_array('reference_data', $this->columns)) {
            $headers[] = 'DATOS DE REFERENCIA';
        }
        
        if (in_array('purchase_order', $this->columns)) {
            $headers[] = 'ORDEN DE COMPRA';
        }
        
        if (in_array('guides', $this->columns)) {
            $headers[] = 'GUÍAS';
        }
        
        if (in_array('plate_numbers', $this->columns)) {
            $headers[] = 'PLACA';
        }
        
        if (in_array('notes', $this->columns)) {
            $headers[] = 'NOTAS C/D';
        }
        
        if (in_array('dispatch', $this->columns)) {
            $headers[] = 'GUÍA DE REMISIÓN';
        }
        
        if (in_array('sales_note', $this->columns)) {
            $headers[] = 'NOTA DE VENTAS';
        }
        
        if (in_array('order_note', $this->columns)) {
            $headers[] = 'PEDIDOS';
        }
        
        if (in_array('pending_to_delivery', $this->columns)) {
            $headers[] = 'PENDIENTE DE ENTREGA';
        }
        
        if (in_array('credit_days', $this->columns)) {
            $headers[] = 'DÍAS DE CRÉDITO';
        }
        
        if (in_array('send_it', $this->columns)) {
            $headers[] = 'CORREO ENVIADO';
        }
        
        if (in_array('sire', $this->columns)) {
            $headers[] = 'SIRE';
        }
        
        return $headers;
    }
}
