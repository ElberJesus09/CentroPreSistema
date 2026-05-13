<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reglamento institucional</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 12px 0; }
        h2 { font-size: 12px; margin: 14px 0 6px 0; }
        ul { margin: 4px 0 0 16px; padding: 0; }
        li { margin-bottom: 4px; line-height: 1.35; }
        .box { border: 1px solid #999; padding: 10px; margin-top: 10px; }
        .sign { margin-top: 28px; }
        .line { border-top: 1px solid #333; width: 42%; margin-top: 36px; padding-top: 4px; text-align: center; font-size: 10px; }
        table.row-sign { width: 100%; }
        table.row-sign td { width: 50%; vertical-align: top; }
    </style>
</head>
<body>
    <h1>Reglamento institucional — admisión</h1>
    <p style="color:#555;font-size:10px;">{{ e(config('app.name')) }} — Postulante: {{ e($student->fullName()) }} (DNI {{ e($student->dni) }})</p>

    <h2>Reglamento y marco general</h2>
    <div class="box">
        <ul>
            <li>El presente documento complementa la ficha de inscripción y forma parte del expediente de admisión.</li>
            <li>La institución podrá verificar la autenticidad de la documentación presentada en cualquier etapa del proceso.</li>
            <li>Las fechas y modalidades del examen de ingreso serán comunicadas por los canales oficiales.</li>
        </ul>
    </div>

    <h2>Normas básicas</h2>
    <div class="box">
        <ul>
            <li>Respeto mutuo entre estudiantes, personal y autoridades de la institución.</li>
            <li>Puntualidad y asistencia a las evaluaciones y actividades programadas.</li>
            <li>Uso adecuado de las instalaciones y recursos educativos.</li>
            <li>Prohibición de conductas que perturben el ambiente de aprendizaje o la seguridad del campus.</li>
        </ul>
    </div>

    <h2>Obligaciones del postulante</h2>
    <div class="box">
        <ul>
            <li>Presentar información veraz y actualizada durante todo el proceso de admisión.</li>
            <li>Cumplir con los requisitos y cronograma establecido por la institución.</li>
            <li>Acudir al examen de ingreso con documento de identidad y materiales autorizados.</li>
        </ul>
    </div>

    <h2>Obligaciones del apoderado</h2>
    <div class="box">
        <ul>
            <li>Supervisar el cumplimiento del reglamento y brindar apoyo al proceso educativo del postulante.</li>
            <li>Mantener actualizados los datos de contacto ante la institución.</li>
            <li>Asumir la responsabilidad derivada de la información declarada en la ficha de inscripción.</li>
        </ul>
    </div>

    <p style="margin-top:14px;font-size:10px;color:#444;">
        Con la firma del presente documento, postulante y apoderado manifiestan haber leído y aceptado el reglamento institucional
        y las normas del proceso de admisión.
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
