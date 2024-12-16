<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Evaluación Cruzada</title>
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
        <h1>Activación de Evaluación Cruzada</h1>
    </div>
    <div class="content">
        <p>Estimado/a {{ $studentName }},</p>
        <p>
            Se ha activado una nueva evaluación cruzada para su grupo {{ $groupName }}.
        </p>
        <img
            src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
            alt="Evaluación cruzada"
            class="image">
        <p>
            Su grupo ha sido asignado para evaluar al grupo <strong>{{ $evaluatedGroupName }}</strong>.
        </p>
        @if ($isRepresentative)
            <p>Como representante del grupo, usted es responsable de enviar la evaluación final.</p>
        @else
            <p>Por favor, colabore con su representante de grupo para completar la evaluación.</p>
        @endif
        <p>El plazo para completar esta evaluación es de una semana a partir de ahora.</p>
        <p>
            Fecha límite: <strong>{{ $deadlineFormatted }}</strong>
        </p>
        <center>
            <a href="{{ env('APP_URL') }}" class="button" style="color: white;">Acceder a la plataforma</a>
        </center>
        <p>Gracias por su participación en este importante proceso.</p>
        <div class="footer">
            <p>Saludos cordiales,<br>El equipo de {{ config('app.name') }}</p>
            <p>© {{ date('Y') }} Red Fox SRL. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
</body>
</html>
