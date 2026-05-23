<?php

namespace App\Services;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Career;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentService
{
    /** Cache catalogos publicos (segundos). */
    private const int CACHE_CATALOG_LONG = 600;

    /** Turnos publicos: TTL corto para reflejar cupos sin saturar DB. */
    private const int CACHE_PUBLIC_SCHEDULES = 45;

    /** Cantidad segura para listados administrativos sin filtros. */
    private const int ADMIN_INITIAL_LIMIT = 100;

    /** Filas por pagina en listados administrativos. */
    private const int ADMIN_PER_PAGE = 25;

    /** Turnos con cupo y activos (consulta directa, sin cache). */
    public function availableSchedulesWithRelations(): Collection
    {
        return $this->queryPublicAvailableSchedules();
    }

    /** Turnos publicos con cache breve. */
    public function cachedPublicAvailableSchedules(): Collection
    {
        return $this->rememberEloquentCollection(
            'students.catalog.schedules.public.v1',
            self::CACHE_PUBLIC_SCHEDULES,
            fn (): Collection => $this->queryPublicAvailableSchedules(),
        );
    }

    /** Carreras activas con cache. */
    public function cachedActiveCareers(): Collection
    {
        return $this->rememberEloquentCollection(
            'students.catalog.careers.active.v2',
            self::CACHE_CATALOG_LONG,
            fn (): Collection => $this->queryActiveCareers(),
        );
    }

    /** Sedes activas con cache. */
    public function cachedActiveCampuses(): Collection
    {
        return $this->rememberEloquentCollection(
            'students.catalog.campuses.active.v1',
            self::CACHE_CATALOG_LONG,
            fn (): Collection => $this->queryActiveCampuses(),
        );
    }

    /** Carreras activas para selects (alias hacia cache). */
    public function activeCareers(): Collection
    {
        return $this->cachedActiveCareers();
    }

