<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lotes</title>
</head>

<body>
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Lote</th>
                            <th>Producto</th>
                            <th>Fecha de vencimiento</th>
                            <th>Estado</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php
                                $item = \App\Models\Tenant\Item::select('description')
                                    ->where('id', $value->item_id)
                                    ->first();
                                $state = '';
                                if ($value->state) {
                                    $state = \Modules\Item\Models\ItemLotsGroupState::select('description')
                                        ->where('id', $value->state_id)
                                        ->first()->description;
                                }

                            @endphp

                            <tr>
                                <td class="celda">{{ $loop->iteration }}</td>
                                <td class="celda">{{ $value->code }}</td>
                                <td class="celda">{{ $item->description }}</td>
                                <td class="celda">{{ $value->date_of_due }}</td>
                                <td class="celda">{{ $state }}</td>
                                <td class="celda">{{ $value->quantity }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
