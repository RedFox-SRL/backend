<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Evaluaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #800080;
            color: white;
            text-align: center;
            padding: 10px;
        }

        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #800080;
            color: white;
        }

        .chart {
            width: 100%;
            height: 300px;
            margin-bottom: 20px;
        }

        .image {
            width: 100%;
            max-width: 400px;
            height: auto;
            margin: 20px 0;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Resumen de Evaluaciones para {{ $teacher->name }}</h1>
    </div>
    <div class="content">
        <p>Estimado/a {{ $teacher->name }},</p>
        <p>Aquí tiene un resumen detallado de las evaluaciones de sus estudiantes:</p>
        <img
            src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
            alt="Resumen de evaluaciones" class="image">

        @foreach($evaluationPeriods as $period)
            <h2>Período de Evaluación: {{ $period->starts_at->format('d/m/Y') }}
                - {{ $period->ends_at->format('d/m/Y') }}</h2>

            <h3>Progreso de Evaluaciones</h3>
            <div class="chart">
                <canvas id="chart{{ $period->id }}"></canvas>
            </div>
            <script>
                var ctx = document.getElementById('chart{{ $period->id }}').getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Completadas', 'Pendientes'],
                        datasets: [{
                            data: [{{ $period->completed_evaluations_count }}, {{ $period->total_evaluations_count - $period->completed_evaluations_count }}],
                            backgroundColor: ['#800080', '#e74c3c']
                        }]
                    }
                });
            </script>

            <h3>Detalle de Evaluaciones</h3>
            <table>
                <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Tipo de Evaluación</th>
                    <th>Estado</th>
                    <th>Fecha de Completado</th>
                </tr>
                </thead>
                <tbody>
                @foreach($period->studentEvaluations as $evaluation)
                    <tr>
                        <td>{{ $evaluation->evaluator->name }}</td>
                        <td>{{ ucfirst($evaluation->evaluationPeriod->type) }}</td>
                        <td>{{ $evaluation->is_completed ? 'Completada' : 'Pendiente' }}</td>
                        <td>{{ $evaluation->completed_at ? $evaluation->completed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endforeach

        <p>Gracias por su atención. Si necesita más información, no dude en contactarnos.</p>
    </div>
</div>
</body>
</html>
