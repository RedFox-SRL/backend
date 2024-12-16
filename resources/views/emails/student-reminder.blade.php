<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Evaluación Pendiente - Sprint {{ $sprint->title }} - Grupo {{ $groupName }}</title>
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
            background-color: rgb(147, 51, 234);
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
            background-color: rgb(147, 51, 234);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .progress {
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
        }

        .progress-bar {
            width: 50%;
            height: 20px;
            background-color: rgb(147, 51, 234);
            border-radius: 5px;
            text-align: center;
            line-height: 20px;
            color: white;
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
        <h1>Recordatorio de Evaluación Pendiente</h1>
    </div>
    <div class="content">
        <p>Hola {{ $studentName }},</p>
        <p>Te recordamos que tienes una evaluación pendiente por completar:</p>
        <img
            src="https://images.unsplash.com/photo-1435527173128-983b87201f4d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1467&q=80"
            alt="Recordatorio de evaluación" class="image">
        <ul>
            <li><strong>Tipo de Evaluación:</strong>
                @if (strtolower($evaluationType) === 'self')
                    Autoevaluación
                @elseif (strtolower($evaluationType) === 'peer')
                    Evaluación entre pares
                @else
                    {{ $evaluationType }}
                @endif
            </li>
            <li><strong>Sprint:</strong> {{ $sprint->title }}</li>
            <li><strong>Grupo:</strong> {{ $groupName }}</li>
            <li><strong>Fecha de Finalización:</strong> {{ $evaluation->evaluationPeriod->ends_at_formatted ?? 'N/A' }}
            </li>
        </ul>
        <p>Por favor, completa tu evaluación lo antes posible.</p>
        <center>
            <a href="{{ config('app.url') }}" class="button" style="color: white;">Acceder a la plataforma</a>
        </center>
        <div class="footer">
            <p>Saludos cordiales,<br>El equipo de {{ config('app.name') }}</p>
            <p>© {{ date('Y') }} Red Fox SRL. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
</body>
</html>
