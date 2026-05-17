<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reglamento institucional</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { color: #0f5f8f; font-size: 16px; margin: 0; }
        h2 { color: #164e63; font-size: 12px; margin: 14px 0 6px 0; }
        ul { margin: 4px 0 0 16px; padding: 0; }
        li { margin-bottom: 4px; line-height: 1.35; }
        .box { background: #f6fbfd; border: 1px solid #8ab4c8; padding: 10px; margin-top: 10px; }
        .sign { margin-top: 28px; }
        .line { border-top: 1px solid #333; width: 42%; margin-top: 36px; padding-top: 4px; text-align: center; font-size: 10px; }
        table.row-sign { width: 100%; }
        table.row-sign td { width: 50%; vertical-align: top; }
        .header { border-bottom: 2px solid #0f5f8f; margin-bottom: 12px; padding-bottom: 8px; width: 100%; }
        .header td { vertical-align: middle; }
        .brand-icon { height: 34px; width: 34px; }
        .meta { color: #475569; font-size: 10px; margin: 2px 0 0 0; }
        .closing { color: #334155; font-size: 10px; margin-top: 14px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:44px;">
                <img class="brand-icon" src="{{ public_path('favicon.png') }}" alt="Icono institucional">
            </td>
            <td>
                <h1>Reglamento institucional - admision</h1>
                <p class="meta">{{ e(config('app.name')) }} - Postulante: {{ e($student->fullName()) }} (DNI {{ e($student->dni) }})</p>
            </td>
        </tr>
    </table>

    <h2>Reglamento y marco general</h2>
    <div class="box">
        <ul>
            <li>El presente documento complementa la ficha de inscripcion y forma parte del expediente de admision.</li>
            <li>La institucion podra verificar la autenticidad de la documentacion presentada en cualquier etapa del proceso.</li>
            <li>Las fechas y modalidades del examen de ingreso seran comunicadas por los canales oficiales.</li>
        </ul>
    </div>

    <h2>Normas basicas</h2>
    <div class="box">
        <ul>
            <li>Respeto mutuo entre estudiantes, personal y autoridades de la institucion.</li>
            <li>Puntualidad y asistencia a las evaluaciones y actividades programadas.</li>
            <li>Uso adecuado de las instalaciones y recursos educativos.</li>
            <li>Prohibicion de conductas que perturben el ambiente de aprendizaje o la seguridad del campus.</li>
        </ul>
    </div>

    <h2>Obligaciones del postulante</h2>
    <div class="box">
        <ul>
            <li>Presentar informacion veraz y actualizada durante todo el proceso de admision.</li>
            <li>Cumplir con los requisitos y cronograma establecido por la institucion.</li>
            <li>Acudir al examen de ingreso con documento de identidad y materiales autorizados.</li>
        </ul>
    </div>

    <h2>Obligaciones del apoderado</h2>
    <div class="box">
        <ul>
            <li>Supervisar el cumplimiento del reglamento y brindar apoyo al proceso educativo del postulante.</li>
            <li>Mantener actualizados los datos de contacto ante la institucion.</li>
            <li>Asumir la responsabilidad derivada de la informacion declarada en la ficha de inscripcion.</li>
        </ul>
    </div>

    <p class="closing">
        Con la firma del presente documento, postulante y apoderado manifiestan haber leido y aceptado el reglamento institucional
        y las normas del proceso de admision.
    </p>

    <table class="row-sign sign">
        <tr>
            <td>
                <div class="line">Firma del postulante</div>
            </td>
            <td>
                <div class="line">Firma del apoderado</div>
            </td>
        </tr>
    </table>
</body>
</html>
