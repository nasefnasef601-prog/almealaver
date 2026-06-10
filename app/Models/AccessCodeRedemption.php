<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessCodeRedemption extends Model
{
    protected $fillable = [
        'access_code_id',
        'user_id',
        'access_grant_id',
        'redeemed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'redeemed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function accessCode(): BelongsTo
    {
        return $this->belongsTo(AccessCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accessGrant(): BelongsTo
    {
        return $this->belongsTo(AccessGrant::class);
    }
}
