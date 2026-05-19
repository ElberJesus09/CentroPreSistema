<?php

namespace App\Observers;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\ExamSetting;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Student;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        if ($this->safeChanges($model) === []) {
            return;
        }

        $this->record($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    private function record(Model $model, string $action): void
    {
        $module = $this->moduleFor($model);

        if ($module === null) {
            return;
        }

        app(ActivityLogService::class)->record(
            $module,
            $action,
            $this->descriptionFor($model, $action),
            $model,
            [
                'changed' => $action === 'updated' ? $this->changedAttributes($model) : null,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function safeChanges(Model $model): array
    {
        $changes = $model->getChanges();

        foreach (['password', 'remember_token', 'updated_at', 'last_login_at'] as $key) {
            unset($changes[$key]);
        }

        return $changes;
    }

    /**
     * @return array<string, array{before: mixed, after: mixed}>
     */
    private function changedAttributes(Model $model): array
    {
        $changes = $this->safeChanges($model);

        return collect($changes)
            ->mapWithKeys(fn (mixed $after, string $field) => [
                $field => [
                    'before' => $model->getOriginal($field),
                    'after' => $after,
                ],
            ])
            ->all();
    }

    private function moduleFor(Model $model): ?string
    {
        return match (true) {
            $model instanceof Staff => 'staff',
            $model instanceof Student => 'students',
            $model instanceof AcademicCycle => 'academic_cycles',
            $model instanceof Campus => 'campuses',
            $model instanceof Shift => 'shifts',
            $model instanceof AcademicCycleShift => 'schedules',
            $model instanceof ExamSetting => 'exam_settings',
            default => null,
        };
    }

    private function descriptionFor(Model $model, string $action): string
    {
        $verb = match ($action) {
            'created' => 'creo',
            'updated' => 'actualizo',
            'deleted' => 'elimino',
            default => $action,
        };

        return sprintf('%s %s: %s', ucfirst($verb), $this->labelFor($model), $this->nameFor($model));
    }

    private function labelFor(Model $model): string
    {
        return match (true) {
            $model instanceof Staff => 'empleado',
            $model instanceof Student => 'alumno',
            $model instanceof AcademicCycle => 'ciclo academico',
            $model instanceof Campus => 'sede',
            $model instanceof Shift => 'turno',
            $model instanceof AcademicCycleShift => 'programacion',
            $model instanceof ExamSetting => 'configuracion de examen',
            default => class_basename($model),
        };
    }

    private function nameFor(Model $model): string
    {
        if ($model instanceof Staff) {
            return trim("{$model->first_name} {$model->last_name}") ?: $model->username;
        }

        if ($model instanceof Student) {
            return $model->fullName() ?: (string) $model->getKey();
        }

        if ($model instanceof AcademicCycleShift) {
            return 'ID '.$model->getKey();
        }

        return (string) ($model->getAttribute('name') ?? 'ID '.$model->getKey());
    }
}
