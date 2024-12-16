<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación a unirse a un grupo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: rgb(147, 51, 234);
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .content {
            padding: 30px 20px;
            text-align: center;
            color: #333;
        }

        .content p {
            font-size: 16px;
            margin: 15px 0;
        }

        .button {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 20px;
            background-color: rgb(147, 51, 234);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }

        .button.reject {
            background-color: #e11d48;
            color: #ffffff;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
            background-color: #f4f4f9;
            border-top: 1px solid #ddd;
        }

        .footer p {
            margin: 0;
        }

        .button:hover {
            opacity: 0.9;
        }

        .button:active {
            opacity: 0.8;
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
        <h1>Invitación a Unirse a un Grupo</h1>
    </div>
    <div class="content">
        <p>Hola <strong>{{ $invitation->invitedStudent->user->name }}</strong>,</p>
        <p>Has sido invitado a unirte al grupo <strong>"{{ $invitation->group->short_name }}"</strong> en el sistema de
            gestión de proyectos.</p>
        <p>Para aceptar la invitación, haz clic en el siguiente enlace:</p>
        <a href="{{ url('/api/invitations/accept/' . $invitation->token) }}" class="button">Aceptar invitación</a>
        <p>Si no deseas unirte al grupo, puedes rechazar la invitación haciendo clic aquí:</p>
        <a href="{{ url('/api/invitations/reject/' . $invitation->token) }}" class="button reject">Rechazar
            invitación</a>
        <p>Esta invitación expirará en <strong>6 horas</strong>.</p>
        <p>Si no has solicitado esta invitación, puedes ignorar este correo electrónico.</p>
    </div>
    <div class="footer">
        <p>Saludos cordiales,<br>El equipo de {{ config('app.name') }}</p>
        <p>© {{ date('Y') }} Red Fox SRL. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
