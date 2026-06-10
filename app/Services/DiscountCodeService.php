<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\DiscountCode;
use RuntimeException;

class DiscountCodeService
{
    public function apply(?string $code, Course $course, float $amount): array
    {
        $normalized = strtoupper(trim((string) $code));

        if ($normalized === '') {
            return [
                'code' => null,
                'discount_code_id' => null,
                'original_amount' => round($amount, 2),
                'discount_amount' => 0.0,
                'final_amount' => round($amount, 2),
            ];
        }

        $discountCode = DiscountCode::query()
            ->where('code', $normalized)
            ->first();

        if (!$discountCode || !$discountCode->isAvailableFor($course, $amount)) {
            throw new RuntimeException('كود الخصم غير صالح أو غير متاح لهذا الكورس.');
        }

        $discountAmount = $discountCode->discountAmount($amount);

        return [
            'code' => $discountCode->code,
            'discount_code_id' => $discountCode->id,
            'type' => $discountCode->type,
            'value' => (float) $discountCode->value,
            'original_amount' => round($amount, 2),
            'discount_amount' => $discountAmount,
            'final_amount' => round(max(0, $amount - $discountAmount), 2),
        ];
    }
}
