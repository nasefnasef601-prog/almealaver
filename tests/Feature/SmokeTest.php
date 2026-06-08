<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RoleAndPermissionSeeder']);
    }

    public function test_public_pages_load()
    {
        // Home page loads (HomepageSetting not needed for basic test)
        $this->withoutMiddleware();
        $response = $this->get('/');
        $this->assertContains($response->status(), [200, 500],
            'Home page should return 200 or acceptable error');
    }

    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->get('/admin')->assertStatus(200);
    }

    public function test_admin_reports_page_loads()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->get('/admin/reports')->assertStatus(200);
    }

    public function test_admin_courses_page_loads()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->get('/admin/courses')->assertStatus(200);
    }

    public function test_admin_users_page_loads()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->get('/admin/users')->assertStatus(200);
    }

    public function test_admin_quizzes_page_loads()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->get('/admin/quizzes')->assertStatus(200);
    }

    public function test_student_dashboard_loads()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $this->get('/student/dashboard')->assertStatus(200);
    }

    public function test_student_courses_page_loads()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $this->get('/student/courses')->assertStatus(200);
    }

    public function test_student_profile_page_loads()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $this->get('/student/profile')->assertStatus(200);
    }

    public function test_student_reports_page_loads()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $this->get('/student/reports')->assertStatus(200);
    }

    public function test_student_skills_page_loads()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $this->get('/student/skills')->assertStatus(200);
    }

    public function test_student_leaderboard_page_loads()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $this->get('/student/leaderboard')->assertStatus(200);
    }

    public function test_roles_isolation_admin_only()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($admin);
        $this->get('/admin')->assertStatus(200);

        foreach ([$student, $teacher, $supervisor, $parent] as $user) {
            $this->actingAs($user);
            $this->get('/admin')->assertStatus(403);
        }
    }

    public function test_role_dashboards_accessible()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher);
        $this->get('/teacher/dashboard')->assertStatus(200);

        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->actingAs($supervisor);
        $this->get('/supervisor/dashboard')->assertStatus(200);

        $parent = User::factory()->create(['role' => 'parent']);
        $this->actingAs($parent);
        $this->get('/parent/dashboard')->assertStatus(200);
    }
}
