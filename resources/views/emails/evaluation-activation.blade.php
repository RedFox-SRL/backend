<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones Activadas</title>
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
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>¡Evaluaciones Activadas!</h1>
    </div>
    <div class="content">
        <h2>Hola {{ $user->name }} {{ $user->last_name }},</h2>
        <p>Las evaluaciones para el sprint actual han sido activadas. Es momento de reflexionar sobre tu desempeño y el
            de tus compañeros.</p>
        <img
            src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
            alt="Evaluación iniciada" class="image">
        <p>Detalles de las evaluaciones:</p>
        <ul>
            @foreach($evaluations as $evaluation)
                <li>{{ ucfirst($evaluation->evaluationPeriod->type) }} evaluación - Fecha
                    límite: {{ $evaluation->evaluationPeriod->ends_at->format('d/m/Y') }}</li>
            @endforeach
        </ul>
        <p>
            <a href="{{ config('app.url') }}/evaluations" class="button">Ir a las Evaluaciones</a>
        </p>
    </div>
</div>
</body>
</html>
