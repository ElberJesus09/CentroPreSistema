<?php

namespace App\Services;

use App\Models\AcademicCycle;
use App\Models\Career;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(?int $year = null, ?int $careerId = null, ?int $academicCycleId = null): array
    {
        $base = $this->filteredStudentQuery($year, $careerId, $academicCycleId);

        return [
            'kpis' => $this->kpis($base),
            'by_status' => $this->studentsByStatus($base),
            'by_career' => $this->studentsByCareer($base),
            'by_cycle' => $this->studentsByCycle($base),
            'recent_payments' => $this->recentPayments($base),
        ];
    }

    /** @return Collection<int, int> */
    public function filterYearOptions(): Collection
    {
        return Student::query()
            ->whereNotNull('registration_date')
            ->pluck('registration_date')
            ->map(fn ($date) => (int) substr((string) $date, 0, 4))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
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
        return Student::query()
            ->when($year !== null, fn (Builder $query) => $query->whereBetween('registration_date', ["{$year}-01-01", "{$year}-12-31"]))
            ->when($careerId !== null, fn (Builder $query) => $query->where('career_id', $careerId))
            ->when($academicCycleId !== null, fn (Builder $query) => $query->where('academic_cycle_id', $academicCycleId));
    }

    /**
     * @return array<string, int|string|null>
     */
    private function kpis(Builder $base): array
    {
        return [
            'students_total' => (int) (clone $base)->count(),
            'students_pending' => (int) (clone $base)->where('status', Student::STATUS_PENDING)->count(),
            'students_active' => (int) (clone $base)->where('status', Student::STATUS_ACTIVE)->count(),
            'students_rejected' => (int) (clone $base)->where('status', Student::STATUS_REJECTED)->count(),
            'payments_registered' => (int) (clone $base)->whereNotNull('payment_voucher_number')->count(),
            'last_payment_date' => (string) ((clone $base)->max('payment_date') ?? ''),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function studentsByStatus(Builder $base): Collection
    {
        return (clone $base)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function studentsByCareer(Builder $base): Collection
    {
        return (clone $base)
            ->leftJoin('careers', 'careers.id', '=', 'students.career_id')
            ->select(
                DB::raw("COALESCE(careers.name, 'Sin carrera') as name"),
                DB::raw('count(students.id) as total'),
            )
            ->groupBy('careers.id', 'careers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function studentsByCycle(Builder $base): Collection
    {
        return (clone $base)
            ->leftJoin('academic_cycles', 'academic_cycles.id', '=', 'students.academic_cycle_id')
            ->select(
                DB::raw("COALESCE(academic_cycles.name, 'Sin ciclo') as name"),
                DB::raw('count(students.id) as total'),
            )
            ->groupBy('academic_cycles.id', 'academic_cycles.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, Student>
     */
    private function recentPayments(Builder $base): Collection
    {
        return (clone $base)
            ->whereNotNull('payment_voucher_number')
            ->with(['career:id,name', 'academicCycle:id,name'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(15)
            ->get([
                'id',
                'first_name',
                'last_name',
                'mother_last_name',
                'dni',
                'career_id',
                'academic_cycle_id',
                'payment_voucher_number',
                'payment_agency_number',
                'payment_date',
            ]);
    }
}
