<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DiscountCode;
use App\Models\Path;
use App\Models\PaymentRequest;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountCodePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_code_reduces_payment_request_amount(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        $path = Path::create([
            'name' => 'General',
            'name_ar' => 'عام',
            'slug' => 'discount-general',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'path_id' => $path->id,
            'name' => 'Math',
            'name_ar' => 'رياضيات',
            'slug' => 'discount-math',
            'is_active' => true,
        ]);
        $skill = Skill::create([
            'name' => 'Basics',
            'name_ar' => 'أساسيات',
            'slug' => 'discount-basics',
            'is_active' => true,
        ]);
        $course = Course::create([
            'title' => 'Course',
            'title_ar' => 'دورة خصم',
            'slug' => 'discount-course',
            'price' => 200,
            'is_free' => false,
            'is_published' => true,
            'status' => 'published',
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'created_by' => $admin->id,
        ]);
        $discount = DiscountCode::create([
            'code' => 'SAVE50',
            'label' => 'Half price',
            'type' => 'percentage',
            'value' => 50,
            'status' => 'active',
            'min_amount' => 100,
            'max_redemptions' => 10,
            'course_ids' => [$course->id],
        ]);

        $this->actingAs($student)
            ->post('/student/payment-request', [
                'course_id' => $course->id,
                'amount' => 200,
                'discount_code' => 'save50',
            ])
            ->assertSessionHas('success');

        $payment = PaymentRequest::first();
        $this->assertNotNull($payment);
        $this->assertSame('100.00', (string) $payment->amount);
        $this->assertSame('SAVE50', $payment->metadata['pricing']['code']);
        $this->assertEquals(100.0, $payment->metadata['pricing']['discount_amount']);
        $this->assertSame(1, $discount->fresh()->current_redemptions);
    }
}
