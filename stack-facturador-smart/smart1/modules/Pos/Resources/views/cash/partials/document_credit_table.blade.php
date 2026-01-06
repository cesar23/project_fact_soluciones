
@php

    $all_documents = $data["document_credit"];
  
@endphp


@if (count($all_documents) > 0)
        
    <p align="center" class="title">Documentos dados a crédito</p>
    <table>
        <thead>
        <tr>
            <th>
                #
            </th>
            <th>
                Tipo documento
            </th>
            <th>
                Documento
            </th>
            <th>
                Fecha emisión
            </th>
            <th>
                Cliente/Proveedor
            </th>
            <th>
                N° Documento
            </th>
            <th>
                Moneda
            </th>
    
            <th>
                Total
            </th>
            <th>Tipo de crédito</th>
        </tr>
        </thead>
        <tbody>
        @foreach($all_documents as $key => $value)
            <tr>
                <td class="celda">
                    {{ $loop->iteration }}
                </td>
           
                <td class="celda">
                    {{ $value['type'] }}
                </td>
                <td class="celda">
                    {{ $value['number'] }}
                </td>
                <td class="celda">
                    {{ $value['date_of_issue'] }}
                </td>
                <td class="celda">
                    {{ $value['customer_name'] }}
                </td>
                <td class="celda">
                    {{ $value['customer_number'] }}
                </td>
                <td class="celda">
                    {{ $value['currency_type_id'] }}
                </td>
          
                <td class="celda">
                    {{ $value['total'] }}
                </td>
                <td class="celda">
                    {{ $value['credit_type'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endif



