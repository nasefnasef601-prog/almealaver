<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RoleAndPermissionSeeder']);
        Storage::fake('public');
    }

    public function test_student_can_request_purchase()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);

        $this->actingAs($student);

        $response = $this->post(route('student.payment-request'), [
            'course_id' => $course->id,
            'amount' => $course->price,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'pending_manual_review',
        ]);
    }

    public function test_student_can_upload_receipt()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);

        $this->actingAs($student);

        $file = UploadedFile::fake()->image('receipt.jpg', 200, 200);

        $response = $this->post(route('student.payment-request'), [
            'course_id' => $course->id,
            'amount' => $course->price,
            'payment_method' => 'bank_transfer',
            'bank_transfer_receipt' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $payment = PaymentRequest::where('user_id', $student->id)->first();
        $this->assertNotNull($payment->bank_transfer_receipt);
    }

    public function test_payment_request_shows_in_student_payments()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        PaymentRequest::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_method' => 'bank_transfer',
            'status' => 'pending_manual_review',
        ]);

        $this->actingAs($student);
        $this->get(route('student.payments'))->assertStatus(200);
    }

    public function test_student_cannot_see_others_payments()
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        PaymentRequest::create([
            'user_id' => $student2->id,
            'course_id' => $course->id,
            'amount' => 200,
            'payment_method' => 'bank_transfer',
            'status' => 'approved',
        ]);

        $this->actingAs($student1);
        $this->get(route('student.payments'))->assertStatus(200);
    }

    public function test_payment_without_course_fails()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->post(route('student.payment-request'), [
            'amount' => 500,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors('course_id');
    }
}
