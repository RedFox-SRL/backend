<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación Rechazada</title>
    <style>
        body {
            font-family: Ubuntu, sans-serif;
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
            background-color: #6b21a8;
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
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #9333ea;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
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
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Invitación Rechazada</h1>
    </div>
    <div class="content">
        <p>Has rechazado la invitación para unirte al grupo.</p>
        <p>Si cambias de opinión, por favor contacta al representante del grupo para una nueva invitación.</p>
        <a href="{{ url('/') }}" class="button">Contactar Soporte</a>
    </div>
    <div class="footer">
        <p>&copy; 2024 Sistema de Gestión de Proyectos. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
