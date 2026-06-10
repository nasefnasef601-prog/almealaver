<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessGrant extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'course_ids',
        'package_id',
        'content_types',
        'path_ids',
        'subject_ids',
        'source_type',
        'source_id',
        'idempotency_key',
        'metadata',
        'grant_type',
        'status',
        'granted_by',
        'payment_request_id',
        'granted_at',
        'starts_at',
        'expires_at',
        'revoked_by',
        'revoked_at',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'course_ids' => 'array',
            'content_types' => 'array',
            'path_ids' => 'array',
            'subject_ids' => 'array',
            'metadata' => 'array',
            'granted_at' => 'datetime',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class, 'payment_request_id');
    }

    public function grantedCourseIds(): array
    {
        $ids = [];

        if ($this->course_id) {
            $ids[] = (string) $this->course_id;
        }

        foreach ((array) $this->course_ids as $courseId) {
            $courseId = (string) $courseId;
            if ($courseId !== '') {
                $ids[] = $courseId;
            }
        }

        return array_values(array_unique($ids));
    }

    public static function activeCourseIdsForUser(int|string $userId): array
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->get()
            ->flatMap(fn (self $grant) => $grant->grantedCourseIds())
            ->unique()
            ->values()
            ->all();
    }

    public static function userHasCourseAccess(int|string $userId, int|string $courseId): bool
    {
        $courseId = (string) $courseId;

        return static::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where(function ($query) use ($courseId) {
                $query->where('course_id', $courseId)
                    ->orWhereJsonContains('course_ids', $courseId);
            })
            ->exists();
    }
}
