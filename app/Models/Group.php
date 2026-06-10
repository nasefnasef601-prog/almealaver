<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name', 'description', 'location', 'type', 'is_active', 'parent_id', 'owner_id',
        'course_ids', 'metadata', 'settings', 'school_id',
    ];

    protected function casts(): array
    {
        return [
            'course_ids' => 'array',
            'metadata' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Group::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'student');
    }

    public function schoolManagers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'school_manager');
    }

    public function classSupervisors(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'class_supervisor');
    }

    public function supervisors(): BelongsToMany
    {
        return $this->users()->wherePivotIn('role', ['supervisor', 'class_supervisor', 'school_manager']);
    }

    public function teachers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'teacher');
    }

    public function linkedCourseIds(): array
    {
        return array_values(array_filter(array_map('strval', (array) $this->course_ids)));
    }
}
