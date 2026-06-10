<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AccessCode;
use App\Models\AccessCodeRedemption;
use App\Models\AccessGrant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccessCodeRedemptionService
{
    public function redeem(string $code, User $user): AccessGrant
    {
        $normalizedCode = strtoupper(trim($code));

        return DB::transaction(function () use ($normalizedCode, $user): AccessGrant {
            $accessCode = AccessCode::query()
                ->with('package')
                ->where('code', $normalizedCode)
                ->lockForUpdate()
                ->first();

            if (!$accessCode || !$accessCode->isRedeemable()) {
                throw new RuntimeException('كود الدخول غير صالح أو انتهت صلاحيته.');
            }

            $package = $accessCode->package;
            if (!$package || !$package->isActive()) {
                throw new RuntimeException('الباقة المرتبطة بالكود غير متاحة حاليا.');
            }

            $existingRedemption = AccessCodeRedemption::query()
                ->where('access_code_id', $accessCode->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingRedemption?->accessGrant) {
                return $existingRedemption->accessGrant;
            }

            $courseIds = array_values(array_filter((array) $package->course_ids));
            $idempotencyKey = 'access_code:'.$accessCode->id.':user:'.$user->id;

            $grant = AccessGrant::updateOrCreate(
                ['idempotency_key' => $idempotencyKey],
                [
                    'user_id' => $user->id,
                    'course_id' => $courseIds[0] ?? null,
                    'course_ids' => $courseIds,
                    'package_id' => (string) $package->id,
                    'content_types' => $package->content_types ?: ['courses'],
                    'path_ids' => $package->path_ids ?: [],
                    'subject_ids' => $package->subject_ids ?: [],
                    'source_type' => 'access_code',
                    'source_id' => 'access_code:'.$accessCode->id,
                    'metadata' => [
                        'access_code' => $accessCode->code,
                        'b2b_package_id' => $package->id,
                        'school_id' => $accessCode->school_id,
                    ],
                    'grant_type' => 'access_code',
                    'status' => 'active',
                    'granted_by' => $package->assigned_teacher_id,
                    'granted_at' => now(),
                    'starts_at' => now(),
                    'expires_at' => $accessCode->expires_at,
                ]
            );

            AccessCodeRedemption::firstOrCreate(
                [
                    'access_code_id' => $accessCode->id,
                    'user_id' => $user->id,
                ],
                [
                    'access_grant_id' => $grant->id,
                    'redeemed_at' => now(),
                    'metadata' => ['source' => 'student_redeem_page'],
                ]
            );

            $accessCode->increment('current_uses');

            if ($accessCode->school_id && !$user->school_id) {
                $user->forceFill(['school_id' => $accessCode->school_id])->save();
            }

            return $grant;
        });
    }
}
