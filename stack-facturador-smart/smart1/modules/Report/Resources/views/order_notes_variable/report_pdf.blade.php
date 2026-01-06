<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <title>Reporte de Notas de Pedido</title>
        <style>
            html {
                font-family: sans-serif;
                font-size: 12px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            table th {
                background-color: #f8f9fa;
                padding: 3px;
                font-weight: bold;
                text-align: left;
                border: 1px solid #000;
            }

            table td {
                padding: 2px 3px;
                border: 1px solid #000;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .text-right { 
                text-align: right; 
            }

            .text-left { 
                text-align: left; 
            }

            h2 {
                text-align: center;
                font-size: 16px;
                margin-bottom: 20px;
            }

            .person-type-total {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            .customer-total {
                background-color: #f8f8f8;
            }

            thead {
                display: table-header-group;
            }
            
            @page {
                margin: 10mm 5mm;
            }
        </style>
    </head>
    <body>
        <h2>Reporte de Notas de Pedido</h2>
        
        <table>
            <thead>
                <tr>
                    @foreach($columns as $column => $label)
                        <th class="{{ in_array($column, ['total', 'item_quantity', 'unit_price']) ? 'text-right' : 'text-left' }}" 
                            style="width: {{ $column === 'item_description' ? '25%' : 'auto' }}">
                            {{ $label }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($records as $chunk)
                    @foreach($chunk as $row)
                        <tr>
                            @foreach($columns as $column => $label)
                                <td class="{{ in_array($column, ['total', 'item_quantity', 'unit_price']) ? 'text-right' : 'text-left' }}"
                                    style="width: {{ $column === 'item_description' ? '25%' : 'auto' }}">
                                    @switch($column)
                                        @case('total')
                                        @case('unit_price')
                                            {{ number_format((float)$row->$column, 2, '.', ',') }}
                                            @break
                                        @case('item_quantity')
                                            {{ number_format((float)$row->quantity, 2, '.', ',') }}
                                            @break
                                        @default
                                            {{ $row->$column }}
                                    @endswitch
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </body>
</html>
