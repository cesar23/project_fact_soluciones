<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Envio de nota de venta</title>
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
    <p>
        Estimad@:

        {{ $customer->name }} <br>
        {!! $localMessage !!}
        {{-- {{ $localMessage }} --}}

    </p>

    <ul>

    </ul>
</body>

</html>
