<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de inscripción</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 12px 0; }
        h2 { font-size: 12px; margin: 16px 0 6px 0; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.meta td { padding: 3px 6px; vertical-align: top; }
        table.meta td.label { width: 28%; color: #444; }
        .box { border: 1px solid #999; padding: 10px; margin-top: 12px; }
        .muted { color: #555; font-size: 10px; line-height: 1.35; }
        .sign { margin-top: 36px; }
        .line { border-top: 1px solid #333; width: 42%; margin-top: 40px; padding-top: 4px; text-align: center; font-size: 10px; }
        .row-sign { width: 100%; }
        .row-sign td { width: 50%; vertical-align: top; }
    </style>
</head>
<body>
    <h1>Ficha de inscripción</h1>
    <p class="muted">{{ e(config('app.name')) }} — Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <h2>Datos del postulante</h2>
    <table class="meta">
        <tr><td class="label">Apellidos y nombres</td><td>{{ e($student->fullName()) }}</td></tr>
        <tr><td class="label">DNI</td><td>{{ e($student->dni) }}</td></tr>
        <tr><td class="label">Fecha de nacimiento</td><td>{{ $student->birth_date?->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Género</td><td>{{ e($student->gender) }}</td></tr>
        <tr><td class="label">Teléfono</td><td>{{ e($student->phone) }}</td></tr>
        <tr><td class="label">Correo</td><td>{{ e($student->email) }}</td></tr>
        <tr><td class="label">Dirección</td><td>{{ e($student->address) }}</td></tr>
    </table>

    <h2>Apoderado</h2>
    @php
        $g = $student->guardian;
    @endphp
    <table class="meta">
        <tr><td class="label">Apellidos y nombres</td><td>{{ $g ? e(trim("{$g->first_name} {$g->last_name} {$g->mother_last_name}")) : '—' }}</td></tr>
        <tr><td class="label">DNI</td><td>{{ $g ? e($g->dni) : '—' }}</td></tr>
        <tr><td class="label">Teléfono</td><td>{{ $g ? e($g->phone) : '—' }}</td></tr>
        <tr><td class="label">Parentesco</td><td>{{ $g ? e($g->relationship) : '—' }}</td></tr>
    </table>

    <h2>Colegio de procedencia</h2>
    @php
        $s = $student->school;
    @endphp
    <table class="meta">
        <tr><td class="label">Nombre</td><td>{{ $s ? e($s->name) : '—' }}</td></tr>
        <tr><td class="label">Ubicación</td><td>{{ $s ? e(trim("{$s->department} / {$s->province} / {$s->district}")) : '—' }}</td></tr>
        <tr><td class="label">Año de egreso</td><td>{{ $s?->graduation_year }}</td></tr>
    </table>

    <h2>Programación académica</h2>
    <table class="meta">
        <tr><td class="label">Carrera</td><td>{{ e($student->career?->name ?? '—') }}</td></tr>
        <tr><td class="label">Sede</td><td>{{ e($student->schedule?->campus?->name ?? '—') }}</td></tr>
        <tr><td class="label">Ciclo</td><td>{{ e($student->schedule?->academicCycle?->name ?? '—') }}</td></tr>
        <tr><td class="label">Turno</td><td>{{ e($student->schedule?->shift?->name ?? '—') }}</td></tr>
        <tr><td class="label">Fecha de inscripción</td><td>{{ $student->registration_date?->format('d/m/Y') }}</td></tr>
    </table>

    <div class="box">
        <strong>Declaración jurada</strong>
        <p class="muted" style="margin-top:6px;">
            El postulante y el apoderado declaran bajo juramento que los datos consignados en la presente ficha son veraces,
            que conocen el reglamento institucional y las condiciones del proceso de admisión, y se comprometen a cumplir las
            normas de la institución. El incumplimiento de esta declaración podrá dar lugar a la anulación de la inscripción.
        </p>
    </div>

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
