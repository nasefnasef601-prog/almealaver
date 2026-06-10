<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class B2BPackage extends Model
{
    protected $table = 'b2b_packages';

    protected $fillable = [
        'school_id',
        'name',
        'assigned_teacher_id',
        'revenue_share_percentage',
        'course_ids',
        'content_types',
        'path_ids',
        'subject_ids',
        'type',
        'discount_percentage',
        'max_students',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'revenue_share_percentage' => 'decimal:2',
            'course_ids' => 'array',
            'content_types' => 'array',
            'path_ids' => 'array',
            'subject_ids' => 'array',
            'discount_percentage' => 'integer',
            'max_students' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function assignedTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
