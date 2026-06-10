<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'label',
        'type',
        'value',
        'status',
        'min_amount',
        'max_redemptions',
        'current_redemptions',
        'starts_at',
        'expires_at',
        'package_ids',
        'course_ids',
        'path_ids',
        'subject_ids',
        'content_types',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_redemptions' => 'integer',
            'current_redemptions' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'package_ids' => 'array',
            'course_ids' => 'array',
            'path_ids' => 'array',
            'subject_ids' => 'array',
            'content_types' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function isAvailableFor(Course $course, float $amount): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_redemptions > 0 && $this->current_redemptions >= $this->max_redemptions) {
            return false;
        }

        if ($amount < (float) $this->min_amount) {
            return false;
        }

        if (!$this->matchesScope($course)) {
            return false;
        }

        return true;
    }

    public function discountAmount(float $amount): float
    {
        $discount = $this->type === 'fixed'
            ? (float) $this->value
            : $amount * ((float) $this->value / 100);

        return round(max(0, min($amount, $discount)), 2);
    }

    private function matchesScope(Course $course): bool
    {
        $courseIds = array_filter((array) $this->course_ids);
        if ($courseIds !== [] && !in_array((string) $course->id, array_map('strval', $courseIds), true)) {
            return false;
        }

        $subjectIds = array_filter((array) $this->subject_ids);
        if ($subjectIds !== [] && !in_array((string) $course->subject_id, array_map('strval', $subjectIds), true)) {
            return false;
        }

        $pathIds = array_filter((array) $this->path_ids);
        $pathId = $course->subject?->path_id;
        if ($pathIds !== [] && !in_array((string) $pathId, array_map('strval', $pathIds), true)) {
            return false;
        }

        return true;
    }
}
