<?php

namespace Tests\Feature;

use App\Models\Path;
use App\Models\PublicBarcodeSubmission;
use App\Models\PublicBarcodeTest;
use App\Models\Question;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBarcodeTestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_barcode_test_can_be_opened_and_submitted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $path = Path::create([
            'name' => 'General',
            'name_ar' => 'عام',
            'slug' => 'general',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'path_id' => $path->id,
            'name' => 'Math',
            'name_ar' => 'رياضيات',
            'slug' => 'math',
            'is_active' => true,
        ]);
        $skill = Skill::create([
            'subject_id' => $subject->id,
            'name' => 'Addition',
            'name_ar' => 'الجمع',
            'slug' => 'addition',
            'is_active' => true,
        ]);
        $question = Question::create([
            'created_by' => $admin->id,
            'question_type' => 'mcq',
            'question_text' => '1 + 1 = ?',
            'question_text_ar' => '١ + ١ = ؟',
            'options' => [
                ['text_ar' => '١', 'is_correct' => false],
                ['text_ar' => '٢', 'is_correct' => true],
            ],
            'correct_answer' => '٢',
            'points' => 1,
            'difficulty' => 'easy',
            'skill_id' => $skill->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $test = PublicBarcodeTest::create([
            'slug' => 'sample-barcode',
            'title' => 'اختبار سريع',
            'path_id' => $path->id,
            'subject_id' => $subject->id,
            'question_ids' => [$question->id],
            'status' => 'active',
            'settings' => [
                'passingScore' => 60,
                'randomizeQuestions' => false,
                'showAnswers' => true,
                'showExplanations' => true,
            ],
        ]);

        $this->get('/barcode-test/'.$test->slug)
            ->assertOk()
            ->assertSee('اختبار سريع')
            ->assertSee('١ + ١ = ؟');

        $this->post('/barcode-test/'.$test->slug, [
            'student_name' => 'طالب تجربة',
            'school_name' => 'مدرسة تجربة',
            'classroom' => '1A',
            'answers' => [
                $question->id => 1,
            ],
        ])
            ->assertOk()
            ->assertSee('نتيجتك');

        $this->assertDatabaseHas('public_barcode_submissions', [
            'public_barcode_test_id' => $test->id,
            'student_name' => 'طالب تجربة',
            'passed' => true,
        ]);
        $this->assertSame(1, PublicBarcodeSubmission::count());
    }
}
