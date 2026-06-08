<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'path_id', 'name', 'name_ar', 'slug', 'description', 'description_ar', 'icon', 'color', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function skills(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Skill::class, Section::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
