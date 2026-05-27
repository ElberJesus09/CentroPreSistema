<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSetting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'exam_date',
        'exam_time',
        'exam_location',
        'institutional_message',
        'registration_mail_enabled',
        'public_results_enabled',
    ];

    /** Fila unica editable desde administracion. */
    public static function singleton(): self
    {
        /** @var self $row */
        $row = static::query()->firstOrCreate(
            ['id' => 1],
            [
                'exam_date' => null,
                'exam_time' => null,
                'exam_location' => null,
                'institutional_message' => null,
                'registration_mail_enabled' => false,
                'public_results_enabled' => false,
            ],
        );

        return $row;
    }

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'registration_mail_enabled' => 'boolean',
            'public_results_enabled' => 'boolean',
        ];
    }
}
