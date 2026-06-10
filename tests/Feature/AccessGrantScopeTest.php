<?php

namespace Tests\Feature;

use App\Models\AccessGrant;
use App\Models\Course;
use App\Models\Path;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessGrantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_course_ids_include_json_scoped_grants(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $path = Path::create([
            'name' => 'Test Path',
            'name_ar' => 'مسار تجريبي',
            'slug' => 'test-path',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $subject = Subject::create([
            'path_id' => $path->id,
            'name' => 'Test Subject',
            'name_ar' => 'مادة تجريبية',
            'slug' => 'test-subject',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $course = Course::create([
            'title' => 'Test Course',
            'title_ar' => 'كورس تجريبي',
            'slug' => 'test-course',
            'subject_id' => $subject->id,
            'created_by' => $user->id,
            'assigned_teacher_id' => $user->id,
            'price' => 0,
            'is_free' => false,
            'is_published' => true,
            'status' => 'active',
        ]);

        AccessGrant::create([
            'user_id' => $user->id,
            'course_ids' => [(string) $course->id],
            'content_types' => ['courses'],
            'path_ids' => [(string) $path->id],
            'subject_ids' => [(string) $subject->id],
            'source_type' => 'admin_manual',
            'source_id' => 'test:json-scope',
            'idempotency_key' => 'test:json-scope:' . $user->id . ':' . $course->id,
            'grant_type' => 'admin',
            'status' => 'active',
            'granted_by' => $user->id,
            'granted_at' => now(),
            'starts_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue(AccessGrant::userHasCourseAccess($user->id, $course->id));
        $this->assertContains((string) $course->id, AccessGrant::activeCourseIdsForUser($user->id));
    }
}
