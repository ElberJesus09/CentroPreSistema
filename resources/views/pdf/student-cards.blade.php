<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Carnets de estudiantes</title>
    <style>
        @page { margin: 1cm .75cm; }

        * { box-sizing: border-box; }

        body {
            background: #ffffff;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5px;
            margin: 0;
        }

        .sheet {
            border-collapse: collapse;
            width: 100%;
        }

        .slot {
            height: 5.8cm;
            padding: .2cm;
            vertical-align: top;
            width: 50%;
        }

        .card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: .22cm;
            height: 5.4cm;
            overflow: hidden;
            position: relative;
            width: 8.5cm;
        }

        .header {
            background: #0f2f6f;
            color: #ffffff;
            height: 1.25cm;
            overflow: hidden;
            padding: .18cm .24cm 0;
            position: relative;
        }

        .header .mark-one {
            background: #1d4ed8;
            border-radius: 1.4cm;
            height: 2.6cm;
            opacity: .34;
            position: absolute;
            right: -.95cm;
            top: -1.55cm;
            width: 2.6cm;
        }

        .brand-row {
            border-collapse: collapse;
            position: relative;
            width: 100%;
            z-index: 2;
        }

        .logo-wrap {
            background: #ffffff;
            border-radius: .16cm;
            height: .82cm;
            padding: .07cm;
            text-align: center;
            vertical-align: middle;
            width: .82cm;
        }

        .logo {
            height: .66cm;
            object-fit: contain;
            width: .66cm;
        }

        .brand-text {
            padding-left: .16cm;
            vertical-align: middle;
        }

        .brand-text h1 {
            font-size: 13px;
            letter-spacing: .3px;
            line-height: 1;
            margin: 0 0 2px;
        }

        .brand-text p {
            color: #dbeafe;
            font-size: 6.2px;
            line-height: 1.2;
            margin: 0;
        }

        .card-type {
            background: rgba(15, 23, 42, .18);
            border-radius: .25cm;
            bottom: 0;
            color: #e0f2fe;
            font-size: 6.2px;
            font-weight: bold;
            letter-spacing: .5px;
            padding: .04cm .16cm;
            position: absolute;
            right: .24cm;
            top: .2cm;
            text-transform: uppercase;
            z-index: 2;
        }

        .student-name {
            padding: .18cm .24cm 0;
            text-align: left;
        }

        .student-name h2 {
            color: #0f172a;
            font-size: 10.2px;
            line-height: 1.15;
            margin: 0;
            text-transform: uppercase;
        }

        .content {
            padding: .12cm .24cm 0;
        }

        .body-grid {
            border-collapse: collapse;
            width: 100%;
        }

        .details-cell {
            padding-right: .18cm;
            vertical-align: top;
            width: 5.55cm;
        }

        .qr-cell {
            text-align: center;
            vertical-align: top;
            width: 2.1cm;
        }

        .info {
            border-collapse: collapse;
            width: 100%;
        }

        .info tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .info td {
            font-size: 7.2px;
            line-height: 1.14;
            padding: .055cm 0;
            vertical-align: top;
        }

        .label {
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            width: 1.35cm;
        }

        .value {
            color: #111827;
        }

        .split {
            border-collapse: collapse;
            width: 100%;
        }

        .split td {
            border: 0;
            padding: 0;
            width: 50%;
        }

        .split .label {
            display: block;
            font-size: 6.7px;
            margin-bottom: 1px;
            width: auto;
        }

        .split .value {
            display: block;
        }

        .qr-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: .14cm;
            height: 1.58cm;
            margin: 0 auto;
            padding: .08cm;
            text-align: center;
            vertical-align: middle;
            width: 1.58cm;
        }

        .qr {
            height: 1.38cm;
            object-fit: contain;
            width: 1.38cm;
        }

        .qr-copy {
            color: #64748b;
            font-size: 6px;
            line-height: 1.18;
            margin-top: .08cm;
            text-align: center;
        }

        .qr-copy strong {
            color: #0f172a;
            display: block;
            font-size: 6.4px;
            margin-bottom: 2px;
        }

        .security-note {
            bottom: .12cm;
            color: #94a3b8;
            font-size: 5.8px;
            left: .24cm;
            position: absolute;
            right: .24cm;
            text-align: left;
        }
    </style>
</head>
<body>
    <table class="sheet">
        @foreach (array_chunk($cards, 2) as $row)
            <tr>
                @foreach ($row as $card)
                    @php($student = $card['student'])
                    @php($plain = static fn ($value) => mb_strtoupper(\Illuminate\Support\Str::ascii((string) ($value ?: '---')), 'UTF-8'))
                    <td class="slot">
                        <div class="card">
                            <div class="header">
                                <div class="mark-one"></div>
                                <table class="brand-row">
                                    <tr>
                                        <td class="logo-wrap">
                                            <img class="logo" src="{{ public_path('favicon.png') }}" alt="Logo CPU UNPRG">
                                        </td>
                                        <td class="brand-text">
                                            <h1>CPU UNPRG</h1>
                                            <p>Centro Preuniversitario<br>Universidad Nacional Pedro Ruiz Gallo</p>
                                        </td>
                                    </tr>
                                </table>

                                <div class="card-type">Carnet institucional</div>
                            </div>

                            <div class="student-name">
                                <h2>{{ $plain($student->fullName()) }}</h2>
                            </div>

                            <div class="content">
                                <table class="body-grid">
                                    <tr>
                                        <td class="details-cell">
                                            <table class="info">
                                                <tr>
                                                    <td class="label">DNI</td>
                                                    <td class="value">{{ $plain($student->dni) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Carrera</td>
                                                    <td class="value">{{ $plain($student->career?->name ?? '---') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Periodo</td>
                                                    <td class="value">
                                                        <table class="split">
                                                            <tr>
                                                                <td>
                                                                    <span class="label">Turno</span>
                                                                    <span class="value">{{ $plain($student->schedule?->shift?->name ?? '---') }}</span>
                                                                </td>
                                                                <td>
                                                                    <span class="label">Ciclo</span>
                                                                    <span class="value">{{ $plain($student->academicCycle?->name ?? $student->schedule?->academicCycle?->name ?? '---') }}</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Correo</td>
                                                    <td class="value">{{ $plain($student->email ?: '---') }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="qr-cell">
                                            <div class="qr-box">
                                                <img class="qr" src="{{ $card['qr_path'] }}" alt="QR">
                                            </div>
                                            <div class="qr-copy">
                                                <strong>VALIDACION QR</strong>
                                                Escanee para verificar los datos.
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="security-note">USO EXCLUSIVO PARA IDENTIFICACION ACADEMICA</div>
                        </div>
                    </td>
                @endforeach
                @if (count($row) === 1)
                    <td class="slot"></td>
                @endif
            </tr>
        @endforeach
    </table>
</body>
</html>
