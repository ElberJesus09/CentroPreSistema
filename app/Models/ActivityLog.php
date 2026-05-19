<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'staff_id',
    'module',
    'action',
    'description',
    'subject_type',
    'subject_id',
    'ip_address',
    'user_agent',
    'properties',
])]
class ActivityLog extends Model
{
    /** @var array<string, string> */
    private const FIELD_LABELS = [
        'academic_cycle_id' => 'Ciclo academico',
        'academic_cycle_shift_id' => 'Programacion',
        'address' => 'Direccion',
        'admission_process_id' => 'Proceso de admision',
        'birth_date' => 'Fecha de nacimiento',
        'campus_id' => 'Sede',
        'capacity' => 'Capacidad',
        'career_id' => 'Carrera',
        'dni' => 'DNI',
        'email' => 'Correo',
        'end_date' => 'Fecha de fin',
        'exam_date' => 'Fecha de examen',
        'exam_location' => 'Lugar de examen',
        'exam_time' => 'Hora de examen',
        'first_name' => 'Nombres',
        'gender' => 'Genero',
        'guardian_id' => 'Apoderado',
        'institutional_message' => 'Mensaje institucional',
        'last_name' => 'Apellido paterno',
        'mother_last_name' => 'Apellido materno',
        'name' => 'Nombre',
        'payment_agency_number' => 'Agencia de pago',
        'payment_date' => 'Fecha de pago',
        'payment_voucher_number' => 'Voucher de pago',
        'phone' => 'Celular',
        'registration_date' => 'Fecha de registro',
        'registration_mail_enabled' => 'Envio de correo',
        'role_id' => 'Rol',
        'school_id' => 'Colegio',
        'shift_id' => 'Turno',
        'start_date' => 'Fecha de inicio',
        'status' => 'Estado',
        'username' => 'Usuario',
    ];

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return list<string>
     */
    public function changeDetails(): array
    {
        $changed = $this->properties['changed'] ?? [];

        if (! is_array($changed)) {
            return [];
        }

        $lines = [];

        foreach ($changed as $field => $change) {
            if (! is_array($change)) {
                continue;
            }

            $lines[] = sprintf(
                '%s: %s -> %s',
                self::FIELD_LABELS[$field] ?? str_replace('_', ' ', ucfirst((string) $field)),
                $this->formatValue($change['before'] ?? null),
                $this->formatValue($change['after'] ?? null),
            );
        }

        return $lines;
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'vacio';
        }

        if (is_bool($value)) {
            return $value ? 'Si' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: 'valor no disponible';
        }

        return mb_substr((string) $value, 0, 120);
    }

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }
}
