<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\DiscountCode;
use App\Models\PaymentRequest;
use App\Services\DiscountCodeService;
use Illuminate\Http\Request;
use RuntimeException;

class PaymentController extends Controller
{
    public function requestPurchase(Request $request, DiscountCodeService $discounts)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'amount' => 'required|numeric|min:0',
            'discount_code' => 'nullable|string|max:80',
            'bank_transfer_receipt' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $course = Course::with('subject')->findOrFail($request->course_id);

        if ($course->is_free) {
            return back()->with('error', 'هذا الكورس مجاني ولا يحتاج إلى دفع.');
        }

        $user = auth()->user();

        $existing = PaymentRequest::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['pending_manual_review', 'approved'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'لديك طلب سابق لهذا الكورس قيد المراجعة.');
        }

        $existingGrant = $user->accessGrants()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($existingGrant) {
            return back()->with('error', 'لديك وصول بالفعل لهذا الكورس.');
        }

        try {
            $pricing = $discounts->apply($request->input('discount_code'), $course, (float) $course->price);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        $data = [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_method' => 'bank_transfer',
            'amount' => $pricing['final_amount'],
            'currency' => 'SAR',
            'status' => 'pending_manual_review',
            'metadata' => [
                'pricing' => $pricing,
            ],
        ];

        if ($request->hasFile('bank_transfer_receipt')) {
            $data['bank_transfer_receipt'] = $request->file('bank_transfer_receipt')
                ->store('payment-receipts', 'public');
        }

        PaymentRequest::create($data);

        if (!empty($pricing['discount_code_id'])) {
            DiscountCode::whereKey($pricing['discount_code_id'])->increment('current_redemptions');
        }

        return back()->with('success', 'تم إرسال طلب الدفع. سيتم مراجعته من قبل الإدارة.');
    }
}
