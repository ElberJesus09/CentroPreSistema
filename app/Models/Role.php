<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;
    public const NAME_SUPER_ADMIN = 'super_admin';

    public const NAME_ADMIN = 'admin';

    public const NAME_TRABAJADOR = 'trabajador';

    public const NAME_DOCENTE = 'docente';

    public const NAME_ASISTENTE = 'asistente';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'status',
    ];

    /** Nombre legible para mostrar en la interfaz. */
    public function displayName(): string
    {
        return match ($this->name) {
            self::NAME_SUPER_ADMIN => 'Super administrador',
            self::NAME_ADMIN => 'Administrador',
            self::NAME_TRABAJADOR => 'Trabajador',
            self::NAME_DOCENTE => 'Docente',
            self::NAME_ASISTENTE => 'Asistente',
            default => $this->name,
        };
    }

    /**
     * @return list<string>
     */
    public static function assignableNamesForActor(?Staff $actor): array
    {
        if ($actor?->isSuperAdmin()) {
            return [
                self::NAME_ADMIN,
                self::NAME_TRABAJADOR,
            ];
        }

        if ($actor?->isAdmin()) {
            return [
                self::NAME_TRABAJADOR,
            ];
        }

        return [];
    }

    /**
     * @return HasMany<Staff, $this>
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
