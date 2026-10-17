<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    public const STATUS_VALID = 'valid';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'code', 'participant_id', 'course_id', 'issued_by',
        'participant_name', 'course_name', 'workload_hours', 'institution_name',
        'issued_at', 'status', 'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'workload_hours' => 'integer',
            'issued_at' => 'date:Y-m-d',
            'revoked_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    public function validationUrl(): string
    {
        return config('certihub.frontend_url').'/validate/'.$this->code;
    }
}
