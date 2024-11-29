<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Evaluación Cruzada</title>
</head>
<body>
    <h1>Recordatorio: Evaluación Cruzada Pendiente</h1>

    <p>Estimado/a {{ $student->user->name }},</p>

    <p>Este es un recordatorio de que la evaluación cruzada de su grupo para la gestión {{ $crossEvaluation->management->name }} está pendiente y próxima a vencer.</p>

    <p>Su grupo debe evaluar al grupo {{ $crossEvaluation->evaluatedGroup->short_name }}.</p>

    @if($student->is_representative)
        <p>Como representante del grupo, es su responsabilidad asegurarse de que la evaluación se complete y envíe antes de la fecha límite.</p>
    @else
        <p>Por favor, asegúrese de colaborar con su representante de grupo para completar la evaluación a tiempo.</p>
    @endif

    <p>Recuerde que el plazo para completar esta evaluación es de una semana desde su activación.</p>

    <p>Gracias por su pronta atención a este asunto.</p>

    <p>Saludos cordiales,<br>Su Equipo Educativo</p>
</body>
</html>
