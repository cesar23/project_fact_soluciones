<?php

namespace Modules\Report\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class PurchaseListExport implements FromCollection, ShouldAutoSize
{
    use Exportable;
    
    protected $records;
    protected $company_name;
    protected $company_number;
    protected $establishment_description = null;

    public function records($records) {
        $this->records = $records;
        
        return $this;
    }
    
    public function company_name($company_name) {
        $this->company_name = $company_name;
        
        return $this;
    }
    
    public function company_number($company_number) {
        $this->company_number = $company_number;
        
        return $this;
    }
    
    public function establishment_description($establishment_description) {
        $this->establishment_description = $establishment_description;
        
        return $this;
    }

    public function collection()
    {
        $data = collect();
        
        // Agregar encabezados de la empresa
        $data->push([
            'Empresa: ' . $this->company_name,
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
        
        $data->push([
            'RUC: ' . $this->company_number,
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
        
        if ($this->establishment_description) {
            $data->push([
                'Sucursal: ' . $this->establishment_description,
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
        }
        
        // Agregar línea en blanco
        $data->push(['', '', '', '', '', '', '']);
        
        // Agregar encabezados de la tabla
        $data->push([
            'F. Emisión',
            'Proveedor',
            'Estado',
            'Estado de pago',
            'Número',
            'Moneda',
            'Total'
        ]);
        
        // Agregar datos de las compras
        foreach ($this->records as $item) {
            $data->push([
                $item['date_of_issue'],
                $item['supplier_name'] . ' - ' . $item['supplier_number'],
                $item['state_type_description'],
                $item['state_type_payment_description'],
                $item['number'],
                $item['currency_type_id'],
                $item['total']
            ]);
        }
        
        return $data;
    }
}
