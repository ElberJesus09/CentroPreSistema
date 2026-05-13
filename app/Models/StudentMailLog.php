<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMailLog extends Model
{
    public const CHANNEL_REGISTRATION_AUTO = 'registration_auto';

    public const CHANNEL_REGISTRATION_MANUAL_RESEND = 'registration_manual_resend';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    /** @var list<string> */
    protected $fillable = [
        'student_id',
        'channel',
        'status',
        'error_message',
        'triggered_by_staff_id',
        'metadata',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'triggered_by_staff_id');
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
