<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { color: #1d4ed8; font-size: 18px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: left; }
        th { background: #eff6ff; }
    </style>
</head>
<body>
    <h1>Reporte académico</h1>
    <p>Generado: {{ $generatedAt->format('Y-m-d H:i') }}</p>
    <p>Promedio general: {{ number_format($report['promedio_general'], 2) }} · Destacados: {{ $report['destacados']->count() }} · Desaprobados: {{ $report['desaprobados']->count() }}</p>
    <table>
        <thead><tr><th>Ranking</th><th>DNI</th><th>Alumno</th><th>Carrera</th><th>Turno</th><th>Aula</th><th>Promedio</th></tr></thead>
        <tbody>
            @foreach ($report['ranking_general'] as $row)
                <tr><td>{{ $row->ranking }}</td><td>{{ $row->dni }}</td><td>{{ $row->alumno }}</td><td>{{ $row->carrera }}</td><td>{{ $row->turno }}</td><td>{{ $row->aula }}</td><td>{{ number_format($row->promedio, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
