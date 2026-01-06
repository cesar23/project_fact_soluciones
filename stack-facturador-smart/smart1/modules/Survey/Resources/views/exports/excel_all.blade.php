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
        <tr></tr>
        <tr>
            <td></td>
            <td
            colspan="7"
            style="background-color:#2694d4; text-align:center;color:white; border: 1px solid black; padding: 5px;"
            >
        CUESTIONARIO
        </td>
            <td></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="background-color:#2694d4; text-align:left;color:white; border: 1px solid black; padding: 5px;">
                    ENCUESTADO
                </th>
                @foreach ($all_questions as $question)
                    <th
                        style="background-color:#2694d4; text-align:center;color:white; border: 1px solid black; padding: 5px;">
                        <strong>
                            {{ mb_strtoupper($question['question_text']) }}
                        </strong>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($all_responses as $responses)
                <tr>
                    <td style="border: 1px solid black; padding: 5px; text-align: left; ">
                        {{ $responses['respondent_info']['respondent_name'] }}
                    </td>
                    @foreach ($responses['questions'] as $question)
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">
                            @if ($question['answered'])
                            &nbsp;{{$question['answer_text'] }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
