<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicBarcodeSubmission extends Model
{
    protected $fillable = [
        'public_barcode_test_id',
        'student_name',
        'school_name',
        'classroom',
        'answers',
        'score',
        'total_points',
        'score_percentage',
        'passed',
        'correct_count',
        'incorrect_count',
        'unanswered_count',
        'review',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'score' => 'decimal:2',
            'total_points' => 'decimal:2',
            'score_percentage' => 'decimal:2',
            'passed' => 'boolean',
            'review' => 'array',
            'metadata' => 'array',
        ];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(PublicBarcodeTest::class, 'public_barcode_test_id');
    }
}
