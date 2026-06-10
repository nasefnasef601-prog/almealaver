<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryItem extends Model
{
    protected $fillable = [
        'title', 'size', 'downloads', 'type', 'path_id', 'subject_id', 'section_id',
        'skill_ids', 'url', 'show_on_platform', 'is_locked', 'owner_type', 'owner_id',
        'created_by', 'assigned_teacher_id', 'approval_status', 'approved_by',
        'approved_at', 'reviewer_notes', 'revenue_share_percentage',
    ];

    protected function casts(): array
    {
        return [
            'downloads' => 'integer',
            'skill_ids' => 'array',
            'show_on_platform' => 'boolean',
            'is_locked' => 'boolean',
            'approved_at' => 'datetime',
            'revenue_share_percentage' => 'decimal:2',
        ];
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeVisibleOnPlatform(Builder $query): Builder
    {
        return $query
            ->where('show_on_platform', true)
            ->where('approval_status', 'approved');
    }
}
