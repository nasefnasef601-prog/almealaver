<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'code', 'address', 'phone', 'email', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(Group::class)->where('type', 'class');
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'teacher');
    }

    public function supervisors(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'supervisor');
    }

    public function b2bPackages(): HasMany
    {
        return $this->hasMany(B2BPackage::class);
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class);
    }
}
