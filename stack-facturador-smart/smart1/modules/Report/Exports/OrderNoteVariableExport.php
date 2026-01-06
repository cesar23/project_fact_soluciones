<?php

namespace Modules\Report\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class OrderNoteVariableExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $collection;
    protected $columns;
    protected $mergeRanges = [];
    protected $currentRow = 2;
    protected $date_end;
    protected $date_start;
    private $groupRecordCount = 0;
    public function __construct($collection, $columns, $date_end, $date_start)
    {
        $this->collection = $collection;
        $this->columns = $columns;
        $this->date_end = $date_end;
        $this->date_start = $date_start;
    }

    private function createKey($row)
    {
        return ($row->customer_number ?? '').'-'.($row->customer_name ?? '').'-'.($row->person_type ?? '')  ;
    }

    public function collection()
    {
        $result = new Collection();
        
        $lastCustomer = null;
        $groupStartRow = 2;
        $quantityTotal = 0;
        $amountTotal = 0;
        $grandTotal = 0; // Para el total general
        
        // Verificar si existen las columnas de cantidad y total
        $hasQuantity = in_array('item_quantity', $this->columns);
        $hasTotal = in_array('total', $this->columns);
        $needTotals = $hasQuantity || $hasTotal;
        $first_element_column = $this->columns[0];
        $linesByCustomer = 0;
        foreach ($this->collection->sortBy($first_element_column) as $row) {
            $customerKey = $this->createKey($row);
            
            if ($lastCustomer !== $customerKey) {
                // Agregar fila de totales para el cliente anterior
                if ($lastCustomer !== null && $needTotals && $linesByCustomer > 1) {
                    $totalRow = [];
                    foreach ($this->columns as $column) {
                        $isBeforeTotals = array_search($column, $this->columns) < array_search('item_quantity', $this->columns) || 
                            (!$hasQuantity && array_search($column, $this->columns) < array_search('total', $this->columns));
                        
                        switch ($column) {
                            case 'item_quantity':
                                $totalRow[$column] = $hasQuantity ? $quantityTotal : '';
                                break;
                            case 'total':
                                $totalRow[$column] = $hasTotal ? $amountTotal : '';
                                break;
                            case 'item_description':
                                if ($isBeforeTotals) {
                                    $totalRow[$column] = 'TOTAL';
                                } else {
                                    $totalRow[$column] = '';
                                }
                                break;
                            default:
                                $totalRow[$column] = '';
                        }
                    }
                    $result->push($totalRow);
                    $this->currentRow++;
                }

                
                if ($lastCustomer !== null && $this->currentRow > $groupStartRow) {
                    $this->mergeRanges[] = [
                        'start' => $groupStartRow,
                        'end' => $this->currentRow - 1
                    ];
                }
                
                $lastCustomer = $customerKey;
                $groupStartRow = $this->currentRow;
                $quantityTotal = 0;
                $amountTotal = 0;
                $linesByCustomer = 0;
            }else{
                $linesByCustomer++;
            }

            // Acumular totales
            if ($hasQuantity) $quantityTotal += $row->quantity;
            if ($hasTotal) {
                $amountTotal += $row->total;
                $grandTotal += $row->total; // Acumular total general
            }

            $rowData = [];
            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'person_type':
                        $rowData[$column] = $this->currentRow === $groupStartRow ? $row->person_type : '';
                        break;
                    case 'customer_number':
                        $rowData[$column] = $this->currentRow === $groupStartRow ? $row->customer_number : '';
                        break;
                    case 'customer_name':
                        $rowData[$column] = $this->currentRow === $groupStartRow ? $row->customer_name : '';
                        break;
                    case 'item_description':
                        $rowData[$column] = $row->item_description;
                        break;
                    case 'item_quantity':
                        $rowData[$column] = $row->quantity;
                        break;
                    case 'unit_price':
                        $rowData[$column] = $row->unit_price;
                        break;
                    case 'total':
                        $rowData[$column] = $row->total;
                        break;
                    case 'delivery_date':
                        $rowData[$column] = $row->delivery_date;
                        break;
                    case 'created_time':
                        $rowData[$column] = $row->created_time;
                        break;
                }
            }
            
            $result->push($rowData);
            $this->currentRow++;
        }

        // Procesar el último grupo y sus totales
        if ($lastCustomer !== null) {
            if ($needTotals && $linesByCustomer > 1) {
                $totalRow = [];
                foreach ($this->columns as $column) {
                    $isBeforeTotals = array_search($column, $this->columns) < array_search('item_quantity', $this->columns) || 
                        (!$hasQuantity && array_search($column, $this->columns) < array_search('total', $this->columns));
                    
                    switch ($column) {
                        case 'item_quantity':
                            $totalRow[$column] = $hasQuantity ? $quantityTotal : '';
                            break;
                        case 'total':
                            $totalRow[$column] = $hasTotal ? $amountTotal : '';
                            break;
                        case 'item_description':
                            if ($isBeforeTotals) {
                                $totalRow[$column] = 'TOTAL';
                            } else {
                                $totalRow[$column] = '';
                            }
                            break;
                        default:
                            $totalRow[$column] = '';
                    }
                }
                $result->push($totalRow);
                $this->currentRow++;
            }
            
            $this->mergeRanges[] = [
                'start' => $groupStartRow,
                'end' => $this->currentRow - 1
            ];
        }

        // Agregar total general si existe la columna total
        if ($hasTotal) {
            // Agregar una fila vacía como separador
            $emptyRow = array_fill_keys($this->columns, '');
            $result->push($emptyRow);
            $this->currentRow++;

            // Agregar fila de total general
            $totalGeneralRow = array_fill_keys($this->columns, '');
            $totalGeneralRow['item_description'] = 'TOTAL GENERAL';
            $totalGeneralRow['total'] = $grandTotal;
            $result->push($totalGeneralRow);
            
            // Guardar el rango para aplicar estilo especial
            $this->mergeRanges[] = [
                'start' => $this->currentRow,
                'end' => $this->currentRow,
                'is_grand_total' => true
            ];
        }

        return $result;
    }

    public function headings(): array
    {
        $dateHeader = ["Reporte del {$this->date_start} al {$this->date_end}"];
        $labels = [
            'person_type' => 'Tipo',
            'customer_number' => 'DNI',
            'customer_name' => 'Cliente',
            'item_description' => 'Producto',
            'delivery_date' => 'Fecha de entrega',
            'created_time' => 'Hora',
            'item_quantity' => 'Cantidad',
            'unit_price' => 'Precio',
            'total' => 'Monto'
        ];

        $columnHeaders = array_map(function($column) use ($labels) {
            return $labels[$column] ?? $column;
        }, $this->columns);

        return array_merge([$dateHeader], [$columnHeaders]);
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            2 => ['font' => ['bold' => true]]
        ];

        // Ajustar el estilo para la fila de cabecera con fechas (ahora en la fila 1)
        $colFirst = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1);
        $colLast = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->columns));
        $sheet->mergeCells("{$colFirst}1:{$colLast}1");
        $sheet->getStyle("{$colFirst}1:{$colLast}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $first_element_column = $this->columns[0];
        $customerNumberIndex = array_search($first_element_column, $this->columns);
        $lastColumnIndex = count($this->columns);
        
        if ($customerNumberIndex !== false) {
            $borderStyle = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

            $totalStyle = [
                'font' => ['bold' => true],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            foreach ($this->mergeRanges as $range) {
                $colFirst = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1);
                $colLast = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);
                
                if (isset($range['is_grand_total'])) {
                    // Estilo especial para el total general
                    $sheet->getStyle("{$colFirst}{$range['start']}:{$colLast}{$range['end']}")
                        ->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E8E8E8'],
                            ],
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                ],
                            ],
                        ]);
                } else {
                    // Estilo normal para grupos de cliente
                    $sheet->getStyle("{$colFirst}{$range['start']}:{$colLast}{$range['end']}")
                        ->applyFromArray($borderStyle);
                    
                    // Aplicar estilo a la fila de totales por cliente
                    $sheet->getStyle("{$colFirst}{$range['end']}:{$colLast}{$range['end']}")
                        ->applyFromArray($totalStyle);
                }
            }
        }

        return $styles;
    }
} 