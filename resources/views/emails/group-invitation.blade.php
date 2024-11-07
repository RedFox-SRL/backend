<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación a unirse a un grupo</title>
</head>
<body>
    <h1>Invitación a unirse a un grupo</h1>
    <p>Hola {{ $invitation->invitedStudent->user->name }},</p>
    <p>Has sido invitado a unirte al grupo "{{ $invitation->group->name }}" en el sistema de gestión de proyectos.</p>
    <p>Para aceptar la invitación, haz clic en el siguiente enlace:</p>
    <a href="{{ url('/invitations/accept/' . $invitation->token) }}">Aceptar invitación</a>
    <p>Si no deseas unirte al grupo, puedes rechazar la invitación haciendo clic aquí:</p>
    <a href="{{ url('/invitations/reject/' . $invitation->token) }}">Rechazar invitación</a>
    <p>Esta invitación expirará en 6 horas.</p>
    <p>Si no has solicitado esta invitación, puedes ignorar este correo electrónico.</p>
</body>
</html>
