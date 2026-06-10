<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessCode extends Model
{
    protected $fillable = [
        'code',
        'school_id',
        'b2b_package_id',
        'max_uses',
        'current_uses',
        'expires_at',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'max_uses' => 'integer',
            'current_uses' => 'integer',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(B2BPackage::class, 'b2b_package_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(AccessCodeRedemption::class);
    }

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function isRedeemable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->max_uses === 0 || $this->current_uses < $this->max_uses;
    }
}
