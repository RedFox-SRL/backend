<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones Activadas para Sprint {{ $sprint->title }} - Grupo {{ $groupName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
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

        .button {
            display: inline-block;
            background-color: #800080;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .image {
            width: 100%;
            max-width: 400px;
            height: auto;
            margin: 20px 0;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>¡Evaluaciones Activadas para Sprint {{ $sprint->title }} - Grupo {{ $groupName }}!</h1>
    </div>
    <div class="content">
        <p>Hola {{ $studentName }},</p>
        <p>Las evaluaciones para el sprint "{{ $sprint->title }}" del grupo "{{ $groupName }}" han sido activadas. Es
            momento de reflexionar sobre tu desempeño y el de tus compañeros.</p>
        <img
            src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
            alt="Evaluación iniciada" class="image">
        <p>Detalles de las evaluaciones:</p>
        <ul>
            @foreach($evaluations as $evaluation)
                <li>
                    @if (strtolower($evaluation->evaluationPeriod->type) === 'self')
                        Autoevaluación
                    @elseif (strtolower($evaluation->evaluationPeriod->type) === 'peer')
                        Evaluación entre pares
                    @else
                        {{ ucfirst($evaluation->evaluationPeriod->type) }}
                    @endif
                    - Fecha límite: {{ $evaluation->evaluationPeriod->ends_at_formatted ?? 'N/A' }}
                </li>
            @endforeach
        </ul>
        <center>
            <a href="{{ env('APP_URL') }}" class="button" style="color: white;">Acceder a la plataforma</a>
        </center>
        <div class="footer">
            <p>Saludos cordiales,<br>El equipo de {{ config('app.name') }}</p>
            <p>© {{ date('Y') }} Red Fox SRL. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
</body>
</html>
