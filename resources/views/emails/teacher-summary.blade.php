<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Evaluaciones del Sprint {{ $sprint->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2, h3 {
            color: #4a5568;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #edf2f7;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Resumen de Evaluaciones del Sprint {{ $sprint->name }}</h1>
    <p>Grupo: {{ $sprint->group->name }}</p>
    <p>Fecha de finalización del sprint: {{ $sprint->end_date->format('d/m/Y') }}</p>

    <h2>Autoevaluaciones</h2>
    @foreach ($summary['self'] as $studentId => $data)
        <h3>{{ $data['name'] }}</h3>
        <table>
            <tr>
                <th>Criterio</th>
                <th>Puntuación</th>
            </tr>
            @foreach ($data['evaluations'][0]['scores'] as $criterion => $score)
                <tr>
                    <td>{{ $criterion }}</td>
                    <td>{{ $score }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <h2>Evaluaciones de Pares</h2>
    @foreach ($summary['peer'] as $studentId => $data)
        <h3>Evaluaciones realizadas por {{ $data['name'] }}</h3>
        @foreach ($data['evaluations'] as $evaluation)
            <h4>Evaluación para: {{ $evaluation['evaluated'] }}</h4>
            <table>
                <tr>
                    <th>Criterio</th>
                    <th>Puntuación</th>
                </tr>
                @foreach ($evaluation['scores'] as $criterion => $score)
                    <tr>
                        <td>{{ $criterion }}</td>
                        <td>{{ $score }}</td>
                    </tr>
                @endforeach
            </table>
        @endforeach
    @endforeach
</div>
</body>
</html>
