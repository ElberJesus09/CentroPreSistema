<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Auditoria de usuarios</title>
    <style>
        @page { margin: 24px 28px; }
        body { color: #1f2933; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.35; }
        h1 { color: #123c69; font-size: 18px; margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e4e8ee; padding: 5px 4px; text-align: left; vertical-align: top; }
        th { background: #eef3f8; color: #415466; font-size: 8px; text-transform: uppercase; }
        .meta { color: #627386; font-size: 9px; margin: 5px 0 16px; }
        .muted { color: #627386; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <header>
        <h1>Auditoria de usuarios</h1>
        <div class="meta">
            Generado: {{ $generatedAt->format('Y-m-d H:i') }} |
            Desde: {{ $filters['date_from'] ?? 'Inicio' }} |
            Hasta: {{ $filters['date_to'] ?? 'Hoy' }} |
            Usuario: {{ $staffName }} |
            Modulo: {{ $moduleName }}
        </div>
    </header>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Modulo</th>
                <th>Accion</th>
                <th>Detalle</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        {{ $log->staff ? trim($log->staff->first_name.' '.$log->staff->last_name) : 'Usuario eliminado' }}
                        @if ($log->staff)
                            <br><span class="muted">{{ $log->staff->username }}</span>
                        @endif
                    </td>
                    <td class="nowrap">{{ $moduleName !== 'Todos' && $filters['module'] === $log->module ? $moduleName : $log->module }}</td>
                    <td class="nowrap">
                        {{ match ($log->action) {
                            'created' => 'Creado',
                            'updated' => 'Actualizado',
                            'deleted' => 'Eliminado',
                            'login' => 'Inicio sesion',
                            'logout' => 'Cierre sesion',
                            'download_pdf' => 'PDF',
                            'download_txt' => 'TXT',
                            default => $log->action,
                        } }}
                    </td>
                    <td>
                        {{ $log->description }}
                        @if ($log->changeDetails() !== [])
                            @foreach ($log->changeDetails() as $detail)
                                <br><span class="muted">{{ $detail }}</span>
                            @endforeach
                        @endif
                    </td>
                    <td class="nowrap">{{ $log->ip_address ?? '---' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">No hay actividades para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
