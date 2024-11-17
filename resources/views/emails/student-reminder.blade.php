<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Evaluación Pendiente - Sprint {{ $sprint->name }} - Grupo {{ $groupName }}</title>
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

        .progress {
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
        }

        .progress-bar {
            width: 50%;
            height: 20px;
            background-color: #800080;
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
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Recordatorio de Evaluación Pendiente</h1>
    </div>
    <div class="content">
        <p>Hola {{ $student->name }},</p>
        <p>Te recordamos que tienes una evaluación pendiente por completar:</p>
        <img
            src="https://images.unsplash.com/photo-1435527173128-983b87201f4d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1467&q=80"
            alt="Recordatorio de evaluación" class="image">
        <ul>
            <li><strong>Tipo de Evaluación:</strong> {{ ucfirst($evaluation->evaluationPeriod->type) }}</li>
            <li><strong>Fecha de Inicio:</strong> {{ $evaluation->evaluationPeriod->starts_at->format('d/m/Y') }}</li>
            <li><strong>Fecha de Finalización:</strong> {{ $evaluation->evaluationPeriod->ends_at->format('d/m/Y') }}
            </li>
        </ul>
        <p>Por favor, completa tu evaluación lo antes posible.</p>
        <p>
            <a href="{{ config('app.url') }}" class="button">Ir a la Evaluación</a>
        </p>
    </div>
</div>
</body>
</html>
