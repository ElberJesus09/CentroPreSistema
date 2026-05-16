<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de alumnos</title>
    <style>
        @page { margin: 28px 32px; }
        body { color: #1f2933; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.35; }
        h1 { color: #123c69; font-size: 20px; margin: 0; }
        h2 { border-bottom: 1px solid #d7dde5; color: #123c69; font-size: 13px; margin: 20px 0 8px; padding-bottom: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e4e8ee; padding: 6px 5px; text-align: left; vertical-align: top; }
        th { background: #eef3f8; color: #415466; font-size: 9px; text-transform: uppercase; }
        .meta { color: #627386; font-size: 10px; margin-top: 5px; }
        .grid { margin-top: 16px; width: 100%; }
        .grid td { border: 1px solid #d7dde5; padding: 10px; width: 16.66%; }
        .label { color: #627386; font-size: 9px; text-transform: uppercase; }
        .value { color: #123c69; font-size: 18px; font-weight: bold; margin-top: 4px; }
        .muted { color: #627386; }
        .right { text-align: right; }
        .two-col { width: 100%; }
        .two-col > tbody > tr > td { border: 0; padding: 0 10px 0 0; width: 50%; }
        .two-col > tbody > tr > td:last-child { padding-right: 0; }
    </style>
</head>
<body>
    <header>
        <h1>Reporte de alumnos</h1>
        <div class="meta">
            Generado: {{ $generatedAt->format('Y-m-d H:i') }}
            @if ($filters['year'] || $filters['career'] || $filters['cycle'])
                | Filtros:
                Año {{ $filters['year'] ?? 'Todos' }},
                Ciclo {{ $filters['cycle'] ?? 'Todos' }},
                Carrera {{ $filters['career'] ?? 'Todas' }}
            @else
                | Sin filtros aplicados
            @endif
        </div>
    </header>

    <table class="grid">
        <tr>
            <td><div class="label">Total alumnos</div><div class="value">{{ $report['kpis']['students_total'] }}</div></td>
            <td><div class="label">Pendientes</div><div class="value">{{ $report['kpis']['students_pending'] }}</div></td>
            <td><div class="label">Activos</div><div class="value">{{ $report['kpis']['students_active'] }}</div></td>
            <td><div class="label">Rechazados</div><div class="value">{{ $report['kpis']['students_rejected'] }}</div></td>
            <td><div class="label">Pagos</div><div class="value">{{ $report['kpis']['payments_registered'] }}</div></td>
            <td><div class="label">Último pago</div><div class="value" style="font-size: 11px;">{{ $report['kpis']['last_payment_date'] ?: '---' }}</div></td>
        </tr>
    </table>

    <table class="two-col">
        <tr>
            <td>
                <h2>Por estado</h2>
                <table>
                    <thead>
                        <tr><th>Estado</th><th class="right">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($report['by_status'] as $row)
                            <tr>
                                <td>{{ ['pending' => 'Pendiente', 'active' => 'Activo', 'rejected' => 'Rechazado'][$row->status] ?? ucfirst((string) $row->status) }}</td>
                                <td class="right">{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="muted">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td>
                <h2>Top carreras</h2>
                <table>
                    <thead>
                        <tr><th>Carrera</th><th class="right">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($report['by_career'] as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td class="right">{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="muted">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <h2>Top ciclos</h2>
    <table>
        <thead>
            <tr><th>Ciclo</th><th class="right">Total</th></tr>
        </thead>
        <tbody>
            @forelse ($report['by_cycle'] as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td class="right">{{ $row->total }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="muted">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Últimos pagos registrados</h2>
    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th>DNI</th>
                <th>Carrera</th>
                <th>Ciclo</th>
                <th>Voucher</th>
                <th>Agencia</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['recent_payments'] as $student)
                <tr>
                    <td>{{ $student->fullName() }}</td>
                    <td>{{ $student->dni }}</td>
                    <td>{{ $student->career?->name ?? '---' }}</td>
                    <td>{{ $student->academicCycle?->name ?? '---' }}</td>
                    <td>{{ $student->payment_voucher_number }}</td>
                    <td>{{ $student->payment_agency_number }}</td>
                    <td>{{ $student->payment_date?->format('Y-m-d') ?? '---' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No hay pagos registrados para estos filtros.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
