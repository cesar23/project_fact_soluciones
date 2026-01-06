@php
    $company_name = $company->name;
    $company_number = $company->number;
@endphp
<table>
    <tr>
        <td>ffechadoc D</td>
        <td>ffechaven D</td>
        <td>ccoddoc C(2)</td>
        <td>cserie C(20)</td>
        <td>cnumero C(20)</td>
        <td>ccodenti C(11)</td>
        <td>cdesenti C(100)</td>
        <td>ctipdoc C(1)</td>
        <td>ccodruc C(15)</td>
        <td>crazsoc C(100)</td>
        <td>nbase2 N(15,2)</td>
        <td>nbase1 N(15,2)</td>
        <td>nexo N(15,2)</td>
        <td>nina N(15,2)</td>
        <td>nisc N(15,2)</td>
        <td>nigv1 N(15,2)</td>
        <td>nicbpers N(15,2)</td>
        <td>nbase3 N(15,2)</td>
        <td>ntots N(15,2)</td>
        <td>ntc N(10,6)</td>
        <td>freffec D</td>
        <td>crefdoc C(2)</td>
        <td>crefser C(6)</td>
        <td>crefnum C(13)</td>
        <td>cmreg C(1)</td>
        <td>ndolar N(15,2)</td>
        <td>ffechaven2 D</td>
        <td>ccond C(3)</td>
        <td>ccodcos C(9)</td>
        <td>ccodcos2 C(9)</td>
        <td>cctabase C(20)</td>
        <td>cctaicbper C(20)</td>
        <td>cctaotrib C(20)</td>
        <td>cctatot C(20)</td>
        <td>nresp N(1)</td>
        <td>nporre N(5,2)</td>
        <td>nimpres N(15,2)</td>
        <td>cserre C(6)</td>
        <td>cnumre C(13)</td>
        <td>ffecre D</td>
        <td>ccodpresu C(10)</td>
        <td>nigv N(5,2)</td>
        <td>cglosa C(80)</td>
        <td>ccodpago C(3)</td>
        <td>nperdenre N(1)</td>
        <td>nbaseres N(15,2)</td>
        <td>cctaperc C(20)</td>
    </tr>
    @foreach($records as $row)
        @if($row['state_type_id'] == '11')
            <tr>
                <td>{{ $row['date_of_issue'] }}</td>
                <td>{{ $row['date_of_due'] }}</td>
                <td>{{ $row['document_type_id'] }}</td>
                <td>{{ $row['series'] }}</td>
                <td>{{ $row['number'] }}</td>
                <td>01</td>
                <td>{{$company_name}}</td>
                <td>{{ $row['customer_identity_document_type_id'] }}</td>
                <td>{{ $row['customer_number'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>{{ $row['exchange_rate_sale'] }}</td>
                <td>{{ $row['db_date_issue'] }}</td>
                <td>{{ $row['db_document_type_id'] }}</td>
                <td>{{ $row['db_series'] }}</td>
                <td>{{ $row['db_number'] }}</td>
                <td>{{ $row['currency'] }}</td>
                <td>{{ $row['amount_usd'] }}</td>
                <td>{{ $row['date_of_due'] }}</td>
                <td>{{ $row['payment_condition'] }}</td>
                <td></td>
                <td></td>
                <td>{{ $row['account_taxed'] }}</td>
                <td></td>
                <td>{{ $row['account_total'] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>18.00</td>
                <td>{{ $row['aditional_information'] }}</td>
                <td>{{ $row['payment_method'] }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @else
            <tr>
                <td>{{ $row['date_of_issue'] }}</td>
                <td>{{ $row['date_of_due'] }}</td>
                <td>{{ $row['document_type_id'] }}</td>
                <td>{{ $row['series'] }}</td>
                <td>{{ $row['number'] }}</td>
                <td>01</td>
                <td>{{$company_name}}</td>
                <td>{{ $row['customer_identity_document_type_id'] }}</td>
                <td>{{ $row['customer_number'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['total_exportation'] }}</td>
                <td>{{ $row['total_taxed'] }}</td>
                <td>{{ $row['total_exonerated'] }}</td>
                <td>{{ $row['total_unaffected'] }}</td>
                <td>{{ $row['total_isc'] }}</td>
                <td>{{ $row['total_igv'] }}</td>
                <td>{{ $row['total_plastic_bag_taxes'] }}</td>
                <td>{{ $row['total_other_taxes'] }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['exchange_rate_sale'] }}</td>
                <td>{{ $row['db_date_issue'] }}</td>
                <td>{{ $row['db_document_type_id'] }}</td>
                <td>{{ $row['db_series'] }}</td>
                <td>{{ $row['db_number'] }}</td>
                <td>{{ $row['currency'] }}</td>
                <td>{{ $row['amount_usd'] }}</td>
                <td>{{ $row['date_of_due'] }}</td>
                <td>{{ $row['payment_condition'] }}</td>
                <td></td>
                <td></td>
                <td>{{ $row['account_taxed'] }}</td>
                <td></td>
                <td>{{ $row['account_total'] }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>18.00</td>
                <td>{{ $row['aditional_information'] }}</td>
                <td>{{ $row['payment_method'] }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endif
    @endforeach
</table>
