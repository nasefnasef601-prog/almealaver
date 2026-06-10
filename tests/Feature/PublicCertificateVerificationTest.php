<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCertificateVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_certificate_page_shows_completion_details(): void
    {
        $user = User::create([
            'name' => 'Student Demo',
            'email' => 'student-demo@example.com',
            'password' => 'password',
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Demo Course',
            'title_ar' => 'دورة تجريبية',
            'slug' => 'demo-course',
            'price' => 0,
            'is_free' => true,
            'is_published' => true,
            'status' => 'published',
            'created_by' => $user->id,
            'difficulty_level' => 'all',
            'has_certificate' => true,
        ]);

        $completion = CourseCompletion::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_code' => 'CERT-VERIFY-1234',
            'completed_at' => now(),
        ]);

        $this->get('/certificate/CERT-VERIFY-1234')
            ->assertOk()
            ->assertSee('CERT-VERIFY-1234')
            ->assertSee('دورة تجريبية');

        $this->assertDatabaseHas('course_completions', [
            'id' => $completion->id,
            'certificate_code' => 'CERT-VERIFY-1234',
        ]);
    }
}
