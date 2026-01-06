<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td colspan="9" style="border: 1px solid black; padding: 5px; text-align: center;">
                <strong style="text-transform: uppercase;">
                    ENCUESTA: {{ mb_strtoupper($title) }}
                </strong>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <strong>EMPRESA:</strong> {{ $company->name }}
            </td>
            <td colspan="3">
                <strong>N° de participantes:</strong> {{ $number_participants }}
            </td>
            <td colspan="3">
                <strong>FECHA:</strong> {{ date('Y-m-d') }}
            </td>
        </tr>
    </table>
    @foreach ($sections as $section)
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <td colspan="9" style="border: 1px solid black; padding: 5px; text-align: center;">
                    <strong style="text-transform: uppercase;">
                        {{ mb_strtoupper($section['section']) }}
                    </strong>
                </td>
            </tr>
        </table>
        @foreach ($section['questions'] as $key => $array_question)
            @php
                $keys = array_keys($array_question);
                $first_key = $keys[0];
            @endphp
            <table style="border-collapse: collapse;width: 100%;">
                <thead>
                    <tr>
                        <th style="background-color:gray; text-align:center; border: 1px solid black; padding: 5px;"
                            colspan="3">
                            @isset($array_question[$first_key]['question'])
                                <strong>
                                    {{ $array_question[$first_key]['question'] }}
                                </strong>
                            @endisset
                        </th>
                        <th style="background-color:gray; text-align:center; border: 1px solid black; padding: 5px;"
                            colspan="3">
                            @isset($array_question[$first_key + 1]['question'])
                                <strong>

                                    {{ $array_question[$first_key + 1]['question'] }}
                                </strong>
                            @endisset
                        </th>
                        <th style="background-color:gray; text-align:center; border: 1px solid black; padding: 5px;"
                            colspan="3">
                            @isset($array_question[$first_key + 2]['question'])
                                <strong>

                                    {{ $array_question[$first_key + 2]['question'] }}
                                </strong>
                            @endisset
                        </th>
                    </tr>
                    <tr>
                        @for ($i = 0; $i < 3; $i++)
                            @if (isset($array_question[$first_key + $i]['answers']))
                                <th
                                    style="background-color:gray; text-align:center; border: 1px solid black; padding: 5px;">
                                    RESPUESTA</th>
                                <th
                                    style="background-color:gray; text-align:center; border: 1px solid black; padding: 5px;">
                                    CANTIDAD</th>
                                <th
                                    style="background-color:gray; text-align:center; border: 1px solid black; padding: 5px;">
                                    PORCENTAJE</th>
                            @else
                                <th style="background-color:gray; text-align:center; border: 1px solid black; padding: 5px;"
                                    colspan="3">
                                </th>
                            @endif
                        @endfor
                    </tr>
                    @php

                        $max = max(
                            count($array_question[$first_key]['answers']),
                            isset($array_question[$first_key + 1])
                                ? count($array_question[$first_key + 1]['answers'])
                                : 0,
                            isset($array_question[$first_key + 2])
                                ? count($array_question[$first_key + 2]['answers'])
                                : 0,
                        );
                    @endphp

                    @for ($i = 0; $i < $max; $i++)
                        <tr>
                            @for ($j = 0; $j < 3; $j++)
                                @if (isset($array_question[$first_key + $j]['answers'][$i]))
                                    <td style="text-align:center; border: 1px solid black; padding: 5px;">
                                        {{ $array_question[$first_key + $j]['answers'][$i]['answer_text'] }}
                                    </td>
                                    <td style="text-align:center; border: 1px solid black; padding: 5px;">
                                        {{ $array_question[$first_key + $j]['answers'][$i]['count'] }}
                                    </td>
                                    <td style="text-align:center; border: 1px solid black; padding: 5px;">
                                        {{ $array_question[$first_key + $j]['answers'][$i]['percentage'] }}%
                                    </td>
                                @else
                                    <td style="text-align:center; border: 1px solid black; padding: 5px;"
                                        colspan="3">
                                    </td>
                                @endif
                            @endfor
                        </tr>
                    @endfor

                </thead>
            </table>
        @endforeach
    @endforeach
</body>

</html>
