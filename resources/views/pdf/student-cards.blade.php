<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Carnets de estudiantes</title>
    <style>
        @page { margin: 22px 20px; }
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 8px; margin: 0; }
        .sheet { width: 100%; border-collapse: collapse; }
        .slot { height: 178px; padding: 5px; vertical-align: top; width: 50%; }
        .card {
            border: 1.4px solid #0f5f8f;
            border-radius: 8px;
            height: 166px;
            overflow: hidden;
            position: relative;
            width: 100%;
        }
        .card-header {
            background: #0f5f8f;
            color: white;
            padding: 7px 9px;
        }
        .logo { height: 28px; width: 28px; }
        .brand { font-size: 15px; font-weight: bold; letter-spacing: .5px; }
        .subtitle { font-size: 7px; margin-top: 1px; }
        .content { padding: 7px 9px; }
        .name { color: #164e63; font-size: 12px; font-weight: bold; line-height: 1.15; margin-bottom: 5px; text-transform: uppercase; }
        .data { width: 100%; border-collapse: collapse; }
        .data td { padding: 1.6px 0; vertical-align: top; }
        .label { color: #64748b; font-size: 7px; font-weight: bold; text-transform: uppercase; width: 46px; }
        .value { color: #111827; font-size: 8px; }
        .qr { bottom: 8px; height: 58px; position: absolute; right: 8px; width: 58px; }
        .left { padding-right: 66px; }
        .footer { bottom: 7px; color: #64748b; font-size: 6.5px; left: 9px; position: absolute; }
    </style>
</head>
<body>
    <table class="sheet">
        @foreach (array_chunk($cards, 2) as $row)
            <tr>
                @foreach ($row as $card)
                    @php($student = $card['student'])
                    <td class="slot">
                        <div class="card">
                            <table class="card-header">
                                <tr>
                                    <td style="width:34px;">
                                        <img class="logo" src="{{ public_path('favicon.png') }}" alt="Logo">
                                    </td>
                                    <td>
                                        <div class="brand">CPU UNPRG</div>
                                        <div class="subtitle">Carnet de estudiante</div>
                                    </td>
                                </tr>
                            </table>
                            <div class="content left">
                                <div class="name">{{ $student->fullName() }}</div>
                                <table class="data">
                                    <tr>
                                        <td class="label">DNI</td>
                                        <td class="value">{{ $student->dni }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Turno</td>
                                        <td class="value">{{ $student->schedule?->shift?->name ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Carrera</td>
                                        <td class="value">{{ $student->career?->name ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Teléfono</td>
                                        <td class="value">{{ $student->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Correo</td>
                                        <td class="value">{{ $student->email }}</td>
                                    </tr>
                                </table>
                            </div>
                            <img class="qr" src="{{ $card['qr_path'] }}" alt="QR">
                            <div class="footer">Generado {{ $generatedAt->format('d/m/Y H:i') }}</div>
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
