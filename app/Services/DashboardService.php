<?php

namespace App\Services;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\AdmissionProcess;
use App\Models\Career;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Datos listos para Chart.js y KPIs según permisos del usuario.
     *
     * @return array<string, mixed>
     */
    public function chartData(?Staff $user, ?int $year = null, ?int $careerId = null, ?int $academicCycleId = null): array
    {
        if ($user === null) {
            return [];
        }

        $out = [];

        if ($user->canAccessStudentsModule()) {
            $base = $this->filteredStudentQuery($year, $careerId, $academicCycleId);

            $out['kpis'] = $this->studentKpis($base);
            $out['student_status'] = $this->studentsByStatusChart($base);
            $out['careers'] = $this->studentsByCareerChart($base);
            $out['registrations'] = $this->registrationsChart($base, $year);
        }

        if ($user->canAccessAcademicCyclesModule()) {
            $occupancy = $this->globalOccupancyDonut($year, $academicCycleId);

            if ($occupancy !== null) {
                $out['occupancy'] = $occupancy;
            }

            $campus = $this->campusLoadChart($year, $academicCycleId);

            if ($campus !== null) {
                $out['campus_load'] = $campus;
            }
        }

        return $out;
    }

    /** @return Collection<int, int> */
    public function filterYearOptions(): Collection
    {
        $fromStudents = Student::query()
            ->whereNotNull('registration_date')
            ->pluck('registration_date')
            ->map(fn ($date) => substr((string) $date, 0, 4));

        $fromProcesses = AdmissionProcess::query()
            ->whereNotNull('start_date')
            ->pluck('start_date')
            ->map(fn ($date) => substr((string) $date, 0, 4));

        $fromCycles = AcademicCycle::query()
            ->whereNotNull('start_date')
            ->pluck('start_date')
            ->map(fn ($date) => substr((string) $date, 0, 4));

        return $fromStudents
            ->merge($fromProcesses)
            ->merge($fromCycles)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->map(fn ($y) => (int) $y);
    }

    /** @return Collection<int, Career> */
    public function filterCareerOptions(): Collection
    {
        return Career::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /** @return Collection<int, AcademicCycle> */
    public function filterCycleOptions(): Collection
    {
        return AcademicCycle::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get(['id', 'name', 'start_date']);
    }

    private function filteredStudentQuery(?int $year, ?int $careerId, ?int $academicCycleId): Builder
    {
        $q = Student::query();

        if ($year !== null) {
            $q->whereBetween('registration_date', ["{$year}-01-01", "{$year}-12-31"]);
        }

        if ($careerId !== null) {
            $q->where('career_id', $careerId);
        }

        if ($academicCycleId !== null) {
            $q->where('academic_cycle_id', $academicCycleId);
        }

        return $q;
    }

    /**
     * @return array<string, int|float>
     */
    private function studentKpis(Builder $base): array
    {
        return [
            'students_total' => (int) (clone $base)->count(),
            'students_pending' => (int) (clone $base)
                ->where('status', Student::STATUS_PENDING)
                ->count(),

            'students_active' => (int) (clone $base)
                ->where('status', Student::STATUS_ACTIVE)
                ->count(),

            'students_rejected' => (int) (clone $base)
                ->where('status', Student::STATUS_REJECTED)
                ->count(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function studentsByStatusChart(Builder $base): array
    {
        $labels = [
            Student::STATUS_PENDING => 'Pendiente',
            Student::STATUS_ACTIVE => 'Activo',
            Student::STATUS_REJECTED => 'Rechazado',
        ];

        $counts = (clone $base)
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
    private function studentsByCareerChart(Builder $base): array
    {
        $rows = (clone $base)
            ->select('career_id', DB::raw('count(*) as c'))
            ->groupBy('career_id')
            ->orderByDesc('c')
            ->limit(8)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'labels' => ['Sin carreras registradas'],
                'values' => [0],
            ];
        }

        $names = Career::query()
            ->whereIn('id', $rows->pluck('career_id')->all())
            ->pluck('name', 'id');

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $labels[] = (string) ($names[$row->career_id] ?? '—');
            $values[] = (int) $row->c;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Inscripciones por mes.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    private function registrationsChart(Builder $base, ?int $year): array
    {
        if ($year !== null) {
            $keys = [];
            $labels = [];

            $monthShort = [
                'ene',
                'feb',
                'mar',
                'abr',
                'may',
                'jun',
                'jul',
                'ago',
                'sep',
                'oct',
                'nov',
                'dic',
            ];

            for ($m = 1; $m <= 12; $m++) {
                $keys[] = sprintf('%04d-%02d', $year, $m);
                $labels[] = $monthShort[$m - 1].' '.$year;
            }

            $counts = array_fill_keys($keys, 0);

            (clone $base)
                ->whereBetween('registration_date', ["{$year}-01-01", "{$year}-12-31"])
                ->select('registration_date')
                ->orderBy('id')
                ->chunk(2000, function ($chunk) use (&$counts): void {
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

        $start = now()->startOfMonth()->subMonths(5);

        $keys = [];
        $labels = [];

        $monthShort = [
            'ene',
            'feb',
            'mar',
            'abr',
            'may',
            'jun',
            'jul',
            'ago',
            'sep',
            'oct',
            'nov',
            'dic',
        ];

        for ($i = 0; $i < 6; $i++) {
            $m = $start->copy()->addMonths($i);

            $key = $m->format('Y-m');

            $keys[] = $key;
            $labels[] = $monthShort[(int) $m->format('n') - 1]
                .' '.$m->format('Y');
        }

        $counts = array_fill_keys($keys, 0);

        (clone $base)
            ->where('registration_date', '>=', $start->toDateString())
            ->select('registration_date')
            ->orderBy('id')
            ->chunk(2000, function ($chunk) use (&$counts): void {
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
     * @return array{labels: list<string>, values: list<int>}|null
     */
    private function globalOccupancyDonut(?int $year, ?int $academicCycleId): ?array
    {
        $base = AcademicCycleShift::query()
            ->where('status', true);

        $this->applyAcademicCycleFilters($base, $year, $academicCycleId);

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
     * @return array{
     *     labels: list<string>,
     *     enrolled: list<int>,
     *     available: list<int>
     * }|null
     */
    private function campusLoadChart(?int $year, ?int $academicCycleId): ?array
    {
        $query = AcademicCycleShift::query()
            ->where('academic_cycle_shifts.status', true)
            ->join(
                'campuses',
                'campuses.id',
                '=',
                'academic_cycle_shifts.campus_id'
            )
            ->select(
                'campuses.name',
                DB::raw('SUM(academic_cycle_shifts.enrolled) as enrolled'),
                DB::raw('SUM(academic_cycle_shifts.capacity) as capacity'),
            )
            ->groupBy('campuses.id', 'campuses.name')
            ->orderBy('campuses.name');

        if ($academicCycleId !== null) {
            $query->where('academic_cycle_shifts.academic_cycle_id', $academicCycleId);
        }

        if ($year !== null) {
            $query
                ->join(
                    'academic_cycles',
                    'academic_cycles.id',
                    '=',
                    'academic_cycle_shifts.academic_cycle_id'
                )
                ->whereBetween('academic_cycles.start_date', ["{$year}-01-01", "{$year}-12-31"]);
        }

        $rows = $query->get();

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
     * @param array<string, mixed> $chartData
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
     * @param array<string, mixed> $chartPayload
     */
    public function hasRenderableCharts(array $chartPayload): bool
    {
        return array_filter($chartPayload) !== [];
    }

    private function applyAcademicCycleFilters(Builder $query, ?int $year, ?int $academicCycleId): void
    {
        if ($academicCycleId !== null) {
            $query->where('academic_cycle_id', $academicCycleId);
        }

        if ($year === null) {
            return;
        }

        $query->whereHas('academicCycle', function (Builder $cycleQuery) use ($year): void {
            $cycleQuery->whereBetween('start_date', ["{$year}-01-01", "{$year}-12-31"]);
        });
    }
}
