<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\ReviewLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLaterSpacedRepetitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_question_for_review_creates_due_card(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $question = $this->createQuestion($teacher);

        $this->actingAs($student)
            ->postJson(route('student.review-later.toggle'), [
                'question_id' => $question->id,
            ])
            ->assertOk()
            ->assertJson(['marked' => true]);

        $card = ReviewLater::where('user_id', $student->id)
            ->where('question_id', $question->id)
            ->firstOrFail();

        $this->assertNotNull($card->next_review_at);
        $this->assertSame(1, $card->interval_days);
        $this->assertSame(0, $card->repetitions);
    }

    public function test_answering_review_card_applies_sm2_schedule(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $question = $this->createQuestion($teacher);
        $card = ReviewLater::create([
            'user_id' => $student->id,
            'question_id' => $question->id,
            'next_review_at' => now()->subMinute(),
        ]);

        $this->actingAs($student)
            ->postJson(route('student.review-later.answer', $card), [
                'quality' => 5,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'interval_days' => 1,
                'repetitions' => 1,
            ]);

        $card->refresh();

        $this->assertSame(5, $card->last_quality);
        $this->assertSame(1, $card->interval_days);
        $this->assertSame(1, $card->repetitions);
        $this->assertTrue($card->next_review_at->isFuture());
    }

    public function test_student_cannot_answer_another_students_review_card(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $question = $this->createQuestion($teacher);
        $card = ReviewLater::create([
            'user_id' => $otherStudent->id,
            'question_id' => $question->id,
            'next_review_at' => now(),
        ]);

        $this->actingAs($student)
            ->postJson(route('student.review-later.answer', $card), [
                'quality' => 4,
            ])
            ->assertForbidden();
    }

    private function createQuestion(User $teacher): Question
    {
        return Question::create([
            'created_by' => $teacher->id,
            'question_type' => 'mcq',
            'question_text' => 'What is 2 + 2?',
            'question_text_ar' => 'ما ناتج 2 + 2؟',
            'options' => [
                ['text' => '3', 'text_ar' => '3', 'is_correct' => false],
                ['text' => '4', 'text_ar' => '4', 'is_correct' => true],
            ],
            'correct_answer' => '1',
            'status' => 'active',
        ]);
    }
}
