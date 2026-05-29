<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de inscripción</title>

    <style>
        @page {
            margin: 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111827;
            margin: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .title {
            text-align: center;
        }

        .title h1 {
            margin: 0;
            font-size: 18px;
            color: #0f5f8f;
        }

        .title h2 {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: #164e63;
        }

        .mini {
            font-size: 8px;
            color: #555;
        }

        /* GRID DOS COLUMNAS */
        .grid {
            width: 100%;
            border-collapse: collapse;
        }

        .grid td {
            width: 50%;
            vertical-align: top;
            padding: 4px;
        }

        /* SECCIONES */
        .section {
            border: 1px solid #8ab4c8;
            margin-bottom: 6px;
        }

        .section-title {
            background: #0f5f8f;
            color: white;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 9px;
        }

        /* TABLAS */
        table.meta {
            width: 100%;
            border-collapse: collapse;
        }

        table.meta td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            vertical-align: top;
        }

        table.meta td.label {
            width: 36%;
            background: #edf7fb;
            font-weight: bold;
            color: #164e63;
        }

        /* DECLARACIÓN */
        .box {
            border: 1px solid #8ab4c8;
            padding: 7px;
            margin-top: 6px;
            background: #f6fbfd;
        }

        .box strong {
            color: #164e63;
        }

        .muted {
            font-size: 8px;
            color: #475569;
            line-height: 1.3;
        }

        /* FIRMAS */
        .signatures {
            width: 100%;
            margin-top: 18px;
        }

        .signatures td {
            width: 50%;
            text-align: center;
        }

        .line {
            border-top: 1px solid #111;
            width: 70%;
            margin: 28px auto 0 auto;
            padding-top: 4px;
            font-size: 8px;
        }

        .footer {
            text-align: center;
            margin-top: 6px;
            font-size: 7px;
            color: #64748b;
        }

        .brand-icon {
            width: 30px;
            height: 30px;
            margin-bottom: 2px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td width="25%">
                    <img class="brand-icon" src="{{ public_path('favicon.png') }}" alt="Icono institucional"><br>
                    <strong>{{ e(config('app.name')) }}</strong><br>
                    <span class="mini">Sistema de admisión</span>
                </td>

                <td width="50%" class="title">
                    <h1>FICHA DE INSCRIPCIÓN</h1>
                    <h2>Proceso de admisión</h2>
                </td>

                <td width="25%" align="right">
                    <strong>DNI:</strong><br>
                    {{ e($student->dni) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- DOS COLUMNAS -->
    <table class="grid">
        <tr>

            <!-- IZQUIERDA -->
            <td>

                <!-- POSTULANTE -->
                <div class="section">
                    <div class="section-title">
                        DATOS PERSONALES
                    </div>

                    <table class="meta">
                        <tr>
                            <td class="label">Nombres</td>
                            <td>{{ e($student->fullName()) }}</td>
                        </tr>

                        <tr>
                            <td class="label">DNI</td>
                            <td>{{ e($student->dni) }}</td>
                        </tr>

                        <tr>
                            <td class="label">Nacimiento</td>
                            <td>{{ $student->birth_date?->format('d/m/Y') }}</td>
                        </tr>

                        <tr>
                            <td class="label">Sexo</td>
                            <td>{{ e($student->gender) }}</td>
                        </tr>

                        <tr>
                            <td class="label">Teléfono</td>
                            <td>{{ e($student->phone) }}</td>
                        </tr>

                        <tr>
                            <td class="label">Correo</td>
                            <td>{{ e($student->email) }}</td>
                        </tr>

                        <tr>
                            <td class="label">Dirección</td>
                            <td>{{ e($student->address) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- APODERADO -->
                @php
                    $g = $student->guardian;
                @endphp

                <div class="section">
                    <div class="section-title">
                        DATOS DEL APODERADO
                    </div>

                    <table class="meta">
                        <tr>
                            <td class="label">Nombres</td>
                            <td>
                                {{ $g ? e(trim("{$g->first_name} {$g->last_name} {$g->mother_last_name}")) : '—' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">DNI</td>
                            <td>{{ $g ? e($g->dni) : '—' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Teléfono</td>
                            <td>{{ $g ? e($g->phone) : '—' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Parentesco</td>
                            <td>{{ $g ? e($g->relationship) : '—' }}</td>
                        </tr>
                    </table>
                </div>

            </td>

            <!-- DERECHA -->
            <td>

                <!-- COLEGIO -->
                @php
                    $s = $student->school;
                @endphp

                <div class="section">
                    <div class="section-title">
                        COLEGIO DE PROCEDENCIA
                    </div>

                    <table class="meta">
                        <tr>
                            <td class="label">Colegio</td>
                            <td>{{ $s ? e($s->name) : '—' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Ubicación</td>
                            <td>
                                {{ $s ? e(trim("{$s->department} / {$s->province} / {$s->district}")) : '—' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">Egreso</td>
                            <td>{{ $s?->graduation_year }}</td>
                        </tr>
                    </table>
                </div>

                <!-- PROGRAMACIÓN -->
                <div class="section">
                    <div class="section-title">
                        PROGRAMACIÓN ACADÉMICA
                    </div>

                    <table class="meta">
                        <tr>
                            <td class="label">Carrera</td>
                            <td>{{ e($student->career?->name ?? '—') }}</td>
                        </tr>

                        <tr>
                            <td class="label">Sede</td>
                            <td>{{ e($student->schedule?->campus?->name ?? '—') }}</td>
                        </tr>

                        <tr>
                            <td class="label">Ciclo</td>
                            <td>{{ e($student->schedule?->academicCycle?->name ?? '—') }}</td>
                        </tr>

                        <tr>
                            <td class="label">Turno</td>
                            <td>{{ e($student->schedule?->shift?->name ?? '—') }}</td>
                        </tr>

                        <tr>
                            <td class="label">Inscripción</td>
                            <td>{{ $student->registration_date?->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- PAGO -->
                <div class="section">
                    <div class="section-title">
                        DATOS DEL PAGO
                    </div>

                    <table class="meta">
                        <tr>
                            <td class="label">Voucher</td>
                            <td>{{ e($student->payment_voucher_number ?? '---') }}</td>
                        </tr>

                        <tr>
                            <td class="label">Agencia</td>
                            <td>{{ e($student->payment_agency_number ?? '---') }}</td>
                        </tr>

                        <tr>
                            <td class="label">Fecha pago</td>
                            <td>{{ $student->payment_date?->format('d/m/Y') ?? '---' }}</td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <!-- DECLARACIÓN -->
    <div class="box">
        <strong>DECLARACIÓN JURADA</strong>

        <p class="muted">
            El postulante y su apoderado declaran bajo juramento que la información consignada en la presente ficha es verdadera,
            completa y corresponde a documentos vigentes. Asimismo, autorizan a la institución a verificar los datos personales,
            académicos y de pago registrados, y aceptan las normas, cronograma, condiciones administrativas y disposiciones del
            proceso de admisión. La falsedad, duplicidad o inconsistencia de la información podrá generar la anulación de la
            inscripción, sin perjuicio de las acciones que correspondan.
        </p>
    </div>

    <!-- FIRMAS -->
    <table class="signatures">
        <tr>
            <td>
                <div class="line">
                    Firma del postulante
                </div>
            </td>

            @if ($student->guardian)
            <td>
                <div class="line">
                    Firma del apoderado
                </div>
            </td>
            @endif
        </tr>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