    /**
     * Listado administrativo con relaciones.
     *
     * @param  array{search?: string|null, year?: int|null, academic_cycle_id?: int|null}  $filters
     */
    public function paginateStudents(int $perPage = self::ADMIN_PER_PAGE, array $filters = []): LengthAwarePaginator
    {
        $query = Student::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'mother_last_name',
                'dni',
                'email',
                'payment_voucher_number',
                'payment_agency_number',
                'payment_date',
                'career_id',
                'academic_cycle_id',
                'academic_cycle_shift_id',
                'admission_process_id',
                'status',
                'registration_date',
            ])
            ->with([
                'career:id,name',
                'academicCycle:id,name,start_date',
                'schedule:id,academic_cycle_id,shift_id',
                'schedule.academicCycle:id,name,start_date',
                'schedule.shift:id,name',
            ]);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $nameTerms = collect(preg_split('/\s+/', $search) ?: [])
                ->map(fn (string $term): string => trim($term))
                ->filter()
                ->values();

            $query->where(function (Builder $q) use ($search, $nameTerms): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mother_last_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('payment_voucher_number', 'like', "%{$search}%")
                    ->orWhere('payment_agency_number', 'like', "%{$search}%")
                    ->orWhere(function (Builder $names) use ($nameTerms): void {
                        foreach ($nameTerms as $term) {
                            $names->where(function (Builder $part) use ($term): void {
                                $part->where('first_name', 'like', "%{$term}%")
                                    ->orWhere('last_name', 'like', "%{$term}%")
                                    ->orWhere('mother_last_name', 'like', "%{$term}%");
                            });
                        }
                    })
                    ->orWhereHas('career', fn (Builder $career) => $career->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('academicCycle', fn (Builder $cycle) => $cycle->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('schedule.academicCycle', fn (Builder $cycle) => $cycle->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('schedule.shift', fn (Builder $shift) => $shift->where('name', 'like', "%{$search}%"));
            });
        }

        $year = $filters['year'] ?? null;
        $cycleId = $filters['academic_cycle_id'] ?? null;
        if ($year !== null || $cycleId !== null) {
            $query->whereHas('schedule.academicCycle', function (Builder $q) use ($year, $cycleId): void {
                if ($year !== null) {
                    $q->whereBetween('start_date', ["{$year}-01-01", "{$year}-12-31"]);
                }

                if ($cycleId !== null) {
                    $q->whereKey($cycleId);
                }
            });
        }

        if ($search === '' && $year === null && $cycleId === null) {
            $ids = $this->recentStudentIds();

            $ids->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereKey($ids->all());
        }

        $query
            ->orderByDesc('registration_date')
            ->orderByDesc('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /** @return SupportCollection<int, int> */
    public function studentFilterYears(): SupportCollection
    {
        return AcademicCycle::query()
            ->whereNotNull('start_date')
            ->pluck('start_date')
            ->map(fn ($date) => (int) substr((string) $date, 0, 4))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    /** @return Collection<int, AcademicCycle> */
    public function studentFilterCycles(): Collection
    {
        return AcademicCycle::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get(['id', 'name', 'start_date']);
    }

    /** Alumno con relaciones para lectura administrativa. */
    public function studentForAdminView(Student $student): Student
    {
        return $student->load([
            'guardian',
            'school',
            'career',
            'academicCycle',
            'admissionProcess',
            'schedule.academicCycle',
            'schedule.campus',
            'schedule.shift',
        ]);
    }

    /**
     * IDs recientes para listar solo las ultimas 100 filas sin filtros.
     *
     * @return SupportCollection<int, int>
     */
    private function recentStudentIds(): SupportCollection
    {
        return Student::query()
            ->orderByDesc('registration_date')
            ->orderByDesc('id')
            ->limit(self::ADMIN_INITIAL_LIMIT)
            ->pluck('id');
    }

    /**
     * Perfil más reciente para autocompletar por DNI.
     *
     * @return array<string, mixed>|null
     */
    public function profileForDni(string $dni): ?array
    {
        if (! preg_match('/^\d{8}$/', $dni)) {
            return null;
        }

        $student = Student::query()
            ->with(['guardian', 'school'])
            ->where('dni', $dni)
            ->orderByDesc('registration_date')
            ->orderByDesc('id')
            ->first();

        if ($student === null) {
            return null;
        }

        return [
            'student' => [
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'mother_last_name' => $student->mother_last_name,
                'dni' => $student->dni,
                'birth_date' => $student->birth_date?->format('Y-m-d'),
                'gender' => $student->gender,
                'phone' => $student->phone,
                'address' => $student->address,
                'email' => $student->email,
            ],
            'guardian' => $student->guardian === null ? null : [
                'first_name' => $student->guardian->first_name,
                'last_name' => $student->guardian->last_name,
                'mother_last_name' => $student->guardian->mother_last_name,
                'dni' => $student->guardian->dni,
                'phone' => $student->guardian->phone,
                'relationship' => $student->guardian->relationship,
            ],
            'school' => $student->school === null ? null : [
                'name' => $student->school->name,
                'department' => $student->school->department,
                'province' => $student->school->province,
                'district' => $student->school->district,
                'graduation_year' => $student->school->graduation_year,
            ],
        ];
    }

    /**
     * Turnos disponibles para alta/edición staff (incluye turno actual aunque este lleno).
     *
     * @return Collection<int, AcademicCycleShift>
     */
    public function schedulesForAdminForms(?Student $current = null): Collection
    {
        return AcademicCycleShift::query()
            ->where(function ($q) use ($current) {
                $q->where(function ($inner) {
                    $inner->where('status', true)
                        ->whereColumn('enrolled', '<', 'capacity');
                });
                if ($current !== null && $current->academic_cycle_shift_id !== null) {
                    $q->orWhere(
                        $q->getModel()->getQualifiedKeyName(),
                        $current->academic_cycle_shift_id,
                    );
                }
            })
            ->with(['academicCycle', 'campus', 'shift'])
            ->orderByDesc('academic_cycle_id')
            ->get();
    }

    /** Etiqueta legible para select de turnos. */
    public function scheduleOptionLabel(AcademicCycleShift $row): string
    {
        $cycle = $row->academicCycle?->name ?? '—';
        $campus = $row->campus?->name ?? '—';
        $shift = $row->shift?->name ?? '—';
        return "{$cycle} — {$campus} — {$shift}";
    }

    /**
     * Registro de alumno + apoderado + colegio; incrementa matriculados bajo bloqueo.
     *
     * @param  array<string, mixed>  $validated
     */
    public function registerStudent(array $validated): Student
    {
        $validated = $this->sanitizeRegistrationPayload($validated);

        try {
            $student = DB::transaction(function () use ($validated) {
                $scheduleId = (int) $validated['academic_cycle_shift_id'];
                $schedule = AcademicCycleShift::query()->lockForUpdate()->findOrFail($scheduleId);
                $this->assertScheduleAcceptsEnrollment($schedule);
                $this->assertStudentIsNotRegisteredInCycle((string) $validated['student']['dni'], (int) $schedule->academic_cycle_id);

                $guardian = Guardian::query()->create($validated['guardian']);
                $school = School::query()->create($validated['school']);

                $status = $validated['status'] ?? Student::STATUS_PENDING;

                $student = Student::query()->create([
                    ...$validated['student'],
                    'career_id' => (int) $validated['career_id'],
                    'academic_cycle_id' => (int) $schedule->academic_cycle_id,
                    'academic_cycle_shift_id' => $scheduleId,
                    'guardian_id' => $guardian->id,
                    'school_id' => $school->id,
                    'registration_date' => now()->toDateString(),
                    'status' => $status,
                ]);

                $schedule->increment('enrolled');

                return $student->fresh()->load(['guardian', 'school', 'career', 'academicCycle', 'schedule.academicCycle', 'schedule.campus', 'schedule.shift']);
            });
        } catch (QueryException $e) {
            $this->throwDuplicateCycleValidationIfNeeded($e);
            $this->throwDuplicatePaymentVoucherValidationIfNeeded($e);

            throw $e;
        }

        $this->forgetPublicScheduleCatalogCache();

        return $student;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateStudent(Student $student, array $validated): Student
    {
        $validated = $this->sanitizeRegistrationPayload($validated);

        try {
            $student = DB::transaction(function () use ($student, $validated) {
                $student->load(['guardian', 'school']);
                $newScheduleId = (int) $validated['academic_cycle_shift_id'];
                $newSchedule = null;

                if ($newScheduleId !== (int) $student->academic_cycle_shift_id) {
                    $oldId = (int) $student->academic_cycle_shift_id;
                    $ids = [$oldId, $newScheduleId];
                    sort($ids);
                    $locked = [];
                    foreach ($ids as $id) {
                        $locked[$id] = AcademicCycleShift::query()->lockForUpdate()->findOrFail($id);
                    }
                    $oldSchedule = $locked[$oldId];
                    $newSchedule = $locked[$newScheduleId];
                    $this->assertScheduleAcceptsEnrollment($newSchedule);
                    $this->assertStudentIsNotRegisteredInCycle(
                        (string) $validated['student']['dni'],
                        (int) $newSchedule->academic_cycle_id,
                        (int) $student->id,
                    );
                    $oldSchedule->decrement('enrolled');
                    $newSchedule->increment('enrolled');
                } else {
                    $newSchedule = AcademicCycleShift::query()->findOrFail($newScheduleId);
                    $this->assertStudentIsNotRegisteredInCycle(
                        (string) $validated['student']['dni'],
                        (int) $newSchedule->academic_cycle_id,
                        (int) $student->id,
                    );
                }

                $student->guardian?->update($validated['guardian']);
                $student->school?->update($validated['school']);

                $student->fill([
                    ...$validated['student'],
                    'career_id' => (int) $validated['career_id'],
                    'academic_cycle_id' => (int) $newSchedule->academic_cycle_id,
                    'academic_cycle_shift_id' => $newScheduleId,
                    'status' => $validated['status'],
                ]);
                $student->save();

                return $student->fresh()->load(['guardian', 'school', 'career', 'academicCycle', 'schedule.academicCycle', 'schedule.campus', 'schedule.shift']);
            });
        } catch (QueryException $e) {
            $this->throwDuplicateCycleValidationIfNeeded($e);
            $this->throwDuplicatePaymentVoucherValidationIfNeeded($e);

            throw $e;
        }

        $this->forgetPublicScheduleCatalogCache();

        return $student;
    }

    /** Elimina alumno, libera cupo y limpia apoderado/colegio asociados. */
    public function deleteStudent(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $schedule = AcademicCycleShift::query()->lockForUpdate()->findOrFail($student->academic_cycle_shift_id);
            $guardianId = $student->guardian_id;
            $schoolId = $student->school_id;

            if ($schedule->enrolled > 0) {
                $schedule->decrement('enrolled');
            }
            $student->delete();

            if ($guardianId !== null && ! Student::query()->where('guardian_id', $guardianId)->exists()) {
                Guardian::query()->whereKey($guardianId)->delete();
            }

            if ($schoolId !== null && ! Student::query()->where('school_id', $schoolId)->exists()) {
                School::query()->whereKey($schoolId)->delete();
            }
        });

        $this->forgetPublicScheduleCatalogCache();
    }

    /** Verifica turno activo y con cupo antes de incrementar. */
    private function assertScheduleAcceptsEnrollment(AcademicCycleShift $schedule): void
    {
        if (! $schedule->status) {
            throw ValidationException::withMessages([
                'academic_cycle_shift_id' => ['El turno seleccionado no está activo.'],
            ]);
        }

        if ($schedule->enrolled >= $schedule->capacity) {
            throw ValidationException::withMessages([
                'academic_cycle_shift_id' => ['No hay vacantes disponibles en el turno seleccionado.'],
            ]);
        }
    }

    private function assertStudentIsNotRegisteredInCycle(string $dni, int $academicCycleId, ?int $ignoreStudentId = null): void
    {
        $exists = Student::query()
            ->where('dni', $dni)
            ->where('academic_cycle_id', $academicCycleId)
            ->when($ignoreStudentId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreStudentId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'student.dni' => ['Este DNI ya tiene una inscripción registrada en el ciclo seleccionado. Puede inscribirse nuevamente solo en un ciclo diferente.'],
            ]);
        }
    }

    private function throwDuplicateCycleValidationIfNeeded(QueryException $e): void
    {
        if (str_contains($e->getMessage(), 'students_dni_academic_cycle_unique')) {
            throw ValidationException::withMessages([
                'student.dni' => ['Este DNI ya tiene una inscripción registrada en el ciclo seleccionado.'],
            ]);
        }
    }

    private function throwDuplicatePaymentVoucherValidationIfNeeded(QueryException $e): void
    {
        if (str_contains($e->getMessage(), 'students_payment_voucher_number_unique')) {
            throw ValidationException::withMessages([
                'student.payment_voucher_number' => ['Este número de voucher ya fue registrado.'],
            ]);
        }
    }

    /**
     * @return Collection<int, AcademicCycleShift>
     */
    private function queryPublicAvailableSchedules(): Collection
    {
        return AcademicCycleShift::query()
            ->where('status', true)
            ->whereColumn('enrolled', '<', 'capacity')
            ->with(['academicCycle', 'campus', 'shift'])
            ->orderByDesc('academic_cycle_id')
            ->orderBy('campus_id')
            ->orderBy('shift_id')
            ->get();
    }

    /**
     * @return Collection<int, Career>
     */
    private function queryActiveCareers(): Collection
    {
        return Career::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Campus>
     */
    private function queryActiveCampuses(): Collection
    {
        return Campus::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Sanitiza strings anidados (XSS / ruido) antes de persistir.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function sanitizeRegistrationPayload(array $validated): array
    {
        foreach (['student', 'guardian', 'school'] as $section) {
            if (! isset($validated[$section]) || ! is_array($validated[$section])) {
                continue;
            }
            foreach ($validated[$section] as $key => $value) {
                if (! is_string($value)) {
                    continue;
                }
                $max = match (true) {
                    $section === 'student' && $key === 'email' => 255,
                    $section === 'school' && $key === 'name' => 255,
                    default => 500,
                };
                $validated[$section][$key] = $this->sanitizePlainString($value, $max);
            }
        }

        if (isset($validated['student']['email']) && is_string($validated['student']['email'])) {
            $validated['student']['email'] = mb_strtolower(trim($validated['student']['email']));
        }

        return $validated;
    }

    private function sanitizePlainString(string $value, int $max): string
    {
        return mb_substr(trim(strip_tags($value)), 0, $max);
    }

    /** Invalida cache de turnos publicos tras cambios de cupos. */
    private function forgetPublicScheduleCatalogCache(): void
    {
        Cache::forget('students.catalog.schedules.public.v1');
    }

    /**
     * Cache de colecciones Eloquent: si el valor deserializado no es una Collection
     * (p. ej. __PHP_Incomplete_Class tras cambios de clases), se invalida y se recalcula.
     *
     * @param  callable(): Collection  $callback
     */
    private function rememberEloquentCollection(string $key, int $ttl, callable $callback): Collection
    {
        $cached = Cache::get($key);

        if ($cached instanceof Collection) {
            return $cached;
        }

        if ($cached !== null) {
            Cache::forget($key);
        }

        $fresh = $callback();
        Cache::put($key, $fresh, $ttl);

        return $fresh;
    }
}
