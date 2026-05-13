<?php

namespace App\Services;

use App\Models\AcademicCycleShift;
use App\Models\Career;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Datos listos para Chart.js y KPIs según permisos del usuario.
     *
     * @return array{
     *   kpis?: array<string, int|float>,
     *   student_status?: array{labels: list<string>, values: list<int>},
     *   careers?: array{labels: list<string>, values: list<int>},
     *   registrations?: array{labels: list<string>, values: list<int>},
     *   occupancy?: array{labels: list<string>, values: list<int>},
     *   campus_load?: array{labels: list<string>, enrolled: list<int>, available: list<int>}
     * }
     */
    public function chartData(?Staff $user): array
    {
        if ($user === null) {
            return [];
        }

        $out = [];

        if ($user->canAccessStudentsModule()) {
            $out['kpis'] = $this->studentKpis();
            $out['student_status'] = $this->studentsByStatusChart();
            $out['careers'] = $this->studentsByCareerChart();
            $out['registrations'] = $this->registrationsLastSixMonthsChart();
        }

        if ($user->canAccessAcademicCyclesModule()) {
            $occupancy = $this->globalOccupancyDonut();
            if ($occupancy !== null) {
                $out['occupancy'] = $occupancy;
            }
            $campus = $this->campusLoadChart();
            if ($campus !== null) {
                $out['campus_load'] = $campus;
            }
        }

        return $out;
    }

    /** KPIs rápidos del módulo alumnos. */
    private function studentKpis(): array
    {
        $base = Student::query();

        return [
            'students_total' => (int) (clone $base)->count(),
            'students_pending' => (int) (clone $base)->where('status', Student::STATUS_PENDING)->count(),
            'students_active' => (int) (clone $base)->where('status', Student::STATUS_ACTIVE)->count(),
            'students_rejected' => (int) (clone $base)->where('status', Student::STATUS_REJECTED)->count(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function studentsByStatusChart(): array
    {
        $labels = [
            Student::STATUS_PENDING => 'Pendiente',
            Student::STATUS_ACTIVE => 'Activo',
            Student::STATUS_REJECTED => 'Rechazado',
        ];

        $counts = Student::query()
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $orderedLabels = [];
        $orderedValues = [];
        foreach ($labels as $key => $label) {
            $orderedLabels[] = $label;
            $orderedValues[] = (int) ($counts[$key] ?? 0);
        }

        return [
            'labels' => $orderedLabels,
            'values' => $orderedValues,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function studentsByCareerChart(): array
    {
        $rows = Career::query()
            ->withCount('students')
            ->orderByDesc('students_count')
            ->limit(8)
            ->get(['name', 'students_count']);

        if ($rows->isEmpty()) {
            return ['labels' => ['Sin carreras registradas'], 'values' => [0]];
        }

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) $row->name;
            $values[] = (int) $row->students_count;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Inscripciones por mes (últimos 6 meses, incluye mes actual).
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function registrationsLastSixMonthsChart(): array
    {
        $start = now()->startOfMonth()->subMonths(5);

        $keys = [];
        $labels = [];
        $monthShort = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        for ($i = 0; $i < 6; $i++) {
            $m = $start->copy()->addMonths($i);
            $key = $m->format('Y-m');
            $keys[] = $key;
            $labels[] = $monthShort[(int) $m->format('n') - 1].' '.$m->format('Y');
        }

        $counts = array_fill_keys($keys, 0);

        Student::query()
            ->where('registration_date', '>=', $start->toDateString())
            ->orderBy('id')
            ->select('registration_date')
            ->chunk(1000, function ($chunk) use (&$counts): void {
                foreach ($chunk as $student) {
                    $k = $student->registration_date?->format('Y-m');
                    if ($k !== null && array_key_exists($k, $counts)) {
                        $counts[$k]++;
                    }
                }
            });

        $values = [];
        foreach ($keys as $key) {
            $values[] = $counts[$key];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Cupos globales en programaciones activas (matriculados vs libres).
     *
     * @return array{labels: list<string>, values: list<int>}|null
     */
    private function globalOccupancyDonut(): ?array
    {
        $base = AcademicCycleShift::query()->where('status', true);
        $capacity = (int) (clone $base)->sum('capacity');
        if ($capacity === 0) {
            return null;
        }

        $enrolled = (int) (clone $base)->sum('enrolled');
        $free = max(0, $capacity - $enrolled);

        return [
            'labels' => ['Matriculados', 'Cupos disponibles'],
            'values' => [$enrolled, $free],
        ];
    }

    /**
     * Carga por sede (programaciones activas).
     *
     * @return array{labels: list<string>, enrolled: list<int>, available: list<int>}|null
     */
    private function campusLoadChart(): ?array
    {
        $rows = AcademicCycleShift::query()
            ->where('academic_cycle_shifts.status', true)
            ->join('campuses', 'campuses.id', '=', 'academic_cycle_shifts.campus_id')
            ->select(
                'campuses.name',
                DB::raw('SUM(academic_cycle_shifts.enrolled) as enrolled'),
                DB::raw('SUM(academic_cycle_shifts.capacity) as capacity'),
            )
            ->groupBy('campuses.id', 'campuses.name')
            ->orderBy('campuses.name')
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $labels = [];
        $enrolled = [];
        $available = [];

        foreach ($rows as $row) {
            $labels[] = (string) $row->name;
            $e = (int) $row->enrolled;
            $c = (int) $row->capacity;
            $enrolled[] = $e;
            $available[] = max(0, $c - $e);
        }

        return [
            'labels' => $labels,
            'enrolled' => $enrolled,
            'available' => $available,
        ];
    }

    /**
     * Subconjunto enviado al cliente (Chart.js).
     *
     * @param  array<string, mixed>  $chartData
     * @return array<string, mixed>
     */
    public function chartPayloadForClient(array $chartData): array
    {
        return array_intersect_key(
            $chartData,
            array_flip([
                'student_status',
                'careers',
                'registrations',
                'occupancy',
                'campus_load',
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     */
    public function hasRenderableCharts(array $chartPayload): bool
    {
        return array_filter($chartPayload) !== [];
    }
}
