<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmación de inscripción</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f4f5;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background-color:#ffffff;border-radius:8px;border:1px solid #e4e4e7;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 8px 28px;">
                            <p style="margin:0;font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:#71717a;">{{ e(config('app.name')) }}</p>
                            <h1 style="margin:12px 0 0 0;font-size:20px;font-weight:600;color:#18181b;">Inscripción registrada</h1>
                            <p style="margin:16px 0 0 0;font-size:15px;line-height:1.6;color:#3f3f46;">
                                Estimado/a <strong>{{ e($student->fullName()) }}</strong>,<br>
                                confirmamos la recepción de su ficha de inscripción. Adjuntamos la documentación institucional en PDF.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 8px 28px;">
                            <div style="border-top:1px solid #e4e4e7;padding-top:16px;">
                                <p style="margin:0 0 8px 0;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#52525b;">Examen de ingreso</p>
                                <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;font-size:14px;color:#3f3f46;">
                                    <tr>
                                        <td style="padding:4px 0;width:96px;color:#71717a;">Fecha</td>
                                        <td style="padding:4px 0;">{{ $exam->exam_date ? $exam->exam_date->format('d/m/Y') : 'Por confirmar' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0;color:#71717a;">Hora</td>
                                        <td style="padding:4px 0;">{{ $exam->exam_time ? e($exam->exam_time) : 'Por confirmar' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0;vertical-align:top;color:#71717a;">Lugar</td>
                                        <td style="padding:4px 0;">{{ $exam->exam_location ? e($exam->exam_location) : 'Por confirmar' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @if ($exam->institutional_message)
                        <tr>
                            <td style="padding:8px 28px 8px 28px;">
                                <div style="background-color:#fafafa;border:1px solid #e4e4e7;border-radius:6px;padding:14px 16px;">
                                    <p style="margin:0;font-size:13px;font-weight:600;color:#18181b;">Indicaciones</p>
                                    <p style="margin:8px 0 0 0;font-size:14px;line-height:1.6;color:#3f3f46;white-space:pre-line;">{{ e($exam->institutional_message) }}</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:16px 28px 28px 28px;">
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#71717a;">
                                Presente su documento de identidad el día del examen. Ante cualquier duda, comuníquese con la institución.
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="margin:20px 0 0 0;font-size:11px;color:#a1a1aa;">Este mensaje fue generado automáticamente. No responda a este correo.</p>
            </td>
        </tr>
    </table>
</body>
</html>
