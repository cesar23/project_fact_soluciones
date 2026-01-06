<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Productos</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="4" style="text-align: center;">
                    <h3>
                        <strong>PRODUCTOS</strong>
                    </h3>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <p><b>Empresa: </b></p>
                </td>
                <td colspan="3" align="center">
                    <p><strong>{{ $company->name }}</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>Número: </strong></p>
                </td>
                <td align="center">{{ $number }}</td>
                <td>
                    <p><strong>F. Emisión: </strong></p>
                </td>
                <td align="center">{{ $date }}</td>
            </tr>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th>
                    <strong>Descripción</strong>
                </th>
                <th>
                    <strong>Cantidad</strong>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $key => $value)
                <tr>
                    <td>{{ $value->item->description }}</td>
                    <td>{{ $value->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
</body>

</html>
