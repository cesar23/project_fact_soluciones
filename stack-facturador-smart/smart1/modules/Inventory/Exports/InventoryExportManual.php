<?php

namespace Modules\Inventory\Exports;

use App\Models\Tenant\ColumnsToReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Tenant\Configuration;

class InventoryExportManual implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithEvents
{
    use Exportable;

    protected $records;
    protected $company;
    protected $establishment;
    protected $format;
    protected $currency;
    protected $totals;
    protected $showSomeColumns = false;
    protected $columns;
    protected $configuration;
    protected $in_stock = false;
    protected $out_stock = false;
    protected $future_stock = false;


    public function __construct()
    {
        $this->configuration = Configuration::first();
        $inventory_reports_index = ColumnsToReport::where('user_id', auth()->user()->id)->where('report', 'inventory_reports_index')->first();
        $columns = [];
        if($inventory_reports_index){
            $columns = $inventory_reports_index->columns;
            $this->in_stock = isset($columns->future_stock->visible) && $columns->future_stock->visible;
            $this->out_stock = isset($columns->out_stock->visible) && $columns->out_stock->visible;
            $this->future_stock = isset($columns->future_stock->visible) && $columns->future_stock->visible;
        }
    }

    public function totals($totals)
    {
        $this->totals = $totals;
        return $this;
    }

    public function showSomeColumns($showSomeColumns)
    {
        $this->showSomeColumns = $showSomeColumns;
        return $this;
    }

    public function currency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function records($records)
    {
        $this->records = $records;
        return $this;
    }

    public function company($company)
    {
        $this->company = $company;
        return $this;
    }

    public function establishment($establishment)
    {
        $this->establishment = $establishment;
        return $this;
    }

    public function format($format)
    {
        $this->format = $format;
        return $this;
    }

    public function collection()
    {
        return collect($this->records);
    }

    public function headings(): array
    {
        $configuration = Configuration::first();
        $headers = [
            '#',
            'Cod. de barras',
            'Cod. Interno',
            'Nombre',
            'Descripción',
            'Categoria'
        ];

        if ($configuration->is_pharmacy) {
            $headers = array_merge($headers, [
                'Laboratorio',
                'Registro sanitario',
                'Lotes'
            ]);
        }

        if ($this->showSomeColumns) {
            $headers = array_merge($headers, ['Stock mínimo']);
        }

        $headers = array_merge($headers, [
            'Stock actual',
            

        ]);
        if ($this->in_stock) {
            $headers = array_merge($headers, ['Entradas futuras']);
        }
        if ($this->out_stock) {
            $headers = array_merge($headers, ['Salidas futuras']);
        }
        if ($this->future_stock) {
            $headers = array_merge($headers, ['Stock futuro']);
        }

        $headers = array_merge($headers, [
            'Vendidos',
        ]);

        if ($this->showSomeColumns) {
            $headers = array_merge($headers, ['Costo', 'Costo Total']);
        }

        if ($this->currency == 'MIX') {
            $headers = array_merge($headers, [
                'Moneda'
            ]);
        }

        $headers = array_merge($headers, [
            'Precio de venta',

        ]);

        if ($this->showSomeColumns) {
            $headers = array_merge($headers, ['Ganancia', 'Ganancia Total']);
        }

        $headers = array_merge($headers, [
            'Marca',
            'Modelo',
            'F. vencimiento',
            'Almacén'
        ]);

        return $headers;
    }

