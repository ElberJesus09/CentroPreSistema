<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;

#[Fillable([
    'staff_id',
    'permission_id',
    'granted_by',
    'expires_at',
])]
class StaffTemporaryPermissionGrant extends Model
{
    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo<Permission, $this>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'granted_by');
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
