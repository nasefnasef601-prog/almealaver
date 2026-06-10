<?php

namespace App\Filament\Resources\AccessGrantResource\Pages;

use App\Filament\Resources\AccessGrantResource;
use App\Models\Course;
use Filament\Resources\Pages\CreateRecord;

class CreateAccessGrant extends CreateRecord
{
    protected static string $resource = AccessGrantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $course = isset($data['course_id']) ? Course::with('subject')->find($data['course_id']) : null;
        $courseIds = array_values(array_unique(array_filter([
            isset($data['course_id']) ? (string) $data['course_id'] : null,
            ...array_map('strval', (array) ($data['course_ids'] ?? [])),
        ])));

        return array_merge($data, [
            'course_ids' => $courseIds,
            'content_types' => $data['content_types'] ?? ['courses'],
            'path_ids' => $data['path_ids'] ?? ($course?->subject?->path_id ? [(string) $course->subject->path_id] : []),
            'subject_ids' => $data['subject_ids'] ?? ($course?->subject_id ? [(string) $course->subject_id] : []),
            'source_type' => $data['source_type'] ?? 'admin_manual',
            'source_id' => $data['source_id'] ?? ('filament_access_grant:' . now()->timestamp),
            'idempotency_key' => $data['idempotency_key'] ?? ('filament_access_grant:' . sha1(json_encode([
                $data['user_id'] ?? null,
                $data['course_id'] ?? null,
                $data['package_id'] ?? null,
                now()->format('c'),
            ]))),
            'metadata' => array_merge([
                'source' => 'filament_admin',
                'scope' => 'course',
            ], (array) ($data['metadata'] ?? [])),
            'granted_at' => $data['granted_at'] ?? now(),
        ]);
    }
}
