<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {{ config('app.name') }}</title>
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

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #6200EA;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #5000D0;
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
        <h1>¡Bienvenido a {{ config('app.name') }}!</h1>
    </div>
    <div class="content">
        @if($user->role == 'student')
            <h2>Hola {{ $user->name }} {{ $user->last_name }},</h2>

            <p>¡Estamos emocionados de darte la bienvenida a nuestra plataforma para el Taller de Ingeniería de
                Software!</p>

            <div class="image-container">
                <img
                    src="https://images.unsplash.com/photo-1531403009284-440f080d1e12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
                    alt="Estudiantes trabajando" style="max-width: 100%; height: auto;">
            </div>

            <p>Tu cuenta ha sido creada exitosamente como <strong>estudiante</strong>. Estamos ansiosos por ver tus
                contribuciones y tu crecimiento durante este semestre.</p>

            <p>Para comenzar tu experiencia, simplemente haz clic en el botón de abajo:</p>

            <center>
                <a href="{{ env('APP_URL') }}/" class="button" style="color: white;">Acceder a la plataforma</a>
            </center>

            <p>¡Esperamos que tengas una experiencia increíble en nuestra plataforma!</p>
        @else
            <h2>Estimado/a Docente {{ $user->name }} {{ $user->last_name }},</h2>

            <p>Es un placer darle la bienvenida a nuestra plataforma para el Taller de Ingeniería de Software. Su
                experiencia y conocimientos serán fundamentales para el éxito de este curso.</p>

            <div class="image-container">
                <img
                    src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
                    alt="Docente en clase" style="max-width: 100%; height: auto;">
            </div>

            <p>Su cuenta ha sido creada exitosamente como <strong>docente</strong>. Estamos seguros de que su guía será
                invaluable para nuestros estudiantes durante este semestre.</p>

            <p>Para acceder a la plataforma y comenzar a explorar las herramientas disponibles, por favor haga clic en
                el siguiente botón:</p>

            <center>
                <a href="{{ env('APP_URL') }}/" class="button" style="color: white;">Acceder a la plataforma</a>
            </center>

            <p>Agradecemos su compromiso con la educación y esperamos que esta plataforma sea una herramienta valiosa en
                su labor docente.</p>
        @endif
    </div>
    <div class="footer">
        <p>Saludos cordiales,<br>El equipo de {{ config('app.name') }}</p>
        <p>© {{ date('Y') }} Red Fox SRL. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
