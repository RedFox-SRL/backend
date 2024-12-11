<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de verificación - {{ config('app.name') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #6200EA;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .content {
            padding: 30px;
        }

        .content h2 {
            color: #6200EA;
            margin-top: 0;
        }

        .verification-code {
            font-size: 36px;
            font-weight: 700;
            color: #6200EA;
            text-align: center;
            margin: 20px 0;
            letter-spacing: 5px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 8px;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .image-container {
            text-align: center;
            margin: 20px 0;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .warning {
            background-color: #FFF3CD;
            border: 1px solid #FFEEBA;
            color: #856404;
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Código de Verificación</h1>
    </div>
    <div class="content">
        <h2>Hola {{ $user->name }} {{ $user->last_name }},</h2>

        <p>Hemos recibido una solicitud de inicio de sesión para tu cuenta en {{ config('app.name') }}.</p>

        <p>Para completar el proceso de inicio de sesión, utiliza el siguiente código de verificación:</p>

        <div class="verification-code">
            {{ $user->verification_code }}
        </div>

        <p>Este código expirará en 15 minutos por razones de seguridad.</p>

        <div class="warning">
            <strong>Importante:</strong>
            <ul>
                <li>Si no has intentado iniciar sesión, ignora este correo.</li>
                <li>Nunca compartas este código con nadie.</li>
                <li>Nuestro equipo nunca te pedirá este código por teléfono o correo electrónico.</li>
                <li>Ingresa el código solo en la página oficial de {{ config('app.name') }}.</li>
            </ul>
        </div>

    </div>
    <div class="footer">
        <p>Saludos cordiales,<br>El equipo de {{ config('app.name') }}</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>

