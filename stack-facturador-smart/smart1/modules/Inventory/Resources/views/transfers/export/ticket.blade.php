<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota de Traslado</title>
    <style>
        @page {
            margin: 5px;
            padding: 0px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            width: 100%;
        }

        .ticket-container {
            width: 100%;
            padding: 0;
        }

        .header-box {
            border: 1px solid #000;
            padding: 3mm;
            text-align: center;
            margin-bottom: 2mm;
        }

        .header-box .ruc {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header-box .title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header-box .number {
            font-size: 12px;
            font-weight: bold;
        }

        .info-section {
            margin-bottom: 2mm;
            width: 100%;
        }

        .info-row {
            margin-bottom: 1mm;
            width: 100%;
            overflow: hidden;
        }

        .info-label {
            font-weight: bold;
            font-size: 10px;
            display: block;
        }

        .info-value {
            font-size: 10px;
            display: block;
            word-wrap: break-word;
        }

        .divider {
            border-top: 1px solid #ccc;
            margin: 1mm 0;
        }

        .table-container {
            margin: 2mm 0;
            width: 100%;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-container th {
            font-size: 11px;
            font-weight: bold;
            padding: 1mm 0.5mm;
            text-align: left;
            border-top: 0.5px solid #000;
            border-bottom: 0.5px solid #000;
        }

        .table-container td {
            font-size: 11px;
            padding: 0.5mm;
            border-bottom: 0.5px solid #000;
            vertical-align: top;
            word-wrap: break-word;
        }

        .table-container th.description,
        .table-container td.description {
            width: 38%;
        }

        .table-container th.code,
        .table-container td.code {
            width: 18%;
            font-size: 10px;
        }

        .table-container th.unit,
        .table-container td.unit {
            width: 14%;
            text-align: center;
        }

        .table-container th.quantity,
        .table-container td.quantity {
            width: 12%;
            text-align: center;
        }

        .table-container th.serie,
        .table-container td.serie {
            width: 18%;
            font-size: 10px;
        }

        .footer-section {
            margin-top: 5mm;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table tr {
            height: 15mm;
        }

        .footer-table td {
            text-align: center;
            vertical-align: bottom;
            padding-top: 3mm;
            font-size: 10px;
            border: none;
        }

        .footer-date-cell {
            width: 100%;
            padding: 0 2mm;
        }

        .date-line {
            border-bottom: 1px solid #000;
            margin: 0 auto 2mm auto;
            width: 60%;
            height: 10mm;
            display: block;
        }

        .signature-cell {
            width: 50%;
            padding: 0 2mm;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin: 0 auto 2mm auto;
            width: 95%;
            height: 10mm;
            display: block;
        }
    </style>
</head>
<body>
    <?php
    use App\Models\Tenant\Company;
    use App\Models\Tenant\Configuration;
    use App\Models\Tenant\User;
    use Modules\Inventory\Models\Inventory;
    use Modules\Inventory\Models\Warehouse;
    use Illuminate\Support\Carbon;
    use Illuminate\Database\Eloquent\Collection;

    $motivo = !empty($data['motivo']) ? $data['motivo'] : '';
    $created_at = !empty($data['created_at']) ? $data['created_at'] : Carbon::now();
    $serie = !empty($data['serie']) ? $data['serie'] : 'NT';
    $number = !empty($data['number']) ? $data['number'] : '0';
    $document_type = !empty($data['document_type']) ? $data['document_type'] : 'NOTA DE TRASLADO';

    $warehouse_from = !empty($data['warehouse_from']) ? $data['warehouse_from'] : new Warehouse();
    $warehouse_to = !empty($data['warehouse_to']) ? $data['warehouse_to'] : new Warehouse();
    $user = !empty($data['user']) ? $data['user'] : new User();
    $company = !empty($data['company']) ? $data['company'] : new Company();
    $configuration = !empty($data['configuration']) ? $data['configuration'] : new Configuration();
    $inventories = !empty($data['inventories']) ? $data['inventories'] : new Collection();
    $item_transfers = !empty($data['item_transfers']) ? $data['item_transfers'] : collect();
    ?>

    <div class="ticket-container">
        <!-- HEADER -->
        <div class="header-box">
            <div class="ruc">R.U.C. {{ $company->number }}</div>
            <div class="title">{{ $document_type }}</div>
            <div class="number">{{ $serie }} - {{ $number }}</div>
        </div>

        <!-- INFO SECTION -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">ALMACEN INICIAL</span>
                <span class="info-value">{{ $warehouse_from->getDescription() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">DIRECCION</span>
                <span class="info-value">{{ $warehouse_from->address ?? '' }}</span>
            </div>

            <div class="divider"></div>

            <div class="info-row">
                <span class="info-label">ALMACEN DESTINO</span>
                <span class="info-value">{{ $warehouse_to->getDescription() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">DIRECCION</span>
                <span class="info-value">{{ $warehouse_to->address ?? '' }}</span>
            </div>

            <div class="divider"></div>

            <div class="info-row">
                <span class="info-label">MOTIVO</span>
                <span class="info-value">{{ $motivo }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">FECHA DOCUMENTO:</span>
                <span class="info-value">{{ $created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">RESPONSABLE:</span>
                <span class="info-value">{{ $user->getName() }}</span>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="description">DESCRIPCION</th>
                        <th class="code">CODIGO INTERNO</th>
                        <th class="unit">UNIDAD</th>
                        <th class="quantity">CANTIDAD</th>
                        <th class="serie">SERIE LOTE</th>
                    </tr>
                </thead>
                <tbody>
                    @if($inventories->count() > 0)
                        @foreach($inventories as $index => $inventory)
                            <?php
                            $item = $inventory->item;
                            $itemCollection = $item->getCollectionData($configuration);

                            $lots = $item_transfers->filter(function($value) use ($item) {
                                return $value['item_id'] == $item->id;
                            });

                            $qty = $inventory->quantity;
                            ?>
                            <tr>
                                <td class="description" colspan="5">{{ substr($itemCollection['description'], 0, 35) }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="code">{{ $itemCollection['internal_id'] }}</td>
                                <td class="unit">{{ substr($itemCollection['unit_type_text'], 0, 10) }}</td>
                                <td class="quantity">{{ $qty }}</td>
                                <td class="serie">
                                    @foreach($lots as $lot)
                                        {{ $lot['code'] }}@if(!$loop->last),@endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay productos</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- FOOTER -->
        <div class="footer-section">
            <table class="footer-table">
                <tr>
                    <td class="signature-cell">
                        <div class="signature-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <div>Fecha</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <br>
                    </td>
                </tr>
                <tr>
                    <td class="signature-cell">
                        <div class="signature-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <div>Autorizado por</div>
                    </td>
                    <td class="signature-cell">
                        <div class="signature-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <div>Recibido por</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
