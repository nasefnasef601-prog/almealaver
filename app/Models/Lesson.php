<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module_id', 'course_id', 'title', 'title_ar', 'description', 'description_ar',
        'content_type', 'content_url', 'content_text', 'video_provider', 'video_url',
        'duration_minutes', 'is_free', 'is_published', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class);
    }
}
