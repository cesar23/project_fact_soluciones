<?php
use App\Models\Tenant\Document;
use App\CoreFacturalo\Helpers\Template\TemplateHelper;
use App\Models\Tenant\SaleNote;

$enabled_sales_agents = App\Models\Tenant\Configuration::getRecordIndividualColumn('enabled_sales_agents');
$style_header = "background-color: #D8E4BC; font-weight: bold;border: 1px solid #000;";
$style_body = "border: 1px solid #000;";
$style_table = "border-collapse: collapse; width: 100%;";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>


    @if (!empty($records))
        <table style="{{$style_table}}">
            <thead>
                <tr>
                    <td style="{{$style_header}}" >ITEM</td>
                    <td style="{{$style_header}}" >FECHA</td>
                    <td style="{{$style_header}}" >TIPO</td>
                    <td style="{{$style_header}}" >NUMERO</td>
                    <td style="{{$style_header}}" >MONEDA</td>
                    <td style="{{$style_header}}" >CODIGO|RUC|DNI</td>
                    <td style="{{$style_header}}" >CLIENTE</td>
                    <td style="{{$style_header}}" >VALOR</td>
                    <td style="{{$style_header}}" >VENTA BOLSA</td>
                    <td style="{{$style_header}}" >EXONERADO</td>
                    <td style="{{$style_header}}" >IGV</td>
                    <td style="{{$style_header}}" >ICBPER</td>
                    <td style="{{$style_header}}" >PERCEPCIÓN</td>
                    <td style="{{$style_header}}" >TOTAL</td>
                    <td style="{{$style_header}}" >SUB</td>
                    <td style="{{$style_header}}" >COSTO</td>
                    <td style="{{$style_header}}" >CTACBLE</td>
                    <td style="{{$style_header}}" >GLOSA</td>
                    <td style="{{$style_header}}" >TDOC REF</td>
                    <td style="{{$style_header}}" >NUMERO REF</td>
                    <td style="{{$style_header}}" >FECHA REF</td>
                    <td style="{{$style_header}}" >IGV REF</td>
                    <td style="{{$style_header}}" >BASE IMP REF</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $key => $value)
                    <?php
                    /** @var \App\Models\Tenant\Document|App\Models\Tenant\SaleNote  $value */
                    $iteration = $loop->iteration;
                    $userCreator = $value->user->name;
                    $document_type = $value->getDocumentType();
                    $seller = \App\CoreFacturalo\Helpers\Template\ReportHelper::getSellerData($value);
                    try {
                        $user = $seller->name;
                    } catch (ErrorException $e) {
                        $user = '';
                    }
                    
                    $date_of_issue = $value->date_of_issue->format('Y-m-d');
                    $document_type_id = $value->document_type_id;
                    $number = $value->series . '-' . $value->number;
                    
                    ?>

                    <tr>
                        <td style="{{$style_body}}">{{ $iteration }}</td>
                        <td style="{{$style_body}}">
                            {{ $date_of_issue }}
                        </td>
                        <td style="{{$style_body}}">
                            {{ $document_type_id }}
                        </td>
                        <td style="{{$style_body}}">
                            {{ $number }}
                        </td>
                        <td style="{{$style_body}}">
                            {{ $value->currency_type_id == 'PEN' ? 'S/' : '$' }}
                        </td>
                        <td style="{{$style_body}}">
                            {{ $value->customer->number }}
                        </td>
                        <td style="{{$style_body}}">
                            {{ $value->customer->name }}
                        </td>
                        <td style="{{$style_body}}">
                            {{ $value->total_value }}
                        </td>
                        <td style="{{$style_body}}"></td>
                        <td style="{{$style_body}}">
                            {{ $value->total_exonerated }}
                        </td>
                        <td style="{{$style_body}}">
                            {{ $value->total_igv }}
                        </td>
                        <td style="{{$style_body}}">
                            0.00
                            {{-- {{ $value->total_icbper }} --}}
                        </td>
                        <td style="{{$style_body}}">
                            0.00
                        </td>
                        <td style="{{$style_body}}">
                            {{ $value->total }}
                        </td>
                        <td style="{{$style_body}}">
                            05
                        </td>
                        <td style="{{$style_body}}"></td>
                        <td style="{{$style_body}}">
                            701211
                        </td>
                        <td style="{{$style_body}}"></td>
                        <td style="{{$style_body}}"></td>
                        <td style="{{$style_body}}"></td>
                        <td style="{{$style_body}}"></td>
                        <td style="{{$style_body}}"></td>
                        <td style="{{$style_body}}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
