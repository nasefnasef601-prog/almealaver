<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PublicBarcodeTest extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'path_id',
        'subject_id',
        'section_id',
        'skill_ids',
        'question_ids',
        'test_kind',
        'status',
        'show_result_to_student',
        'collect_school',
        'collect_classroom',
        'settings',
        'starts_at',
        'ends_at',
        'max_submissions',
        'created_by',
        'owner_type',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'skill_ids' => 'array',
            'question_ids' => 'array',
            'show_result_to_student' => 'boolean',
            'collect_school' => 'boolean',
            'collect_classroom' => 'boolean',
            'settings' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(PublicBarcodeSubmission::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function hasReachedSubmissionLimit(): bool
    {
        return $this->max_submissions !== null
            && $this->submissions()->count() >= $this->max_submissions;
    }

    public function settingsValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function questionsForTaking(): Collection
    {
        $ids = array_values(array_map('intval', array_filter($this->question_ids ?? [])));

        if ($ids === []) {
            return collect();
        }

        $questions = Question::query()
            ->whereIn('id', $ids)
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (Question $question) => array_search($question->id, $ids, true))
            ->values();

        if ((bool) $this->settingsValue('randomizeQuestions', true)) {
            return $questions->shuffle()->values();
        }

        return $questions;
    }
}
