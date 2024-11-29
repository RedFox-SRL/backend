<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Evaluación Cruzada</title>
</head>
<body>
    <h1>Evaluación Cruzada Activada</h1>

    <p>Estimado/a {{ $student->user->name }},</p>

    <p>Se ha activado una nueva evaluación cruzada para su grupo en la gestión {{ $crossEvaluation->management->name }}.</p>

    <p>Su grupo ha sido asignado para evaluar al grupo {{ $crossEvaluation->evaluatedGroup->short_name }}.</p>

    @if($student->is_representative)
        <p>Como representante del grupo, usted es responsable de enviar la evaluación final.</p>
    @else
        <p>Por favor, colabore con su representante de grupo para completar la evaluación.</p>
    @endif

    <p>El plazo para completar esta evaluación es de una semana a partir de ahora.</p>

    <p>Gracias por su participación en este importante proceso.</p>

    <p>Saludos cordiales,<br>Su Equipo Educativo</p>
</body>
</html>
