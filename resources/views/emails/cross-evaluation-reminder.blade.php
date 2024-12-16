<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio: Evaluación Cruzada Pendiente</title>
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
        <h1>Recordatorio: Evaluación Cruzada Pendiente</h1>
    </div>
    <div class="content">
        <p>Estimado/a {{ $studentName }},</p>
        <p>
            Este es un recordatorio de que la evaluación cruzada de su grupo {{ $groupName }} que está pendiente y
            próxima a vencer.
        </p>
        <img
            src="https://images.unsplash.com/photo-1434626881859-194d67b2b86f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1474&q=80"
            alt="Recordatorio de evaluación"
            class="image">
        <p>
            Su grupo debe evaluar al grupo <strong>{{ $evaluatedGroupName }}</strong>.
        </p>
        @if ($isRepresentative)
            <p>
                Como representante del grupo, es su responsabilidad asegurarse de que la evaluación se complete y envíe
                antes de la fecha límite.
            </p>
        @else
            <p>
                Por favor, asegúrese de colaborar con su representante de grupo para completar la evaluación a tiempo.
            </p>
        @endif
        <p>Recuerde que el plazo para completar esta evaluación es de una semana desde su activación.</p>
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
