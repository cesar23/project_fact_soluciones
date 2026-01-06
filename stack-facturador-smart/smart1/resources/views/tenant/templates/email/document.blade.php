<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Comprobante de Pago Electrónico</title>
    <style>
        body {
            color: #000;
        }

        ul {
            list-style: none;
        }
    </style>
</head>

<body>
    <p>Estimad@:
        @if ($document->customer)
            {{ $document->customer->name }}
        @else
            {{ $document->supplier->name }}
        @endif
        , informamos que su comprobante electrónico ha sido emitido exitosamente.
    </p>
    <p>Los datos de su comprobante electrónico son:</p>
    <ul>
        <li>Razón social: {{ $company->name }}</li>
        <li>Teléfono: {{ $document->establishment->telephone }}</li>
        <li>Fecha de emisión: {{ $document->date_of_issue->format('d/m/Y') }}</li>
        <li>Nro. de comprobante: {{ $document->series . '-' . $document->number }}</li>
        <li>Total: {{ $document->currency_type_id == 'PEN' ? 'S/ ' : '$ ' }} {{ $document->total }}</li>
        @isset($bank_accounts)
            @if (is_iterable($bank_accounts) && count($bank_accounts) > 1)
                @foreach ($bank_accounts as $account)
                    <li>Cuentas bancarias: {{ $account->description }}: {{ $account->number }}</li>
                @endforeach
            @endif
        @endisset
    </ul>
</body>

</html>
