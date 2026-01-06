<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Suscripciones</title>

    <style>
        .text-danger {
            color: red;
        }
    </style>
</head>

<body>
    <h4>Suscripciones</h4>
    {{-- fecha --}}
    @php
        $date = date('d-m-Y');
        $general_months = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    @endphp
    <h5>Fecha: {{ $date }}</h5>
    {{-- tabla --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Cliente</th>
                <th>Celular</th>
                <th>Comentario</th>
                <th>Categoría</th>

                <th>Fecha</th>
                <th>Color</th>

                <th>Enero</th>
                <th>Febrero</th>
                <th>Marzo</th>
                <th>Abril</th>
                <th>Mayo</th>
                <th>Junio</th>
                <th>Julio</th>
                <th>Agosto</th>
                <th>Septiembre</th>
                <th>Octubre</th>
                <th>Noviembre</th>
                <th>Diciembre</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($records as $idx => $record)
                @php
                    $parent = \App\Models\Tenant\Person::find($record->parent_id);
                    $grade = null;
                    $student = \Modules\Suscription\Models\Tenant\UserRelSuscriptionPlan::where('children_customer_id', $record->id)
                        ->latest()
                        ->first();
                    if ($student) {
                        $grade = $student->grade;
                    }
                    
                    $months = [];
                    // $currentYear = date('Y');
                    $months = [];
                    //for loop for last 12 months
                    for ($i = 0; $i < 12; $i++) {
                        $month = $i + 1;
                        $date = Carbon\Carbon::createFromDate($currentYear, $month, 1)->format('Y-m-d');
                    
                        $payment_suscription = \Modules\Suscription\Models\Tenant\SuscriptionPayment::where('child_id', $record->id)
                            ->where('client_id', $record->parent_id)
                            ->where('period', $date)
                            ->get();
                        $total = 0;
                        foreach ($payment_suscription as $key => $value) {
                            if ($value->document) {
                                $periods = count($value->document->periods) != 0 ? count($value->document->periods) : 1;
                                $total += $value->document->total / $periods;
                            } else {
                                $periods = count($value->sale_note->periods) != 0 ? count($value->sale_note->periods) : 1;
                                $total += $value->sale_note->total / $periods;
                            }
                        }
                    
                        $months[] = $total;
                        $general_months[$i] += $total;
                    }
                    $color = '';
                    if ($record->color) {
                        $color = 'background-color:' . $record->color . ';';
                    }
                @endphp
                <tr>

                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $record->name }}</td>
                    @if ($parent)
                        <td>{{ $parent->name }}</td>
                        <td>{{ $parent->number }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                    <td>{{ $record->observation }}</td>
                    <td>{{ $grade }}</td>
                    <td>{{ $record->person_date }}</td>
                    @if ($color != '')
                        <td style="{{ $color }}">
                        </td>
                    @else
                        <td>
                        </td>
                    @endif

                    @foreach ($months as $month)
                        <td>{{ $month }}</td>
                    @endforeach
                </tr>
            @endforeach
            <tr>
                <td colspan="6"></td>
                <td>
                    Total
                </td>
                @foreach ($general_months as $month)
                    <td>{{ $month }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
</body>

</html>
