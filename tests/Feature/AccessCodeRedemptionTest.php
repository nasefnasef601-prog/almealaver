<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\AccessGrant;
use App\Models\B2BPackage;
use App\Models\Course;
use App\Models\Path;
use App\Models\School;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessCodeRedemptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_redeem_access_code_into_scoped_access_grant(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        $school = School::create([
            'name' => 'Demo School',
            'name_ar' => 'مدرسة تجربة',
            'code' => 'DEMO-SCHOOL',
            'is_active' => true,
        ]);
        $path = Path::create([
            'name' => 'General',
            'name_ar' => 'عام',
            'slug' => 'access-code-general',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'path_id' => $path->id,
            'name' => 'Math',
            'name_ar' => 'رياضيات',
            'slug' => 'access-code-math',
            'is_active' => true,
        ]);
        $skill = Skill::create([
            'name' => 'Basics',
            'name_ar' => 'أساسيات',
            'slug' => 'access-code-basics',
            'is_active' => true,
        ]);
        $course = Course::create([
            'title' => 'Course',
            'title_ar' => 'دورة تجربة',
            'slug' => 'access-code-course',
            'price' => 100,
            'is_free' => false,
            'is_published' => true,
            'status' => 'published',
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'created_by' => $admin->id,
        ]);
        $package = B2BPackage::create([
            'school_id' => $school->id,
            'name' => 'School Access',
            'course_ids' => [$course->id],
            'content_types' => ['courses'],
            'path_ids' => [$path->id],
            'subject_ids' => [$subject->id],
            'status' => 'active',
        ]);
        $code = AccessCode::create([
            'code' => 'SCHOOL-2026',
            'school_id' => $school->id,
            'b2b_package_id' => $package->id,
            'max_uses' => 5,
            'status' => 'active',
        ]);

        $this->actingAs($student)
            ->post('/student/access-code', ['code' => 'school-2026'])
            ->assertRedirect('/student/dashboard?tab=my-courses');

        $grant = AccessGrant::first();
        $this->assertNotNull($grant);
        $this->assertSame('access_code', $grant->grant_type);
        $this->assertSame('access_code', $grant->source_type);
        $this->assertSame([(string) $course->id], $grant->grantedCourseIds());
        $this->assertDatabaseHas('access_code_redemptions', [
            'access_code_id' => $code->id,
            'user_id' => $student->id,
        ]);
        $this->assertSame(1, $code->fresh()->current_uses);
        $this->assertSame($school->id, $student->fresh()->school_id);
    }
}
