<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    protected $fillable = [
        'user_id', 'favoriteable_id', 'favoriteable_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoriteable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfCourse($query, $courseId)
    {
        return $query->where('favoriteable_type', Course::class)
            ->where('favoriteable_id', $courseId);
    }

    public function scopeOfLesson($query, $lessonId)
    {
        return $query->where('favoriteable_type', Lesson::class)
            ->where('favoriteable_id', $lessonId);
    }

    public static function isFavorited(int $userId, string $type, int $id): bool
    {
        return static::where('user_id', $userId)
            ->where('favoriteable_type', $type)
            ->where('favoriteable_id', $id)
            ->exists();
    }

    public static function toggle(int $userId, string $type, int $id): bool
    {
        $existing = static::where('user_id', $userId)
            ->where('favoriteable_type', $type)
            ->where('favoriteable_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        static::create([
            'user_id' => $userId,
            'favoriteable_type' => $type,
            'favoriteable_id' => $id,
        ]);
        return true;
    }
}
