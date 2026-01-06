<!DOCTYPE html>
<html lang="es">

<head>
</head>

<body>

    <table class="table" width="100%">
        <thead>
            <tr>
                <th rowspan="2" valign="middle" align="center">
                    Código interno
                </th>
                <th colspan="{{ count($warehouses) }}" align="center">
                    Precio
                </th>
            </tr>
            <tr>
                @foreach ($warehouses as $warehouse)
                    <th>
                        {{ $warehouse->description }}
                    </th>
                @endforeach
            </tr>
        </thead>

    </table>

</body>

</html>