    public function map($row): array
    {
        //inventory_reports_index
    
        $total_line = $row['stock'] * $row['purchase_unit_price'];
        $profit = $row['sale_unit_price'] - $row['purchase_unit_price'];

        $base_data = [
            $this->collection()->search($row) + 1,
            $row['barcode'],
            $row['internal_id'],
            $row['name'],
            $row['description'],
            $row['item_category_name']
        ];

        if ($this->configuration->is_pharmacy) {
            $lots = collect($row['lots_group'])->map(function ($lot) {
                return $lot['code'] . ' - ' . $lot['date_of_due'];
            })->join("\n");

            $pharmacy_data = [
                $row['laboratory'],
                $row['num_reg_san'],
                $lots
            ];
            $base_data = array_merge($base_data, $pharmacy_data);
        }
        $final_data = [];
        if ($this->showSomeColumns) {
            $final_data[] = $row['stock_min'];
        }

        $final_data = array_merge($final_data, [
            $row['stock'],
            
        ]);

        if ($this->in_stock) {
            $final_data[] = $row['in_stock'];
        }
        if ($this->out_stock) {
            $final_data[] = $row['out_stock'];
        }
        if ($this->future_stock) {
            $final_data[] = $row['future_stock'];
        }
        
        $final_data[] = $row['kardex_quantity'];

        if ($this->showSomeColumns) {
            $final_data = array_merge($final_data, [
                $row['purchase_unit_price'],
                $total_line
            ]);
        }

        if ($this->currency == 'MIX') {
            $final_data[] = $row['currency_type_id'] == 'PEN' ? 'S/' : '$';
        }

        $final_data = array_merge($final_data, [
            $row['sale_unit_price'],

        ]);

        if ($this->showSomeColumns) {
            $final_data = array_merge($final_data, [
                number_format($profit, 2, '.', ''),
                number_format(abs($profit * $row['stock']), 2, '.', ''),
            ]);
        }

        $final_data = array_merge($final_data, [
            $row['brand_name'],
            $row['model'],
            $row['date_of_due'],
            $row['warehouse_name']
        ]);

        return array_merge($base_data, $final_data);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Insertamos solo las filas necesarias para el encabezado
                $event->sheet->insertNewRowBefore(1, 5);

                // Encabezado
                $event->sheet->mergeCells('A1:M1');
                $event->sheet->setCellValue('A1', 'Reporte Inventario');

                $event->sheet->setCellValue('A2', 'Empresa:');
                $event->sheet->setCellValue('C2', $this->company->name);

                $event->sheet->setCellValue('A3', 'RUC:');
                $event->sheet->setCellValue('C3', $this->company->number);

                $event->sheet->setCellValue('A4', 'Establecimiento:');
                $event->sheet->setCellValue('C4', $this->establishment->description . ' - ' .
                    $this->establishment->address . ' - ' .
                    $this->establishment->department->description . ' - ' .
                    $this->establishment->district->description);

                $event->sheet->setCellValue('A5', 'Fecha:');
                $event->sheet->setCellValue('C5', date('d/m/Y'));
            },
            AfterSheet::class => function (AfterSheet $event) {
                // Obtenemos la última fila con datos
                $highestRow = $event->sheet->getHighestRow();

                // Agregamos una fila adicional para los totales
                $highestRow++;

                // Agregar totales después de los registros
                if($this->showSomeColumns){
                    if ($this->currency == 'MIX') {
                        $event->sheet->setCellValue('I' . $highestRow, 'Total global (S/)');
                        
                        $event->sheet->setCellValue('J' . $highestRow, 'S/ ' . number_format($this->totals->purchase_unit_price_pen, 2, '.', ''));
                        $event->sheet->setCellValue('K' . $highestRow, 'S/ ' . number_format($this->totals->total_pen, 2, '.', ''));
                        $event->sheet->setCellValue('M' . $highestRow, 'S/ ' . number_format($this->totals->sale_unit_price_pen, 2, '.', ''));
                        $event->sheet->setCellValue('N' . $highestRow, 'S/ ' . number_format($this->totals->total_profit_pen, 2, '.', ''));
                        $event->sheet->setCellValue('O' . $highestRow, 'S/ ' . number_format($this->totals->total_all_profit_pen, 2, '.', ''));
    
                        // Fila para USD
                        $highestRow++;
                        $event->sheet->setCellValue('I' . $highestRow, 'Total global ($)');
                        $event->sheet->setCellValue('J' . $highestRow, '$ ' . number_format($this->totals->purchase_unit_price_usd, 2, '.', ''));
                        $event->sheet->setCellValue('K' . $highestRow, '$ ' . number_format($this->totals->total_usd, 2, '.', ''));
                        $event->sheet->setCellValue('M' . $highestRow, '$ ' . number_format($this->totals->sale_unit_price_usd, 2, '.', ''));
                        $event->sheet->setCellValue('N' . $highestRow, '$ ' . number_format($this->totals->total_profit_usd, 2, '.', ''));
                        $event->sheet->setCellValue('O' . $highestRow, '$ ' . number_format($this->totals->total_all_profit_usd, 2, '.', ''));
    
                        // Aplicar estilo a los totales
                        $event->sheet->getStyle('I' . ($highestRow - 1) . ':O' . $highestRow)->getFont()->setBold(true);
                    } else {
                        $currency_symbol = $this->currency == 'PEN' ? 'S/' : '$';
                        $event->sheet->setCellValue('I' . $highestRow, 'Total global');
                        $event->sheet->setCellValue('J' . $highestRow, $currency_symbol . ' ' . number_format($this->totals->purchase_unit_price, 2, '.', ''));
                        $event->sheet->setCellValue('K' . $highestRow, $currency_symbol . ' ' . number_format($this->totals->total, 2, '.', ''));
                        $event->sheet->setCellValue('L' . $highestRow, $currency_symbol . ' ' . number_format($this->totals->sale_unit_price, 2, '.', ''));
                        $event->sheet->setCellValue('M' . $highestRow, $currency_symbol . ' ' . number_format($this->totals->total_profit, 2, '.', ''));
                        $event->sheet->setCellValue('N' . $highestRow, $currency_symbol . ' ' . number_format($this->totals->total_all_profit, 2, '.', ''));
    
                        // Aplicar estilo a los totales
                        $event->sheet->getStyle('I' . $highestRow . ':N' . $highestRow)->getFont()->setBold(true);
                    }
                }

                // Estilos para el encabezado
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(24);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $event->sheet->getStyle('A2:A5')->getFont()->setBold(true);
            }
        ];
    }
}
