<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Central place to write the audit trail (login, logout, issuance, changes, deletions, validations).
 */
class ActivityLogger
{
    public function log(
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $meta = [],
        ?int $userId = null,
    ): ActivityLog {
        $request = request();

        return ActivityLog::create([
            'user_id' => $userId ?? $request->user()?->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'meta' => $meta ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
